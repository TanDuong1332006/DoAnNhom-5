<?php
// Chỉ là launcher trên máy audit, KHÔNG đưa vào gói chuyển máy.
// Credential hiện tại chỉ truyền trong environment tiến trình, không lưu file/báo cáo.
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$env=Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if(($env['DB_CONNECTION']??'')!=='mysql'||!in_array($env['DB_HOST']??'',['127.0.0.1','localhost'],true)||($env['DB_DATABASE']??'')!=='doan_nhom_5'||!empty($env['DB_URL']))throw new RuntimeException('Unexpected local connection');
$clone=json_decode(file_get_contents(__DIR__.'/../image-audit-2026-11-20/clone-location.json'),true)['path'];
$runtime=sys_get_temp_dir().'/sofep-image-restored-'.bin2hex(random_bytes(6));
$childEnv=getenv();
foreach(['HOST','PORT','USERNAME','PASSWORD'] as $key)$childEnv['SOFEP_DEMO_DB_'.$key]=$env['DB_'.$key]??($key==='PORT'?'3306':'');
$bundle=__DIR__.'/portable-demo';
$process=proc_open([PHP_BINARY,$bundle.'/import.php',$clone.'/themmoik27be',$runtime],[0=>['pipe','r'],1=>['file',__DIR__.'/import-output.txt','a'],2=>['file',__DIR__.'/import-error.txt','a']],$pipes,$bundle,$childEnv);
fclose($pipes[0]);if(proc_close($process)!==0)throw new RuntimeException('Import failed; see local error report');
$childEnv['SOFEP_DEMO_SESSION']=$runtime.'/session.json';
$server=proc_open([PHP_BINARY,'-S','127.0.0.1:8038',$bundle.'/server.php'],[0=>['pipe','r'],1=>['file',$runtime.'/server.out','a'],2=>['file',$runtime.'/server.err','a']],$serverPipes,$bundle,$childEnv);
fclose($serverPipes[0]);$status=proc_get_status($server);
$session=json_decode(file_get_contents($runtime.'/session.json'),true);
file_put_contents(__DIR__.'/uat-location.json',json_encode(['runtime'=>$runtime,'clean_source'=>$clone,'database'=>$session['database'],'server_pid'=>$status['pid'],'port'=>8038,'credential_files_created'=>false],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo "Isolated server launched; runtime pointer recorded.\n";
// Giữ launcher sống để Windows không thu hồi proc handle; chỉ server UAT ở foreground của tiến trình này.
while(proc_get_status($server)['running'])usleep(500000);
