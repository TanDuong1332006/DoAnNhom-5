// Giữ tương thích các URL cũ trong component, nhưng luôn tôn trọng cấu hình API.
export function resolveApiUrl(url, configuredBase) {
  const legacyBase = 'http://127.0.0.1:8000/api';
  const base = (configuredBase || legacyBase).replace(/\/+$/, '');
  if (typeof url === 'string' && (url === legacyBase || url.startsWith(legacyBase + '/'))) {
    return base + url.slice(legacyBase.length);
  }
  return url;
}
