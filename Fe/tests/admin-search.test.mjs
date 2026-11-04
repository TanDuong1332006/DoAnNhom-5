import test from 'node:test';
import assert from 'node:assert/strict';
import { matchesSearch, normalizeSearch, searchSections } from '../src/services/adminSearch.js';

test('Admin tìm chức năng bằng tiếng Việt, không dấu, từ đồng nghĩa và lỗi gõ một ký tự', () => {
  assert.equal(normalizeSearch('  ĐƠN HÀNG '), 'don hang');
  for (const query of ['Sản phẩm', 'san pham', 'product', 'san phamm']) assert.ok(searchSections(query).some(r => r.path === '/admin/san-pham'));
  assert.ok(searchSections('voucher').some(r => r.path === '/admin/ma-giam-gia'));
  assert.ok(searchSections('hinh anh').some(r => r.label === 'Quản lý hình ảnh'));
  assert.deepEqual(searchSections(''), []);
  assert.deepEqual(searchSections('khong tim thay xyz'), []);
  assert.equal(matchesSearch('Áo thun SOFEP SKU-123', 'ao SKU-123'), true);
  assert.equal(matchesSearch('Áo thun SOFEP SKU-123', 'quan'), false);
  assert.equal(matchesSearch('Áo UAT-6', 'UAT-5'), false);
});
