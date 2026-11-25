<?php
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$e=Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if(!in_array($e['DB_HOST']??'',['127.0.0.1','localhost'],true)||!empty($e['DB_URL']))throw new RuntimeException('Local only');
$s=json_decode(file_get_contents(__DIR__.'/state.json'),true);
if(!preg_match('/^sofep_image_restore_[0-9]{8}_[a-f0-9]{12}$/D',$s['trial_database']))throw new RuntimeException('UAT only');
$env=getenv();foreach(['HOST','PORT','USERNAME','PASSWORD'] as $k)$env['SOFEP_DEMO_DB_'.$k]=$e['DB_'.$k]??($k==='PORT'?'3306':'');$env['SOFEP_DEMO_SESSION']=$s['runtime'].'/session.json';
$p=proc_open([PHP_BINARY,'-S','127.0.0.1:8044',__DIR__.'/router.php'],[0=>['pipe','r'],1=>['file',$s['runtime'].'/server.out','a'],2=>['file',$s['runtime'].'/server.err','a']],$pipes,__DIR__,$env);fclose($pipes[0]);while(proc_get_status($p)['running'])usleep(500000);
