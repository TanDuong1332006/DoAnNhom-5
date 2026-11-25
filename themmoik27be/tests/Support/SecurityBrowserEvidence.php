<?php

require __DIR__.'/SecurityBrowserBoot.php';
$session = json_decode(file_get_contents(trim(file_get_contents(sys_get_temp_dir().'/sofep-n5147-browser-session-path.txt'))), true);
$app = securityBrowserBoot($session);
$cache = Illuminate\Support\Facades\Cache::store();
$cache->put('isolation-witness', 'UAT-only-probe', 300);
$limiter = app(Illuminate\Cache\RateLimiter::class);
$limiter->hit('isolation-limiter-witness', 300);
$rows = Illuminate\Support\Facades\DB::table('cache')->where('key', 'like', $session['cache_prefix'].'%')->get();
$owned = Illuminate\Support\Facades\DB::table('cache')->where('key', $session['cache_prefix'].'isolation-witness')->exists();
$role = new ReflectionProperty($limiter, 'cache'); $store = $role->getValue($limiter)->getStore();
$orders = App\Models\DonHang::whereHas('khachHang', fn ($q) => $q->where('email', $session['customer']))
    ->get(['id','phuong_thuc_thanh_toan','trang_thai_thanh_toan','tong_thanh_toan']);
$evidence = ['checked_at_utc' => gmdate(DATE_ATOM), 'database' => $session['database'],
    'configured_connections' => array_keys(config('database.connections')), 'default_database' => config('database.default'),
    'cache_database' => $cache->getStore()->getConnection()->getDatabaseName(),
    'limiter_database' => $store->getConnection()->getDatabaseName(), 'lock_database' => $store->getLockConnection()->getDatabaseName(),
    'cache_prefix' => $session['cache_prefix'], 'cache_rows_in_prefix' => $rows->count(),
    'default_cache_witness_found_in_uat' => $owned,
    'limiter_witness_found_in_uat' => Illuminate\Support\Facades\DB::table('cache')->where('key', $session['cache_prefix'].'isolation-limiter-witness')->exists(),
    'mail_transport' => config('mail.default'), 'mailers_available' => array_keys(config('mail.mailers')),
    'orders' => $orders->toArray(), 'fixture_variant_stock' => App\Models\ChiTietSanPham::findOrFail($session['variant_id'])->so_luong_ton,
    'mobile_fixture_activated' => (bool) App\Models\KhachHang::where('email', $session['mobile'])->value('is_kich_hoat'),
    'passed' => $owned && $store->getConnection()->getDatabaseName() === $session['database']];
file_put_contents(__DIR__.'/../../../docs/N5-147/browser-isolation.json', json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['passed' => $evidence['passed'], 'orders' => count($evidence['orders']), 'stock' => $evidence['fixture_variant_stock'], 'isolated_cache_rows' => $rows->count()]).PHP_EOL;
