import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import {createHash} from 'node:crypto';
import {fileURLToPath} from 'node:url';
import {productImageUrl,imagePlaceholder} from '../../Fe/src/services/productImages.js';
const dir=path.dirname(fileURLToPath(import.meta.url)),repo=path.resolve(dir,'../..');
const read=f=>JSON.parse(fs.readFileSync(f));
const u=read(dir+'/uat.json'),input=read(dir+'/inputs.json'),db=read(dir+'/uat-catalog.json');
const source=read(repo+'/docs/product-photo-approval-2026-11-20/catalog-snapshot.json').data;
const manifest=read(input.extracted+'/manifest.json');
const sha=b=>createHash('sha256').update(b).digest('hex');
const base='http://127.0.0.1:8043',auditOnly=process.argv.includes('--audit-only'),rows=auditOnly?read(dir+'/comparison.json'):[];
async function get(url,status=200){const r=await fetch(new URL(url,base),{headers:{Origin:'http://127.0.0.1:5195',Connection:'close'},signal:AbortSignal.timeout(20000)});const b=Buffer.from(await r.arrayBuffer());assert.equal(r.status,status,url);return {r,b};}
if(!auditOnly){
const all=JSON.parse((await get('/api/client/san-phams?all=1')).b).data;assert.equal(all.length,168);
for(const p of db.san_phams){
 const api=all.find(a=>a.id===p.id);assert.ok(api);assert.equal(api.hinh_anh,p.hinh_anh);
 const resolved=productImageUrl(p.hinh_anh,base+'/api',true),placeholder=resolved===imagePlaceholder;
 const {r,b}=await get(resolved);assert.match(r.headers.get('content-type'),/^image\//);
 const m=manifest.images.find(i=>i.product_id===p.id);
 const physical=m?path.join(u.storage,m.path):input.extracted+'/product-placeholder.svg';
 assert.equal(sha(b),sha(fs.readFileSync(physical)));
 if(m)assert.equal(sha(b),m.sha256);
 const detail=JSON.parse((await get('/api/client/demo/san-pham/'+p.id)).b).data.san_pham;
 const gallery=db.anh_san_phams.filter(g=>g.id_san_pham===p.id);
 assert.deepEqual(detail.anh_san_phams.map(g=>g.id).sort(),gallery.map(g=>g.id).sort());
 for(const g of gallery){const image=await get(g.duong_dan_anh);assert.equal(sha(image.b),sha(b));}
 rows.push({id:p.id,name:p.ten_san_pham,category:p.id_danh_muc,mysql_main:p.hinh_anh,api_main:api.hinh_anh,website_resolved:resolved,gallery_ids:gallery.map(g=>g.id),file:physical,sha256:sha(b),http:r.status,cors:r.headers.get('access-control-allow-origin'),display:placeholder?'Placeholder':'Ảnh minh họa'});
}
const fixture=db.chi_tiet_san_phams.find(v=>v.id===510),g=db.anh_san_phams.find(g=>g.id===fixture.id_anh_san_pham);assert.equal(g.id_san_pham,fixture.id_san_pham);await get(g.duong_dan_anh);
await get('/api/product-images/1/illustration-00000000-0000-0000-0000-000000000000.jpg',404);
assert.equal(productImageUrl('',base+'/api',true),imagePlaceholder);
const held=db.chi_tiet_san_phams.filter(v=>v.id_san_pham===172);assert.equal(held.filter(v=>v.id_anh_san_pham).length,0);
}
assert.ok(Object.values(db.sensitive_counts).every(n=>n===0));
const mainRefs=new Set(source.products.map(p=>p.hinh_anh)),order=new Map(source.order_image_references.map(r=>[r.hinh_anh,Number(r.reference_count)]));
const groups=new Map();for(const g of source.gallery){const key=g.id_san_pham+'|'+g.duong_dan_anh;if(!groups.has(key))groups.set(key,[]);groups.get(key).push(g.id);}
const galleryAudit=source.gallery.map(g=>{
 const variantIds=source.variants.filter(v=>v.id_anh_san_pham===g.id).map(v=>v.id);
 const isMain=mainRefs.has(g.duong_dan_anh),orderRefs=order.get(g.duong_dan_anh)||0;
 const retained=db.anh_san_phams.some(x=>x.id===g.id&&new URL(x.duong_dan_anh,base).pathname===new URL(g.duong_dan_anh,base).pathname);
 return {id:g.id,product_id:g.id_san_pham,url:g.duong_dan_anh,main_reference:isMain,variant_ids:variantIds,order_references:orderRefs,duplicate_ids:groups.get(g.id_san_pham+'|'+g.duong_dan_anh),in_demo:retained,decision:retained?'Giữ trong demo':variantIds.length||orderRefs||isMain?'Bảo toàn tham chiếu nguồn; không xóa chính':'Đề xuất bỏ khỏi catalog demo; vẫn còn tham chiếu gallery chính'};
});
const candidates=galleryAudit.filter(g=>!g.in_demo&&!g.main_reference&&!g.variant_ids.length&&!g.order_references);
const uniqueGroups=[...groups.values()],duplicateExtra=uniqueGroups.reduce((n,g)=>n+Math.max(g.length-1,0),0);
const preservation=source.order_image_references.map(r=>({...r,main_reference:mainRefs.has(r.hinh_anh),gallery_ids:source.gallery.filter(g=>g.duong_dan_anh===r.hinh_anh).map(g=>g.id)}));
const fileChecks=manifest.images.map(i=>{const file=path.join(repo,'themmoik27be/storage/app/public',i.path);return {file:i.path,unchanged:fs.existsSync(file)&&sha(fs.readFileSync(file))===i.sha256};});assert.ok(fileChecks.every(x=>x.unchanged));
const summary={passed:true,products:rows.length,illustrations:rows.filter(r=>r.display==='Ảnh minh họa').length,placeholders:rows.filter(r=>r.display==='Placeholder').length,restored_images:manifest.images.length,gallery:db.anh_san_phams.length,variant_fixture:{id:510,image_id:manifest.fixture_variant.image_id,http:200,synthetic:true},original_id172_variant_links_restored:false,source_gallery:source.gallery.length,source_duplicate_extra_rows:duplicateExtra,source_non_demo_rows:galleryAudit.filter(g=>!g.in_demo).length,cleanup_demo_candidates:candidates.length,order_urls:preservation.length,order_references:preservation.reduce((n,r)=>n+Number(r.reference_count),0),sensitive_counts:db.sensitive_counts,offline:'Chưa ngắt Internet thực tế; CSP chỉ cho local',main_files_unchanged:fileChecks.length};
for(const [name,data] of Object.entries({'comparison':rows,'gallery-audit':galleryAudit,'demo-cleanup-candidates':candidates,'order-preservation':preservation,'http-results':summary}))fs.writeFileSync(dir+'/'+name+'.json',JSON.stringify(data,null,2));
const esc=s=>String(s).replaceAll('|','/');
fs.writeFileSync(dir+'/COMPARISON.md','# Website ↔ MySQL ↔ Laravel ↔ Storage UAT\n\nToàn bộ 168 sản phẩm; website_resolved là kết quả resolver. Quan sát browser lấy mẫu riêng trong báo cáo.\n\n|ID|Tên|MySQL = API|Website|HTTP|SHA-256|\n|---|---|---|---|---|---|\n'+rows.map(r=>`|${r.id}|${esc(r.name)}|${r.mysql_main}|${r.display}|${r.http}|${r.sha256}|`).join('\n'));
fs.writeFileSync(dir+'/CLEANUP_CANDIDATES.md','# Đề xuất loại khỏi bản catalog demo — chưa xóa nguồn\n\nCác dòng dưới không là ảnh chính, không có FK biến thể hoặc URL snapshot đơn theo catalog đã đọc. Chúng vẫn được bảng gallery chính tham chiếu; không gọi là file mồ côi. Chờ PO duyệt mọi thao tác xóa. Các dòng có tham chiếu cần giữ nằm trong gallery-audit.json.\n\n|ID ảnh|ID sản phẩm|URL|Các ID cùng sản phẩm và URL|\n|---|---|---|---|\n'+candidates.map(r=>`|${r.id}|${r.product_id}|${r.url}|${r.duplicate_ids.join(',')}|`).join('\n'));
console.log(JSON.stringify(summary));
