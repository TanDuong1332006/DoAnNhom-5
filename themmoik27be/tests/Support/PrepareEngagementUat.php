<?php
// Chỉ chuẩn bị module/data demo trong database UAT đã tồn tại và được xác minh.
require __DIR__.'/MySqlUat.php';
$manifest=$argv[1]??'';
$session=is_file($manifest)?json_decode(file_get_contents($manifest),true):null;
if(($session['purpose']??'')!=='SOFEP isolated MySQL UAT') throw new RuntimeException('Verified UAT manifest required.');
$app=uatBoot($session['database']);
if(DB::connection()->getDatabaseName()!==$session['database']) throw new RuntimeException('Database mismatch.');
if(Schema::hasTable('private_feedback')) throw new RuntimeException('Engagement already prepared; do not recreate existing data.');
$before=['products'=>DB::table('san_phams')->count(),'orders'=>DB::table('don_hangs')->count()];
(require __DIR__.'/../../database/migrations/2026_11_06_000001_create_customer_engagement_tables.php')->up();
$password=bin2hex(random_bytes(16));
$stamp=bin2hex(random_bytes(4));$accounts=[];
foreach(['eligible','other'] as $kind){
 $customer=App\Models\KhachHang::create(['ho_va_ten'=>'Customer '.$kind.' UAT','email'=>'eng-'.$kind.'-'.$stamp.'@sofep.example.test','mat_khau'=>Hash::make($password),'role'=>'customer','is_khoa'=>0,'is_kich_hoat'=>1]);
 $accounts[$kind]=['id'=>$customer->id,'email'=>$customer->email];
}
for($i=0;$i<3;$i++)App\Models\DonHang::create(['id_khach_hang'=>$accounts['eligible']['id'],'ma_don_hang'=>'UAT_LOYAL_'.bin2hex(random_bytes(8)),
 'ho_ten'=>'Fixture only','so_dien_thoai'=>'0900000000','dia_chi_giao'=>'Isolated UAT, no delivery','tong_tien_hang'=>1000000,'phi_van_chuyen'=>0,'tien_giam_gia'=>0,'tong_thanh_toan'=>1000000,
 'phuong_thuc_thanh_toan'=>'COD','trang_thai_thanh_toan'=>'da_thanh_toan','trang_thai_don_hang'=>'da_giao','ghi_chu'=>'Synthetic loyalty history in isolated UAT only.']);
$target=dirname($manifest).'/engagement-session.json';
file_put_contents($target,json_encode(['purpose'=>$session['purpose'],'database'=>$session['database'],'source_manifest'=>$manifest,'password'=>$password,'accounts'=>$accounts,'api_port'=>8016],JSON_THROW_ON_ERROR));
echo json_encode(['database'=>$session['database'],'catalog_before'=>$before['products'],'catalog_after'=>DB::table('san_phams')->count(),'orders_before'=>$before['orders'],'synthetic_completed_orders'=>3,'engagement_session'=>$target,'migration_applied'=>true],JSON_UNESCAPED_UNICODE).PHP_EOL;
