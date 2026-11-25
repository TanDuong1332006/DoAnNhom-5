<?php

// N5-147: chỉ tạo database UAT ngẫu nhiên mới, không migrate/seed/reset DB hiện hữu.
use App\Models\{KhachHang, User};
use App\Services\PasswordResetOtp;
use Illuminate\Support\Facades\{Artisan, Cache, DB, Hash, Mail, Schema};
use Illuminate\Support\Carbon;

require __DIR__.'/../../vendor/autoload.php';
if (PHP_SAPI !== 'cli' || is_file(__DIR__.'/../../bootstrap/cache/config.php')) {
    throw new RuntimeException('Security UAT requires CLI without cached config.');
}
$app = require __DIR__.'/../../bootstrap/app.php';
$database = null;
$app->afterBootstrapping(Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) use (&$database) {
$mysql = config('database.connections.mysql');
if (!app()->environment('local') || config('database.default') !== 'mysql'
    || !in_array($mysql['host'], ['localhost', '127.0.0.1', '::1'], true)
    || !empty($mysql['url']) || !empty($mysql['unix_socket'])) {
    throw new RuntimeException('Refusing unverified non-local MySQL configuration.');
}
$database = 'sofep_security_uat_'.date('Ymd').'_'.bin2hex(random_bytes(6));
if (!preg_match('/^sofep_security_uat_[0-9]{8}_[a-f0-9]{12}$/D', $database) || $database === $mysql['database']) {
    throw new RuntimeException('Unsafe UAT name.');
}
// Kết nối server không chọn database nghiệp vụ; CREATE không IF EXISTS để tránh dùng lại DB.
$server = new PDO('mysql:host='.$mysql['host'].';port='.$mysql['port'].';charset=utf8mb4', $mysql['username'], $mysql['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$server->exec('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$server = null;
config([
    'database.connections.security_uat' => array_merge($mysql, ['database' => $database, 'url' => null]),
    'database.default' => 'security_uat', 'app.env' => 'testing', 'cache.default' => 'database',
    'cache.stores.database.connection' => 'security_uat', 'cache.stores.database.lock_connection' => 'security_uat',
    'cache.stores.database.table' => 'cache', 'cache.stores.database.lock_table' => 'cache_locks',
    'cache.prefix' => $database, 'session.driver' => 'array', 'mail.default' => 'array',
    'queue.default' => 'sync', 'logging.default' => 'null', 'broadcasting.default' => 'null', 'hashing.bcrypt.rounds' => 4,
]);
$app->instance('env', 'testing');
});
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$limiter = app(Illuminate\Cache\RateLimiter::class);
$cacheProperty = new ReflectionProperty($limiter, 'cache');
$limiterStore = $cacheProperty->getValue($limiter)->getStore();
if (!$limiterStore instanceof Illuminate\Cache\DatabaseStore
    || $limiterStore->getConnection()->getDatabaseName() !== $database
    || $limiterStore->getLockConnection()->getDatabaseName() !== $database) {
    throw new RuntimeException('Refusing limiter cache outside generated UAT.');
}
if (DB::connection()->getDatabaseName() !== $database || DB::selectOne('SELECT DATABASE() AS name')->name !== $database) {
    throw new RuntimeException('UAT connection mismatch.');
}
$checks = [];
$output = __DIR__.'/../../../docs/N5-147/mysql-uat.json';
$result = ['checked_at' => date(DATE_ATOM), 'database' => $database, 'new_database_only' => true,
    'main_or_barber_selected' => false, 'external_mail_sent' => false, 'limiter_cache_isolation_verified' => true, 'passed' => false];
$assert = function (bool $ok, string $label) use (&$checks) {
    $checks[] = ['case' => $label, 'passed' => $ok];
    if (!$ok) throw new RuntimeException('Security UAT assertion failed: '.$label);
};
$api = function (string $label, string $path, array $data, int $expected, ?string $token = null, string $method = 'POST') use ($app, &$checks) {
    $app['auth']->forgetGuards();
    $request = Illuminate\Http\Request::create('/api/'.$path, $method, $data, [], [], ['HTTP_ACCEPT' => 'application/json', 'REMOTE_ADDR' => '127.0.0.147']);
    if ($token) $request->headers->set('Authorization', 'Bearer '.$token);
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request); $kernel->terminate($request, $response);
    $ok = $response->getStatusCode() === $expected;
    $checks[] = ['case' => $label, 'expected' => $expected, 'actual' => $response->getStatusCode(),
        'retry_after' => $response->headers->get('Retry-After'), 'passed' => $ok];
    if (!$ok) throw new RuntimeException('Security UAT HTTP failed: '.$label.' actual '.$response->getStatusCode());
    return json_decode($response->getContent(), true);
};
try {
    $code = Artisan::call('migrate', ['--database' => 'security_uat', '--force' => true]);
    $assert($code === 0, 'real_migrations_from_empty_mysql');
    $result['migration_count'] = DB::table('migrations')->count();
    $result['mysql_version'] = DB::selectOne('SELECT VERSION() AS version')->version;
    $assert(Schema::hasColumn('khach_hangs', 'role') && Schema::hasTable('private_feedback') && Schema::hasTable('saved_vouchers'), 'security_and_engagement_schema_present');
    $assert(Artisan::call('migrate', ['--database' => 'security_uat', '--force' => true]) === 0, 'second_migrate_has_no_pending_work');
    Mail::fake();
    $password = bin2hex(random_bytes(16)); // Chỉ trong bộ nhớ, không lưu hoặc xuất secrets.
    $email = 'customer-147@sofep.example.test';
    $api('registration_created', 'dang-ky', ['ho_va_ten' => 'Security UAT', 'email' => $email,
        'so_dien_thoai' => '0900000000', 'mat_khau' => $password, 'xac_nhan_mk' => $password], 201);
    $customer = KhachHang::where('email', $email)->firstOrFail();
    $assert($customer->role === 'customer', 'role_baseline_works_without_fixture_schema_patch');
    $api('unverified_login_forbidden', 'dang-nhap', ['email' => $email, 'mat_khau' => $password], 403);
    $customer->update(['otp_hash' => Hash::make('123456'), 'otp_expires_at' => now()->addMinutes(10)]);
    $api('registration_wrong_otp', 'xac-thuc-otp', ['email' => $email, 'otp' => '000000'], 422);
    $api('registration_correct_otp', 'xac-thuc-otp', ['email' => $email, 'otp' => '123456'], 200);
    $api('registration_replay_rejected', 'xac-thuc-otp', ['email' => $email, 'otp' => '123456'], 422);
    $customerLogin = $api('customer_login', 'dang-nhap', ['email' => $email, 'mat_khau' => $password], 200);
    $customer->createToken('second-uat-device');
    $admin = User::create(['name' => 'Admin UAT', 'email' => 'admin-147@sofep.example.test', 'password' => Hash::make($password)]);
    $adminLogin = $api('admin_login', 'admin-login', ['email' => $admin->email, 'mat_khau' => $password], 200);
    $api('admin_allowed', 'admin/danh-muc/get-data', [], 200, $adminLogin['token'], 'GET');
    $api('customer_admin_forbidden', 'admin/danh-muc/get-data', [], 403, $customerLogin['token'], 'GET');
    $api('guest_checkout_unauthorized', 'client/dat-hang', [], 401);
    $api('invalid_token_unauthorized', 'kiem-tra-dang-nhap', [], 401, 'invalid-147', 'GET');
    $api('send_reset_otp', 'forgot-password/send-otp', ['email' => $email], 200);
    $otp = (string) Mail::sent(App\Mail\SendOtpMail::class)->last()->otp;
    $api('resend_cooldown_429', 'forgot-password/send-otp', ['email' => $email], 429);
    $record = Cache::get(app(PasswordResetOtp::class)->key($email));
    $assert(Hash::check($otp, $record['hash']) && $otp !== $record['hash'], 'reset_cache_contains_hash_not_plaintext');
    $api('reset_wrong_otp', 'forgot-password/verify-otp', ['email' => $email, 'otp' => '000000'], 400);
    $api('reset_verify_correct', 'forgot-password/verify-otp', ['email' => $email, 'otp' => $otp], 200);
    $newPassword = bin2hex(random_bytes(16));
    $reset = ['email' => $email, 'otp' => $otp, 'password' => $newPassword, 'password_confirmation' => $newPassword];
    $api('reset_success', 'forgot-password/reset-password', $reset, 200);
    $api('old_customer_token_401', 'kiem-tra-dang-nhap', [], 401, $customerLogin['token'], 'GET');
    $assert($customer->tokens()->count() === 0, 'all_customer_devices_revoked');
    $api('admin_token_preserved', 'admin/danh-muc/get-data', [], 200, $adminLogin['token'], 'GET');
    $api('reset_replay_rejected', 'forgot-password/reset-password', $reset, 400);
    $api('old_password_rejected', 'dang-nhap', ['email' => $email, 'mat_khau' => $password], 401);
    $newLogin = $api('new_password_accepted', 'dang-nhap', ['email' => $email, 'mat_khau' => $newPassword], 200);
    $customer->tokens()->update(['expires_at' => now()->subMinute()]);
    $api('expired_token_401', 'kiem-tra-dang-nhap', [], 401, $newLogin['token'], 'GET');
    // Bốn lần verify/reset trước; lần thứ năm vẫn được xử lý, lần thứ sáu bị chặn.
    $api('fifth_otp_attempt_invalid', 'forgot-password/verify-otp', ['email' => $email, 'otp' => '000000'], 400);
    $api('shared_verify_reset_budget_429', 'forgot-password/verify-otp', ['email' => $email, 'otp' => '000000'], 429);
    Carbon::setTestNow(now()->addSeconds(61));
    $api('resend_after_cooldown', 'forgot-password/send-otp', ['email' => $email], 200);
    $api('resend_does_not_clear_guess_budget', 'forgot-password/reset-password', $reset, 429);
    Carbon::setTestNow(now()->addMinutes(6));
    $expiredOtp = (string) Mail::sent(App\Mail\SendOtpMail::class)->last()->otp;
    $api('expired_reset_otp_rejected', 'forgot-password/reset-password', array_merge($reset, ['otp' => $expiredOtp]), 400);
    $aliases = ['dang-nhap', 'admin-login', 'auth/admin-login'];
    for ($i = 0; $i < 10; $i++) $api('wrong_login_'.$i, $aliases[$i % 3], ['email' => $admin->email, 'mat_khau' => 'wrong-147-password'], 401);
    $api('shared_login_aliases_429', 'dang-nhap', ['email' => $admin->email, 'mat_khau' => $password], 429);
    $assert(DB::selectOne('SELECT DATABASE() AS name')->name === $database, 'all_checks_stayed_on_generated_uat');
    $result['passed'] = true;
} catch (Throwable $error) {
    // Không ghi raw exception/SQL/credentials ra artifact.
    $result['failure_type'] = get_class($error);
    throw $error;
} finally {
    Carbon::setTestNow();
    $result['checks'] = $checks;
    $result['database_retained'] = true;
    if (!is_dir(dirname($output))) mkdir(dirname($output), 0700, true);
    file_put_contents($output, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['passed' => $result['passed'], 'database' => $database, 'checks' => count($checks), 'report' => 'docs/N5-147/mysql-uat.json']).PHP_EOL;
}
