<?php
// Local demo maintenance only. No schema, credentials or account mutations.
use App\Http\Controllers\Admin\ProductImageController;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/../../../themmoik27be/vendor/autoload.php';
$app = require __DIR__.'/../../../themmoik27be/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connection = config('database.connections.'.config('database.default'));
if (!app()->environment('local') || config('database.default') !== 'mysql'
    || !in_array($connection['host'], ['127.0.0.1', 'localhost', '::1'], true)
    || !empty($connection['url']) || $connection['database'] !== 'doan_nhom_5'
    || DB::selectOne('SELECT DATABASE() AS db')->db !== 'doan_nhom_5') {
    throw new RuntimeException('Refusing non-local-demo database.');
}
$before = collect(json_decode(file_get_contents(__DIR__.'/before.json'), true, 512, JSON_THROW_ON_ERROR))->keyBy('id');
$apply = in_array('--apply', $argv, true);
$controller = app(ProductImageController::class);
$results = [];
foreach (glob(__DIR__.'/assets/*.json') as $metadataPath) {
    $meta = json_decode(ltrim(file_get_contents($metadataPath), "\xEF\xBB\xBF"), true, 512, JSON_THROW_ON_ERROR);
    if (($meta['review'] ?? null) !== 'approved') continue;
    $product = SanPham::with('anhSanPhams')->findOrFail($meta['id']);
    if ($product->ma_san_pham !== $meta['sku'] || !$before->has($product->id)) throw new RuntimeException('SKU mismatch.');
    if ((int) $product->trang_thai !== 1) continue;
    $path = __DIR__.'/assets/'.$product->id.'.jpg';
    $image = getimagesize($path);
    if (!$image || $image[0] > 6000 || $image[1] > 6000 || filesize($path) > 2048 * 1024) throw new RuntimeException('Invalid/oversize asset '.$product->id);
    $hash = hash_file('sha256', $path);
    if (($meta['sha256'] ?? null) !== $hash) throw new RuntimeException('Asset manifest hash mismatch '.$product->id);
    $existing = $product->anhSanPhams->first(function ($photo) use ($product, $hash) {
        $filename = basename(parse_url($photo->duong_dan_anh, PHP_URL_PATH));
        $stored = Storage::disk('public')->path('products/'.$product->id.'/'.$filename);
        return is_file($stored) && hash_file('sha256', $stored) === $hash;
    });
    if (!$apply) { $results[] = ['id' => $product->id, 'sku' => $meta['sku'], 'action' => $existing ? 'reuse' : 'upload']; continue; }
    // Durable per-SKU backup before calling the official upload/arrange mechanism.
    $backup = __DIR__.'/backup-'.$product->id.'.json';
    if (!is_file($backup)) file_put_contents($backup, $product->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    if (!$existing) {
        $request = Request::create('http://127.0.0.1:8000/api/admin/san-pham/'.$product->id.'/images', 'POST', ['image_kind' => 'illustration'], [], ['images' => [new UploadedFile($path, $product->id.'.jpg', 'image/jpeg', null, true)]]);
        $controller->upload($request, $product->id);
        $existing = $product->anhSanPhams()->orderByDesc('id')->firstOrFail();
    }
    $ids = $product->anhSanPhams()->orderBy('thu_tu')->orderBy('id')->pluck('id')->all();
    $previousOrder = $ids;
    $ids = array_values(array_diff($ids, [$existing->id]));
    array_unshift($ids, $existing->id);
    if ($previousOrder !== $ids || $product->fresh()->hinh_anh !== $existing->duong_dan_anh) {
        $controller->arrange(Request::create('http://127.0.0.1:8000/api/admin/san-pham/'.$product->id.'/images/order', 'POST', ['ids' => $ids, 'main_id' => $existing->id]), $product->id);
    }
    $entry = ['id' => $product->id, 'sku' => $meta['sku'], 'sha256' => $hash, 'main_id' => $existing->id, 'url' => $existing->duong_dan_anh, 'at' => gmdate('c')];
    file_put_contents(__DIR__.'/published-'.$product->id.'.json', json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    $results[] = $entry;
}
file_put_contents(__DIR__.'/after.json', SanPham::with(['danhMuc', 'chiTietSanPhams.mauSac', 'chiTietSanPhams.kichThuoc', 'anhSanPhams'])->orderBy('id')->get()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
echo json_encode(['mode' => $apply ? 'apply' : 'dry-run', 'processed' => count($results), 'results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
