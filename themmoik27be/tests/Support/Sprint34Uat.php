<?php

// Chỉ fixture task, tái sử dụng bootstrap DB/cache/mail/storage cô lập đã kiểm chứng.
require __DIR__.'/SecurityBrowserBoot.php';
use Illuminate\Support\Facades\{DB, Hash};
use App\Models\{KhachHang, User, SanPham, ChiTietSanPham, MaGiamGia, DonHang};

$pointer = sys_get_temp_dir().'/sofep-sprint34-session-path.txt';
$mode = $argv[1] ?? '';
if (PHP_SAPI !== 'cli-server' && $mode === 'prepare') {
    $old = json_decode(file_get_contents(trim(file_get_contents(sys_get_temp_dir().'/sofep-n5142-session-path.txt'))), true);
    $tag = bin2hex(random_bytes(4)); $root = sys_get_temp_dir().'/sofep-sprint34-'.$tag;
    mkdir($root, 0700); mkdir($root.'/private', 0700); mkdir($root.'/public', 0700);
    $s = ['purpose'=>'SOFEP N5-147 browser UAT', 'task'=>'Sprint34', 'database'=>$old['database'], 'root'=>$root, 'cache_prefix'=>'s34-'.$tag.':'];
    securityBrowserBoot($s);
    $password = 'Uat-sprint34-19!';
    $s['admin_email'] = 'admin34-'.$tag.'@sofep.example.test';
    $admin = User::create(['name'=>'Admin Sprint34', 'email'=>$s['admin_email'], 'password'=>Hash::make($password)]);
    $s['admin_id']=$admin->id; $s['customers']=[];
    foreach (range(0, 1) as $i) {
        $email = 'customer34-'.$tag.'-'.$i.'@sofep.example.test';
        $c=KhachHang::create(['ho_va_ten'=>'Customer Sprint34 '.$i,'email'=>$email,'mat_khau'=>Hash::make($password),'role'=>'customer','is_kich_hoat'=>1,'is_khoa'=>0]);
        $s['customers'][]=$c->id; if ($i===0) $s['customer_email']=$email;
    }
    $s['cases']=[];
    foreach (['stock','coupon','duplicate','per_customer','browser'] as $name) {
        $p=SanPham::findOrFail($old['product_id'])->replicate();
        $p->ma_san_pham='S34-'.$tag.'-'.$name; $p->ten_san_pham='UAT Sprint34 '.$name.' '.$tag; $p->slug_san_pham='s34-'.$tag.'-'.$name;
        $p->hinh_anh='http://127.0.0.1:8029/__uat/image.svg'; $p->so_luong_ton=$name==='stock'?1:10; $p->save();
        $v=ChiTietSanPham::findOrFail($old['variants'][0])->replicate(); $v->id_san_pham=$p->id; $v->so_luong_ton=$p->so_luong_ton; $v->save();
        $coupon=MaGiamGia::findOrFail($old['coupon_id'])->replicate();
        $coupon->ma_code='S34'.strtoupper($tag).strtoupper($name); $coupon->so_luong=$name==='coupon'?1:100;
        $coupon->so_luong_da_dung=0; $coupon->ngay_bat_dau=now()->subDay()->toDateString(); $coupon->ngay_ket_thuc=now()->addDays(3)->toDateString();
        $coupon->gioi_han_moi_khach=$name==='per_customer'?1:null; $coupon->save();
        $s['cases'][$name]=['product'=>$p->id,'variant'=>$v->id,'coupon_id'=>$coupon->id,'code'=>$coupon->ma_code,'slug'=>$p->slug_san_pham];
    }
    file_put_contents($root.'/session.json', json_encode($s, JSON_PRETTY_PRINT)); file_put_contents($pointer, $root.'/session.json');
    echo json_encode(['database'=>$s['database'],'root'=>$root,'admin_email'=>$s['admin_email'],'customer_email'=>$s['customer_email'],'cases'=>$s['cases']]); exit;
}
$s=json_decode(file_get_contents(trim(file_get_contents($pointer))),true);
if (($s['task']??'')!=='Sprint34') throw new RuntimeException('Wrong UAT task.');
$app=securityBrowserBoot($s); config(['cors.allowed_origins'=>['http://127.0.0.1:5189']]);
$call=function($method,$uri,$payload=[],$actor=null,$key=null)use($app){
    $app['auth']->forgetGuards(); $token=$actor?->createToken('sprint34-uat')->plainTextToken;
    $r=Illuminate\Http\Request::create('/api/'.$uri,$method,$payload,[],[],['HTTP_ACCEPT'=>'application/json','REMOTE_ADDR'=>'127.0.0.134']);
    if($token)$r->headers->set('Authorization','Bearer '.$token);if($key)$r->headers->set('Idempotency-Key',$key);
    $kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$res=$kernel->handle($r);$kernel->terminate($r,$res);
    if($token)Laravel\Sanctum\PersonalAccessToken::findToken($token)?->delete();
    return ['status'=>$res->getStatusCode(),'body'=>json_decode($res->getContent(),true)];
};
if(PHP_SAPI==='cli-server') {
    if(!in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)){http_response_code(403);exit;}
    $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
    if(is_file($s['root'].'/dashboard-failure') && str_starts_with($path,'/api/admin/thong-ke/')){http_response_code(503);header('Content-Type: application/json');echo json_encode(['status'=>0,'message'=>'UAT injected failure']);exit;}
    if($path==='/__uat/status'){header('Content-Type: application/json');echo json_encode(['database'=>$s['database'],'cache_prefix'=>$s['cache_prefix'],'connections'=>array_keys(config('database.connections'))]);exit;}
    if($path==='/__uat/image.svg'){header('Content-Type: image/svg+xml');echo '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="500"><rect width="400" height="500" fill="#ddd"/><text x="20" y="250" font-size="28">SOFEP UAT Sprint34</text></svg>';exit;}
    $r=Illuminate\Http\Request::capture();$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$res=$kernel->handle($r);$res->send();$kernel->terminate($r,$res);
    file_put_contents($s['root'].'/http.jsonl',json_encode(['path'=>$r->path(),'method'=>$r->method(),'status'=>$res->getStatusCode()]).PHP_EOL,FILE_APPEND|LOCK_EX);exit;
}
if($mode==='worker') {
    $name=$argv[2];$worker=(int)$argv[3];$case=$s['cases'][$name];
    $customer=KhachHang::findOrFail($s['customers'][in_array($name,['duplicate','per_customer'],true)?0:$worker]);
    $payload=['ho_ten'=>'UAT Sprint34','so_dien_thoai'=>'0900000134','dia_chi_giao'=>'Fixture UAT, không giao hàng','phuong_thuc_thanh_toan'=>'COD',
        'ma_giam_gia_su_dung'=>$case['code'],'items'=>[['id'=>$case['product'],'id_chi_tiet_san_pham'=>$case['variant'],'quantity'=>1]]];
    $key=$name==='duplicate'?'a1340000-0000-4000-8000-'.str_pad((string)$case['product'],12,'0',STR_PAD_LEFT):null;
    while(microtime(true)<(float)$argv[4])usleep(1000);
    $res=$call('POST','client/dat-hang',$payload,$customer,$key);
    echo json_encode(['status'=>$res['status'],'order'=>$res['body']['don_hang']['id']??null,'message'=>$res['body']['message']??null]);exit;
}
if($mode==='verify') {
    $checks=[];$assert=function($ok,$name)use(&$checks){$checks[]=['case'=>$name,'passed'=>(bool)$ok];if(!$ok)throw new RuntimeException($name);};
    foreach(['stock','coupon','duplicate','per_customer']as$name){
        $c=$s['cases'][$name];$orders=DonHang::whereHas('chiTiets',fn($q)=>$q->where('id_san_pham',$c['product']))->get();
        $assert($orders->count()===1,$name.' exactly one order');
        $assert((int)ChiTietSanPham::find($c['variant'])->so_luong_ton===($name==='stock'?0:9),$name.' stock once');
        $assert((int)MaGiamGia::find($c['coupon_id'])->so_luong_da_dung===1,$name.' voucher once');
        $o=$orders->first();$admin=User::findOrFail($s['admin_id']);
        foreach(range(1,2)as$attempt){$res=$call('POST','admin/don-hang/update-status',['id'=>$o->id,'trang_thai_don_hang'=>'da_huy'],$admin);$assert($res['status']===200,$name.' cancel '.$attempt);}
        $assert((int)ChiTietSanPham::find($c['variant'])->so_luong_ton===($name==='stock'?1:10),$name.' restored stock once');
        $assert((int)MaGiamGia::find($c['coupon_id'])->so_luong_da_dung===0,$name.' restored voucher once');
    }
    $owner=KhachHang::findOrFail($s['customers'][0]);$other=KhachHang::findOrFail($s['customers'][1]);
    $list=$call('GET','client/don-hang',[],$owner);$assert($list['status']===200,'History MySQL owner list');
    $id=$list['body']['data']['data'][0]['id'];
    $assert($call('GET','client/don-hang/'.$id,[],$owner)['status']===200,'History MySQL owner detail');
    $assert($call('GET','client/don-hang/'.$id,[],$other)['status']===404,'History MySQL other customer 404');
    $assert($call('GET','client/don-hang')['status']===401,'History MySQL Guest 401');
    $assert($call('GET','client/don-hang',[],User::findOrFail($s['admin_id']))['status']===403,'History MySQL Admin 403');
    echo json_encode(['database'=>$s['database'],'cache_prefix'=>$s['cache_prefix'],'passed'=>true,'checks'=>$checks]);exit;
}
throw new RuntimeException('Unknown UAT mode.');
