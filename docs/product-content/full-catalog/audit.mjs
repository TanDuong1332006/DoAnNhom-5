import { readFileSync, writeFileSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { productImageGallery } from '../../../Fe/src/services/productImages.js';

const stage = process.argv[2] || 'before';
if (!['before', 'after'].includes(stage)) throw Error('Use before or after');
const root = new URL('./', import.meta.url);
const products = JSON.parse(readFileSync(new URL(stage + '.json', root), 'utf8'));
const urls = [...new Set(products.flatMap(p => [p.hinh_anh, ...p.anh_san_phams.map(a => a.duong_dan_anh)]).filter(Boolean))];
const checks = {};
let cursor = 0;
await Promise.all(Array.from({ length: 6 }, async () => {
  while (cursor < urls.length) {
    const url = urls[cursor++];
    try {
      const response = await fetch(url, { signal: AbortSignal.timeout(20000) });
      const bytes = Buffer.from(await response.arrayBuffer());
      const signature = bytes.subarray(0, 12).toString('hex');
      const image = signature.startsWith('89504e470d0a1a0a') || signature.startsWith('ffd8ff') || (bytes.toString('ascii', 0, 4) === 'RIFF' && bytes.toString('ascii', 8, 12) === 'WEBP');
      checks[url] = { status: response.status, image, bytes: bytes.length, type: response.headers.get('content-type'), sha256: createHash('sha256').update(bytes).digest('hex'), valid: response.status === 200 && image };
    } catch (error) { checks[url] = { valid: false, error: error.name }; }
  }
}));
const inventory = products.map(p => {
  const all = [p.hinh_anh, ...p.anh_san_phams.map(a => a.duong_dan_anh)].filter(Boolean);
  const visible = productImageGallery(p);
  return { id: p.id, sku: p.ma_san_pham, name: p.ten_san_pham, category: p.danh_muc?.ten_danh_muc, selling: p.trang_thai === 1, main: p.hinh_anh, gallery: all, duplicate_links: all.length - new Set(all).size, unverified_stock: all.filter(u => /unsplash\.com/.test(u)), valid_visible: visible.filter(u => checks[u]?.valid), main_valid: visible.includes(p.hinh_anh) && !!checks[p.hinh_anh]?.valid };
});
const selling = inventory.filter(p => p.selling);
const summary = { total: products.length, selling: selling.length, selling_with_valid_main: selling.filter(p => p.main_valid).length, selling_with_visible_image: selling.filter(p => p.valid_visible.length).length, missing: selling.filter(p => !p.main_valid).map(p => ({ id: p.id, sku: p.sku, name: p.name })), checked_urls: urls.length, checked_at: new Date().toISOString() };
writeFileSync(new URL(stage + '-audit.json', root), JSON.stringify({ summary, inventory, checks }, null, 2));
console.log(JSON.stringify({ ...summary, missing: summary.missing.length }, null, 2));
