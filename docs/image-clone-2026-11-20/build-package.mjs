import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {createHash} from 'node:crypto';
const root=path.dirname(fileURLToPath(import.meta.url)), repo=path.resolve(root,'../..');
const data=JSON.parse(fs.readFileSync(path.join(root,'source-catalog.json')));
const provenance=JSON.parse(fs.readFileSync(path.join(repo,'docs/product-content/full-catalog/manifest.json')));
const output=path.join(root,'portable-demo');
if(fs.existsSync(output))throw Error('Refuse overwrite; move previous package explicitly first');
fs.mkdirSync(output,{recursive:true});
const sha=b=>createHash('sha256').update(b).digest('hex');
const approved=new Map(provenance.map(p=>[p.published.url,p]));
const manifest=[], physical=[],excluded=[];
const references=new Set([...data.catalog.san_phams.map(p=>p.hinh_anh),...data.catalog.anh_san_phams.map(a=>a.duong_dan_anh)]);
for(const url of references){
 const match=new URL(url,'http://localhost').pathname.match(/^\/api\/product-images\/(\d+)\/((?:illustration-)?[a-f0-9-]{36}\.(?:jpg|jpeg|png|webp))$/i);
 if(!match)continue;
 const relative=`products/${match[1]}/${match[2]}`;
 const file=path.join(repo,'themmoik27be/storage/app/public',relative);
 const exists=fs.existsSync(file), bytes=exists?fs.readFileSync(file):null;
 physical.push({url,file:relative,exists,sha256:bytes?sha(bytes):null});
 const p=approved.get(url);
 if(!exists||!p||p.sha256!==sha(bytes)||p.published.sha256!==sha(bytes)){
  excluded.push({url,product_id:Number(match[1]),reason:!exists?'missing physical file':!p?'not in first-party AI provenance manifest; permission/SKU verification pending':'checksum mismatch'});continue;
 }
 const destination=path.join(output,'media',relative);fs.mkdirSync(path.dirname(destination),{recursive:true});fs.writeFileSync(destination,bytes);
 manifest.push({product_id:Number(match[1]),gallery_id:p.published.main_id,path:relative,url:`/api/product-images/${match[1]}/${match[2]}`,bytes:bytes.length,sha256:sha(bytes),source:'OpenAI built-in image_gen',usage:'Local UAT demo illustration only; not a photograph or a verified representation of actual SKU',kind:'illustration',commercial_sku_approved:false});
}
const catalog=structuredClone(data.catalog),galleryIds=new Set(manifest.map(m=>Number(m.gallery_id))),byProduct=new Map(manifest.map(m=>[m.product_id,m]));
for(const p of catalog.san_phams)p.hinh_anh=byProduct.get(Number(p.id))?.url||'/product-placeholder.svg';
catalog.anh_san_phams=catalog.anh_san_phams.filter(a=>galleryIds.has(Number(a.id))).map(a=>({...a,duong_dan_anh:byProduct.get(Number(a.id_san_pham)).url}));
const excludedVariants=[];
for(const v of catalog.chi_tiet_san_phams)if(v.id_anh_san_pham&&!galleryIds.has(Number(v.id_anh_san_pham))){excludedVariants.push({variant_id:v.id,product_id:v.id_san_pham,excluded_gallery_id:v.id_anh_san_pham,uat_fallback:'null image reference; placeholder or product main'});v.id_anh_san_pham=null;}
// Fixture mới chỉ nằm trong database demo, xác minh quan hệ ảnh biến thể mà không giả nhận màu hàng thật.
const first=manifest[0], fixtureId=Math.max(...catalog.chi_tiet_san_phams.map(v=>Number(v.id)))+1;
catalog.chi_tiet_san_phams.push({id:fixtureId,id_san_pham:first.product_id,id_mau_sac:null,id_kich_thuoc:null,gia_chenh_lech:0,so_luong_ton:1,trang_thai:1,id_anh_san_pham:first.gallery_id});
const placeholder=fs.readFileSync(path.join(repo,'Fe/public/product-placeholder.svg'));
fs.writeFileSync(path.join(output,'product-placeholder.svg'),placeholder);
fs.writeFileSync(path.join(output,'catalog.json'),JSON.stringify(catalog,null,2));
fs.writeFileSync(path.join(output,'manifest.json'),JSON.stringify({version:1,purpose:'SOFEP portable local image demo',images:manifest,placeholder:{path:'product-placeholder.svg',sha256:sha(placeholder)},catalog_sha256:sha(fs.readFileSync(path.join(output,'catalog.json'))),fixture_variant:{id:fixtureId,product_id:first.product_id,image_id:first.gallery_id,synthetic:true},warning:'Main/gallery AI images are for the isolated diagnostic preview. Production frontend must keep its unverified-image placeholder policy.'},null,2));
for(const name of ['boot.php','import.php','server.php','verify.mjs','README.md'])fs.copyFileSync(path.join(root,name),path.join(output,name));
fs.writeFileSync(path.join(root,'physical-files.json'),JSON.stringify({files:physical,found:physical.filter(f=>f.exists).length,missing:physical.filter(f=>!f.exists)},null,2));
fs.writeFileSync(path.join(root,'excluded-images.json'),JSON.stringify({local_files:excluded,external_urls:[...references].filter(u=>/^https?:/.test(u)&&!new URL(u).hostname.match(/^(localhost|127\.0\.0\.1)$/)),excluded_variant_links:excludedVariants},null,2));
const summary={physical:physical.length,found:physical.filter(f=>f.exists).length,bundled:manifest.length,excluded_local:excluded.length,products:catalog.san_phams.length,gallery:catalog.anh_san_phams.length,variants:catalog.chi_tiet_san_phams.length,original_variant_links_excluded:excludedVariants.length,fixture_variant_id:fixtureId,package_bytes:manifest.reduce((s,m)=>s+m.bytes,0)};
fs.writeFileSync(path.join(root,'package-summary.json'),JSON.stringify(summary,null,2));console.log(JSON.stringify(summary,null,2));
