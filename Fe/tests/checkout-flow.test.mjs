import { apiUrl } from '../src/services/apiConfig.js';
import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
import { randomUUID } from 'node:crypto';

function checkout(axios, session = { token: 'test-session' }) {
  const memory = new Map(), messages = [];
  const store = { items: [{ id: 5, id_chi_tiet_san_pham: 5, so_luong: 1, gia: 205000 }], subtotal: 205000, count: 1,
    clearCart() { this.items = []; this.subtotal = 0; this.count = 0; }, toggleCheckout() {} };
  const storage = { getItem: k => memory.get(k) || null, setItem: (k,v) => memory.set(k,v), removeItem: k => memory.delete(k) };
  const script = readFileSync(new URL('../src/components/Client/CheckoutModal.vue', import.meta.url), 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1].replace(/^import .*;\r?\n/gm, '').replace('export default', 'globalThis.definition =');
  const context = {apiUrl, ProductImage: {},  cartStore: store, customerSession: () => session, clearCustomerSession() {}, axios: { ...axios, get: axios.get || (async () => ({ data: { status: 1, nguoi_dung: { role: 'customer' } } })) }, sessionStorage: storage, localStorage: storage, crypto: { randomUUID }, console, setInterval: () => 1, clearInterval() {} };
  vm.runInNewContext(script, context);
  const state = context.definition.data();
  state.$toast = { error: value => messages.push(value), success() {}, warning() {} };
  for (const [name, method] of Object.entries(context.definition.methods)) state[name] = method.bind(state);
  for (const [name, method] of Object.entries(context.definition.computed)) Object.defineProperty(state, name, { get: method.bind(state) });
  state.form = { ho_ten: 'Khách demo', so_dien_thoai: '0900000000', dia_chi_giao: 'UAT only', ghi_chu: '', phuong_thuc: 'MB_BANK' };
  return { state, definition: context.definition, memory, messages, store };
}
const settle = () => new Promise(resolve => setImmediate(resolve));

test('Customer cập nhật trạng thái xử lý từ API chủ đơn, chặn bấm lặp và giữ trạng thái hủy để đọc', async () => {
  let complete, calls = 0;
  const { state, store } = checkout({ get: url => {
    calls++; assert.ok(url.endsWith('/HD_OWNER'));
    return new Promise(resolve => { complete = resolve; });
  } });
  state.currentStep = 'success';
  state.donHangData = { ma_don_hang: 'HD_OWNER', trang_thai_thanh_toan: 'da_thanh_toan', trang_thai_don_hang: 'cho_xac_nhan' };
  const before = JSON.stringify(store.items);
  const pending = state.kiemTraGiaoDichNgay(); state.kiemTraGiaoDichNgay();
  assert.equal(state.loadingCheck, true); assert.equal(calls, 1);
  complete({ data: { status: 1, da_thanh_toan: true, don_hang: { ma_don_hang: 'HD_OWNER', trang_thai_thanh_toan: 'da_thanh_toan', trang_thai_don_hang: 'da_giao' } } });
  await pending;
  assert.equal(state.trangThaiDonHang, 'Đã giao'); assert.equal(state.loadingCheck, false);
  const cancelled = state.kiemTraGiaoDichNgay();
  complete({ data: { status: 1, da_thanh_toan: false, don_hang: { ma_don_hang: 'HD_OWNER', trang_thai_don_hang: 'da_huy' } } });
  await cancelled;
  assert.equal(state.trangThaiDonHang, 'Đã hủy'); assert.equal(state.currentStep, 'success');
  assert.equal(state.tieuDeKetQuaDonHang, 'Đơn Hàng Đã Hủy');
  state.donHangData = { trang_thai_don_hang: 'da_giao', trang_thai_thanh_toan: 'da_thanh_toan', phuong_thuc_thanh_toan: 'COD' };
  assert.equal(state.trangThaiThanhToan, 'Đã thanh toán');
  state.donHangData.phuong_thuc_thanh_toan = 'MB_BANK';
  assert.equal(state.trangThaiThanhToan, 'Đã thanh toán mô phỏng');
  assert.equal(JSON.stringify(store.items), before);
});

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
  const { state, definition, memory } = checkout({ get: async url => url.includes('kiem-tra-dang-nhap') ? { data: { status: 1, nguoi_dung: { role: 'customer' } } } : ({ data: { status: 1, da_thanh_toan: false, don_hang: { ma_don_hang: 'HD_PENDING', tong_thanh_toan: 235000, ho_ten: 'Khách UAT', so_dien_thoai: '0900000000', dia_chi_giao: 'UAT only', phuong_thuc_thanh_toan: 'MB_BANK', trang_thai_don_hang: 'cho_xac_nhan' } } }) });
  memory.set('sofep_pending_demo_order', JSON.stringify({ code: 'HD_PENDING', token: 'fixture-capability', total: 235000 }));
  await definition.mounted.call(state);
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


test('Guest và phiên hết hạn không tạo đơn, giữ nguyên giỏ và hiện yêu cầu đăng nhập', async () => {
  let orders = 0;
  for (const status of [401, 403]) {
    const { state, store } = checkout({ get: async () => { throw { response: { status } }; }, post: async () => { orders++; } });
    await state.taoDonHang();
    assert.equal(state.currentStep, 'auth');
    assert.equal(store.items[0].id_chi_tiet_san_pham, 5);
    assert.equal(state.loadingTaoDon, false);
  }
  assert.equal(orders, 0);
});

test('hết phiên giữa xác minh và đặt hàng vẫn giữ giỏ', async () => {
  const { state, store } = checkout({ post: async () => { throw { response: { status: 401 } }; } });
  await state.taoDonHang(); await settle();
  assert.equal(state.currentStep, 'auth');
  assert.equal(store.items.length, 1);
});

test('ý định checkout qua đăng ký giữ nguyên biến thể, không thêm giỏ lần nữa', async () => {
  const { state, store, memory } = checkout({});
  state.$route = { fullPath: '/san-pham/ao-mau' };
  state.$router = { push: path => assert.equal(path, '/dang-ky') };
  state.goAuth('/dang-ky');
  assert.equal(memory.get('sofep_checkout_return'), '/san-pham/ao-mau');
  assert.equal(store.items.length, 1);
  assert.equal(store.items[0].id_chi_tiet_san_pham, 5);
});


test('Guest không có phiên mở checkout hoặc khôi phục đơn cũ không gọi API thanh toán', async () => {
  const calls = [];
  const { state, definition, memory, store } = checkout({ get: async url => calls.push(url), post: async url => calls.push(url) }, null);
  await definition.watch['cartStore.isOpenCheckout'].call(state, true);
  assert.equal(state.currentStep, 'auth');
  memory.set('sofep_pending_demo_order', JSON.stringify({ code: 'HD_OLD', total: 100 }));
  await definition.mounted.call(state);
  assert.equal(state.currentStep, 'auth');
  assert.equal(calls.length, 0);
  assert.equal(store.items.length, 1);
});

test('Customer quay lại sau đăng nhập: mở checkout, giữ đúng variant và số lượng', async () => {
  const { state, definition, memory, store } = checkout({});
  let opened;
  store.toggleCheckout = value => { opened = value; };
  memory.set('sofep_checkout_return', '/san-pham/ao-mau');
  const before = JSON.stringify(store.items);
  await definition.mounted.call(state);
  await definition.watch['cartStore.isOpenCheckout'].call(state, true);
  assert.equal(opened, true);
  assert.equal(state.currentStep, 'form');
  assert.equal(JSON.stringify(store.items), before);
});

test('Quay lại cửa hàng đóng modal và giữ giỏ; modal auth có đúng ba hành động', () => {
  const { state, store } = checkout({}, null);
  let opened;
  store.toggleCheckout = value => { opened = value; };
  const before = JSON.stringify(store.items);
  state.dongModal();
  assert.equal(opened, false);
  assert.equal(JSON.stringify(store.items), before);
  const template = readFileSync(new URL('../src/components/Client/CheckoutModal.vue', import.meta.url), 'utf8');
  const auth = template.split('class="checkout-auth-content"')[1].split('<div v-else-if="currentStep')[0];
  assert.equal((auth.match(/<button /g) || []).length, 3);
  assert.match(auth, /Đăng nhập để tiếp tục mua hàng/);
  assert.match(auth, /@click="dongModal">Quay lại cửa hàng/);
});


test('OTP thành công yêu cầu đăng nhập tiếp và giữ giỏ cùng ý định checkout', async () => {
  const source = readFileSync(new URL('../src/components/DangKy/index.vue', import.meta.url), 'utf8');
  const script = source.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/^import .*;\r?\n/gm, '').replace('export default', 'globalThis.registration =');
  const memory = new Map([['sofep_checkout_return','/san-pham/test'],['fashion_store_cart','[{"id":9,"id_chi_tiet_san_pham":44,"so_luong":2}]']]);
  const storage = { getItem:key=>memory.get(key), setItem:(key,value)=>memory.set(key,value), removeItem:key=>memory.delete(key) };
  const urls = [], destinations = [];
  const context = {apiUrl, ProductImage: {},  axios:{ post:async url=>{ urls.push(url); return {data:{status:1}}; } },localStorage:storage,sessionStorage:storage,setTimeout:callback=>callback() };
  vm.runInNewContext(script,context);
  const state = {...context.registration.data(),$toast:{success(){},error(){}},$router:{push:path=>destinations.push(path)}};
  state.dangKy = {role:'customer',ho_va_ten:'Customer Test',email:'otp@example.test',so_dien_thoai:'0900000000',mat_khau:'test-only',xac_nhan_mk:'test-only'};
  context.registration.methods.dangkyTaiKhoan.call(state); await settle();
  assert.equal(state.showOtp,true);
  state.otp = '123456';
  context.registration.methods.xacThucOtp.call(state); await settle();
  assert.equal(destinations[0],'/dang-nhap');
  assert.equal(memory.get('sofep_checkout_return'),'/san-pham/test');
  assert.equal(JSON.parse(memory.get('fashion_store_cart'))[0].id_chi_tiet_san_pham,44);
  assert.equal(memory.has('customer_token'),false);
  assert.ok(urls.every(url=>!url.includes('dat-hang') && !url.includes('giao-dich')));
});
