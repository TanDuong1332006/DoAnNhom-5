<?php
// Launcher chỉ dùng trên máy kiểm thử; không phân phối trong ZIP.
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$e=Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if(!in_array($e['DB_HOST']??'',['127.0.0.1','localhost'],true)||!empty($e['DB_URL']))throw new RuntimeException('Local only');
$u=json_decode(file_get_contents(__DIR__.'/uat-location.json'),true);$env=getenv();foreach(['HOST','PORT','USERNAME','PASSWORD'] as $k)$env['SOFEP_DEMO_DB_'.$k]=$e['DB_'.$k]??($k==='PORT'?'3306':'');
$p=proc_open([PHP_BINARY,$u['root'].'/handoff/setup.php','8047','5200'],[0=>['pipe','r'],1=>['file',__DIR__.'/setup.out','w'],2=>['file',__DIR__.'/setup.err','w']],$pipes,$u['root'],$env);fclose($pipes[0]);$code=proc_close($p);if($code!==0)throw new RuntimeException('Setup failed; inspect setup.err');
copy($u['root'].'/install-result.json',__DIR__.'/install-result.json');echo "Independent ZIP setup completed.\n";


