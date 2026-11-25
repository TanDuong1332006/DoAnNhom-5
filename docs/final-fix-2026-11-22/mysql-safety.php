<?php
$loc=json_decode(file_get_contents(__DIR__.'/uat-location.json'),true);$be=$loc['root'].'/themmoik27be';require $be.'/vendor/autoload.php';$app=require $be.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();$p=Illuminate\Support\Facades\DB::connection()->getPdo();$db=$p->query('SELECT DATABASE()')->fetchColumn();$installed=json_decode(file_get_contents($loc['root'].'/install-result.json'),true);if($db!==$installed['database']||!str_starts_with($db,'sofep_demo_'))throw new RuntimeException('UAT only');
$mode=$argv[1]??'capture';
if($mode==='race'){
    $category=(int)($argv[2]??0);if(!$category)throw new RuntimeException('Category required');
    $p->beginTransaction();
    $q=$p->prepare('INSERT INTO san_phams (id_danh_muc,ma_san_pham,ten_san_pham,slug_san_pham,gia_ban,trang_thai) VALUES (?, ?, ?, ?, 100,0)');$key='race-'.bin2hex(random_bytes(6));$q->execute([$category,$key,'UAT race only',$key]);$id=(int)$p->lastInsertId();
    file_put_contents(__DIR__.'/race-ready.json',json_encode(['id'=>$id,'category'=>$category]));
    // Hai kết nối MySQL: giữ shared FK lock trong lúc HTTP thử khóa parent để xóa.
    usleep(1500000);$p->commit();echo json_encode(['committed'=>true,'id'=>$id]);exit;
}
$p->exec('START TRANSACTION READ ONLY');$hashes=[];foreach(['danh_mucs','san_phams','anh_san_phams','chi_tiet_san_phams','don_hangs','chi_tiet_don_hangs','danh_gia_san_phams'] as $table){$rows=$p->query('SELECT * FROM '.$table.' ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);$hashes[$table]=['count'=>count($rows),'sha256'=>hash('sha256',json_encode($rows))];}$p->rollBack();
if($mode==='capture')file_put_contents(__DIR__.'/uat-before-rejection.json',json_encode($hashes,JSON_PRETTY_PRINT));else {if($hashes!==json_decode(file_get_contents(__DIR__.'/uat-before-rejection.json'),true))throw new RuntimeException('Data changed after rejected operations');file_put_contents(__DIR__.'/uat-rejection-preservation.json',json_encode(['passed'=>true,'tables'=>$hashes],JSON_PRETTY_PRINT));}echo json_encode(['passed'=>true,'mode'=>$mode]);
