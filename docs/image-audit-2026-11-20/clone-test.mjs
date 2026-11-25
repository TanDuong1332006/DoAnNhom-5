import fs from 'node:fs';
import path from 'node:path';
import http from 'node:http';
import assert from 'node:assert/strict';
import { fileURLToPath } from 'node:url';
import { productImageUrl, imagePlaceholder, isIllustrativeImage } from '../../Fe/src/services/productImages.js';
const root = path.dirname(fileURLToPath(import.meta.url));
const {path: clone} = JSON.parse(fs.readFileSync(path.join(root, 'clone-location.json')));
const catalog = JSON.parse(fs.readFileSync(path.join(root, 'catalog.json')));
// Chỉ phục vụ file source sạch. Không chạy ứng dụng hoặc đọc storage máy PO.
const server = http.createServer((req, res) => {
 const name = req.url === '/product-placeholder.svg' ? 'Fe/public/product-placeholder.svg' : req.url?.replace(/^\//, '');
 const target = path.resolve(clone, name || '');
 if (!target.startsWith(path.resolve(clone)+path.sep) || !fs.existsSync(target) || !fs.statSync(target).isFile()) {res.writeHead(404).end(); return;}
 res.writeHead(200); fs.createReadStream(target).pipe(res);
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
try {
 const origin = `http://127.0.0.1:${server.address().port}`;
 const placeholder = await fetch(origin+imagePlaceholder);
 assert.equal(placeholder.status, 200); assert.match(await placeholder.text(), /CHƯA CÓ ẢNH/);
 const uploads = [...new Set(catalog.gallery.map(g => g.duong_dan_anh).filter(u => /\/api\/product-images\//.test(u)))];
 const missing = [];
 for (const u of uploads) {
  const match = new URL(u).pathname.match(/\/api\/product-images\/(\d+)\/(.+)/);
  const relative = `themmoik27be/storage/app/public/products/${match[1]}/${match[2]}`;
  const response = await fetch(origin+'/'+relative);
  if (response.status === 404) missing.push(u);
 }
 const illustration = catalog.products.filter(p => isIllustrativeImage(p.hinh_anh));
 for (const p of illustration) assert.equal(productImageUrl(p.hinh_anh), imagePlaceholder);
 // Mô phỏng resource loader mất Internet, loopback vẫn hoạt động.
 const offlineLoad = async url => {
  if (/^https?:/.test(url) && new URL(url).origin !== origin) throw new Error('External network blocked by test');
  return fetch(new URL(url, origin));
 };
 assert.equal((await offlineLoad(imagePlaceholder)).status, 200);
 const remote = catalog.gallery.find(g => g.duong_dan_anh.includes('unsplash.com')).duong_dan_anh;
 await assert.rejects(() => offlineLoad(remote), /External network blocked/);
 const result = { test: 'isolated clean HEAD file server and patched resolver; no browser or Laravel HTTP route', clone, upload_urls: uploads.length, missing_upload_urls: missing, placeholder_http: 200, illustration_main_uses_placeholder: illustration.length, offline_placeholder_http: 200, offline_external_blocked: true, warning: 'Offline is resource-loader simulation, not OS Internet disconnect or browser rendering' };
 fs.writeFileSync(path.join(root, 'clone-test.json'), JSON.stringify(result, null, 2));
 console.log(JSON.stringify({...result, missing_upload_urls: missing.length}, null, 2));
} finally { await new Promise(resolve => server.close(resolve)); }
