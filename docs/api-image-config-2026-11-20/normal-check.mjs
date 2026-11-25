import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import assert from 'node:assert/strict';
const dir=path.dirname(fileURLToPath(import.meta.url)),input=JSON.parse(fs.readFileSync(path.join(dir,'uat.json'))),secret=JSON.parse(fs.readFileSync(path.join(input.root,'fixture-private.json')));
const checks=[];const base='http://127.0.0.1:8042';
async function get(route,expected=200,options={}){const r=await fetch(base+route,{headers:{Accept:'application/json',Origin:'http://127.0.0.1:5194',Connection:'close',...options.headers},...options,signal:AbortSignal.timeout(20000)});const bytes=Buffer.from(await r.arrayBuffer());checks.push({route,status:r.status,expected});assert.equal(r.status,expected);return{r,bytes};}
const products=JSON.parse((await get('/api/client/san-phams?all=1')).bytes).data;assert.equal(products.length,168);
const detail=JSON.parse((await get('/api/client/demo/san-pham/1')).bytes).data.san_pham;assert.ok(detail.anh_san_phams.length);
const image=await get(detail.hinh_anh);assert.match(image.r.headers.get('content-type'),/^image\//);assert.equal(image.r.headers.get('access-control-allow-origin'),'http://127.0.0.1:5194');
await get('/product-placeholder.svg');
await get('/api/product-images/1/illustration-00000000-0000-0000-0000-000000000000.jpg',404);
await get('/api/product-images/1/invalid.php',404);
const login=await get('/api/dang-nhap',200,{method:'POST',headers:{Accept:'application/json','Content-Type':'application/json',Origin:'http://127.0.0.1:5194',Connection:'close'},body:JSON.stringify({email:secret.customer,mat_khau:secret.password})});assert.equal(JSON.parse(login.bytes).nguoi_dung.role,'customer');
const isolation=JSON.parse(fs.readFileSync(path.join(dir,'normal-server-isolation.json')));assert.ok(isolation.passed);
fs.writeFileSync(path.join(dir,'normal-server-results.json'),JSON.stringify({passed:true,server:'php artisan serve --host=127.0.0.1 --port=8042 --no-reload',database:isolation.database,storage:isolation.storage,checks},null,2));console.log(JSON.stringify({passed:true,checks:checks.length}));
