<?php
if(PHP_SAPI!=='cli-server'||!in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)){http_response_code(403);exit;}
require __DIR__.'/boot.php';
$file=getenv('SOFEP_DEMO_SESSION');if(!$file||!is_file($file))throw new RuntimeException('Missing isolated session');
$session=json_decode(file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
$app=demoBoot($session);
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if($path==='/__demo/status'){
 header('Content-Type: application/json');echo json_encode(['purpose'=>$session['purpose'],'database'=>$session['database'],'storage_root'=>config('filesystems.disks.public.root'),'external_services'=>'disabled']);exit;
}
if($path==='/product-placeholder.svg') {header('Content-Type: image/svg+xml');readfile(__DIR__.'/product-placeholder.svg');exit;}
if($path==='/__demo/images'){
 $manifest=json_decode(file_get_contents(__DIR__.'/manifest.json'),true);
 header('Content-Type: text/html; charset=utf-8');
 header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'unsafe-inline'");
 echo '<!doctype html><html lang="vi"><meta charset="utf-8"><title>SOFEP — thư viện ảnh UAT</title><style>body{font:16px system-ui;background:#f6f4ef;color:#272421;margin:24px}main{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:18px}figure{margin:0;background:white;padding:12px}img{width:100%;aspect-ratio:1;object-fit:contain}figcaption{font-size:13px}h1{font-size:26px}</style><h1>SOFEP · thư viện ảnh demo cô lập</h1><p>Ảnh AI để kiểm tra khôi phục file, không phải ảnh chụp đúng SKU. Giao diện bán hàng tiếp tục dùng placeholder cho ảnh chưa xác minh.</p><main>';
 foreach($manifest['images'] as $image){$url=htmlspecialchars($image['url'],ENT_QUOTES,'UTF-8');$id=(int)$image['product_id'];echo '<figure><img loading="lazy" src="'.$url.'" alt="Ảnh minh họa demo của sản phẩm '.$id.'"><figcaption>ID '.$id.' · Ảnh AI minh họa UAT</figcaption></figure>';}
 echo '</main></html>';exit;
}
$request=Illuminate\Http\Request::capture();$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$response=$kernel->handle($request);$response->send();$kernel->terminate($request,$response);
