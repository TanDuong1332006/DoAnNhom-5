<?php

// CLI prepare chỉ ghi fixture trên UAT có sẵn; cli-server chỉ loopback, không SMTP thật.
require __DIR__.'/SecurityBrowserBoot.php';
$pointer = sys_get_temp_dir().'/sofep-n5147-browser-session-path.txt';
if (PHP_SAPI === 'cli' && ($argv[1] ?? '') === 'prepare') {
    $result = json_decode(file_get_contents(__DIR__.'/../../../docs/N5-147/mysql-uat.json'), true);
    if (!($result['passed'] ?? false)) throw new RuntimeException('Requires successful security UAT.');
    $suffix = bin2hex(random_bytes(4)); $root = sys_get_temp_dir().'/sofep-n5147-browser-'.$suffix;
    mkdir($root, 0700); mkdir($root.'/private', 0700); mkdir($root.'/public', 0700);
    $session = ['purpose' => 'SOFEP N5-147 browser UAT', 'database' => $result['database'], 'root' => $root,
        'cache_prefix' => 'n5147-browser-'.$suffix.':', 'admin' => 'browser-admin-'.$suffix.'@sofep.example.test',
        'customer' => 'browser-customer-'.$suffix.'@sofep.example.test', 'mobile' => 'browser-mobile-'.$suffix.'@sofep.example.test'];
    $app = securityBrowserBoot($session);
    $password = 'Uat-browser-147-demo!'; // Credential fixture local, không dùng cho tài khoản thật.
    App\Models\User::create(['name' => 'Browser UAT Admin', 'email' => $session['admin'], 'password' => Illuminate\Support\Facades\Hash::make($password)]);
    $category = Illuminate\Support\Facades\DB::table('danh_mucs')->insertGetId(['ten_danh_muc' => 'Browser UAT', 'slug_danh_muc' => 'browser-uat-'.$suffix, 'trang_thai' => 1]);
    $product = App\Models\SanPham::create(['id_danh_muc' => $category, 'ma_san_pham' => 'UAT147-'.$suffix,
        'ten_san_pham' => 'Áo thử nghiệm bảo mật UAT', 'slug_san_pham' => 'ao-uat-147-'.$suffix,
        'gia_ban' => 250000, 'so_luong_ton' => 20, 'trang_thai' => 1,
        'hinh_anh' => 'http://127.0.0.1:8027/__uat/product.svg', 'mo_ta_ngan' => 'Fixture UAT, không bán thật.']);
    $variant = App\Models\ChiTietSanPham::create(['id_san_pham' => $product->id, 'so_luong_ton' => 20, 'gia_chenh_lech' => 0, 'trang_thai' => 1]);
    $session += ['product_id' => $product->id, 'variant_id' => $variant->id, 'slug' => $product->slug_san_pham];
    file_put_contents($root.'/session.json', json_encode($session, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents($pointer, $root.'/session.json');
    echo json_encode(['session' => $root.'/session.json', 'database' => $session['database'], 'admin' => $session['admin'],
        'customer' => $session['customer'], 'mobile' => $session['mobile'], 'slug' => $session['slug']]).PHP_EOL;
    exit;
}
if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true) || !is_file($pointer)) { http_response_code(403); exit; }
$session = json_decode(file_get_contents(trim(file_get_contents($pointer))), true);
$app = securityBrowserBoot($session);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/__uat/status') {
    header('Content-Type: application/json');
    echo json_encode(['purpose' => $session['purpose'], 'database' => $session['database'], 'cache_prefix' => $session['cache_prefix'],
        'cache_connection' => 'security_browser', 'lock_connection' => 'security_browser', 'mail' => 'array MIME spool; external disabled']); exit;
}
if ($path === '/__uat/product.svg') {
    header('Content-Type: image/svg+xml'); echo '<svg xmlns="http://www.w3.org/2000/svg" width="480" height="640"><rect width="480" height="640" fill="#eee5d7"/><text x="50" y="300" font-size="32">SOFEP UAT 147</text></svg>'; exit;
}
$request = Illuminate\Http\Request::capture(); $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request); $response->send(); $kernel->terminate($request, $response);
file_put_contents($session['root'].'/requests.jsonl', json_encode(['at' => gmdate(DATE_ATOM), 'method' => $request->method(),
    'path' => $request->path(), 'status' => $response->getStatusCode(), 'retry_after' => $response->headers->get('Retry-After')]).PHP_EOL, FILE_APPEND | LOCK_EX);
