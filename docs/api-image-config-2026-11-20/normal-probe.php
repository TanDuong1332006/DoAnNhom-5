<?php
$input=json_decode(file_get_contents(__DIR__.'/uat.json'),true);
require $input['app'].'/vendor/autoload.php';
$app=require $input['app'].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(Illuminate\Support\Facades\DB::selectOne('SELECT DATABASE() AS db')->db!==$input['database']
 || realpath(config('filesystems.disks.public.root'))!==realpath($input['normal_storage']))throw new RuntimeException('Normal server isolation failed');
file_put_contents(__DIR__.'/normal-server-isolation.json',json_encode(['database'=>$input['database'],'storage'=>config('filesystems.disks.public.root'),'connection'=>config('database.default'),'cors'=>config('cors.allowed_origins'),'passed'=>true],JSON_PRETTY_PRINT));
