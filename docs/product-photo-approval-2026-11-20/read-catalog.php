<?php
// Chỉ SELECT catalog và tổng tham chiếu ảnh lịch sử. Không boot Laravel hoặc ghi database.
$backend=realpath(__DIR__.'/../../themmoik27be');
require $backend.'/vendor/autoload.php';
$e=Dotenv\Dotenv::parse(file_get_contents($backend.'/.env'));
if(($e['DB_CONNECTION']??'')!=='mysql'||($e['DB_DATABASE']??'')!=='doan_nhom_5'||!in_array($e['DB_HOST']??'',['127.0.0.1','localhost'],true)||!empty($e['DB_URL']))throw new RuntimeException('Unexpected source database');
try{
 $db=new PDO('mysql:host='.$e['DB_HOST'].';port='.($e['DB_PORT']??3306).';dbname=doan_nhom_5;charset=utf8mb4',$e['DB_USERNAME'],$e['DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 $db->exec('START TRANSACTION READ ONLY');
 $variantColumns=array_column($db->query('SHOW COLUMNS FROM chi_tiet_san_phams')->fetchAll(PDO::FETCH_ASSOC),'Field');
 $variantSkuColumns=array_values(array_intersect(['sku','ma_sku','ma_bien_the','ma_san_pham'],$variantColumns));
 $tables=[
  'products'=>'SELECT id,id_danh_muc,ma_san_pham,ten_san_pham,hinh_anh,mo_ta_ngan,trang_thai FROM san_phams ORDER BY id',
  'gallery'=>'SELECT id,id_san_pham,duong_dan_anh,thu_tu FROM anh_san_phams ORDER BY id_san_pham,thu_tu,id',
  'variants'=>'SELECT id,id_san_pham,id_mau_sac,id_kich_thuoc,id_anh_san_pham,trang_thai'.($variantSkuColumns?','.implode(',',$variantSkuColumns):'').' FROM chi_tiet_san_phams ORDER BY id_san_pham,id',
  'colors'=>'SELECT id,ten_mau,ma_mau,trang_thai FROM mau_sacs ORDER BY id',
  'sizes'=>'SELECT id,ten_kich_thuoc,trang_thai FROM kich_thuocs ORDER BY id',
  'categories'=>'SELECT id,ten_danh_muc,trang_thai FROM danh_mucs ORDER BY id',
  'order_image_references'=>'SELECT hinh_anh,COUNT(*) AS reference_count FROM chi_tiet_don_hangs WHERE hinh_anh IS NOT NULL GROUP BY hinh_anh ORDER BY hinh_anh',
 ];
 foreach($tables as $key=>$sql)$data[$key]=$db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
 $db->rollBack();
 $snapshot=['captured_at'=>gmdate('c'),'variant_sku_columns'=>$variantSkuColumns,'data'=>$data];
 if(in_array('--verify',$argv,true)){
  $prior=json_decode(file_get_contents(__DIR__.'/catalog-snapshot.json'),true,512,JSON_THROW_ON_ERROR);
  $same=hash_equals(hash('sha256',json_encode($prior['data'])),hash('sha256',json_encode($data)));
  file_put_contents(__DIR__.'/source-unchanged.json',json_encode(['checked_at'=>gmdate('c'),'catalog_and_order_image_references_unchanged'=>$same],JSON_PRETTY_PRINT));
  echo $same?"Catalog unchanged.\n":"Catalog changed externally; review snapshot freshness.\n";exit($same?0:1);
 }
 file_put_contents(__DIR__.'/catalog-snapshot.json',json_encode($snapshot,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
 echo json_encode(['products'=>count($data['products']),'gallery'=>count($data['gallery']),'variants'=>count($data['variants']),'variant_sku_columns'=>$variantSkuColumns]).PHP_EOL;
}catch(Throwable $error){if(isset($db)&&$db->inTransaction())$db->rollBack();fwrite(STDERR,'Read-only export failed: '.get_class($error).PHP_EOL);exit(1);}
