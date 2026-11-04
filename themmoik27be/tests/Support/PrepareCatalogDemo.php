<?php

// Chỉ sao chép catalog vào database UAT mới; không sao chép khách/đơn/lịch sử thật.
require __DIR__.'/MySqlUat.php';
use Illuminate\Support\Facades\{DB, Schema, Hash};
use Illuminate\Database\Schema\Blueprint;

uatBoot();
$originalDatabase = config('database.connections.mysql.database');
$originalRoot = config('filesystems.disks.public.root');
config(['database.connections.catalog_source' => config('database.connections.mysql')]);
$source = DB::connection('catalog_source');
$products = $source->table('san_phams')->where('trang_thai', 1)->orderBy('id')->get();
if ($products->isEmpty() || $products->contains(fn ($product) => $product->id_cua_hang !== null) || $products->contains(fn ($product) => in_array($product->id, [170, 171]))) throw new RuntimeException('Catalog demo refuses empty catalog or Seller-owned products.');
$ids = $products->pluck('id');
$tables = [];
foreach (['danh_mucs', 'thuong_hieus', 'mau_sacs', 'kich_thuocs'] as $table) $tables[$table] = $source->table($table)->get();
$tables['san_phams'] = $products;
foreach (['chi_tiet_san_phams', 'anh_san_phams'] as $table) $tables[$table] = $source->table($table)->whereIn('id_san_pham', $ids)->get();

$database = 'sofep_uat_'.date('Ymd').'_'.bin2hex(random_bytes(6));
if ($database === $originalDatabase) throw new RuntimeException('Unsafe database target.');
DB::statement('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
config(['database.connections.mysql.database' => $database]); DB::purge('mysql');
foreach (glob(__DIR__.'/../../database/migrations/*.php') as $path) (require $path)->up();
if (!Schema::hasColumn('khach_hangs', 'role')) Schema::table('khach_hangs', fn (Blueprint $table) => $table->string('role')->default('customer'));
$targetRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.$database.DIRECTORY_SEPARATOR.'uploads';
$copied = [];
$copyImage = function (?string $url, bool $required = false) use ($originalRoot, $targetRoot, &$copied): ?string {
    if (!$url || !preg_match('~^(?:http://(?:127\.0\.0\.1|localhost):8000)?/api/product-images/(\d+)/([a-zA-Z0-9-]+\.(?:jpg|jpeg|png|webp))$~D', $url, $match)) {
        if ($required) throw new RuntimeException('A primary image is not a verified local raster.');
        return $url;
    }
    $relative = 'products/'.$match[1].'/'.$match[2];
    $origin = $originalRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($origin)) { if ($required) throw new RuntimeException('Primary raster file missing.'); return $url; }
    $target = $targetRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!isset($copied[$relative])) {
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0700, true);
        if (!copy($origin, $target) || hash_file('sha256', $origin) !== hash_file('sha256', $target)) throw new RuntimeException('Raster copy verification failed.');
        $copied[$relative] = true;
    }
    return 'http://127.0.0.1:8012/api/product-images/'.$match[1].'/'.$match[2];
};

DB::transaction(function () use ($tables, $copyImage) {
    foreach ($tables as $table => $rows) {
        $columns = array_flip(Schema::getColumnListing($table));
        foreach ($rows as $row) {
            $data = array_intersect_key((array) $row, $columns);
            if ($table === 'san_phams') $data['hinh_anh'] = $copyImage($data['hinh_anh'], true);
            if ($table === 'anh_san_phams') $data['duong_dan_anh'] = $copyImage($data['duong_dan_anh']);
            DB::table($table)->insert($data);
        }
    }
});

$password = bin2hex(random_bytes(10));
App\Models\User::create(['name' => 'Admin Demo SOFEP', 'email' => 'uat-admin@sofep.example.test', 'password' => Hash::make($password)]);
foreach (['customer', 'seller'] as $role) App\Models\KhachHang::create(['ho_va_ten' => 'Khách demo '.$role, 'email' => 'uat-'.$role.'@sofep.example.test', 'mat_khau' => Hash::make($password), 'role' => $role, 'is_khoa' => 0, 'is_kich_hoat' => 1]);
foreach ([['DEMO10', 'all', null, null], ['NEW15', 'new', 1, null], ['VIP20', 'selected', 1, [1]]] as [$code, $audience, $limit, $customers]) {
    App\Models\MaGiamGia::create(['ma_code' => $code, 'ten_giam_gia' => 'Ưu đãi demo '.$code, 'loai_giam' => 'phan_tram', 'gia_tri' => $code === 'DEMO10' ? 10 : ($code === 'NEW15' ? 15 : 20), 'giam_toi_da' => 100000, 'so_luong' => 100, 'doi_tuong' => $audience, 'gioi_han_moi_khach' => $limit, 'ids_khach_hang' => $customers, 'ngay_bat_dau' => date('Y-m-d'), 'ngay_ket_thuc' => date('Y-m-d', strtotime('+7 days'))]);
}
$directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.$database;
if (!is_dir($directory)) mkdir($directory, 0700, true);
file_put_contents($directory.'/session.json', json_encode(['purpose' => 'SOFEP isolated MySQL UAT', 'database' => $database, 'password' => $password, 'catalog_products' => $products->count(), 'api_port' => 8012, 'frontend_port' => 5175], JSON_THROW_ON_ERROR));
$summary = ['database' => $database, 'session' => $directory.'/session.json', 'catalog_products' => $products->count(), 'copied_rasters' => count($copied), 'real_customers_copied' => 0, 'real_orders_copied' => 0, 'seller_products_copied' => 0];
file_put_contents(__DIR__.'/../../../docs/sofep-catalog-demo.json', json_encode($summary, JSON_PRETTY_PRINT));
echo json_encode($summary, JSON_UNESCAPED_UNICODE).PHP_EOL;
