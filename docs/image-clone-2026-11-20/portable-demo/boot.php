<?php
// Runtime UAT only. Không đọc .env ứng dụng, không có connection database chính.
function demoBoot(array $session) {
 if (!preg_match('/^sofep_image_restore_[0-9]{8}_[a-f0-9]{12}$/D',$session['database']??'')) throw new RuntimeException('Invalid UAT database');
 $appRoot=$session['app_root'];$runtime=$session['runtime'];
 if(is_file($appRoot.'/bootstrap/cache/config.php')||is_file($appRoot.'/.env'))throw new RuntimeException('Requires clean source without .env/config cache');
 require_once $appRoot.'/vendor/autoload.php';
 foreach(['app/public','app/private','framework/cache/data','framework/sessions','framework/views','logs'] as $folder)if(!is_dir($runtime.'/storage/'.$folder))mkdir($runtime.'/storage/'.$folder,0700,true);
 $app=require $appRoot.'/bootstrap/app.php';
 $app->useStoragePath($runtime.'/storage');
 $app->afterBootstrapping(Illuminate\Foundation\Bootstrap\LoadConfiguration::class,function($app)use($session,$runtime){
  $host=getenv('SOFEP_DEMO_DB_HOST')?:'127.0.0.1';if(!in_array($host,['localhost','127.0.0.1','::1'],true))throw new RuntimeException('Local MySQL only');
  $mysql=['driver'=>'mysql','host'=>$host,'port'=>getenv('SOFEP_DEMO_DB_PORT')?:3306,'database'=>$session['database'],'username'=>getenv('SOFEP_DEMO_DB_USERNAME')?:'root','password'=>getenv('SOFEP_DEMO_DB_PASSWORD')?:'','unix_socket'=>'','charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true,'engine'=>null,'url'=>null];
  config(['database.default'=>'demo','database.connections'=>['demo'=>$mysql], 'app.env'=>'testing','app.debug'=>false,'app.key'=>'base64:'.base64_encode(random_bytes(32)), 'cache.default'=>'array','session.driver'=>'array','mail.default'=>'array','mail.mailers'=>['array'=>['transport'=>'array']],'queue.default'=>'sync','broadcasting.default'=>'null','logging.default'=>'null','filesystems.disks.public.root'=>$runtime.'/storage/app/public','filesystems.disks.local.root'=>$runtime.'/storage/app/private']);
  $app->instance('env','testing');
 });
 $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
 if(Illuminate\Support\Facades\DB::selectOne('SELECT DATABASE() AS db')->db!==$session['database'])throw new RuntimeException('Database isolation failed');
 return $app;
}
