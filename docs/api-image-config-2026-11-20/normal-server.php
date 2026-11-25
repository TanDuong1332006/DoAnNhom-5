<?php
// Laravel chuẩn, cấu hình qua environment tiến trình, tương đương .env trên clone riêng.
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$input=json_decode(file_get_contents(__DIR__.'/uat.json'),true);
$e=Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if(!in_array($e['DB_HOST']??'',['127.0.0.1','localhost'],true)||!empty($e['DB_URL']))throw new RuntimeException('Non-local DB');
$env=getenv();foreach(['HOST','PORT','USERNAME','PASSWORD'] as $k)$env['DB_'.$k]=$e['DB_'.$k]??($k==='PORT'?'3306':'');
$env=array_merge($env,['DB_CONNECTION'=>'mysql','DB_DATABASE'=>$input['database'],'DB_URL'=>'','APP_ENV'=>'testing','APP_DEBUG'=>'false','APP_KEY'=>'base64:'.base64_encode(random_bytes(32)),'APP_URL'=>'http://127.0.0.1:8042','LARAVEL_STORAGE_PATH'=>dirname($input['storage'],2),'CACHE_STORE'=>'array','SESSION_DRIVER'=>'array','QUEUE_CONNECTION'=>'sync','MAIL_MAILER'=>'array','BROADCAST_CONNECTION'=>'null','LOG_CHANNEL'=>'null','CORS_ALLOWED_ORIGINS'=>'http://127.0.0.1:5173,http://localhost:5173,http://127.0.0.1:5194']);
// Artisan chuẩn dùng storage riêng của clone đã copy ảnh từ ZIP; không dùng storage chính.
$oldEnv='LARAVEL_STORAGE_PATH="'.str_replace('\\','/',dirname($input['storage'],2)).'"'.PHP_EOL;
if(is_file($input['app'].'/.env') && file_get_contents($input['app'].'/.env')!==$oldEnv)throw new RuntimeException('Refuse unknown UAT environment file');
file_put_contents($input['app'].'/.env','');
unset($env['LARAVEL_STORAGE_PATH']);
$probe=proc_open([PHP_BINARY,__DIR__.'/normal-probe.php'],[0=>['pipe','r'],1=>['file',$input['root'].'/normal-probe.out','a'],2=>['file',$input['root'].'/normal-probe.err','a']],$pipes,$input['app'],$env);fclose($pipes[0]);if(proc_close($probe)!==0)throw new RuntimeException('Normal Laravel probe refused configuration');
$server=proc_open([PHP_BINARY,'artisan','serve','--host=127.0.0.1','--port=8042','--no-reload'],[0=>['pipe','r'],1=>['file',$input['root'].'/normal-server.out','a'],2=>['file',$input['root'].'/normal-server.err','a']],$pipes,$input['app'],$env);fclose($pipes[0]);
echo "Normal Laravel artisan serve launched on isolated environment.\n";
while(proc_get_status($server)['running'])usleep(500000);
