import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
const read = file => readFileSync(new URL('../src/' + file, import.meta.url), 'utf8');

test('Admin hiển thị lý do backend từ chối xóa và lỗi mạng có fallback', async () => {
  const source = read('components/Admin/DanhMuc/index.vue');
  const messages = [], calls = [];
  let error = { response: { status: 409, data: { message: 'Danh mục còn sản phẩm. Hãy tạm ẩn thay vì xóa.' } } };
  const context = { apiUrl: x => '/api' + x, axios: { post: (...args) => { calls.push(args); return Promise.reject(error); } } };
  vm.runInNewContext(source.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/^import .*;?\r?\n/gm, '').replace('export default', 'globalThis.definition ='), context);
  const state = { delete_DanhMuc: { id: 1 }, $toast: { error: message => messages.push(message) }, loadData() { assert.fail('Rejected deletion must not reload as success'); } };
  context.definition.methods.xoaDanhMuc.call(state);
  await new Promise(resolve => setImmediate(resolve));
  assert.equal(messages[0], error.response.data.message);
  assert.equal(calls[0][0], '/api/admin/danh-muc/destroy');
  error = new Error('Network');
  context.definition.methods.xoaDanhMuc.call(state);
  await new Promise(resolve => setImmediate(resolve));
  assert.equal(messages[1], 'Có lỗi xảy ra khi xóa!');
});

test('URL đánh giá cũ chỉ redirect Admin; góp ý và thông báo giữ route', () => {
  const context = { createRouter: options => ({ ...options, beforeEach() {} }), createWebHistory: () => ({}), checkLogin() {} };
  vm.runInNewContext(read('router/index.js').replace(/^import .*;\r?\n/gm, '').replace('export default router;', 'globalThis.router = router;'), context);
  const routes = context.router.routes;
  const old = routes.find(x => x.path === '/admin/danh-gia');
  assert.equal(old.redirect, '/admin');
  assert.equal(old.component, undefined);
  assert(!read('layout/components/MenuRocker.vue').includes('/admin/danh-gia'));
  for (const route of ['/gop-y', '/admin/gop-y', '/thong-bao', '/vi-uu-dai']) assert.equal(typeof routes.find(x => x.path === route)?.component, 'function');
  assert(read('layout/wrapper/ClientLayout.vue').includes("this.$router.push('/gop-y')"));
});
