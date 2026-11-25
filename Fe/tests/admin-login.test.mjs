import { apiUrl } from '../src/services/apiConfig.js';
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import { saveSession } from '../src/services/authSessions.js';

// Chạy logic component thật với API/storage/router giả lập, không gọi dịch vụ.
function setup(response, error) {
  const source = readFileSync(new URL('../src/components/DangNhap/index.vue', import.meta.url), 'utf8');
  const calls = [];
  const storage = new Map();
  const messages = [];
  const destinations = [];
  const axios = {
    async post(url, payload) {
      calls.push({ url, payload: { ...payload } });
      if (error) throw error;
      return { data: response };
    }
  };
  const script = source.match(/<script>([\s\S]*?)<\/script>/)[1]
    .replace(/^import .*;\r?\n/gm, '')
    .replace('export default', 'globalThis.component =')
    .replaceAll('import.meta.env.VITE_API_URL', JSON.stringify('http://api.test/api'));
  const storageApi = { getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value), removeItem: key => storage.delete(key) };
  const context = {apiUrl: path => apiUrl(path, 'http://api.test/api'), ProductImage: {},  axios, sessionStorage: storageApi, localStorage: storageApi, saveSession: (token, user) => saveSession(token, user, storageApi) };
  vm.runInNewContext(script, context);
  const component = context.component;
  const instance = {
    ...component.data(),
    $toast: { error: message => messages.push(message), success() {} },
    $router: { push: async path => destinations.push(path) }
  };
  return { component, instance, calls, storage, messages, destinations };
}

test('form đăng nhập không điền sẵn thông tin tài khoản', () => {
  const { instance } = setup();
  assert.equal(instance.dangNhap.email, '');
  assert.equal(instance.dangNhap.mat_khau, '');
});

test('Admin nhập thông tin hợp lệ: gửi API, lưu token và điều hướng Admin', async () => {
  const state = setup({ status: 1, token: 'isolated-test-token', nguoi_dung: { id: 1, role: 'admin' } });
  state.instance.dangNhap = { email: 'admin@example.test', mat_khau: 'Actual-admin-pass-471!' };
  await state.component.methods.dangnhapTK.call(state.instance);
  assert.equal(state.calls[0].url, 'http://api.test/api/dang-nhap');
  assert.deepEqual(state.calls[0].payload, state.instance.dangNhap);
  assert.equal(state.storage.get('auth_token'), 'isolated-test-token');
  assert.equal(JSON.parse(state.storage.get('user')).role, 'admin');
  assert.deepEqual(state.destinations, ['/admin']);
  assert.equal(state.instance.loading, false);
});

test('API trả 401: hiện lỗi và không lưu phiên hoặc điều hướng Admin', async () => {
  const state = setup(undefined, { response: { status: 401, data: { status: 0, message: 'Sai thông tin đăng nhập' } } });
  state.instance.dangNhap = { email: 'admin@example.test', mat_khau: 'Wrong-password-471!' };
  await state.component.methods.dangnhapTK.call(state.instance);
  assert.equal(state.storage.size, 0);
  assert.deepEqual(state.destinations, []);
  assert.deepEqual(state.messages, ['Sai thông tin đăng nhập']);
  assert.equal(state.instance.loading, false);
});

test('API trả 429: hiện thông báo giới hạn, không tạo phiên và mở lại nút đăng nhập', async () => {
  const state = setup(undefined, { response: { status: 429, data: { message: 'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau.' } } });
  await state.component.methods.dangnhapTK.call(state.instance);
  assert.equal(state.storage.size, 0);
  assert.deepEqual(state.destinations, []);
  assert.deepEqual(state.messages, ['Bạn đã thử quá nhiều lần. Vui lòng thử lại sau.']);
  assert.equal(state.instance.loading, false);
});


test('Customer đăng nhập quay về checkout đã lưu mà không xóa giỏ', async () => {
  const state = setup({ status: 1, token: 'test-customer-token', nguoi_dung: { id: 2, role: 'customer' } });
  state.storage.set('sofep_checkout_return', '/san-pham/ao-so-mi');
  state.storage.set('fashion_store_cart', '[{"id":3,"id_chi_tiet_san_pham":15,"so_luong":2}]');
  await state.component.methods.dangnhapTK.call(state.instance);
  assert.deepEqual(state.destinations, ['/san-pham/ao-so-mi']);
  assert.equal(JSON.parse(state.storage.get('fashion_store_cart'))[0].so_luong, 2);
  assert.equal(state.storage.get('sofep_checkout_return'), '/san-pham/ao-so-mi');
});


test('Customer đăng nhập từ trung tâm thông báo quay lại đúng trang, không tạo ý định checkout', async () => {
  const state = setup({ status: 1, token: 'test-inbox-token', nguoi_dung: { id: 2, role: 'customer' } });
  state.storage.set('sofep_customer_return', '/thong-bao');
  await state.component.methods.dangnhapTK.call(state.instance);
  assert.deepEqual(state.destinations, ['/thong-bao']);
  assert.equal(state.storage.has('sofep_customer_return'), false);
  assert.equal(state.storage.has('sofep_checkout_return'), false);
});
