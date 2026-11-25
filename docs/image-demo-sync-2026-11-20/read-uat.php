<?php
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$e=Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
$u=json_decode(file_get_contents(__DIR__.'/uat.json'),true);
if(!in_array($e['DB_HOST']??'',['127.0.0.1','localhost'],true)||!preg_match('/^sofep_image_restore_[0-9]{8}_[a-f0-9]{12}$/D',$u['database']))throw new RuntimeException('UAT only');
$db=new PDO('mysql:host='.$e['DB_HOST'].';port='.($e['DB_PORT']??3306).';dbname='.$u['database'].';charset=utf8mb4',$e['DB_USERNAME'],$e['DB_PASSWORD']??'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec('START TRANSACTION READ ONLY');$out=[];
foreach(['san_phams','anh_san_phams','chi_tiet_san_phams'] as $t)$out[$t]=$db->query('SELECT * FROM '.$t.' ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach(['khach_hangs','don_hangs','chi_tiet_don_hangs','personal_access_tokens','users'] as $t)$out['sensitive_counts'][$t]=(int)$db->query('SELECT COUNT(*) FROM '.$t)->fetchColumn();
$db->rollBack();file_put_contents(__DIR__.'/uat-catalog.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo "UAT catalog exported read-only.\n";
