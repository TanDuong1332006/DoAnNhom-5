<?php
// Chỉ database/storage UAT đã được tạo từ ZIP. Không boot ứng dụng chính.
$input=json_decode(file_get_contents(__DIR__.'/uat.json'),true);
if (($argv[1]??'') !== 'worker') {
require __DIR__.'/../../themmoik27be/vendor/autoload.php';
$e=Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../../themmoik27be/.env'));
if(!in_array($e['DB_HOST']??'',['localhost','127.0.0.1'],true)||!empty($e['DB_URL']))throw new RuntimeException('Non-local DB refused');
$env=getenv();foreach(['HOST','PORT','USERNAME','PASSWORD'] as $k)$env['SOFEP_DEMO_DB_'.$k]=$e['DB_'.$k]??($k==='PORT'?'3306':'');
$env['CORS_ALLOWED_ORIGINS']='http://127.0.0.1:5173,http://localhost:5173,http://127.0.0.1:5194';
$env['SOFEP_CONFIG_UAT']=$input['session'];
}
if(($argv[1]??'')!=='worker'){
 $p=proc_open([PHP_BINARY,__FILE__,'worker'],[0=>['pipe','r'],1=>['file',__DIR__.'/prepare.out','a'],2=>['file',__DIR__.'/prepare.err','a']],$pipes,__DIR__,$env);fclose($pipes[0]);if(proc_close($p)!==0)throw new RuntimeException('UAT fixture preparation failed');
 $env['SOFEP_DEMO_SESSION']=$input['session'];
 $p=proc_open([PHP_BINARY,'-S','127.0.0.1:8042',$input['bundle'].'/server.php'],[0=>['pipe','r'],1=>['file',$input['root'].'/server.out','a'],2=>['file',$input['root'].'/server.err','a']],$pipes,$input['bundle'],$env);fclose($pipes[0]);
 file_put_contents(__DIR__.'/server.json',json_encode(['pid'=>proc_get_status($p)['pid'],'port'=>8042,'database'=>$input['database']],JSON_PRETTY_PRINT));echo "API-config UAT ready.\n";
 while(proc_get_status($p)['running'])usleep(500000);exit;
}
require $input['bundle'].'/boot.php';
$session=json_decode(file_get_contents($input['session']),true);$app=demoBoot($session);
if(Illuminate\Support\Facades\DB::connection()->getDatabaseName()!==$input['database'])throw new RuntimeException('Wrong database');
if(Illuminate\Support\Facades\Artisan::call('migrate',['--database'=>'demo','--force'=>true])!==0)throw new RuntimeException('UAT migrations failed');
$password=bin2hex(random_bytes(14));$suffix=bin2hex(random_bytes(4));
$customer=App\Models\KhachHang::create(['ho_va_ten'=>'Customer cấu hình UAT','email'=>'customer-config-'.$suffix.'@sofep.example.test','mat_khau'=>Illuminate\Support\Facades\Hash::make($password),'role'=>'customer','is_kich_hoat'=>1,'is_khoa'=>0]);
$admin=App\Models\User::create(['name'=>'Admin cấu hình UAT','email'=>'admin-config-'.$suffix.'@sofep.example.test','password'=>Illuminate\Support\Facades\Hash::make($password)]);
$seller=App\Models\KhachHang::create(['ho_va_ten'=>'Seller cũ UAT','email'=>'seller-config-'.$suffix.'@sofep.example.test','mat_khau'=>Illuminate\Support\Facades\Hash::make($password),'role'=>'seller','is_kich_hoat'=>1,'is_khoa'=>0]);
// Chỉ fixture tổng hợp trong thư mục TEMP; không ghi credential vào source/báo cáo/gói.
file_put_contents($input['root'].'/fixture-private.json',json_encode(['password'=>$password,'customer'=>$customer->email,'admin'=>$admin->email,'seller_token'=>$seller->createToken('config-test')->plainTextToken]));
file_put_contents(__DIR__.'/prepare-result.json',json_encode(['database'=>$input['database'],'migrations'=>Illuminate\Support\Facades\DB::table('migrations')->count(),'fixture_accounts'=>3,'storage'=>config('filesystems.disks.public.root'),'cors'=>config('cors.allowed_origins')],JSON_PRETTY_PRINT));
