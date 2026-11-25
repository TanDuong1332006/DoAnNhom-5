<?php
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$e=Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if(($e['DB_HOST']??'')!=='127.0.0.1'||!empty($e['DB_URL']))exit(1);
$db=new PDO('mysql:host='.$e['DB_HOST'].';port='.($e['DB_PORT']??3306).';charset=utf8mb4',$e['DB_USERNAME'],$e['DB_PASSWORD']);
$q=$db->query("SELECT ID, DB, COMMAND, TIME, STATE, LEFT(INFO,180) AS sql_preview FROM information_schema.PROCESSLIST WHERE DB LIKE 'sofep_image_restore_%'")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($q,JSON_PRETTY_PRINT);
