<?php
// Chỉ SELECT dữ liệu catalog và số tham chiếu ảnh đơn hàng; không boot Laravel.
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$env = Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if (($env['DB_CONNECTION'] ?? '') !== 'mysql' || !in_array($env['DB_HOST'] ?? '', ['127.0.0.1', 'localhost']) || ($env['DB_DATABASE'] ?? '') !== 'doan_nhom_5' || !empty($env['DB_URL'])) {
    fwrite(STDERR, "Refused: requires expected local MySQL.\n"); exit(1);
}
try {
    $db = new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT'] ?? '3306').';dbname='.$env['DB_DATABASE'].';charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec('START TRANSACTION READ ONLY');
    $result = ['captured_at' => gmdate('c'), 'products' => $db->query('SELECT id, ten_san_pham, ma_san_pham, hinh_anh, trang_thai FROM san_phams')->fetchAll(PDO::FETCH_ASSOC),
        'gallery' => $db->query('SELECT id, id_san_pham, duong_dan_anh, thu_tu FROM anh_san_phams')->fetchAll(PDO::FETCH_ASSOC),
        'variants' => $db->query('SELECT id, id_san_pham, id_anh_san_pham FROM chi_tiet_san_phams')->fetchAll(PDO::FETCH_ASSOC),
        'order_image_references' => $db->query('SELECT hinh_anh, COUNT(*) AS reference_count FROM chi_tiet_don_hangs WHERE hinh_anh IS NOT NULL GROUP BY hinh_anh')->fetchAll(PDO::FETCH_ASSOC)];
    $db->rollBack();
    file_put_contents(__DIR__.'/catalog.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    echo 'Read-only catalog exported; no customer/order records exported.'.PHP_EOL;
} catch (Throwable $e) { if (isset($db) && $db->inTransaction()) $db->rollBack(); fwrite(STDERR, 'Catalog read failed: '.get_class($e).PHP_EOL); exit(1); }
