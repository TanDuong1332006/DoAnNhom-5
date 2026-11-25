<?php
// Worker demo chỉ dành cho manifest MySQL UAT đã xác minh, không dùng database chính.
require __DIR__.'/MySqlUat.php';
$manifest=$argv[1]??'';$session=is_file($manifest)?json_decode(file_get_contents($manifest),true):null;
if(($session['purpose']??'')!=='SOFEP isolated MySQL UAT')throw new RuntimeException('Verified UAT manifest required.');
$app=uatBoot($session['database']);
if(DB::connection()->getDatabaseName()!==$session['database'] || !Schema::hasTable('customer_campaigns'))throw new RuntimeException('Engagement UAT is not ready.');
$once=in_array('--once',$argv,true);
do {
    try {
        $count=app(App\Services\CustomerCampaigns::class)->deliverDue(100);
        if($count || $once)echo json_encode(['database'=>$session['database'],'delivered'=>$count],JSON_UNESCAPED_UNICODE).PHP_EOL;
    } catch(Throwable $error) { fwrite(STDERR,"UAT worker could not deliver this batch; retrying later.\n"); }
    if(!$once)sleep(10);
} while(!$once);
