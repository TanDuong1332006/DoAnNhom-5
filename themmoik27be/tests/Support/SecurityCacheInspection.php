<?php

// Chỉ đọc cache chính bằng PDO read-only. Không gọi Cache::get (có thể xóa dòng hết hạn).
require __DIR__.'/../../vendor/autoload.php';
$original = [];
$app = require __DIR__.'/../../bootstrap/app.php';
if (is_file(__DIR__.'/../../bootstrap/cache/config.php')) throw new RuntimeException('Refusing cached config.');
$app->afterBootstrapping(Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) use (&$original) {
    $original = ['mysql' => config('database.connections.mysql'), 'key' => config('app.key'),
        'prefix' => config('cache.stores.database.prefix', config('cache.prefix')),
        'table' => config('cache.stores.database.table', 'cache'), 'locks' => config('cache.stores.database.lock_table') ?: 'cache_locks'];
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
        'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'mail.default' => 'array', 'logging.default' => 'null']);
});
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$c = $original['mysql'];
if ($c['database'] !== 'doan_nhom_5' || !in_array($c['host'], ['127.0.0.1', 'localhost', '::1'], true) || !empty($c['url'])) {
    throw new RuntimeException('Unexpected source for read-only inspection.');
}
foreach (['table', 'locks'] as $field) if (!preg_match('/^[a-z0-9_]+$/iD', $original[$field])) throw new RuntimeException('Unsafe table.');
$pdo = new PDO('mysql:host='.$c['host'].';port='.$c['port'].';dbname='.$c['database'].';charset=utf8mb4', $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('SET SESSION TRANSACTION READ ONLY');
$pdo->exec('START TRANSACTION READ ONLY');
$hash = fn ($v) => hash_hmac('sha256', $v, $original['key']);
$email = 'customer-147@sofep.example.test'; $admin = 'admin-147@sofep.example.test'; $ip = '127.0.0.147';
$groups = [
    'auth-register' => ['email:'.$hash($email), 'ip:'.$hash($ip)],
    'auth-register-otp' => ['email:'.$hash($email), 'ip:'.$hash($ip)],
    'auth-login' => ['email:'.$hash($email), 'email:'.$hash($admin), 'ip:'.$hash($ip)],
    'auth-reset-send' => ['cooldown:'.$hash($email), 'hour:'.$hash($email), 'ip:'.$hash($ip)],
    'auth-reset-otp' => ['email:'.$hash($email), 'ip:'.$hash($ip)],
];
$keys = [];
foreach ($groups as $limiter => $buckets) foreach ($buckets as $bucket) {
    $k = $original['prefix'].md5($limiter.$bucket);
    $keys[$k] = ['limiter' => $limiter, 'scope' => str_starts_with($bucket, 'ip:') ? 'fixture-ip' : 'fixture-account', 'kind' => 'counter'];
    $keys[$k.':timer'] = ['limiter' => $limiter, 'scope' => $keys[$k]['scope'], 'kind' => 'timer'];
}
$resetKey = $original['prefix'].'sofep:reset:'.$hash($email);
$keys[$resetKey] = ['limiter' => 'PasswordResetOtp', 'scope' => 'fixture-account', 'kind' => 'otp-record'];
$keys[$original['prefix'].'otp_reset_'.$email] = ['limiter' => 'legacy-otp', 'scope' => 'fixture-account', 'kind' => 'legacy-record'];
$st = $pdo->prepare('SELECT `key`, `value`, expiration FROM `'.$original['table'].'` WHERE `key` IN ('.implode(',', array_fill(0, count($keys), '?')).') ORDER BY `key`');
$st->execute(array_keys($keys)); $rows = $st->fetchAll(PDO::FETCH_ASSOC); $now = time();
$safe = [];
foreach ($keys as $key => $meta) {
    $match = array_values(array_filter($rows, fn ($r) => $r['key'] === $key)); $r = $match[0] ?? null;
    $item = $meta + ['key_sha256' => hash('sha256', $key), 'present' => $r !== null];
    if ($r) {
        $item += ['expiration_utc' => gmdate(DATE_ATOM, $r['expiration']), 'active' => (int) $r['expiration'] > $now];
        if ($meta['kind'] === 'counter') { $v = @unserialize($r['value'], ['allowed_classes' => false]); $item['counter'] = is_numeric($v) ? (int) $v : null; }
    }
    $safe[] = $item;
}
$lock = $pdo->prepare('SELECT `key`, expiration FROM `'.$original['locks'].'` WHERE `key` = ?');
$lock->execute([$resetKey.':lock']); $locks = $lock->fetchAll(PDO::FETCH_ASSOC);
$uatPrefixOnMain = null;
$pointer = sys_get_temp_dir().'/sofep-n5147-browser-session-path.txt';
if (is_file($pointer)) {
    $session = json_decode(file_get_contents(trim(file_get_contents($pointer))), true);
    if (($session['purpose'] ?? '') === 'SOFEP N5-147 browser UAT') {
        $query = $pdo->prepare('SELECT COUNT(*) FROM `'.$original['table'].'` WHERE LEFT(`key`, ?) = ?');
        $query->execute([strlen($session['cache_prefix']), $session['cache_prefix']]);
        $uatPrefixOnMain = (int) $query->fetchColumn();
    }
}
$pdo->commit();
$result = ['checked_at_utc' => gmdate(DATE_ATOM), 'mode' => 'PDO START TRANSACTION READ ONLY; SELECT only; no Cache API',
    'database' => 'doan_nhom_5', 'expected_runtime_keys' => count($keys), 'rows_found' => count($rows),
    'active_rows' => count(array_filter($safe, fn ($r) => $r['active'] ?? false)),
    'matched_rows_sha256' => hash('sha256', json_encode($rows)), 'keys' => $safe,
    'fixture_lock_present' => count($locks) > 0, 'fixture_lock_active' => count(array_filter($locks, fn ($r) => (int)$r['expiration'] > $now)) > 0,
    'current_browser_uat_prefix_rows_on_main' => $uatPrefixOnMain,
    'main_writes' => 0, 'real_login_requests_sent_to_main' => 0,
    'impact' => 'Keys derive only from .example.test fixtures and 127.0.0.147; real account identities not inspected. Expired keys are not effective limiter blocks. Main HTTP auth was not called because it would write counters.'];
$label = $argv[1] ?? 'before';
if (!in_array($label, ['before', 'after'], true)) throw new RuntimeException('Invalid snapshot label.');
file_put_contents(__DIR__.'/../../../docs/N5-147/cache-main-'.$label.'.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['snapshot' => $label, 'expected_keys' => count($keys), 'found' => count($rows), 'active' => $result['active_rows'], 'main_writes' => 0]).PHP_EOL;
