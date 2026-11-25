<?php
require __DIR__.'/boot.php';
if(PHP_SAPI!=='cli'||count($argv)!==3)throw new RuntimeException('Usage: php import.php CLEAN_BACKEND NEW_RUNTIME_DIRECTORY');
$appRoot=realpath($argv[1]);$runtime=$argv[2];
if(!$appRoot||!is_file($appRoot.'/artisan')||file_exists($runtime))throw new RuntimeException('Requires clean Laravel source and NEW runtime directory');
require_once $appRoot.'/vendor/autoload.php';
$manifest=json_decode(file_get_contents(__DIR__.'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
if(hash_file('sha256',__DIR__.'/catalog.json')!==$manifest['catalog_sha256'])throw new RuntimeException('Catalog checksum mismatch');
$catalog=json_decode(file_get_contents(__DIR__.'/catalog.json'),true,512,JSON_THROW_ON_ERROR);
$allowed=['danh_mucs','thuong_hieus','mau_sacs','kich_thuocs','san_phams','anh_san_phams','chi_tiet_san_phams'];
if(array_keys($catalog)!==$allowed)throw new RuntimeException('Unexpected catalog tables');
foreach($manifest['images'] as $image){if(!preg_match('~^products/[0-9]+/illustration-[a-f0-9-]{36}\.jpg$~D',$image['path']))throw new RuntimeException('Invalid image path');if(hash_file('sha256',__DIR__.'/media/'.$image['path'])!==$image['sha256'])throw new RuntimeException('Image checksum mismatch');}
if(hash_file('sha256',__DIR__.'/product-placeholder.svg')!==$manifest['placeholder']['sha256'])throw new RuntimeException('Placeholder checksum mismatch');
$host=getenv('SOFEP_DEMO_DB_HOST')?:'127.0.0.1';if(!in_array($host,['127.0.0.1','localhost','::1'],true))throw new RuntimeException('Local MySQL only');
$database='sofep_image_restore_'.gmdate('Ymd').'_'.bin2hex(random_bytes(6));
$db=new PDO('mysql:host='.$host.';port='.(getenv('SOFEP_DEMO_DB_PORT')?:3306).';charset=utf8mb4',getenv('SOFEP_DEMO_DB_USERNAME')?:'root',getenv('SOFEP_DEMO_DB_PASSWORD')?:'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$db=null;
mkdir($runtime,0700,true);$runtime=realpath($runtime);
$session=['purpose'=>'SOFEP isolated image restore','database'=>$database,'runtime'=>$runtime,'app_root'=>$appRoot,'package'=>__DIR__];
file_put_contents($runtime.'/session.json',json_encode($session,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
$app=demoBoot($session);
if(Illuminate\Support\Facades\Artisan::call('migrate',['--database'=>'demo','--force'=>true])!==0)throw new RuntimeException('Clean-source migration failed');
Illuminate\Support\Facades\DB::transaction(function()use($catalog,$allowed){foreach($allowed as $table)foreach(array_chunk($catalog[$table],100) as $rows)Illuminate\Support\Facades\DB::table($table)->insert($rows);});
foreach($manifest['images'] as $image){$target=$runtime.'/storage/app/public/'.$image['path'];if(!is_dir(dirname($target)))mkdir(dirname($target),0700,true);if(!copy(__DIR__.'/media/'.$image['path'],$target)||hash_file('sha256',$target)!==$image['sha256'])throw new RuntimeException('Copy failed');}
$counts=[];foreach($allowed as $table)$counts[$table]=Illuminate\Support\Facades\DB::table($table)->count();
foreach(['khach_hangs','don_hangs','chi_tiet_don_hangs','personal_access_tokens','users'] as $table)if(Illuminate\Support\Facades\DB::table($table)->count()!==0)throw new RuntimeException('Unexpected account/order/token data');
file_put_contents($runtime.'/import-result.json',json_encode(['database'=>$database,'counts'=>$counts,'restored_images'=>count($manifest['images']),'customer_order_token_user_rows'=>0,'migrations'=>Illuminate\Support\Facades\DB::table('migrations')->count(),'fixture_variant'=>$manifest['fixture_variant']],JSON_PRETTY_PRINT));
echo json_encode(['database'=>$database,'runtime'=>$runtime,'restored_images'=>count($manifest['images'])]).PHP_EOL;
