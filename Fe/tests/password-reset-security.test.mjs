import { apiUrl } from '../src/services/apiConfig.js';
import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import { customerSession, clearCustomerSession } from '../src/services/authSessions.js';

function setup(error) {
  const storage = new Map([
    ['customer_token', 'customer-uat-token'], ['customer_user', JSON.stringify({ email: 'qa@example.test', role: 'customer' })],
    ['admin_token', 'admin-uat-token'], ['fashion_store_cart', '[{"id":1}]'],
  ]);
  const storageApi = { getItem: key => storage.get(key), setItem: (key, val) => storage.set(key, val), removeItem: key => storage.delete(key) };
  const calls = [], routes = [];
  const source = readFileSync(new URL('../src/components/QuenMatKhau/index.vue', import.meta.url), 'utf8');
  const script = source.match(/<script setup>([\s\S]*?)<\/script>/)[1]
    .replace(/^import .*;\r?\n/gm, '')
    .replaceAll('import.meta.env.VITE_API_URL', JSON.stringify('http://uat.example.test/api'));
  const context = {apiUrl: path => apiUrl(path, 'http://uat.example.test/api'), ProductImage: {}, 
    ref: value => ({ value }), useRouter: () => ({ push: path => routes.push(path) }), alert() {},
    customerSession: () => customerSession(storageApi),
    clearCustomerSession: token => clearCustomerSession(token, storageApi),
    axios: { async post(url, data) { calls.push({ url, data }); if (error) throw error; return { data: {} }; } },
  };
  vm.runInNewContext(script + '\nglobalThis.state = {email, otp, password, passwordConfirmation, step, isLoading, errorMessage, handleSendOtp, handleVerifyOtp, handleResetPassword};', context);
  const state = context.state;
  Object.assign(state.email, { value: 'qa@example.test' });
  state.otp.value = '123456'; state.password.value = state.passwordConfirmation.value = 'Only-test-147!';
  return { state, calls, routes, storage };
}

test('reset gửi đúng API UAT, thành công xóa phiên Customer cùng email, giữ Admin và giỏ', async () => {
  const x = setup(); await x.state.handleResetPassword();
  assert.equal(x.calls[0].url, 'http://uat.example.test/api/forgot-password/reset-password');
  assert.equal(x.storage.has('customer_token'), false);
  assert.equal(x.storage.get('admin_token'), 'admin-uat-token');
  assert.equal(x.storage.get('fashion_store_cart'), '[{"id":1}]');
  assert.deepEqual(x.routes, ['/dang-nhap']);
});

test('reset tài khoản khác không xóa phiên Customer không liên quan', async () => {
  const x = setup(); x.state.email.value = 'other@example.test';
  await x.state.handleResetPassword();
  assert.equal(x.storage.get('customer_token'), 'customer-uat-token');
});

test('429 ở send/verify/reset hiển thị lỗi, giữ bước và phiên, không tự retry', async () => {
  for (const handler of ['handleSendOtp', 'handleVerifyOtp', 'handleResetPassword']) {
    const x = setup({ response: { status: 429, data: { message: 'Vui lòng thử lại sau.' } } });
    await x.state[handler]();
    assert.equal(x.state.errorMessage.value, 'Vui lòng thử lại sau.');
    assert.equal(x.state.step.value, 1);
    assert.equal(x.state.isLoading.value, false);
    assert.equal(x.storage.get('customer_token'), 'customer-uat-token');
    assert.equal(x.calls.length, 1);
    assert.deepEqual(x.routes, []);
  }
});
