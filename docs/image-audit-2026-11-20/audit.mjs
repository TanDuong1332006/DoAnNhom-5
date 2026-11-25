import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { productImageUrl, imagePlaceholder, isIllustrativeImage } from '../../Fe/src/services/productImages.js';
const dir = path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1'));
const root = path.resolve(dir, '../..');
const read = name => JSON.parse(fs.readFileSync(path.join(dir, name), 'utf8'));
const data = read('catalog.json');
const clone = read('clone-location.json');
const tracked = new Set(execFileSync('git', ['ls-files'], { cwd: root, encoding: 'utf8' }).trim().split(/\r?\n/));
const source = url => !url || /placeholder/.test(url) ? 'placeholder' : /(^|\.)unsplash\.com$/.test(new URL(url, 'http://localhost').hostname) ? 'Unsplash' : /\/(api\/product-images|storage)\//.test(url) ? 'local upload' : /^https?:/.test(url) ? 'external URL' : 'frontend asset';
function fileFor(url) {
 const p = new URL(url, 'http://localhost').pathname;
 const upload = p.match(/^\/api\/product-images\/(\d+)\/([^/]+)$/);
 if (upload) return `themmoik27be/storage/app/public/products/${upload[1]}/${upload[2]}`;
 if (p.startsWith('/storage/')) return 'themmoik27be/storage/app/public/'+p.slice(9);
 return /^https?:/.test(url) ? null : 'Fe/public/'+p.replace(/^\//, '');
}
const orderRefs = new Map(data.order_image_references.map(r => [r.hinh_anh, Number(r.reference_count)]));
const rows = [];
for (const product of data.products) {
 const gallery = data.gallery.filter(g => Number(g.id_san_pham) === Number(product.id));
 const urls = [...new Set([product.hinh_anh, ...gallery.map(g => g.duong_dan_anh)].filter(Boolean))];
 for (const url of urls) {
  const entries = gallery.filter(g => g.duong_dan_anh === url);
  const variants = data.variants.filter(v => entries.some(g => Number(v.id_anh_san_pham) === Number(g.id)));
  const file = fileFor(url), type = source(url), main = url === product.hinh_anh;
  rows.push({ id: product.id, name: product.ten_san_pham, url, type, main, gallery_ids: entries.map(g => g.id).join('|'), variant_ids: variants.map(v => v.id).join('|'), order_references: orderRefs.get(url) || 0,
   using: [main && 'main', entries.length && 'gallery', variants.length && 'variant', orderRefs.has(url) && 'order snapshot'].filter(Boolean).join('|'),
   assessment: isIllustrativeImage(url) ? 'AI minh họa; không phải ảnh chụp SKU; chờ PO cung cấp ảnh' : type === 'Unsplash' ? 'Stock chưa xác minh SKU' : 'Chưa xác minh trực quan với PO',
   action: isIllustrativeImage(url) || type === 'Unsplash' ? 'Thay hiển thị bằng placeholder; giữ file và liên kết' : 'Giữ; chờ PO xác nhận đúng SKU',
   local_file: file, exists_here: file ? fs.existsSync(path.join(root, file)) : null,
   tracked: file ? tracked.has(file) : null, clone_file_exists: file ? fs.existsSync(path.join(clone.path, file)) : null,
   another_machine: file ? (fs.existsSync(path.join(clone.path, file)) ? 'Có file trong HEAD; cần phục vụ đúng route' : 'Thiếu file trong clone HEAD') : 'Phụ thuộc Internet và host bên ngoài',
   offline_display: productImageUrl(url) === imagePlaceholder ? 'placeholder local' : file && fs.existsSync(path.join(clone.path, file)) ? 'Có file; cần server local' : 'Không có ảnh nguồn; cần fallback',
  });
 }
}
// URL chỉ còn trong snapshot đơn: tuyệt đối bảo toàn, không cần thông tin khách hàng.
for (const [url, count] of orderRefs) if (!rows.some(r => r.url === url)) rows.push({ id: '', name: '(chỉ còn trong lịch sử đơn)', url, type: source(url), using: 'order snapshot', order_references: count, action: 'Giữ bắt buộc; không sửa snapshot', another_machine: 'Cần đóng gói file hoặc giữ host nguồn' });
const urls = [...new Set(rows.map(r => r.url))];
const checks = {}; let cursor = 0;
await Promise.all(Array.from({ length: 8 }, async () => {
 while(cursor < urls.length) {
  const url = urls[cursor++];
  try {
   if (!/^https?:/.test(url)) { checks[url] = { status: 'filesystem-only' }; continue; }
   const response = await fetch(url, { signal: AbortSignal.timeout(8000) });
   const bytes = Buffer.from(await response.arrayBuffer());
   const raster = bytes.subarray(0,3).toString('hex') === 'ffd8ff' || bytes.subarray(0,8).toString('hex') === '89504e470d0a1a0a' || (bytes.toString('ascii',0,4) === 'RIFF' && bytes.toString('ascii',8,12) === 'WEBP');
   checks[url] = { status: response.status, raster, bytes: bytes.length };
  } catch (e) { checks[url] = { status: 'unreachable', error: e.name }; }
 }
}));
for (const row of rows) row.online_check = JSON.stringify(checks[row.url]);
const uniqueBySource = Object.fromEntries([...new Set(rows.map(r => r.type))].map(t => [t, new Set(rows.filter(r => r.type === t).map(r => r.url)).size]));
const summary = { captured_at: data.captured_at, tested_at: new Date().toISOString(), products: data.products.length, selling: data.products.filter(p => Number(p.trang_thai) === 1).length, gallery_records: data.gallery.length, variants: data.variants.length, variants_with_image: data.variants.filter(v => v.id_anh_san_pham).length, product_url_rows: rows.length, unique_urls: urls.length, unique_by_source: uniqueBySource,
 illustrative_main_products: data.products.filter(p => isIllustrativeImage(p.hinh_anh)).map(p => p.id), missing_clone_upload_urls: new Set(rows.filter(r => r.type === 'local upload' && !r.clone_file_exists).map(r => r.url)).size,
 http_404: Object.values(checks).filter(c => c.status === 404).length, unreachable: Object.values(checks).filter(c => c.status === 'unreachable').length, valid_online_rasters: Object.values(checks).filter(c => c.status === 200 && c.raster).length,
 clone_commit: clone.commit, placeholder_in_clone: fs.existsSync(path.join(clone.path, 'Fe/public/product-placeholder.svg')), scope: 'HTTP + filesystem clone + resolver policy; browser offline and full Laravel UAT not verified' };
fs.writeFileSync(path.join(dir, 'inventory.json'), JSON.stringify({summary, rows, checks}, null, 2));
const csv = list => { const keys = [...new Set(list.flatMap(Object.keys))]; return '\ufeff'+[keys,...list.map(r => keys.map(k => r[k] ?? ''))].map(r => r.map(v => '"'+String(v).replaceAll('"','""')+'"').join(',')).join('\r\n'); };
fs.writeFileSync(path.join(dir, 'inventory.csv'), csv(rows));
fs.writeFileSync(path.join(dir, 'po-replacements.csv'), csv(rows.filter(r => isIllustrativeImage(r.url) || r.type === 'Unsplash' || r.type === 'external URL')));
fs.writeFileSync(path.join(dir, 'summary.json'), JSON.stringify(summary, null, 2));
console.log(JSON.stringify(summary, null, 2));
