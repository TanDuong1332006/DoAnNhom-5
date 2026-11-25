<?php
// Chỉ SELECT số lượng; không boot app chính, không đọc/nộp nội dung đánh giá/góp ý.
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$e=Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if(($e['DB_DATABASE']??'')!=='doan_nhom_5'||!in_array($e['DB_HOST']??'',['127.0.0.1','localhost'],true)||!empty($e['DB_URL']))throw new RuntimeException('Unexpected database');
$p=new PDO('mysql:host='.$e['DB_HOST'].';port='.($e['DB_PORT']??3306).';dbname=doan_nhom_5;charset=utf8mb4',$e['DB_USERNAME'],$e['DB_PASSWORD']??'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);$p->exec('START TRANSACTION READ ONLY');
$result=['database'=>'doan_nhom_5','review_rows'=>(int)$p->query('SELECT COUNT(*) FROM danh_gia_san_phams')->fetchColumn(),'approved_review_rows'=>(int)$p->query('SELECT COUNT(*) FROM danh_gia_san_phams WHERE trang_thai=1')->fetchColumn(),'scope'=>'Only aggregate SELECT; no customer/review/feedback contents exported'];$p->rollBack();file_put_contents(__DIR__.'/review-counts.json',json_encode($result,JSON_PRETTY_PRINT));echo json_encode($result);
