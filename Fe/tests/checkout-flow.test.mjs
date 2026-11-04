import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
import { randomUUID } from 'node:crypto';

function checkout(axios) {
  const memory = new Map(), messages = [];
  const store = { items: [{ id: 5, id_chi_tiet_san_pham: 5, so_luong: 1, gia: 205000 }], subtotal: 205000, count: 1,
    clearCart() { this.items = []; this.subtotal = 0; this.count = 0; }, toggleCheckout() {} };
  const storage = { getItem: k => memory.get(k) || null, setItem: (k,v) => memory.set(k,v), removeItem: k => memory.delete(k) };
  const script = readFileSync(new URL('../src/components/Client/CheckoutModal.vue', import.meta.url), 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1].replace(/^import .*;\r?\n/gm, '').replace('export default', 'globalThis.definition =');
  const context = { cartStore: store, axios, sessionStorage: storage, localStorage: storage, crypto: { randomUUID }, console, setInterval: () => 1, clearInterval() {} };
  vm.runInNewContext(script, context);
  const state = context.definition.data();
  state.$toast = { error: value => messages.push(value), success() {}, warning() {} };
  for (const [name, method] of Object.entries(context.definition.methods)) state[name] = method.bind(state);
  for (const [name, method] of Object.entries(context.definition.computed)) Object.defineProperty(state, name, { get: method.bind(state) });
  state.form = { ho_ten: 'Khách demo', so_dien_thoai: '0900000000', dia_chi_giao: 'UAT only', ghi_chu: '', phuong_thuc: 'MB_BANK' };
  return { state, definition: context.definition, memory, messages, store };
}
const settle = () => new Promise(resolve => setImmediate(resolve));

test('checkout kiểm tra voucher, gửi variant và khóa đặt hàng; chặn bấm lặp, giữ phiên thanh toán demo', async () => {
  const calls = [];
  const { state, store, memory } = checkout({ post: async (url, payload, options) => {
    calls.push({url, payload, options});
    if (url.includes('kiem-tra-voucher')) return { data: { status: 1, message: 'Mã hợp lệ', data: { tong_thanh_toan: 214500, tien_giam_gia: 20500 } } };
    return { data: { status: 1, don_hang: { ma_don_hang: 'HD_DEMO', tong_thanh_toan: 214500 }, payment_token: 'fixture-only-capability', ngan_hang: { so_tien: 214500 } } };
  }});
  state.voucherCode = 'DEMO10'; await state.checkVoucher();
  assert.equal(state.tongThanhToan, 214500);
  state.taoDonHang(); state.taoDonHang(); await settle();
  assert.equal(calls.length, 2);
  assert.equal(calls[1].payload.items[0].id_chi_tiet_san_pham, 5);
  assert.match(calls[1].options.headers['Idempotency-Key'], /^[a-f0-9-]{36}$/);
  assert.equal(state.currentStep, 'payment'); assert.equal(store.items.length, 0);
  assert.equal(JSON.parse(memory.get('sofep_pending_demo_order')).code, 'HD_DEMO');
  assert.equal(JSON.parse(memory.get('sofep_pending_demo_order')).ho_ten, undefined);
});

test('voucher lỗi giữ giỏ và hiện lý do backend; response cũ không ghi đè khi đổi mã', async () => {
  let complete;
  const { state, store } = checkout({ post: () => new Promise(resolve => { complete = resolve; }) });
  state.voucherCode = 'OLD'; const pending = state.checkVoucher(); state.voucherCode = 'NEW';
  complete({ data: { data: { tong_thanh_toan: 1 }, message: 'Old response' } }); await pending;
  assert.equal(state.quote, null); assert.equal(store.items.length, 1);
  const failed = checkout({ post: async () => { throw { response: { data: { errors: { ma_giam_gia_su_dung: ['Mã đã hết lượt.'] } } } }; } });
  await failed.state.checkVoucher(); assert.equal(failed.state.voucherMessage, 'Mã đã hết lượt.'); assert.equal(failed.store.items.length, 1);
});

test('reload khôi phục đơn demo đang chờ và lấy lại thông tin người nhận từ API có quyền với đơn', async () => {
  const { state, definition, memory } = checkout({ get: async () => ({ data: { status: 1, da_thanh_toan: false, don_hang: { ma_don_hang: 'HD_PENDING', tong_thanh_toan: 235000, ho_ten: 'Khách UAT', so_dien_thoai: '0900000000', dia_chi_giao: 'UAT only', phuong_thuc_thanh_toan: 'MB_BANK', trang_thai_don_hang: 'cho_xac_nhan' } } }) });
  memory.set('sofep_pending_demo_order', JSON.stringify({ code: 'HD_PENDING', token: 'fixture-capability', total: 235000 }));
  definition.mounted.call(state); await settle();
  assert.equal(state.currentStep, 'payment'); assert.equal(state.form.ho_ten, 'Khách UAT');
  assert.equal(state.donHangData.tong_thanh_toan, 235000);
  state.clearPendingDemo();
  assert.equal(state.currentStep, 'form'); assert.equal(state.paymentToken, '');
  assert.equal(memory.has('sofep_pending_demo_order'), false);
});

test('lỗi dòng giỏ chỉ rõ món cũ và chỉ bỏ dòng đó khi khách chọn lại', async () => {
  let calls = 0;
  const { state, store } = checkout({ post: async () => {
    calls++;
    throw { response: { data: { errors: { 'items.1': ['Vui lòng chọn lại màu và kích thước sản phẩm.'] } } } };
  }});
  store.items.push({ id: 8, itemKey: 'old-line', ten_san_pham: 'Ghim cài', so_luong: 1 });
  store.removeItem = key => { store.items = store.items.filter(item => item.itemKey !== key); };
  state.$router = { push: async path => assert.equal(path, '/san-pham/8') };
  await state.checkVoucher();
  assert.equal(state.voucherMessage, '');
  assert.equal(state.cartIssue.item.ten_san_pham, 'Ghim cài');
  assert.equal(store.items.length, 2);
  state.taoDonHang();
  assert.equal(calls, 1);
  await state.reselectItem();
  assert.equal(store.items.length, 1);
  assert.equal(store.items[0].id_chi_tiet_san_pham, 5);
});
