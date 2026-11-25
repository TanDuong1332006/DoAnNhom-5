<?php
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$app=require __DIR__.'/../../themmoik27be/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db=Illuminate\Support\Facades\DB::selectOne('SELECT DATABASE() AS db')->db;
$root=config('filesystems.disks.public.root');
if($db!=='doan_nhom_5'||realpath($root)!==realpath(__DIR__.'/../../themmoik27be/storage/app/public'))throw new RuntimeException('Wrong PO environment');
file_put_contents(__DIR__.'/environment-probe.json',json_encode(['database'=>$db,'connection'=>config('database.default'),'public_storage'=>$root,'config_cached'=>$app->configurationIsCached(),'cors_origins'=>config('cors.allowed_origins'),'frontend_api_url'=>'http://127.0.0.1:8000/api','frontend_demo_flag_in_env'=>false],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));echo "PO Laravel database/storage verified read-only.\n";
