import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createHash } from 'node:crypto';
const dir = path.dirname(fileURLToPath(import.meta.url));
const repo = path.resolve(dir, '../..');
const data = JSON.parse(fs.readFileSync(path.join(dir, 'catalog.json')));
const inventory = JSON.parse(fs.readFileSync(path.join(dir, 'inventory.json')));
const storage = path.join(repo, 'themmoik27be/storage/app/public');
const urls = new Map(inventory.rows.filter(r => r.local_file).map(r => [path.resolve(repo,r.local_file),r.url]));
const orderUrls = new Map(data.order_image_references.map(r => [r.hinh_anh, Number(r.reference_count)]));
const orderFiles = new Map();
for (const [url,count] of orderUrls) {
 const p = new URL(url, 'http://localhost').pathname;
 const m = p.match(/^\/api\/product-images\/(\d+)\/([^/]+)$/);
 if (m) orderFiles.set(path.resolve(storage,'products',m[1],m[2]), count);
 else if (p.startsWith('/storage/')) orderFiles.set(path.resolve(storage,p.slice(9)),count);
}
const files = [];
function walk(folder) {
 if (!fs.existsSync(folder)) return;
 for (const item of fs.readdirSync(folder,{withFileTypes:true})) {
  const file=path.join(folder,item.name);
  if(item.isDirectory()) walk(file);
  else if (/\.(jpe?g|png|webp|gif|svg|avif)$/i.test(item.name)) {
   const catalogRef=urls.get(file), orders=orderFiles.get(file)||0;
   files.push({file:path.relative(repo,file).replaceAll('\\','/'),sha256:createHash('sha256').update(fs.readFileSync(file)).digest('hex'),bytes:fs.statSync(file).size,catalog_url:catalogRef||null,order_references:orders,decision:orders?'Bắt buộc giữ: lịch sử đơn':catalogRef?'Giữ: catalog/biến thể':'Giữ cách ly: chưa có tham chiếu trong phạm vi kiểm kê; KHÔNG kết luận được phép xóa'});
  }
 }
}
walk(storage);
const historyOnly=data.order_image_references.filter(r=>!inventory.rows.some(i=>i.url===r.hinh_anh));
fs.writeFileSync(path.join(dir,'storage-preservation.json'),JSON.stringify({files,total_files:files.length,unreferenced_in_catalog_or_order_scope:files.filter(f=>!f.catalog_url&&!f.order_references).length,history_only_urls:historyOnly,scope:'storage/app/public only; refs san_phams, anh_san_phams, chi_tiet_san_phams, chi_tiet_don_hangs; other consumers not ruled out'},null,2));
// Không chứa bảng khách hàng/đơn hàng; fixture để phân phối chỉ dùng placeholder.
fs.writeFileSync(path.join(dir,'demo-catalog-portable.json'),JSON.stringify({purpose:'portable image demo; placeholder until PO approval',products:data.products.map(p=>({...p,hinh_anh:'/product-placeholder.svg'})),gallery:[],variants:data.variants},null,2));
const stats={storage_files:files.length,unreferenced:files.filter(f=>!f.catalog_url&&!f.order_references).length,history_unique_urls:orderUrls.size,history_references:[...orderUrls.values()].reduce((a,b)=>a+b,0),history_only_urls:historyOnly.length,illustrative_upload_urls:new Set(inventory.rows.filter(r=>r.url.includes('/illustration-')).map(r=>r.url)).size};
fs.writeFileSync(path.join(dir,'preservation-summary.json'),JSON.stringify(stats,null,2));
console.log(JSON.stringify(stats,null,2));
