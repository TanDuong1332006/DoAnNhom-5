<?php

// UAT dùng database mới có tên ngẫu nhiên. Không đọc/ghi dữ liệu nghiệp vụ hiện tại.
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;

function uatBoot(?string $database = null)
{
    require_once __DIR__.'/../../vendor/autoload.php';
    if (is_file(__DIR__.'/../../bootstrap/cache/config.php')) throw new RuntimeException('UAT refuses cached application configuration.');
    $app = require __DIR__.'/../../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $mysql = config('database.connections.mysql');
    if (!app()->environment('local') || config('database.default') !== 'mysql' || !in_array($mysql['host'], ['localhost', '127.0.0.1', '::1'], true) || !empty($mysql['url'])) throw new RuntimeException('UAT requires verified local loopback MySQL without DB_URL.');
    if ($database !== null) {
        if (!preg_match('/^sofep_uat_[0-9]{8}_[a-f0-9]{12}$/D', $database) || $database === $mysql['database']) throw new RuntimeException('Unsafe UAT database name.');
        config(['database.connections.mysql.database' => $database]);
        DB::purge('mysql');
    }
    config(['app.env' => 'testing', 'cache.default' => 'array', 'session.driver' => 'array', 'mail.default' => 'array', 'queue.default' => 'sync', 'logging.default' => 'null']);
    config(['cors.allowed_origins' => ['http://127.0.0.1:5174', 'http://127.0.0.1:5175', 'http://127.0.0.1:5180', 'http://127.0.0.1:5181']]);
    if ($database !== null) config(['filesystems.disks.public.root' => sys_get_temp_dir().DIRECTORY_SEPARATOR.$database.DIRECTORY_SEPARATOR.'uploads']);
    $app->instance('env', 'testing');
    return $app;
}

function uatPayload(int $product = 1, int $quantity = 1, ?string $voucher = null): array
{
    return ['ho_ten' => 'Khách UAT', 'so_dien_thoai' => '0900000000', 'dia_chi_giao' => 'Địa chỉ demo UAT',
        'phuong_thuc_thanh_toan' => 'MB_BANK', 'tong_thanh_toan' => 1, 'ma_giam_gia_su_dung' => $voucher,
        'items' => [['id' => $product, 'id_chi_tiet_san_pham' => $product, 'quantity' => $quantity, 'gia_ban' => 1]]];
}

function uatRequest($app, string $uri, array $payload, string $method = 'POST', ?string $key = null, ?string $token = null)
{
    $app['auth']->forgetGuards();
    $request = Illuminate\Http\Request::create($uri, $method, $payload, [], [], ['HTTP_ACCEPT' => 'application/json', ...($key ? ['HTTP_IDEMPOTENCY_KEY' => $key] : [])]);
    if ($token) $request->headers->set('Authorization', 'Bearer '.$token);
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    return $response;
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $mode = $argv[1] ?? '';
    if ($mode === 'prepare') {
        $app = uatBoot();
        $database = 'sofep_uat_'.date('Ymd').'_'.bin2hex(random_bytes(6));
        DB::statement('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        config(['database.connections.mysql.database' => $database]); DB::purge('mysql');
        foreach (glob(__DIR__.'/../../database/migrations/*.php') as $path) (require $path)->up();
        // Source hiện có dùng role nhưng migration lịch sử chưa khai báo cột này.
        if (!Schema::hasColumn('khach_hangs', 'role')) Schema::table('khach_hangs', fn (Blueprint $table) => $table->string('role')->default('customer'));
        DB::table('danh_mucs')->insert(['id' => 1, 'ten_danh_muc' => 'Danh mục UAT', 'slug_danh_muc' => 'uat', 'trang_thai' => 1]);
        DB::table('mau_sacs')->insert(['id' => 1, 'ten_mau' => 'Đen', 'ma_mau' => '#000000', 'trang_thai' => 1]);
        DB::table('kich_thuocs')->insert(['id' => 1, 'ten_kich_thuoc' => 'M', 'trang_thai' => 1]);
        foreach (range(1, 6) as $id) {
            DB::table('san_phams')->insert(['id' => $id, 'id_danh_muc' => 1, 'ma_san_pham' => 'UAT-'.$id, 'ten_san_pham' => 'Áo demo UAT '.$id,
                'slug_san_pham' => 'ao-demo-uat-'.$id, 'gia_ban' => 250000, 'gia_khuyen_mai' => 200000,
                'hinh_anh' => 'http://127.0.0.1:8011/__uat/illustration-00000000-0000-4000-8000-000000000001.jpg', 'mo_ta_ngan' => 'Sản phẩm thử nghiệm cô lập, không bán thật.', 'is_noi_bat' => 1, 'trang_thai' => 1]);
            DB::table('chi_tiet_san_phams')->insert(['id' => $id, 'id_san_pham' => $id, 'id_mau_sac' => 1, 'id_kich_thuoc' => 1, 'gia_chenh_lech' => 5000, 'so_luong_ton' => $id === 1 ? 1 : 20, 'trang_thai' => 1]);
        }
        foreach (['LAST' => 1, 'DEMO10' => 100, 'CANCEL' => 1] as $code => $quantity) App\Models\MaGiamGia::create(['ma_code' => $code, 'ten_giam_gia' => 'Voucher demo '.$code, 'loai_giam' => 'phan_tram', 'gia_tri' => 10, 'so_luong' => $quantity, 'ngay_bat_dau' => date('Y-m-d'), 'ngay_ket_thuc' => date('Y-m-d', strtotime('+7 days'))]);
        $password = bin2hex(random_bytes(10));
        App\Models\User::create(['name' => 'Admin UAT', 'email' => 'uat-admin@sofep.example.test', 'password' => Hash::make($password)]);
        foreach (['customer', 'seller'] as $role) App\Models\KhachHang::create(['ho_va_ten' => 'UAT '.$role, 'email' => 'uat-'.$role.'@sofep.example.test', 'mat_khau' => Hash::make($password), 'role' => $role, 'is_khoa' => 0, 'is_kich_hoat' => 1]);
        App\Models\MaGiamGia::create(['ma_code' => 'ONCE', 'ten_giam_gia' => 'Mỗi khách một lượt', 'loai_giam' => 'co_dinh', 'gia_tri' => 10000, 'so_luong' => 100, 'doi_tuong' => 'selected', 'ids_khach_hang' => [1], 'gioi_han_moi_khach' => 1]);
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.$database;
        mkdir($directory, 0700);
        file_put_contents($directory.'/session.json', json_encode(['purpose' => 'SOFEP isolated MySQL UAT', 'database' => $database, 'password' => $password], JSON_THROW_ON_ERROR));
        echo json_encode(['database' => $database, 'session' => $directory.'/session.json', 'migrations' => 'applied only to newly created UAT database']).PHP_EOL;
    } elseif ($mode === 'worker') {
        $app = uatBoot($argv[2] ?? '');
        $token = !empty($argv[6]) ? App\Models\KhachHang::findOrFail((int) $argv[6])->createToken('concurrent-uat')->plainTextToken : null;
        $response = uatRequest($app, '/api/client/dat-hang', uatPayload((int) $argv[3], 1, ($argv[4] ?? '') ?: null), 'POST', ($argv[5] ?? '') ?: null, $token);
        if ($token) Laravel\Sanctum\PersonalAccessToken::findToken($token)?->delete();
        $json = json_decode($response->getContent(), true);
        echo json_encode(['status' => $response->getStatusCode(), 'order' => $json['don_hang']['id'] ?? null, 'total' => $json['don_hang']['tong_thanh_toan'] ?? null, 'message' => $json['message'] ?? null]);
    } elseif ($mode === 'api-audit') {
        $app = uatBoot($argv[2] ?? '');
        $token = App\Models\User::firstOrFail()->createToken('api-audit')->plainTextToken;
        $checks = [];
        foreach (app('router')->getRoutes() as $route) {
            if (!str_starts_with($route->uri(), 'api/admin/') || !in_array('GET', $route->methods()) || str_contains($route->uri(), '{')) continue;
            $response = uatRequest($app, '/'.$route->uri(), [], 'GET', null, $token);
            $checks[] = ['path' => '/'.$route->uri(), 'status' => $response->getStatusCode()];
        }
        Laravel\Sanctum\PersonalAccessToken::findToken($token)?->delete();
        $result = ['database' => $argv[2], 'checked_at' => date(DATE_ATOM), 'checks' => $checks, 'passed' => count($checks) > 0 && collect($checks)->every(fn ($check) => $check['status'] === 200)];
        file_put_contents(__DIR__.'/../../../docs/sofep-admin-api-audit.json', json_encode($result, JSON_PRETTY_PRINT));
        echo json_encode($result); if (!$result['passed']) exit(1);
    } elseif ($mode === 'browser') {
        uatBoot($argv[2] ?? '');
        DB::table('san_phams')->update(['hinh_anh' => 'http://127.0.0.1:8011/__uat/illustration-00000000-0000-4000-8000-000000000001.jpg']);
        echo json_encode(['fixture_images' => 'updated only in isolated UAT']);
    } elseif ($mode === 'inspect') {
        uatBoot($argv[2] ?? '');
        echo json_encode(['orders' => DB::table('don_hangs')->count(), 'stock' => DB::table('chi_tiet_san_phams')->orderBy('id')->pluck('so_luong_ton', 'id'), 'coupons' => DB::table('ma_giam_gias')->pluck('so_luong_da_dung', 'ma_code')]);
    } elseif ($mode === 'lifecycle') {
        $app = uatBoot($argv[2] ?? '');
        $response = uatRequest($app, '/api/client/dat-hang', uatPayload(6, 2, 'CANCEL'));
        if ($response->getStatusCode() !== 201) throw new RuntimeException('UAT checkout failed: '.$response->getContent());
        $data = json_decode($response->getContent(), true); $id = $data['don_hang']['id'];
        app(App\Services\OrderLifecycle::class)->update($id, 'da_huy');
        app(App\Services\OrderLifecycle::class)->update($id, 'da_huy');
        if (DB::table('chi_tiet_san_phams')->where('id', 6)->value('so_luong_ton') != 20 || DB::table('ma_giam_gias')->where('ma_code', 'CANCEL')->value('so_luong_da_dung') != 0) throw new RuntimeException('Cancellation did not restore exactly once.');
        $response = uatRequest($app, '/api/client/dat-hang', uatPayload(6, 1, 'CANCEL'));
        $data = json_decode($response->getContent(), true);
        $payment = ['ma_hoa_don' => $data['don_hang']['ma_don_hang'], 'payment_token' => $data['payment_token']];
        if (uatRequest($app, '/api/giao-dich/mo-phong', $payment)->getStatusCode() !== 200) throw new RuntimeException('Simulation failed.');
        if (uatRequest($app, '/api/giao-dich/mo-phong', $payment)->getStatusCode() !== 200) throw new RuntimeException('Repeated simulation failed.');
        echo json_encode(['cancellation_restored_once' => true, 'voucher_reusable_after_cancel' => $response->getStatusCode() === 201, 'demo_payment_repeatable' => true]);
    } else { throw new RuntimeException('Use prepare, worker, inspect or lifecycle.'); }
}
