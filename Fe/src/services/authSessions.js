// Tách phiên theo vai trò, vẫn đọc được các khóa cũ để không làm mất phiên hiện có.
function userAt(storage, key) {
  try { return JSON.parse(storage.getItem(key) || 'null'); } catch { return null; }
}
function legacyUser(storage) { return userAt(storage, 'user') || userAt(storage, 'nguoi_dung'); }
export function customerSession(storage = localStorage) {
  const user = userAt(storage, 'customer_user');
  const token = storage.getItem('customer_token');
  if (token && user?.role === 'customer') return { token, user };
  const legacy = legacyUser(storage);
  const legacyToken = storage.getItem('auth_token') || storage.getItem('nguoi_dung_token');
  return legacyToken && legacy && (legacy.role || 'customer') === 'customer' ? { token: legacyToken, user: legacy } : null;
}
export function adminToken(storage = localStorage) {
  return storage.getItem('admin_token') || (legacyUser(storage)?.role === 'admin' ? storage.getItem('auth_token') || storage.getItem('nguoi_dung_token') : null);
}
export function saveSession(token, user, storage = localStorage) {
  if (!token || !['admin', 'customer'].includes(user?.role)) throw new Error('Phiên đăng nhập không hợp lệ.');
  const prefix = user.role === 'admin' ? 'admin' : 'customer';
  storage.setItem(prefix + '_token', token); storage.setItem(prefix + '_user', JSON.stringify(user));
  // Không để đăng nhập Admin ghi đè phiên Customer đang hoạt động.
  if (user.role === 'customer' || !customerSession(storage)) {
    for (const key of ['auth_token', 'nguoi_dung_token']) storage.setItem(key, token);
    for (const key of ['user', 'nguoi_dung']) storage.setItem(key, JSON.stringify(user));
  }
}
function clearRole(role, token, storage) {
  const prefix = role === 'customer' ? 'customer' : 'admin';
  if (storage.getItem(prefix + '_token') === token) {
    storage.removeItem(prefix + '_token'); storage.removeItem(prefix + '_user');
  }
  const ownedAlias = ['auth_token', 'nguoi_dung_token'].some(key => storage.getItem(key) === token);
  if (ownedAlias && (legacyUser(storage)?.role || 'customer') === role) {
    for (const key of ['user', 'nguoi_dung']) storage.removeItem(key);
  }
  for (const key of ['auth_token', 'nguoi_dung_token']) if (storage.getItem(key) === token) storage.removeItem(key);
}
export function clearCustomerSession(token, storage = localStorage) {
  if (token) clearRole('customer', token, storage);
}
export function clearAdminSession(token, storage = localStorage) {
  if (token) clearRole('admin', token, storage);
}
export function requestToken(url, storage = localStorage) {
  const path = String(url || '').split('?')[0];
  return /(?:^|\/)api\/admin(?:\/|$)/.test(path) ? adminToken(storage) : customerSession(storage)?.token;
}
