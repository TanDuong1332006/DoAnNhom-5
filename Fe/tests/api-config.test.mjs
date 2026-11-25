import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync, readdirSync } from 'node:fs';
import { apiUrl, normalizeApiBase, backendAssetUrl } from '../src/services/apiConfig.js';
import { resolveApiUrl } from '../src/services/resolveApiUrl.js';
import { productImageUrl, productImageGallery, imagePlaceholder } from '../src/services/productImages.js';

test('nguồn ID 172 đang HOLD không được hiển thị, ảnh mới của cùng sản phẩm không bị chặn theo ID', () => {
  for (const value of [
    'https://thoitrangbigsize.vn/wp-content/uploads/2025/03/den-3.jpg?v=2',
    'http://localhost:8000/api/product-images/172/dd7b32f3-7ee8-4f6e-b7f0-e256ee801dc9.jpg',
    '/storage/products/172/dd7b32f3-7ee8-4f6e-b7f0-e256ee801dc9.jpg',
  ]) assert.equal(productImageUrl(value, 'http://localhost:8042/api'), imagePlaceholder);
  assert.equal(productImageUrl('/api/product-images/172/approved-new-photo.jpg', 'http://localhost:8042/api'), 'http://localhost:8042/api/product-images/172/approved-new-photo.jpg');
});

test('hai nguồn ID 172 đang HOLD dùng placeholder, không chặn ảnh mới cùng sản phẩm', () => {
  assert.equal(productImageUrl('https://thoitrangbigsize.vn/wp-content/uploads/2025/03/den-3.jpg?v=1'), imagePlaceholder);
  assert.equal(productImageUrl('http://localhost:8042/api/product-images/172/dd7b32f3-7ee8-4f6e-b7f0-e256ee801dc9.jpg'), imagePlaceholder);
  assert.equal(productImageUrl('/api/product-images/172/new-approved-photo.jpg', 'http://localhost:8042/api'), 'http://localhost:8042/api/product-images/172/new-approved-photo.jpg');
});

test('API base mặc định giữ BE 8000; port mới, prefix và query request được bảo toàn', () => {
  assert.equal(normalizeApiBase('  '), 'http://127.0.0.1:8000/api');
  const base = ' http://localhost:8042/api/// ';
  for (const path of ['/dang-nhap', '/admin/san-pham/1/images', '/client/dat-hang', '/client/san-phams?all=1']) {
    assert.equal(apiUrl(path, base), 'http://localhost:8042/api' + path);
    for (const host of ['127.0.0.1', 'localhost']) assert.equal(resolveApiUrl(`http://${host}:8000/api${path}`, base), apiUrl(path, base));
  }
  for (const invalid of ['javascript:alert(1)', 'https://name:secret@api.example/api', 'https://api.example/api?key=secret', 'https://api.example/api#hash']) assert.throws(() => normalizeApiBase(invalid));
});

test('ảnh upload đúng được đưa qua host BE mới, giữ URL ngoài và không mutate nguồn', () => {
  const base = 'http://localhost:8042/api';
  for (const path of ['/api/product-images/172/photo.jpg', '/storage/products/172/photo.jpg']) {
    assert.equal(productImageUrl(path, base), 'http://localhost:8042' + path);
    for (const host of ['127.0.0.1', 'localhost']) assert.equal(productImageUrl(`http://${host}:8000${path}?v=2`, base), 'http://localhost:8042' + path + '?v=2');
  }
  assert.equal(backendAssetUrl('https://cdn.example/photo.jpg', base), 'https://cdn.example/photo.jpg');
  assert.equal(backendAssetUrl('http://localhost:8000/unrelated.png', base), 'http://localhost:8000/unrelated.png');
  const source = '/api/product-images/1/illustration-12345678-1234-1234-1234-123456789abc.jpg';
  const product = { hinh_anh: source, anh_san_phams: [{ duong_dan_anh: source }] };
  assert.equal(productImageUrl(source, base), 'http://localhost:8042'+source);
  assert.equal(productImageUrl(source, base, false), imagePlaceholder);
  assert.equal(productImageGallery(product).length, 1);
  assert.equal(product.hinh_anh, source);
});

test('source app chỉ có một default URL, không còn API origin cứng trong các caller', () => {
  const root = new URL('../src/', import.meta.url);
  function inspect(folder) {
    for (const entry of readdirSync(folder, { withFileTypes: true })) {
      const child = new URL(entry.name + (entry.isDirectory() ? '/' : ''), folder);
      if (entry.isDirectory()) { if (entry.name !== 'assets') inspect(child); }
      else if (/\.(vue|js)$/.test(entry.name) && entry.name !== 'apiConfig.js') assert.doesNotMatch(readFileSync(child, 'utf8'), /https?:\/\/(?:127\.0\.0\.1|localhost):8000\/api/);
    }
  }
  inspect(root);
});
