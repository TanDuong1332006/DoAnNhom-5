<?php
// Chạy riêng sau read-catalog.php. Không tạo bảng hoặc ghi vào database nguồn.
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$env = Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if (($env['DB_CONNECTION'] ?? '') !== 'mysql' || !in_array($env['DB_HOST'] ?? '', ['127.0.0.1', 'localhost']) || !empty($env['DB_URL'])) exit(1);
try {
    $db = new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT'] ?? '3306').';charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $name = 'sofep_image_uat_'.gmdate('Ymd_His').'_'.bin2hex(random_bytes(3));
    $db->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4');
    $db->exec('USE `'.$name.'`');
    if ($db->query('SELECT DATABASE()')->fetchColumn() !== $name || !str_starts_with($name, 'sofep_image_uat_')) exit(1);
    // Fixture catalog tối thiểu cho kiểm thử ảnh, không phải schema đầy đủ Laravel.
    $db->exec('CREATE TABLE san_phams (id BIGINT PRIMARY KEY, ten_san_pham TEXT, ma_san_pham TEXT, hinh_anh TEXT, trang_thai INT)');
    $db->exec('CREATE TABLE anh_san_phams (id BIGINT PRIMARY KEY, id_san_pham BIGINT, duong_dan_anh TEXT, thu_tu INT)');
    $db->exec('CREATE TABLE chi_tiet_san_phams (id BIGINT PRIMARY KEY, id_san_pham BIGINT, id_anh_san_pham BIGINT NULL)');
    $data = json_decode(file_get_contents(__DIR__.'/catalog.json'), true, 512, JSON_THROW_ON_ERROR);
    foreach (['products' => ['san_phams', ['id','ten_san_pham','ma_san_pham','hinh_anh','trang_thai']], 'gallery' => ['anh_san_phams', ['id','id_san_pham','duong_dan_anh','thu_tu']], 'variants' => ['chi_tiet_san_phams', ['id','id_san_pham','id_anh_san_pham']]] as $key => [$table, $columns]) {
        $insert = $db->prepare('INSERT INTO '.$table.' ('.implode(',', $columns).') VALUES ('.implode(',', array_fill(0, count($columns), '?')).')');
        foreach ($data[$key] as $row) $insert->execute(array_map(fn ($column) => $row[$column], $columns));
        $counts[$table] = (int)$db->query('SELECT COUNT(*) FROM '.$table)->fetchColumn();
    }
    file_put_contents(__DIR__.'/demo-database.json', json_encode(['database' => $name, 'counts' => $counts, 'customer_tables' => 0, 'order_tables' => 0, 'scope' => 'image catalog fixture only; not full Laravel schema'], JSON_PRETTY_PRINT));
    echo "Isolated MySQL image fixture ready; no source writes.\n";
} catch (Throwable $e) { fwrite(STDERR, "Demo setup failed; details suppressed.\n"); exit(1); }
