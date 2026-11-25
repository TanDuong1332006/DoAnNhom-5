import fs from 'node:fs';import path from 'node:path';import assert from 'node:assert/strict';import {createHash} from 'node:crypto';import {fileURLToPath} from 'node:url';
import {productImageUrl,productImageGallery,imagePlaceholder,isProductImage} from '../../Fe/src/services/productImages.js';
const dir=path.dirname(fileURLToPath(import.meta.url)),read=f=>JSON.parse(fs.readFileSync(path.join(dir,f))),s=read('state.json'),phase=process.argv[2];assert.ok(['before','after'].includes(phase));
const snap=read(phase==='before'?'scenario-before.json':'scenario-after.json'),products=snap.san_phams.rows,gallery=snap.anh_san_phams.rows,variants=snap.chi_tiet_san_phams.rows;
const base='http://127.0.0.1:8044',origin='http://127.0.0.1:5196',hash=b=>createHash('sha256').update(b).digest('hex'),checks=[];
const cache=new Map();async function get(route,expected=200){const url=new URL(route,base);assert.equal(url.origin,base,'Never call old PO host or Internet');if(cache.has(url.href))return cache.get(url.href);let r;try{r=await fetch(url,{headers:{Accept:'application/json',Origin:origin,Connection:'close'},signal:AbortSignal.timeout(45000)});}catch(e){fs.writeFileSync(path.join(dir,'http-error-'+phase+'.json'),JSON.stringify({route:url.pathname,error:e.name,completed:checks},null,2));throw e;}const b=Buffer.from(await r.arrayBuffer());checks.push({route:url.pathname,status:r.status});assert.equal(r.status,expected,url.href);const x={r,b};cache.set(url.href,x);return x;}
const all=JSON.parse((await get('/api/client/san-phams?all=1')).b).data;assert.equal(all.length,168);
const sample=new Set();for(const category of new Set(products.map(p=>p.id_danh_muc)))sample.add(products.find(p=>p.id_danh_muc===category&&p.id!==172).id);sample.add(172);
const displayed=[],details=[];let remoteExcluded=0,localGallery=0;
for(const p of products){const api=all.find(x=>x.id===p.id);assert.equal(api.hinh_anh,p.hinh_anh);const resolved=productImageUrl(p.hinh_anh,base+'/api',true);const isPlaceholder=resolved===imagePlaceholder;
 if(phase==='after'||sample.has(p.id)){const {r,b}=await get(resolved);assert.match(r.headers.get('content-type'),/^image\//);if(!isPlaceholder){assert.equal(r.headers.get('access-control-allow-origin'),origin);const m=new URL(p.hinh_anh,base).pathname.match(/^\/api\/product-images\/(\d+)\/([^/]+)$/);assert.ok(m);assert.equal(hash(b),hash(fs.readFileSync(path.join(s.storage,'products',m[1],m[2]))));}}
 displayed.push({id:p.id,url:resolved,placeholder:isPlaceholder});
 if(phase==='after'||sample.has(p.id)){const detail=JSON.parse((await get('/api/client/demo/san-pham/'+p.id)).b).data.san_pham;const gg=gallery.filter(g=>g.id_san_pham===p.id);assert.deepEqual(detail.anh_san_phams.map(x=>x.id).sort(),gg.map(x=>x.id).sort());const allowed=gg.filter(g=>isProductImage(g.duong_dan_anh,true));const excluded=gg.filter(g=>!isProductImage(g.duong_dan_anh,true));remoteExcluded+=excluded.length;
  if(phase==='after')for(const g of allowed){const url=productImageUrl(g.duong_dan_anh,base+'/api',true);const image=await get(url);assert.match(image.r.headers.get('content-type'),/^image\//);localGallery++;}
  details.push({id:p.id,raw_gallery:gg.length,displayable_gallery:allowed.length,excluded_gallery:excluded.length});
 }
 if((displayed.length%25)===0)fs.writeFileSync(path.join(dir,'http-progress.json'),JSON.stringify({phase,checked_products:displayed.length,requests:checks.length}));
}
assert.equal(displayed.filter(x=>!x.placeholder).length,167);assert.equal(displayed.find(x=>x.id===172).placeholder,true);
const fixture=variants.find(v=>v.id===510),img=gallery.find(g=>g.id===fixture.id_anh_san_pham);assert.equal(img.id_san_pham,fixture.id_san_pham);await get(productImageUrl(img.duong_dan_anh,base+'/api',true));
await get('/api/product-images/1/illustration-00000000-0000-0000-0000-000000000000.jpg',404);await get('/api/product-images/1/not-an-image.php',404);
const out={phase,main_passed:true,products:168,illustrations:167,placeholders:1,http_main_scope:phase==='after'?'All 168':'9 products: 8 categories + ID172',raw_gallery:gallery.length,local_gallery_http_checked:localGallery,blocked_gallery_rows_in_checked_details:remoteExcluded,detail_count:details.length,fixture_variant:{id:510,image_id:fixture.id_anh_san_pham,passed:true},requests:checks.length,external_requests:0,details,checks};
fs.writeFileSync(path.join(dir,'http-'+phase+'.json'),JSON.stringify(out,null,2));console.log(JSON.stringify({...out,details:undefined,checks:undefined}));
