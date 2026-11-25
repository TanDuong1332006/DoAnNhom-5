import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {createHash} from 'node:crypto';
const dir=path.dirname(fileURLToPath(import.meta.url)),repo=path.resolve(dir,'../..');
const excluded=JSON.parse(fs.readFileSync(path.join(repo,'docs/image-clone-2026-11-20/excluded-images.json'))).local_files;
const published=JSON.parse(fs.readFileSync(path.join(repo,'docs/product-content/published.json'))),sources=JSON.parse(fs.readFileSync(path.join(repo,'docs/product-content/sources.json')));
const rows=excluded.map(item=>{
 const match=new URL(item.url).pathname.match(/\/api\/product-images\/(\d+)\/(.+)/),file=`products/${match[1]}/${match[2]}`;
 const bytes=fs.readFileSync(path.join(repo,'themmoik27be/storage/app/public',file)),hash=createHash('sha256').update(bytes).digest('hex');
 let provenance=null;
 for(const p of Object.values(published)){const idx=(p.uploaded||[]).findIndex(u=>u.duong_dan_anh===item.url);if(idx>=0){const key=p.sources?.[idx];const source=sources.find(s=>s.key===key);provenance=source?{key,source_url:source.source,recorded_license:source.license,license_url:source.license_url,source_sha256_matches:source.sha256===hash}:null;break;}}
 return {product_id:item.product_id,file,sha256:hash,exists:true,source_record:provenance,decision:'HOLD — không đưa vào gói; PO xác minh quyền phân phối và đúng SKU',limitations:provenance?'Tài liệu nguồn lịch sử có sẵn; chưa xác minh lại giấy phép/ánh xạ với PO.':'Chưa nối được file với bằng chứng nguồn/giấy phép; không suy từ tên illustration hoặc ảnh giống nhau.'};
});
const input=JSON.parse(fs.readFileSync(path.join(dir,'inputs.json'))),manifest=JSON.parse(fs.readFileSync(path.join(input.extracted,'manifest.json')));
for(const r of rows)if(manifest.images.some(i=>i.path===r.file))throw Error('Excluded file accidentally bundled');
fs.writeFileSync(path.join(dir,'20-files-review.json'),JSON.stringify({count:rows.length,with_historical_source_record:rows.filter(r=>r.source_record).length,added_to_package:0,rows},null,2));
console.log(JSON.stringify({count:rows.length,with_historical_source_record:rows.filter(r=>r.source_record).length,added_to_package:0},null,2));
