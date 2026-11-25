<?php
// Local launcher; không phân phối credential hoặc sao chép .env vào UAT.
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$e=Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if(($e['DB_CONNECTION']??'')!=='mysql'||!in_array($e['DB_HOST']??'',['127.0.0.1','localhost'],true)||!empty($e['DB_URL']))throw new RuntimeException('Unexpected MySQL endpoint');
$input=json_decode(file_get_contents(__DIR__.'/inputs.json'),true);$bundle=$input['extracted'];
$runtime=sys_get_temp_dir().'/sofep-handoff-runtime-'.bin2hex(random_bytes(6));
$env=getenv();foreach(['HOST','PORT','USERNAME','PASSWORD'] as $key)$env['SOFEP_DEMO_DB_'.$key]=$e['DB_'.$key]??($key==='PORT'?'3306':'');
$p=proc_open([PHP_BINARY,$bundle.'/import.php',$input['source'].'/themmoik27be',$runtime],[0=>['pipe','r'],1=>['file',__DIR__.'/import.out','a'],2=>['file',__DIR__.'/import.err','a']],$pipes,$bundle,$env);fclose($pipes[0]);if(proc_close($p)!==0)throw new RuntimeException('ZIP import failed');
$env['SOFEP_DEMO_SESSION']=$runtime.'/session.json';
$server=proc_open([PHP_BINARY,'-S','127.0.0.1:'.$input['backend_port'],$bundle.'/server.php'],[0=>['pipe','r'],1=>['file',$runtime.'/server.out','a'],2=>['file',$runtime.'/server.err','a']],$pipes,$bundle,$env);fclose($pipes[0]);
$session=json_decode(file_get_contents($runtime.'/session.json'),true);
file_put_contents(__DIR__.'/uat.json',json_encode(['runtime'=>$runtime,'database'=>$session['database'],'storage'=>$runtime.'/storage/app/public','port'=>$input['backend_port'],'pid'=>proc_get_status($server)['pid'],'from_zip'=>true],JSON_PRETTY_PRINT));
copy($runtime.'/import-result.json',__DIR__.'/import-result.json');
echo "Fresh ZIP import finished; independent UAT server running.\n";
while(proc_get_status($server)['running'])usleep(500000);

