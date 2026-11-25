import { apiBaseUrl, normalizeApiBase } from './apiConfig.js';
// Tương thích URL cũ của tích hợp còn lưu ngoài source; caller mới dùng apiUrl.
export function resolveApiUrl(url, configuredBase = apiBaseUrl) {
  const match = typeof url === 'string' && url.match(/^https?:\/\/(?:127\.0\.0\.1|localhost):8000\/api(?=\/|\?|#|$)/);
  if (match) {
    return normalizeApiBase(configuredBase) + url.slice(match[0].length);
  }
  return url;
}
