<?php

// Router chỉ dành cho PHP development server trên loopback, không dùng production.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}
$directory = realpath(getenv('SOFEP_UAT_DIR') ?: '');
$manifest = $directory ? $directory.DIRECTORY_SEPARATOR.'session.json' : '';
if (!$directory || !str_starts_with(basename($directory), 'sofep-uat-') || !is_file($manifest)
    || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Disposable UAT fixture required.');
}
$session = json_decode(file_get_contents($manifest), true, flags: JSON_THROW_ON_ERROR);
$database = realpath($directory.DIRECTORY_SEPARATOR.'uat.sqlite');
if (($session['purpose'] ?? '') !== 'SOFEP disposable browser fixture' || !$database
    || realpath($session['database'] ?? '') !== $database || is_file(__DIR__.'/../../bootstrap/cache/config.php')) {
    http_response_code(403);
    exit('Unsafe UAT configuration.');
}
foreach (['APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $database,
    'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'MAIL_MAILER' => 'array',
    'QUEUE_CONNECTION' => 'sync', 'LOG_CHANNEL' => 'null'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/__uat/status') {
    header('Content-Type: application/json');
    echo json_encode(['purpose' => $session['purpose'], 'database' => basename($database), 'driver' => 'sqlite',
        'fixture' => hash_file('sha256', $manifest)]);
    exit;
}
if ($path === '/__uat/image.svg') {
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="500"><rect width="400" height="500" fill="#f5ecd8"/><path d="M100 130 160 90h80l60 40-30 80-30-15v200h-80V195l-30 15Z" fill="#202020"/><text x="200" y="450" text-anchor="middle" font-size="22">SOFEP UAT 3A</text></svg>';
    exit;
}
if (!str_starts_with($path, '/api/') || str_contains($path, 'giao-dich') || str_contains($path, 'thanh-toan')
    || $path === '/api/client/dat-hang') {
    http_response_code(404);
    exit;
}
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->handleRequest(\Illuminate\Http\Request::capture());
