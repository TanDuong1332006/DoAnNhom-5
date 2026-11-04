import test from 'node:test';
import assert from 'node:assert/strict';
import { resolveApiUrl } from '../src/services/resolveApiUrl.js';

test('URL cũ của Admin, Client và kiểm tra phiên dùng cùng API base cấu hình', () => {
  for (const path of ['/admin/san-pham/create', '/client/san-pham/ao-sofep', '/kiem-tra-dang-nhap']) {
    assert.equal(resolveApiUrl('http://127.0.0.1:8000/api' + path, 'http://127.0.0.1:8013/api/'), 'http://127.0.0.1:8013/api' + path);
  }
});

test('không đổi URL bên thứ ba, URL tương đối hoặc hostname chỉ giống prefix', () => {
  for (const url of ['https://example.test/api', '/api/client/san-phams', 'http://127.0.0.1:8000/api-other']) {
    assert.equal(resolveApiUrl(url, 'http://127.0.0.1:8013/api'), url);
  }
  assert.equal(resolveApiUrl('http://127.0.0.1:8000/api/client/san-phams', undefined), 'http://127.0.0.1:8000/api/client/san-phams');
});
