import assert from 'node:assert/strict';
import { readFileSync, writeFileSync, readdirSync } from 'node:fs';
import { productImageGallery } from '../../../Fe/src/services/productImages.js';

const root = new URL('./', import.meta.url);
const before = JSON.parse(readFileSync(new URL('before.json', root), 'utf8'));
const after = JSON.parse(readFileSync(new URL('after.json', root), 'utf8'));
const base = 'http://127.0.0.1:8000/api/';
const issues = [];
const audit = JSON.parse(readFileSync(new URL('after-audit.json', root), 'utf8'));
const publications = readdirSync(root).filter(name => /^published-\d+\.json$/.test(name)).map(name => JSON.parse(readFileSync(new URL(name, root), 'utf8')));
for (const p of publications) assert.equal(audit.checks[p.url]?.sha256, p.sha256, 'HTTP payload matches assigned asset '+p.id);
let requests = 0;
async function api(path) {
  const response = await fetch(base + path, { headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(20000) });
  assert.equal(response.status, 200, path);
  requests++;
  return response.json();
}
assert.equal(before.length, after.length, 'product count preserved');
for (const p of before) {
  const current = after.find(a => a.id === p.id);
  for (const key of ['ma_san_pham', 'ten_san_pham', 'slug_san_pham', 'gia_ban', 'gia_khuyen_mai', 'so_luong_ton', 'trang_thai', 'id_danh_muc', 'mo_ta_ngan', 'mo_ta_chi_tiet', 'chi_tiet_san_phams']) assert.deepEqual(current[key], p[key], `${p.id}:${key}`);
  for (const old of p.anh_san_phams) assert.ok(current.anh_san_phams.some(a => a.id === old.id && a.duong_dan_anh === old.duong_dan_anh), 'historical image preserved '+old.id);
}
const selling = after.filter(p => p.trang_thai === 1);
const catalog = (await api('client/san-phams?all=1')).data;
assert.equal(catalog.length, selling.length);
for (const product of selling) {
  try {
    const item = catalog.find(p => p.id === product.id);
    assert.equal(item.hinh_anh, product.hinh_anh);
    assert.ok(productImageGallery(item).includes(product.hinh_anh));
    // Presentation 2 is read-only, unlike the legacy detail endpoint's view counter.
    const detail = (await api('client/san-pham/'+encodeURIComponent(product.slug_san_pham)+'?presentation=2')).data.san_pham;
    assert.equal(detail.hinh_anh, product.hinh_anh);
    assert.ok(productImageGallery(detail).includes(product.hinh_anh));
    const search = (await api('client/san-phams?all=1&tu_khoa='+encodeURIComponent(product.ten_san_pham))).data;
    assert.equal(search.find(p => p.id === product.id)?.hinh_anh, product.hinh_anh);
  } catch (error) { issues.push({ id: product.id, sku: product.ma_san_pham, error: error.message }); }
}
for (const id of new Set(selling.map(p => p.id_danh_muc))) {
  const data = (await api('client/san-phams?all=1&id_danh_muc='+id)).data;
  assert.deepEqual(data.map(p => p.id).sort((a,b) => a-b), selling.filter(p => p.id_danh_muc === id).map(p => p.id).sort((a,b) => a-b));
  assert.ok(data.every(p => productImageGallery(p).includes(p.hinh_anh)));
}
const home = (await api('client/home-data')).data;
for (const key of ['san_pham_noi_bat', 'san_pham_moi']) assert.ok(home[key].every(p => productImageGallery(p).includes(p.hinh_anh)));
// Quick View uses this legacy endpoint; its existing behavior increments one view.
const quickViewProduct = selling[0];
const quickView = (await api('client/san-pham/'+encodeURIComponent(quickViewProduct.slug_san_pham))).data.san_pham;
assert.equal(quickView.hinh_anh, quickViewProduct.hinh_anh);
assert.ok(productImageGallery(quickView).includes(quickViewProduct.hinh_anh));
const denied = await fetch(base+'admin/san-pham/1/images', { method: 'POST', headers: { Accept: 'application/json' } });
assert.equal(denied.status, 401);
const report = { checked: selling.length, requests, issues, immutable_product_fields_preserved: true, historical_links_preserved: true, quick_view_api_product_id: quickViewProduct.id, quick_view_expected_view_counter_increment: 1, anonymous_upload_status: denied.status, browser: 'Unavailable: computer-use stopped because current Windows browser URL could not be verified', checked_at: new Date().toISOString() };
writeFileSync(new URL('api-verification.json', root), JSON.stringify(report, null, 2));
console.log(JSON.stringify(report, null, 2));
if (issues.length) process.exitCode = 1;
