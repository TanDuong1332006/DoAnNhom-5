// Test HTTP thật, chỉ chạy sau khi khởi tạo fixture UAT dùng một lần.
import assert from 'node:assert/strict';
import { readFileSync, realpathSync } from 'node:fs';
import { basename, join } from 'node:path';
import { createHash } from 'node:crypto';
import { variantPrice } from '../src/services/productCatalog.js';

const directory = realpathSync(process.env.SOFEP_UAT_DIR || 'missing-uat-directory');
assert.ok(basename(directory).startsWith('sofep-uat-'));
const manifest = readFileSync(join(directory, 'session.json'), 'utf8');
const fixture = JSON.parse(manifest);
assert.equal(fixture.purpose, 'SOFEP disposable browser fixture');
assert.equal(realpathSync(fixture.database), realpathSync(join(directory, 'uat.sqlite')));
const base = 'http://127.0.0.1:8013';
const identity = await (await fetch(base + '/__uat/status')).json();
assert.equal(identity.driver, 'sqlite');
assert.equal(identity.fixture, createHash('sha256').update(manifest).digest('hex'));

let checks = 0;
async function api(method, path, token, data, status = 200) {
  const response = await fetch(base + '/api/' + path, {
    method, headers: { Accept: 'application/json', 'Content-Type': 'application/json', Origin: 'http://127.0.0.1:5173', ...(token ? { Authorization: 'Bearer ' + token } : {}) },
    ...(data ? { body: JSON.stringify(data) } : {})
  });
  const result = await response.json();
  assert.equal(response.status, status, method + ' ' + path + ': ' + result.message);
  checks++;
  return result;
}
const admin = await api('POST', 'dang-nhap', null, { email: 'admin@sofep.example.test', mat_khau: fixture.password });
const customer = await api('POST', 'dang-nhap', null, { email: 'customer@sofep.example.test', mat_khau: fixture.password });
await api('GET', 'admin/san-pham/get-data', customer.token, null, 403);
await api('GET', 'kiem-tra-dang-nhap', 'invalid-token', null, 401);
await api('POST', 'dang-nhap', null, { email: 'seller@sofep.example.test', mat_khau: fixture.password }, 403);
await api('GET', 'seller/san-pham/get-data', admin.token, null, 404);
const payload = { ten_san_pham: 'HTTP UAT ' + Date.now(), id_danh_muc: 1, gia_ban: 200000, gia_khuyen_mai: 180000,
  hinh_anh: base + '/__uat/image.svg', mo_ta_ngan: 'HTTP short description', mo_ta_chi_tiet: 'HTTP full description' };
const created = await api('POST', 'admin/san-pham/create', admin.token, payload, 201);
assert.equal(created.data.trang_thai, 0);
const id = created.data.id;
const originalSlug = created.data.slug_san_pham;
try {
  await api('GET', 'client/san-pham/' + originalSlug, customer.token, null, 404);
  const variant = { id_san_pham: id, id_mau_sac: 1, id_kich_thuoc: 1, so_luong_ton: 7, gia_chenh_lech: 25000 };
  await api('POST', 'admin/chi-tiet-san-pham/create', admin.token, variant, 201);
  await api('POST', 'admin/chi-tiet-san-pham/create', admin.token, variant, 422);
  await api('POST', 'admin/san-pham/create', admin.token, { ...payload, gia_ban: -1 }, 422);
  await api('POST', 'admin/san-pham/create', admin.token, { ...payload, slug_san_pham: originalSlug }, 422);
  await api('POST', 'admin/san-pham/change-status', admin.token, { id });
  const detail = (await api('GET', 'client/san-pham/' + originalSlug, customer.token)).data.san_pham;
  assert.equal(detail.hinh_anh, payload.hinh_anh);
  assert.equal(detail.mo_ta_ngan, payload.mo_ta_ngan);
  assert.equal(detail.mo_ta_chi_tiet, payload.mo_ta_chi_tiet);
  assert.equal(detail.chi_tiet_san_phams[0].so_luong_ton, 7);
  assert.equal(detail.chi_tiet_san_phams[0].mau_sac.ten_mau, 'Đen');
  assert.equal(variantPrice(detail, detail.chi_tiet_san_phams[0]), 205000);
  assert.equal((await api('GET', 'client/san-phams?all=1&tu_khoa=' + encodeURIComponent(payload.ten_san_pham), customer.token)).data.length, 1);
  const newSlug = originalSlug + '-edited';
  await api('POST', 'admin/san-pham/update', admin.token, { ...payload, id, slug_san_pham: newSlug });
  await api('GET', 'client/san-pham/' + originalSlug, customer.token, null, 404);
  await api('GET', 'client/san-pham/' + newSlug, customer.token);
  await api('POST', 'admin/san-pham/change-status', admin.token, { id });
  await api('GET', 'client/san-pham/' + newSlug, customer.token, null, 404);
  assert.equal((await api('GET', 'client/san-phams?all=1&tu_khoa=' + encodeURIComponent(payload.ten_san_pham), customer.token)).data.length, 0);
} finally {
  await api('POST', 'admin/san-pham/destroy', admin.token, { id });
}
await api('POST', 'dang-xuat', admin.token, {});
await api('GET', 'kiem-tra-dang-nhap', admin.token, null, 401);
console.log(JSON.stringify({ environment: 'disposable SQLite over real HTTP', checks, result: 'passed' }));
