export function normalizeSearch(value) {
  return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
}

function nearWord(a, b) {
  // Không sửa gần đúng mã/SKU chứa số sang một SKU khác.
  if (/\d/.test(a) || /\d/.test(b)) return false;
  if (a.length < 4 || Math.abs(a.length - b.length) > 1) return false;
  const row = Array.from({ length: b.length + 1 }, (_, i) => i);
  for (let i = 1; i <= a.length; i++) {
    let prev = row[0]; row[0] = i;
    for (let j = 1; j <= b.length; j++) {
      const old = row[j];
      row[j] = Math.min(row[j] + 1, row[j - 1] + 1, prev + (a[i - 1] === b[j - 1] ? 0 : 1));
      prev = old;
    }
  }
  return row[b.length] <= 1;
}

export function matchesSearch(value, query) {
  const text = normalizeSearch(value), words = text.split(/\s+/);
  return normalizeSearch(query).split(/\s+/).every(word => text.includes(word) || words.some(candidate => nearWord(word, candidate)));
}

const sections = [
  ['Sản phẩm', 'san pham product sku hang hoa', '/admin/san-pham'],
  ['Đơn hàng', 'don hang order hoa don', '/admin/don-hang'],
  ['Khách hàng', 'khach hang customer tai khoan', '/admin/khach-hang'],
  ['Danh mục', 'danh muc category nhom hang', '/admin/danh-muc'],
  ['Mã giảm giá', 'ma giam gia coupon voucher uu dai khuyen mai', '/admin/ma-giam-gia'],
  ['Thống kê', 'thong ke doanh thu bao cao dashboard', '/admin/thong-ke/doanh-thu-theo-thang'],
  ['Quản lý hình ảnh', 'quan ly hinh anh image gallery photo', '/admin/san-pham'],
  ['Biến thể và tồn kho', 'bien the ton kho size mau sac stock', '/admin/chi-tiet-san-pham'],
];

export function searchSections(query) {
  return normalizeSearch(query) ? sections.filter(([label, aliases]) => matchesSearch(`${label} ${aliases}`, query)).map(([label, , path]) => ({ label, path })) : [];
}
