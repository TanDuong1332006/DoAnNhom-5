import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import {fileURLToPath} from 'node:url';
import {createHash} from 'node:crypto';
const dir=path.dirname(fileURLToPath(import.meta.url));
const base=new URL(process.argv[2]||'http://127.0.0.1:8038');
assert.ok(['localhost','127.0.0.1'].includes(base.hostname),'loopback UAT only');
const output=process.argv[3]||path.join(dir,'verify-result.json');
const manifest=JSON.parse(fs.readFileSync(path.join(dir,'manifest.json'))),checks=[];
async function get(url,expected=200){const start=performance.now();const response=await fetch(new URL(url,base),{headers:{Accept:'application/json',Connection:'close'},signal:AbortSignal.timeout(20000)});const bytes=Buffer.from(await response.arrayBuffer());assert.equal(response.status,expected,`${url}: HTTP ${response.status}`);checks.push({url,status:response.status,ms:Math.round(performance.now()-start),bytes:bytes.length});return {response,bytes};}
let result={passed:false,scope:'real Laravel HTTP on clean local source; no remote machine or browser assertion'};
try{
 const status=JSON.parse((await get('/__demo/status')).bytes);assert.match(status.database,/^sofep_image_restore_[0-9]{8}_[a-f0-9]{12}$/);result.database=status.database;
 const products=JSON.parse((await get('/api/client/san-phams?all=1')).bytes).data;
 assert.ok(Array.isArray(products));assert.equal(products.length,168);
 for(const image of manifest.images){
  const {response,bytes}=await get(image.url);assert.match(response.headers.get('content-type'),/^image\//);assert.equal(createHash('sha256').update(bytes).digest('hex'),image.sha256);assert.equal(bytes.subarray(0,3).toString('hex'),'ffd8ff');
  const product=products.find(p=>Number(p.id)===image.product_id);assert.equal(product.hinh_anh,image.url);
  const detail=JSON.parse((await get(`/api/client/demo/san-pham/${image.product_id}`)).bytes).data.san_pham;
  assert.equal(detail.hinh_anh,image.url);assert.ok(detail.anh_san_phams.some(a=>Number(a.id)===Number(image.gallery_id)&&a.duong_dan_anh===image.url));
 }
 const f=manifest.fixture_variant;
 const fixture=products.find(p=>Number(p.id)===Number(f.product_id)).chi_tiet_san_phams.find(v=>Number(v.id)===Number(f.id));assert.equal(Number(fixture.id_anh_san_pham),Number(f.image_id));
 const product172=products.find(p=>Number(p.id)===172);assert.equal(product172.hinh_anh,'/product-placeholder.svg');
 assert.ok(product172.chi_tiet_san_phams.every(v=>v.id_anh_san_pham===null));
 const placeholder=await get('/product-placeholder.svg');assert.equal(createHash('sha256').update(placeholder.bytes).digest('hex'),manifest.placeholder.sha256);
 await get('/api/product-images/1/illustration-00000000-0000-0000-0000-000000000000.jpg',404);
 await get('/api/product-images/1/invalid.php',404);
 await get('/api/admin/san-pham/1/images',405);
 const guest=await fetch(new URL('/api/admin/san-pham/1/images',base),{method:'POST',headers:{Accept:'application/json',Connection:'close'},signal:AbortSignal.timeout(20000)});assert.equal(guest.status,401);await guest.arrayBuffer();
 const preview=(await get('/__demo/images')).bytes.toString('utf8');assert.ok(!/https?:\/\//.test(preview));
 // Không ngắt mạng hệ điều hành: kiểm tra toàn bộ graph tài nguyên chỉ dùng loopback.
 for(const p of products){assert.ok(p.hinh_anh.startsWith('/'));}
 for(const image of manifest.images)assert.ok(image.url.startsWith('/'));
 result={...result,passed:true,products:products.length,restored_images:manifest.images.length,main_gallery_pairs:manifest.images.length,synthetic_variant_image_verified:true,excluded_original_variant_images:9,placeholder_172:true,expected_404s:2,guest_upload_status:guest.status,all_demo_resource_urls_relative:true,offline:'No external resource in preview/catalog; OS/browser Internet disconnect not performed'};
}catch(e){result.error=e.message;process.exitCode=1;}finally{result.checks=checks;fs.writeFileSync(output,JSON.stringify(result,null,2));console.log(JSON.stringify({...result,checks:checks.length},null,2));}
