import { apiUrl } from '../src/services/apiConfig.js';
import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';

function component(path, get, session = () => ({ token: 'uat' })) {
  const source = readFileSync(new URL(path, import.meta.url), 'utf8');
  const script = source.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/^import .*;\r?\n/gm, '').replace('export default', 'globalThis.definition =');
  const context = {apiUrl, ProductImage: {},  axios: { get }, customerSession: session, console };
  vm.runInNewContext(script, context);
  const state = context.definition.data();
  for (const [key, method] of Object.entries(context.definition.methods)) state[key] = method.bind(state);
  return { state, source };
}

test('Dashboard không công bố số 0 khi API lỗi; tải lại và response nghiệp vụ lỗi', async () => {
  let fail = true, invalid = false;
  const { state, source } = component('../src/components/Admin/Dashboard/index.vue', async url => {
    if (fail && url.includes('tong-quan')) throw new Error('offline');
    return { data: { status: invalid ? 0 : 1, data: url.includes('tong-quan') ? { tong_don_hang: 7 } : [] } };
  });
  await state.loadDashboardData(); assert.ok(state.dashboardError); assert.equal(state.loadingDashboard, false);
  assert.match(source, /v-else-if="dashboardError"/); assert.match(source, /<template v-else>/);
  fail = false; await state.loadDashboardData(); assert.equal(state.dashboardError, ''); assert.equal(state.thong_ke.tong_don_hang, 7);
  invalid = true; await state.loadDashboardData(); assert.ok(state.dashboardError);
});

test('Lịch sử đơn phân trang, lỗi tải không biến thành giỏ trống, retry và chi tiết cập nhật', async () => {
  let fail = true, calls = 0;
  const { state } = component('../src/pages/client/MyOrders.vue', async (url, config) => {
    calls++;
    if (fail) throw { response: { status: 401 } };
    if (config) return { data: { status: 1, data: { data: [{ id: 5 }], current_page: config.params.page, last_page: 3 } } };
    return { data: { status: 1, data: { id: 5, trang_thai_don_hang: 'da_giao', chi_tiets: [] } } };
  });
  await state.fetchOrders(); assert.match(state.error, /hết hạn/); assert.equal(state.loading, false);
  fail = false; await state.fetchOrders(2); assert.equal(state.page, 2); assert.equal(state.lastPage, 3); assert.equal(state.error, '');
  await state.showOrder(5); assert.equal(state.selected.id, 5); assert.equal(state.statusLabel(state.selected.trang_thai_don_hang), 'Đã giao');
  fail = true; await state.showOrder(9); assert.equal(state.selected, null); assert.ok(state.detailError); assert.equal(state.detailLoading, false);
  assert.equal(state.paymentLabel('chua_thanh_toan'), 'Chưa thanh toán'); assert.ok(calls >= 4);
});

test('Danh sách đơn chặn request lặp trong khi chờ và chi tiết từ chối 404', async () => {
  let resolve, calls = 0;
  const { state } = component('../src/pages/client/MyOrders.vue', () => { calls++; return new Promise(r => { resolve = r; }); });
  const pending = state.fetchOrders(); await state.fetchOrders(); assert.equal(calls, 1);
  resolve({ data: { status: 1, data: { data: [], current_page: 1, last_page: 1 } } }); await pending;
  assert.equal(state.loading, false); assert.equal(state.orders.length, 0);
  assert.match(state.orderError({ response: { status: 404 } }), /không có quyền/);
});

test('Đăng xuất/đổi Customer xóa đơn cũ và không nhận response đang chờ của phiên trước', async () => {
  let session = { token: 'A' }, listResolve, detailResolve;
  const { state } = component('../src/pages/client/MyOrders.vue', (url, config) => new Promise(resolve => {
    if (config) listResolve = resolve; else detailResolve = resolve;
  }), () => session);
  state.syncSession();
  const oldListResolve = listResolve;
  state.orders = [{ id: 1 }]; state.selected = { id: 1 };
  const oldDetail = state.showOrder(1);
  session = null; state.syncSession();
  assert.equal(state.signedIn, false); assert.equal(state.orders.length, 0); assert.equal(state.selected, null);
  session = { token: 'B' }; state.syncSession();
  oldListResolve({ data: { status: 1, data: { data: [{ id: 1 }], current_page: 1, last_page: 1 } } });
  detailResolve({ data: { status: 1, data: { id: 1 } } }); await oldDetail;
  await new Promise(resolve => setImmediate(resolve));
  assert.equal(state.orders.length, 0); assert.equal(state.selected, null); assert.equal(state.loading, true);
  listResolve({ data: { status: 1, data: { data: [{ id: 2 }], current_page: 1, last_page: 1 } } });
  await new Promise(resolve => setImmediate(resolve));
  assert.equal(state.orders[0].id, 2); assert.equal(state.loading, false);
});
