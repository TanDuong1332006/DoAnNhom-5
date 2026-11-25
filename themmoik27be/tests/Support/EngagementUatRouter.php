<?php
if(PHP_SAPI!=='cli-server' || !in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)){http_response_code(403);exit;}
$manifest=getenv('SOFEP_MYSQL_UAT_SESSION');$session=$manifest&&is_file($manifest)?json_decode(file_get_contents($manifest),true):null;
if(($session['purpose']??'')!=='SOFEP isolated MySQL UAT'){http_response_code(403);exit;}
require __DIR__.'/MySqlUat.php';$app=uatBoot($session['database']);
$root=sys_get_temp_dir().DIRECTORY_SEPARATOR.$session['database'].DIRECTORY_SEPARATOR.'engagement';
config(['filesystems.disks.local.root'=>$root.DIRECTORY_SEPARATOR.'private','cache.default'=>'file','cache.stores.file.path'=>$root.DIRECTORY_SEPARATOR.'cache']);
if(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)==='/__uat/status'){
 header('Content-Type: application/json');echo json_encode(['purpose'=>'SOFEP isolated MySQL UAT','database'=>$session['database'],'module'=>'customer engagement','driver'=>'mysql']);exit;
}
$request=Illuminate\Http\Request::capture();
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$response=$kernel->handle($request);
// Bản sao catalog UAT lưu origin 8012 cũ. Chỉ đổi origin asset trong response UAT,
// không sửa dữ liệu/ảnh và không tác động API/catalog chính 8000.
if($response instanceof Illuminate\Http\JsonResponse && preg_match('~^api/client/(home-data|san-phams|san-pham/|demo/san-pham/)~',$request->path())){
    $data=$response->getData(true);$origin=$request->getSchemeAndHttpHost();
    if(is_array($data)){
        array_walk_recursive($data,function(&$value)use($origin){
            if(is_string($value) && str_starts_with($value,'http://127.0.0.1:8012/api/product-images/'))$value=$origin.substr($value,strlen('http://127.0.0.1:8012'));
        });
        $response->setData($data);
    }
}
$response->send();$kernel->terminate($request,$response);
