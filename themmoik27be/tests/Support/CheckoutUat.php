<?php

// N5-142 tái sử dụng bootstrap đã cô lập từ trước provider boot. Không chọn DB chính.
require __DIR__.'/SecurityBrowserBoot.php';
use Illuminate\Support\Facades\{DB, Hash};
use App\Models\{User, KhachHang, SanPham, ChiTietSanPham, MaGiamGia, DonHang};
$pointer = sys_get_temp_dir().'/sofep-n5142-session-path.txt';
if (PHP_SAPI === 'cli' && ($argv[1] ?? '') === 'prepare') {
    $previous = json_decode(file_get_contents(__DIR__.'/../../../docs/N5-147/mysql-uat.json'), true);
    if (!($previous['passed'] ?? false)) throw new RuntimeException('Verified UAT database required.');
    $tag = bin2hex(random_bytes(3)); $root = sys_get_temp_dir().'/sofep-n5142-'.$tag; mkdir($root, 0700); mkdir($root.'/private',0700); mkdir($root.'/public',0700);
    $session = ['purpose'=>'SOFEP N5-147 browser UAT','task'=>'N5-142','database'=>$previous['database'],'root'=>$root,'cache_prefix'=>'n5142-'.$tag.':',
        'admin'=>'admin142-'.$tag.'@sofep.example.test','customer'=>'customer142-'.$tag.'@sofep.example.test','other'=>'other142-'.$tag.'@sofep.example.test','api'=>'api142-'.$tag.'@sofep.example.test','seller'=>'seller142-'.$tag.'@sofep.example.test'];
    securityBrowserBoot($session);
    $password = Hash::make('Uat-checkout-142!');
    User::create(['name'=>'Admin N142','email'=>$session['admin'],'password'=>$password]);
    foreach (['customer','other','api','seller'] as $kind) KhachHang::create(['ho_va_ten'=>'UAT N142 '.$kind,'email'=>$session[$kind],'mat_khau'=>$password,'role'=>$kind==='seller'?'seller':'customer','is_khoa'=>0,'is_kich_hoat'=>1]);
    $category = DB::table('danh_mucs')->insertGetId(['ten_danh_muc'=>'UAT N142','slug_danh_muc'=>'uat142-'.$tag,'trang_thai'=>1]);
    $black = DB::table('mau_sacs')->insertGetId(['ten_mau'=>'Đen N142','ma_mau'=>'#111111','trang_thai'=>1]);
    $blue = DB::table('mau_sacs')->insertGetId(['ten_mau'=>'Xanh N142','ma_mau'=>'#2468ac','trang_thai'=>1]);
    $medium = DB::table('kich_thuocs')->insertGetId(['ten_kich_thuoc'=>'M142','trang_thai'=>1]);
    $large = DB::table('kich_thuocs')->insertGetId(['ten_kich_thuoc'=>'L142','trang_thai'=>1]);
    $product = SanPham::create(['id_danh_muc'=>$category,'ma_san_pham'=>'UAT142-'.$tag,'ten_san_pham'=>'Áo biến thể N142 '.$tag,'slug_san_pham'=>'ao-n142-'.$tag,
        'gia_ban'=>250000,'gia_khuyen_mai'=>200000,'trang_thai'=>1,'so_luong_ton'=>30,'hinh_anh'=>'http://127.0.0.1:8028/__uat/image.svg','mo_ta_ngan'=>'Fixture N5-142, không bán thật.']);
    $ids=[];
    foreach ([[$black,$medium,20000],[$black,$large,30000],[$blue,$medium,0]] as [$color,$size,$delta]) $ids[]=ChiTietSanPham::create(['id_san_pham'=>$product->id,'id_mau_sac'=>$color,'id_kich_thuoc'=>$size,'so_luong_ton'=>10,'gia_chenh_lech'=>$delta,'trang_thai'=>1])->id;
    $coupon = MaGiamGia::create(['ma_code'=>'N142'.strtoupper($tag),'ten_giam_gia'=>'UAT142 10%','loai_giam'=>'phan_tram','gia_tri'=>10,'so_luong'=>20,'so_luong_da_dung'=>0,'gia_tri_toi_thieu'=>100000,'giam_toi_da'=>50000,'ngay_bat_dau'=>now()->subDay()->toDateString(),'ngay_ket_thuc'=>now()->addDays(2)->toDateString(),'trang_thai'=>1,'doi_tuong'=>'all']);
    $session += ['product_id'=>$product->id,'slug'=>$product->slug_san_pham,'variants'=>$ids,'coupon'=>$coupon->ma_code,'coupon_id'=>$coupon->id];
    file_put_contents($root.'/session.json',json_encode($session,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));file_put_contents($pointer,$root.'/session.json');
    echo json_encode($session,JSON_UNESCAPED_UNICODE).PHP_EOL;exit;
}
if (!is_file($pointer)) throw new RuntimeException('UAT session missing.');
$session=json_decode(file_get_contents(trim(file_get_contents($pointer))),true);
if (($session['task']??'')!=='N5-142') throw new RuntimeException('Wrong task session.');
$app=securityBrowserBoot($session);config(['cors.allowed_origins'=>['http://127.0.0.1:5188']]);
if(PHP_SAPI==='cli-server') {
    if(!in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)){http_response_code(403);exit;}
    $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
    if($path==='/__uat/status'){header('Content-Type: application/json');echo json_encode(['task'=>'N5-142','database'=>$session['database'],'cache_prefix'=>$session['cache_prefix'],'isolated_connections'=>array_keys(config('database.connections'))]);exit;}
    if($path==='/__uat/image.svg'){header('Content-Type: image/svg+xml');echo '<svg xmlns="http://www.w3.org/2000/svg" width="480" height="640"><rect width="480" height="640" fill="#eadbcb"/><text x="40" y="300" font-size="36">SOFEP UAT 142</text></svg>';exit;}
    $r=Illuminate\Http\Request::capture();$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$res=$kernel->handle($r);$res->send();$kernel->terminate($r,$res);
    $j=json_decode($res->getContent(),true);file_put_contents($session['root'].'/requests.jsonl',json_encode(['at'=>gmdate(DATE_ATOM),'path'=>$r->path(),'method'=>$r->method(),'status'=>$res->getStatusCode(),'order_id'=>$j['don_hang']['id']??null,'payment'=>$j['don_hang']['trang_thai_thanh_toan']??null,'success'=>$j['success']??null]).PHP_EOL,FILE_APPEND|LOCK_EX);exit;
}
$mode=$argv[1]??'';
if($mode==='inspect') {
    $orders=DonHang::whereIn('id_khach_hang',KhachHang::whereIn('email',[$session['customer'],$session['api']])->pluck('id'))->get(['id','ma_don_hang','trang_thai_don_hang','trang_thai_thanh_toan','phuong_thuc_thanh_toan','tong_thanh_toan']);
    echo json_encode(['orders'=>$orders,'stock'=>ChiTietSanPham::whereIn('id',$session['variants'])->pluck('so_luong_ton','id'),'voucher_used'=>MaGiamGia::findOrFail($session['coupon_id'])->so_luong_da_dung],JSON_UNESCAPED_UNICODE).PHP_EOL;exit;
}
if($mode!=='api')throw new RuntimeException('Unknown UAT mode.');
$checks=[];
$api=function($name,$method,$path,$data,$expected,$token=null,$key=null)use($app,&$checks){
    $app['auth']->forgetGuards();$r=Illuminate\Http\Request::create('/api/'.$path,$method,$data,[],[],['HTTP_ACCEPT'=>'application/json','REMOTE_ADDR'=>'127.0.0.142']);
    if($token)$r->headers->set('Authorization','Bearer '.$token);if($key)$r->headers->set('Idempotency-Key',$key);
    $kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$res=$kernel->handle($r);$kernel->terminate($r,$res);$j=json_decode($res->getContent(),true);
    $checks[]=['case'=>$name,'expected'=>$expected,'actual'=>$res->getStatusCode(),'passed'=>$res->getStatusCode()===$expected];
    if($res->getStatusCode()!==$expected)throw new RuntimeException('Failed UAT: '.$name);return $j;
};
$assert=function($ok,$name)use(&$checks){$checks[]=['case'=>$name,'passed'=>(bool)$ok];if(!$ok)throw new RuntimeException('Failed invariant: '.$name);};
$owner=KhachHang::where('email',$session['api'])->firstOrFail();$token=$owner->createToken('uat142')->plainTextToken;
$other=KhachHang::where('email',$session['other'])->firstOrFail()->createToken('uat142')->plainTextToken;
$seller=KhachHang::where('email',$session['seller'])->firstOrFail()->createToken('uat142')->plainTextToken;
$admin=User::where('email',$session['admin'])->firstOrFail()->createToken('uat142')->plainTextToken;
$payload=['ho_ten'=>'UAT N142 api','so_dien_thoai'=>'0900000142','dia_chi_giao'=>'Địa chỉ fixture, không giao hàng','phuong_thuc_thanh_toan'=>'MB_BANK','ma_giam_gia_su_dung'=>$session['coupon'],'tong_thanh_toan'=>1,'items'=>[['id'=>$session['product_id'],'id_chi_tiet_san_pham'=>$session['variants'][0],'quantity'=>2,'gia_ban'=>1]]];
$stock=fn()=>ChiTietSanPham::findOrFail($session['variants'][0])->so_luong_ton;$usage=fn()=>MaGiamGia::findOrFail($session['coupon_id'])->so_luong_da_dung;
$before=$stock();$used=$usage();$passed=false;
try{
 $api('Guest create order denied','POST','client/dat-hang',$payload,401);
 $api('Seller create order denied','POST','client/dat-hang',$payload,403,$seller);
 $api('Admin create Customer order denied','POST','client/dat-hang',$payload,403,$admin);
 $api('Customer admin API denied','GET','admin/don-hang/get-data',[],403,$token);
 $bad=$payload;$bad['items'][0]['quantity']=999;$api('Out of stock rolls back','POST','client/dat-hang',$bad,422,$token);
 $bad=$payload;$bad['ma_giam_gia_su_dung']='INVALID142';$api('Invalid voucher rolls back','POST','client/dat-hang',$bad,422,$token);
 $bad=$payload;unset($bad['dia_chi_giao']);$api('Missing shipping address rejected','POST','client/dat-hang',$bad,422,$token);
 $assert($stock()===$before&&$usage()===$used,'Failed checkouts preserve stock and voucher');
 $q=$api('Quote valid voucher','POST','client/kiem-tra-voucher',$payload,200,$token);
 $assert((float)$q['data']['tong_thanh_toan']===426000.0,'Server total 440000 - 44000 + 30000');
 $assert($stock()===$before&&$usage()===$used,'Quote does not reserve resources');
 $key=(string)Illuminate\Support\Str::uuid();$o=$api('Create valid simulated order','POST','client/dat-hang',$payload,201,$token,$key)['don_hang'];
 $repeat=$api('Idempotent retry returns same order','POST','client/dat-hang',$payload,201,$token,$key)['don_hang'];
 $assert($o['id']===$repeat['id']&&$stock()===$before-2&&$usage()===$used+1,'Repeated checkout consumes stock/voucher once');
 $bad=$payload;$bad['items'][0]['quantity']=1;$api('Key reused for changed cart rejected','POST','client/dat-hang',$bad,409,$token,$key);
 $code=$o['ma_don_hang'];$api('Other Customer cannot read order','GET','kiem-tra-thanh-toan/'.$code,[],403,$other);
 $api('Other Customer cannot simulate payment','POST','giao-dich/mo-phong',['ma_hoa_don'=>$code],403,$other);
 $api('Failed payment simulation','POST','giao-dich/mo-phong',['ma_hoa_don'=>$code,'ket_qua'=>'failed'],200,$token);
 $assert(DonHang::find($o['id'])->trang_thai_thanh_toan==='chua_thanh_toan','Failed payment remains unpaid');
 $api('Successful payment simulation','POST','giao-dich/mo-phong',['ma_hoa_don'=>$code],200,$token);
 $api('Repeated payment simulation','POST','giao-dich/mo-phong',['ma_hoa_don'=>$code],200,$token);
 $assert($stock()===$before-2&&$usage()===$used+1,'Payment retry does not consume resources again');
 $api('Cannot skip order lifecycle','POST','admin/don-hang/update-status',['id'=>$o['id'],'trang_thai_don_hang'=>'da_giao'],409,$admin);
 foreach(['da_xac_nhan','dang_giao','da_giao']as$status){$api('Admin transitions '.$status,'POST','admin/don-hang/update-status',['id'=>$o['id'],'trang_thai_don_hang'=>$status],200,$admin);$r=$api('Owner reads '.$status,'GET','kiem-tra-thanh-toan/'.$code,[],200,$token);$assert($r['don_hang']['trang_thai_don_hang']===$status,'Owner status matches '.$status);}
 $api('Cannot cancel paid order','POST','admin/don-hang/update-status',['id'=>$o['id'],'trang_thai_don_hang'=>'da_huy'],409,$admin);
 $cod=$payload;$cod['items'][0]['quantity']=1;$cod['phuong_thuc_thanh_toan']='COD';$c=$api('Create COD','POST','client/dat-hang',$cod,201,$token)['don_hang'];
 $assert($c['trang_thai_thanh_toan']==='chua_thanh_toan','COD starts unpaid');
 $api('COD cannot use bank simulation','POST','giao-dich/mo-phong',['ma_hoa_don'=>$c['ma_don_hang']],409,$token);
 $api('Cannot complete unpaid COD','POST','admin/don-hang/update-status',['id'=>$c['id'],'trang_thai_don_hang'=>'da_giao'],409,$admin);
 $api('Admin cancels unpaid order','POST','admin/don-hang/update-status',['id'=>$c['id'],'trang_thai_don_hang'=>'da_huy'],200,$admin);
 $api('Repeat cancellation is safe','POST','admin/don-hang/update-status',['id'=>$c['id'],'trang_thai_don_hang'=>'da_huy'],200,$admin);
 $assert($stock()===$before-2&&$usage()===$used+1,'Cancellation restores stock/voucher exactly once');
 $api('Customer cannot cancel via Admin API','POST','admin/don-hang/update-status',['id'=>$c['id'],'trang_thai_don_hang'=>'da_huy'],403,$token);
 $passed=true;
}finally{if(!is_dir(__DIR__.'/../../../docs/N5-142'))mkdir(__DIR__.'/../../../docs/N5-142',0700,true);file_put_contents(__DIR__.'/../../../docs/N5-142/api-uat.json',json_encode(['database'=>$session['database'],'cache_prefix'=>$session['cache_prefix'],'passed'=>$passed,'checks'=>$checks,'stock_before'=>$before,'stock_after'=>$stock(),'voucher_before'=>$used,'voucher_after'=>$usage()],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo json_encode(['passed'=>$passed,'checks'=>count($checks)]).PHP_EOL;}
