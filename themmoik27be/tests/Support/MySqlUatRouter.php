<?php

if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(403); exit; }
$manifest = getenv('SOFEP_MYSQL_UAT_SESSION');
$session = $manifest && is_file($manifest) ? json_decode(file_get_contents($manifest), true) : null;
if (($session['purpose'] ?? '') !== 'SOFEP isolated MySQL UAT') { http_response_code(403); exit; }
require __DIR__.'/MySqlUat.php';
$app = uatBoot($session['database']);
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/__uat/status') {
    header('Content-Type: application/json'); echo json_encode(['purpose' => $session['purpose'], 'database' => $session['database'], 'driver' => 'mysql']); exit;
}
if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/__uat/illustration-00000000-0000-4000-8000-000000000001.jpg') {
    header('Content-Type: image/jpeg'); readfile(__DIR__.'/../../../docs/product-content/full-catalog/assets/1.jpg'); exit;
}
$app->handleRequest(Illuminate\Http\Request::capture());
