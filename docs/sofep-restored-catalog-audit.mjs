import { writeFileSync } from 'node:fs';
import { createHash } from 'node:crypto';
const api = 'http://127.0.0.1:8000';
const get = async path => {
  const r = await fetch(api + path, {headers:{Accept:'application/json'}, signal:AbortSignal.timeout(20000)});
  if (!r.ok) throw Error(`${path}: HTTP ${r.status}`);
  return r.json();
};
const products = (await get('/api/client/san-phams?all=1')).data;
if (!Array.isArray(products)) throw Error('Expected full catalog');
const images = new Map(), details = [], failures = [];
const register = (url, id, kind) => {
  if (!url) { failures.push({id,kind,reason:'missing URL'}); return; }
  const absolute = new URL(url, api).href;
  if (!images.has(absolute)) images.set(absolute, {url:absolute, references:[]});
  images.get(absolute).references.push({id,kind});
};
let cursor=0;
await Promise.all(Array.from({length:4}, async()=>{
  while(cursor < products.length) {
    const p=products[cursor++]; register(p.hinh_anh,p.id,'primary');
    try {
      const d = (await get('/api/client/san-pham/'+encodeURIComponent(p.slug_san_pham || p.id)+'?presentation=2')).data.san_pham;
      for (const image of d.anh_san_phams || []) register(image.duong_dan_anh,p.id,'gallery');
      details.push({id:p.id,name:p.ten_san_pham,slug:p.slug_san_pham,price:Number(p.gia_ban),sale:Number(p.gia_khuyen_mai),variants:d.chi_tiet_san_phams?.length || 0,gallery:d.anh_san_phams?.length || 0});
    } catch(error) { failures.push({id:p.id,kind:'detail',reason:error.message}); }
  }
}));
const checks=[...images.values()];cursor=0;
await Promise.all(Array.from({length:6},async()=>{
  while(cursor<checks.length){
    const row=checks[cursor++];
    try{
      const response=await fetch(row.url,{signal:AbortSignal.timeout(20000)});
      const bytes=Buffer.from(await response.arrayBuffer()), hex=bytes.subarray(0,8).toString('hex');
      row.status=response.status;
      row.valid=response.ok && (hex.startsWith('ffd8ff') || hex==='89504e470d0a1a0a' || (bytes.toString('ascii',0,4)==='RIFF' && bytes.toString('ascii',8,12)==='WEBP'));
      row.bytes=bytes.length; row.sha256=createHash('sha256').update(bytes).digest('hex');
    }catch(error){row.valid=false;row.reason=error.name;}
  }
}));
const validPrimaries=products.filter(p=>images.get(new URL(p.hinh_anh,api).href)?.valid).length;
const report={checked_at:new Date().toISOString(),api,products:products.length,details_ok:details.length,visible_variants:details.reduce((sum,p)=>sum+p.variants,0),gallery_references:details.reduce((sum,p)=>sum+p.gallery,0),valid_primary:validPrimaries,unique_images:checks.length,valid_images:checks.filter(r=>r.valid).length,failures,invalid_images:checks.filter(r=>!r.valid),samples:details.filter(p=>p.variants>0).slice(0,3),details,images:checks};
writeFileSync(new URL('./sofep-restored-catalog-audit.json',import.meta.url),JSON.stringify(report,null,2));
console.log(JSON.stringify({...report,details:undefined,images:undefined},null,2));
