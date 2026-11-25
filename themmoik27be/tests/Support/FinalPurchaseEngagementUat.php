<?php

require __DIR__.'/SecurityBrowserBoot.php';
use Illuminate\Support\Facades\Hash;
use App\Models\{User, KhachHang, SanPham, ChiTietSanPham};

$pointer=sys_get_temp_dir().'/sofep-final-uat-session-path.txt';
if(PHP_SAPI==='cli' && ($argv[1]??'')==='prepare') {
    $old=json_decode(file_get_contents(trim(file_get_contents(sys_get_temp_dir().'/sofep-sprint34-session-path.txt'))),true);
    $tag=bin2hex(random_bytes(4));$root=sys_get_temp_dir().'/sofep-final-uat-'.$tag;
    mkdir($root,0700);mkdir($root.'/private',0700);mkdir($root.'/public',0700);
    $s=['purpose'=>'SOFEP N5-147 browser UAT','task'=>'Final142143','database'=>$old['database'],'root'=>$root,'cache_prefix'=>'final142143-'.$tag.':'];
    securityBrowserBoot($s);
    $s['password']=bin2hex(random_bytes(12));$s['accounts']=[];
    foreach(['admin','customer','other']as$role){
        $email='final-'.$tag.'-'.$role.'@sofep.example.test';
        $actor=$role==='admin'?User::create(['name'=>'Admin final UAT','email'=>$email,'password'=>Hash::make($s['password'])])
            :KhachHang::create(['ho_va_ten'=>'Final UAT '.$role,'email'=>$email,'mat_khau'=>Hash::make($s['password']),'role'=>'customer','is_kich_hoat'=>1,'is_khoa'=>0]);
        $s['accounts'][$role]=['id'=>$actor->id,'email'=>$email];
    }
    $p=SanPham::findOrFail($old['cases']['browser']['product'])->replicate();
    $p->ma_san_pham='FINAL'.$tag;$p->ten_san_pham='Áo Final UAT '.$tag;$p->slug_san_pham='final-uat-'.$tag;
    $p->hinh_anh='http://127.0.0.1:8030/__uat/image.svg';$p->so_luong_ton=30;$p->save();
    $v=ChiTietSanPham::findOrFail($old['cases']['browser']['variant'])->replicate();$v->id_san_pham=$p->id;$v->so_luong_ton=30;$v->save();
    $s+=['tag'=>$tag,'product'=>$p->id,'variant'=>$v->id,'slug'=>$p->slug_san_pham];
    file_put_contents($root.'/session.json',json_encode($s,JSON_PRETTY_PRINT));file_put_contents($pointer,$root.'/session.json');
    echo json_encode(['database'=>$s['database'],'cache_prefix'=>$s['cache_prefix'],'session'=>$root.'/session.json','slug'=>$s['slug']]);exit;
}
$s=json_decode(file_get_contents(trim(file_get_contents($pointer))),true);
if(($s['task']??'')!=='Final142143')throw new RuntimeException('Wrong UAT manifest.');
$app=securityBrowserBoot($s);config(['cors.allowed_origins'=>['http://127.0.0.1:5190']]);
if(PHP_SAPI==='cli-server'){
    if(!in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)){http_response_code(403);exit;}
    $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
    if($path==='/__uat/status'){header('Content-Type: application/json');echo json_encode(['task'=>$s['task'],'database'=>$s['database'],'cache_prefix'=>$s['cache_prefix'],'connections'=>array_keys(config('database.connections')),'external_mail'=>false]);exit;}
    if($path==='/__uat/image.svg'){header('Content-Type: image/svg+xml');echo '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="500"><rect width="400" height="500" fill="#dcc"/><text x="30" y="250" font-size="28">SOFEP UAT</text></svg>';exit;}
    $r=Illuminate\Http\Request::capture();$k=$app->make(Illuminate\Contracts\Http\Kernel::class);$res=$k->handle($r);$res->send();$k->terminate($r,$res);
    file_put_contents($s['root'].'/http.jsonl',json_encode(['at'=>gmdate(DATE_ATOM),'method'=>$r->method(),'path'=>$r->path(),'status'=>$res->getStatusCode()]).PHP_EOL,FILE_APPEND|LOCK_EX);exit;
}
if(($argv[1]??'')==='inspect'){
    echo json_encode(['database'=>$s['database'],'stock'=>ChiTietSanPham::findOrFail($s['variant'])->so_luong_ton,
        'orders'=>App\Models\DonHang::whereIn('id_khach_hang',array_column(array_intersect_key($s['accounts'],array_flip(['customer','other'])),'id'))->get(['id','ma_don_hang','tong_thanh_toan','trang_thai_don_hang','trang_thai_thanh_toan']),
        'vouchers'=>App\Models\MaGiamGia::where('ma_code','like','F'.$s['tag'].'%')->get(['id','ma_code','so_luong_da_dung'])]);exit;
}
throw new RuntimeException('Unknown UAT mode.');
