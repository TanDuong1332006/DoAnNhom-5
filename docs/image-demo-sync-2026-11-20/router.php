<?php
if(PHP_SAPI!=='cli-server'||!in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)){http_response_code(403);exit;}
$input=json_decode(file_get_contents(__DIR__.'/inputs.json'),true);
require $input['extracted'].'/boot.php';
$session=json_decode(file_get_contents(getenv('SOFEP_DEMO_SESSION')),true);
$app=demoBoot($session);
config(['cors.allowed_origins'=>['http://127.0.0.1:5195'],'cors.paths'=>['api/*','sanctum/csrf-cookie']]);
if(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)==='/product-placeholder.svg'){header('Content-Type:image/svg+xml');readfile($input['extracted'].'/product-placeholder.svg');exit;}
$request=Illuminate\Http\Request::capture();$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$response=$kernel->handle($request);$response->send();$kernel->terminate($request,$response);
