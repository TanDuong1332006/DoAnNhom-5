<?php
// Export cột catalog theo allowlist, không SELECT thông tin khách/đơn/token.
$backend = realpath(__DIR__.'/../../themmoik27be');
require $backend.'/vendor/autoload.php';
$env = Dotenv\Dotenv::parse(file_get_contents($backend.'/.env'));
if (($env['DB_CONNECTION'] ?? '') !== 'mysql' || !in_array($env['DB_HOST'] ?? '', ['localhost','127.0.0.1']) || ($env['DB_DATABASE'] ?? '') !== 'doan_nhom_5' || !empty($env['DB_URL'])) throw new RuntimeException('Refuse unexpected source');
try {
 $db = new PDO('mysql:host='.$env['DB_HOST'].';port='.($env['DB_PORT']??3306).';dbname=doan_nhom_5;charset=utf8mb4',$env['DB_USERNAME'],$env['DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 $db->exec('START TRANSACTION READ ONLY');
 $columns = [
 'danh_mucs'=>'id,ten_danh_muc,slug_danh_muc,mo_ta,trang_thai',
 'thuong_hieus'=>'id,ten_thuong_hieu,slug_thuong_hieu,trang_thai',
 'mau_sacs'=>'id,ten_mau,ma_mau,trang_thai',
 'kich_thuocs'=>'id,ten_kich_thuoc,mo_ta,trang_thai',
 'san_phams'=>'id,id_danh_muc,id_thuong_hieu,ma_san_pham,ten_san_pham,slug_san_pham,gia_ban,gia_khuyen_mai,hinh_anh,mo_ta_ngan,mo_ta_chi_tiet,so_luong_ton,is_noi_bat,is_flash_sale,trang_thai',
 'anh_san_phams'=>'id,id_san_pham,duong_dan_anh,thu_tu',
 'chi_tiet_san_phams'=>'id,id_san_pham,id_mau_sac,id_kich_thuoc,gia_chenh_lech,so_luong_ton,trang_thai,id_anh_san_pham'];
 foreach($columns as $table=>$fields) $catalog[$table]=$db->query('SELECT '.$fields.' FROM '.$table.' ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
 $orders=$db->query('SELECT hinh_anh, COUNT(*) AS reference_count FROM chi_tiet_don_hangs WHERE hinh_anh IS NOT NULL GROUP BY hinh_anh ORDER BY hinh_anh')->fetchAll(PDO::FETCH_ASSOC);
 $db->rollBack();
 $capture=['catalog'=>$catalog,'order_image_references'=>$orders];
 if(in_array('--verify',$argv,true)){
  $before=json_decode(file_get_contents(__DIR__.'/source-fingerprint.json'),true);
  $after=hash('sha256',json_encode($capture));$same=hash_equals($before['sha256'],$after);
  file_put_contents(__DIR__.'/source-unchanged.json',json_encode(['checked_at'=>gmdate('c'),'catalog_and_order_image_reference_fingerprint_unchanged'=>$same,'before'=>$before['sha256'],'after'=>$after],JSON_PRETTY_PRINT));
  echo $same?"Source catalog and order image references unchanged.\n":"Source changed since capture; investigate before concluding.\n";exit($same?0:1);
 }
 file_put_contents(__DIR__.'/source-catalog.json',json_encode($capture,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
 file_put_contents(__DIR__.'/source-fingerprint.json',json_encode(['sha256'=>hash('sha256',json_encode($capture)),'captured_at'=>gmdate('c'),'catalog_tables'=>array_map('count',$catalog),'order_image_urls'=>count($orders)],JSON_PRETTY_PRINT));
 echo "Read-only catalog captured.\n";
} catch(Throwable $e){if(isset($db)&&$db->inTransaction())$db->rollBack();fwrite(STDERR,"Catalog export failed: ".get_class($e)."\n");exit(1);}
