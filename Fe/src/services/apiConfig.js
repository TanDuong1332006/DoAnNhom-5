export const defaultApiBaseUrl = 'http://127.0.0.1:8000/api';

export function normalizeApiBase(value) {
  const base = (value?.trim() || defaultApiBaseUrl).replace(/\/+$/, '');
  const url = new URL(base);
  if (!['http:', 'https:'].includes(url.protocol) || url.username || url.password || url.search || url.hash) {
    throw new Error('VITE_API_URL phải là URL HTTP(S) của API, không chứa credential, query hoặc fragment.');
  }
  return base;
}

export const apiBaseUrl = normalizeApiBase(import.meta.env?.VITE_API_URL);
export function apiUrl(path = '', base = apiBaseUrl) {
  return normalizeApiBase(base) + (path ? '/' + String(path).replace(/^\/+/, '') : '');
}

// Chỉ chuyển URL upload nội bộ đã lưu với origin local cũ; giữ nguyên URL website khác.
export function backendAssetUrl(value, base = apiBaseUrl) {
  if (typeof value !== 'string') return value;
  const origin = new URL(normalizeApiBase(base)).origin;
  if (/^\/(?:storage|api\/product-images)\//.test(value)) return new URL(value, origin).href;
  try {
    const url = new URL(value);
    if (['127.0.0.1', 'localhost', '[::1]'].includes(url.hostname) && url.port === '8000'
      && /^\/(?:storage|api\/product-images)\//.test(url.pathname)) {
      return origin + url.pathname + url.search + url.hash;
    }
  } catch { /* Asset frontend tương đối. */ }
  return value;
}
