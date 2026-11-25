<?php
// Chạy chỉ trong bản giải nén độc lập. Không đọc .env hoặc database máy PO.
if(PHP_SAPI!=='cli')throw new RuntimeException('CLI only');
$root=dirname(__DIR__);$be=$root.'/themmoik27be';$fe=$root.'/Fe';
if(!is_file($root.'/SOURCE_MANIFEST.json')||!is_file($be.'/vendor/autoload.php'))throw new RuntimeException('Extract handoff ZIP and install Composer dependencies first');
if(is_file($be.'/.env')||is_file($fe.'/.env')||is_file($be.'/bootstrap/cache/config.php'))throw new RuntimeException('Requires fresh extracted copy without .env/config cache; refuse overwrite');
require $be.'/vendor/autoload.php';
$host=getenv('SOFEP_DEMO_DB_HOST')?:'127.0.0.1';$port=getenv('SOFEP_DEMO_DB_PORT')?:'3306';$user=getenv('SOFEP_DEMO_DB_USERNAME')?:'root';$pass=getenv('SOFEP_DEMO_DB_PASSWORD')?:'';
if(!in_array($host,['127.0.0.1','localhost','::1'],true)||!ctype_digit($port))throw new RuntimeException('Local MySQL only');
$bePort=(int)($argv[1]??8000);$fePort=(int)($argv[2]??5173);foreach([$bePort,$fePort] as $p)if($p<1024||$p>65535)throw new RuntimeException('Invalid port');
$manifest=json_decode(file_get_contents($root.'/demo/manifest.json'),true,512,JSON_THROW_ON_ERROR);$catalogFile=$root.'/demo/catalog.json';
if(hash_file('sha256',$catalogFile)!==$manifest['catalog_sha256'])throw new RuntimeException('Catalog checksum mismatch');
$catalog=json_decode(file_get_contents($catalogFile),true,512,JSON_THROW_ON_ERROR);$tables=['danh_mucs','thuong_hieus','mau_sacs','kich_thuocs','san_phams','anh_san_phams','chi_tiet_san_phams'];
if(array_keys($catalog)!==$tables||count($catalog['san_phams'])!==168||count($manifest['images'])!==167)throw new RuntimeException('Invalid catalog allowlist/count');
foreach($manifest['images'] as $i){if(!preg_match('~^products/[0-9]+/illustration-[a-f0-9-]{36}\.jpg$~D',$i['path'])||hash_file('sha256',$root.'/demo/media/'.$i['path'])!==$i['sha256'])throw new RuntimeException('Image checksum mismatch');}
if(hash_file('sha256',$root.'/demo/product-placeholder.svg')!==$manifest['placeholder']['sha256'])throw new RuntimeException('Placeholder checksum mismatch');
$name='sofep_demo_'.gmdate('Ymd').'_'.bin2hex(random_bytes(6));
$pdo=new PDO('mysql:host='.$host.';port='.$port.';charset=utf8mb4',$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);$pdo->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$pdo=null;
function qenv($s){if(preg_match('/[\r\n\x00]/',$s))throw new RuntimeException('Invalid environment value');return '"'.strtr($s,['\\'=>'\\\\','"'=>'\\"','$'=>'\\$']).'"';}
$origins=array_values(array_unique(['http://localhost:5173','http://127.0.0.1:5173','http://localhost:'.$fePort,'http://127.0.0.1:'.$fePort]));
$values=['APP_NAME'=>'SOFEP','APP_ENV'=>'local','APP_DEBUG'=>'false','APP_KEY'=>'base64:'.base64_encode(random_bytes(32)),'APP_URL'=>'http://127.0.0.1:'.$bePort,'DB_CONNECTION'=>'mysql','DB_HOST'=>$host,'DB_PORT'=>$port,'DB_DATABASE'=>$name,'DB_USERNAME'=>$user,'DB_PASSWORD'=>$pass,'DB_URL'=>'','CACHE_STORE'=>'file','SESSION_DRIVER'=>'file','QUEUE_CONNECTION'=>'sync','MAIL_MAILER'=>'array','BROADCAST_CONNECTION'=>'null','LOG_CHANNEL'=>'single','CORS_ALLOWED_ORIGINS'=>implode(',',$origins)];
$text='';foreach($values as $k=>$v)$text.=$k.'='.qenv($v).PHP_EOL;file_put_contents($be.'/.env',$text);file_put_contents($fe.'/.env','VITE_API_URL=http://127.0.0.1:'.$bePort.'/api'.PHP_EOL);
foreach(['bootstrap/cache','storage/app/public','storage/app/private','storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs'] as $f)if(!is_dir($be.'/'.$f))mkdir($be.'/'.$f,0700,true);
$app=require $be.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(Illuminate\Support\Facades\DB::selectOne('SELECT DATABASE() AS db')->db!==$name||!preg_match('/^sofep_demo_[0-9]{8}_[a-f0-9]{12}$/D',$name))throw new RuntimeException('Wrong demo connection');
if(Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true])!==0)throw new RuntimeException('Migration failed');
Illuminate\Support\Facades\DB::transaction(function()use($tables,$catalog){foreach($tables as $t)foreach(array_chunk($catalog[$t],100) as $rows)Illuminate\Support\Facades\DB::table($t)->insert($rows);});
foreach($manifest['images'] as $i){$to=$be.'/storage/app/public/'.$i['path'];if(!is_dir(dirname($to)))mkdir(dirname($to),0700,true);if(!copy($root.'/demo/media/'.$i['path'],$to)||hash_file('sha256',$to)!==$i['sha256'])throw new RuntimeException('Image import mismatch');}
copy($root.'/demo/product-placeholder.svg',$be.'/public/product-placeholder.svg');
$accounts=[];foreach(['admin','customer'] as $role){$password=bin2hex(random_bytes(18));$email=$role.'@sofep.example.test';if($role==='admin')App\Models\User::create(['name'=>'SOFEP Demo Admin','email'=>$email,'password'=>Illuminate\Support\Facades\Hash::make($password)]);else App\Models\KhachHang::create(['ho_va_ten'=>'SOFEP Demo Customer','email'=>$email,'mat_khau'=>Illuminate\Support\Facades\Hash::make($password),'role'=>'customer','is_kich_hoat'=>1,'is_khoa'=>0]);$accounts[$role]=['email'=>$email,'password'=>$password];}
// File bí mật chỉ sinh trên máy người cài, không nằm trong ZIP/Git.
file_put_contents($be.'/storage/app/private/demo-access.json',json_encode($accounts,JSON_PRETTY_PRINT));@chmod($be.'/storage/app/private/demo-access.json',0600);
$counts=[];foreach($tables as $t)$counts[$t]=Illuminate\Support\Facades\DB::table($t)->count();$orderCount=Illuminate\Support\Facades\DB::table('don_hangs')->count();
$result=['passed'=>true,'database'=>$name,'backend_port'=>$bePort,'frontend_port'=>$fePort,'catalog_counts'=>$counts,'restored_images'=>167,'placeholder_product_id'=>172,'orders'=>$orderCount,'accounts_generated_locally'=>2,'tokens'=>Illuminate\Support\Facades\DB::table('personal_access_tokens')->count(),'migrations'=>Illuminate\Support\Facades\DB::table('migrations')->count()];
file_put_contents($root.'/install-result.json',json_encode($result,JSON_PRETTY_PRINT));echo json_encode($result,JSON_PRETTY_PRINT).PHP_EOL;
echo 'Credentials generated locally: themmoik27be/storage/app/private/demo-access.json (do not share).'.PHP_EOL;
