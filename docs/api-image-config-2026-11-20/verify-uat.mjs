import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {createHash,randomUUID} from 'node:crypto';
import assert from 'node:assert/strict';
import {productImageUrl,imagePlaceholder,productImageGallery} from '../../Fe/src/services/productImages.js';
const dir=path.dirname(fileURLToPath(import.meta.url)),input=JSON.parse(fs.readFileSync(path.join(dir,'uat.json'))),base='http://127.0.0.1:8042';
const secret=JSON.parse(fs.readFileSync(path.join(input.root,'fixture-private.json'))),manifest=JSON.parse(fs.readFileSync(path.join(input.bundle,'manifest.json'))),checks=[];
async function call(label,route,status=200,body,token,method=body?'POST':'GET',headers={}){
 const r=await fetch(base+route,{method,headers:{Accept:'application/json',Origin:'http://127.0.0.1:5194',Connection:'close',...(body?{'Content-Type':'application/json'}:{}),...(token?{Authorization:'Bearer '+token}:{}),...headers},body:body?JSON.stringify(body):undefined,signal:AbortSignal.timeout(20000)});
 const bytes=Buffer.from(await r.arrayBuffer());checks.push({label,route,expected:status,actual:r.status,passed:r.status===status});assert.equal(r.status,status,label+': '+bytes.toString().slice(0,120));return{response:r,bytes,data:r.headers.get('content-type')?.includes('json')?JSON.parse(bytes):null};
}
let result={passed:false};
try{
 const status=(await call('isolation','/__demo/status')).data;assert.equal(status.database,input.database);assert.equal(path.normalize(status.storage_root),path.normalize(input.storage));
 for(const origin of ['http://127.0.0.1:5194','http://127.0.0.1:5173']){
  const r=await call('CORS allowed '+origin,'/api/client/san-phams',204,undefined,undefined,'OPTIONS',{Origin:origin,'Access-Control-Request-Method':'POST','Access-Control-Request-Headers':'authorization,content-type,idempotency-key'});assert.equal(r.response.headers.get('access-control-allow-origin'),origin);
 }
 const denied=await call('CORS denied','/api/client/san-phams',204,undefined,undefined,'OPTIONS',{Origin:'http://untrusted.example','Access-Control-Request-Method':'GET'});assert.notEqual(denied.response.headers.get('access-control-allow-origin'),'http://untrusted.example');
 const products=(await call('catalog','/api/client/san-phams?all=1')).data.data;assert.equal(products.length,168);
 assert.equal(products.filter(p=>productImageUrl(p.hinh_anh,base+'/api')===imagePlaceholder).length,168);
 for(const image of manifest.images){
  const r=await call('image '+image.product_id,image.url);assert.equal(createHash('sha256').update(r.bytes).digest('hex'),image.sha256);
 }
 const detail=(await call('gallery and variant','/api/client/demo/san-pham/1')).data.data.san_pham;
 assert.ok(detail.anh_san_phams.some(i=>Number(i.id)===436));assert.ok(detail.chi_tiet_san_phams.some(v=>Number(v.id)===510&&Number(v.id_anh_san_pham)===436));assert.deepEqual(productImageGallery(detail),[]);
 await call('placeholder',imagePlaceholder);await call('missing image','/api/product-images/1/illustration-00000000-0000-0000-0000-000000000000.jpg',404);
 const customer=(await call('customer login','/api/dang-nhap',200,{email:secret.customer,mat_khau:secret.password})).data;
 const admin=(await call('admin login','/api/dang-nhap',200,{email:secret.admin,mat_khau:secret.password})).data;
 assert.equal(customer.nguoi_dung.role,'customer');assert.equal(admin.nguoi_dung.role,'admin');
 await call('customer session','/api/kiem-tra-dang-nhap',200,undefined,customer.token);
 await call('admin allowed','/api/admin/san-pham/get-data',200,undefined,admin.token);
 await call('customer admin forbidden','/api/admin/san-pham/get-data',403,undefined,customer.token);
 await call('seller admin forbidden','/api/admin/san-pham/get-data',403,undefined,secret.seller_token);
 await call('seller catalog forbidden','/api/client/san-phams',403,undefined,secret.seller_token);
 await call('guest checkout denied','/api/client/dat-hang',401,{});
 await call('admin checkout denied','/api/client/dat-hang',403,{},admin.token);
 await call('seller checkout denied','/api/client/dat-hang',403,{},secret.seller_token);
 await call('customer upload denied','/api/admin/san-pham/1/images',403,{},customer.token);
 await call('seller upload denied','/api/admin/san-pham/1/images',403,{},secret.seller_token);
 await call('admin upload validation','/api/admin/san-pham/1/images',422,{},admin.token);
 const payload={ho_ten:'Fixture UAT',so_dien_thoai:'0900000000',dia_chi_giao:'Dia chi thu nghiem UAT',phuong_thuc_thanh_toan:'COD',items:[{id:1,id_chi_tiet_san_pham:1,quantity:1}]};
 await call('cart quote','/api/client/kiem-tra-voucher',200,payload,customer.token);
 const order=(await call('COD checkout UAT','/api/client/dat-hang',201,payload,customer.token,'POST',{'Idempotency-Key':randomUUID()})).data;
 await call('own orders','/api/client/don-hang',200,undefined,customer.token);
 result={passed:true,database:input.database,fe_origin:'http://127.0.0.1:5194',be_origin:base,images_http:manifest.images.length,products:products.length,policy_placeholders:168,sku_approved_images:0,synthetic_variant_checked:true,login_roles:['admin','customer'],seller_denied:true,cors_allowed_and_denied:true,checkout_cod_201:!!order.don_hang?.id,scope:'UAT only; one synthetic COD order; no production data or external payment'};
}catch(e){result.error=e.message;process.exitCode=1;}finally{fs.writeFileSync(path.join(dir,'uat-results.json'),JSON.stringify({...result,checks},null,2));console.log(JSON.stringify({...result,checks:checks.length},null,2));}
