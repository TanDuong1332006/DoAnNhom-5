import test from 'node:test';
import assert from 'node:assert/strict';
import { galleryOf } from '../src/services/productCatalog.js';
import { colorGroups, sizesFor, initialSelection, findVariant, discountFor, remainingQuantity, purchaseSelection, productGallery, relatedProducts } from '../src/services/productDetail.js';

const product = {
  id: 9, ten_san_pham: 'Test only', gia_ban: 350000, gia_khuyen_mai: 290000, chi_tiet_san_phams_count: 3,
  hinh_anh: '/front.jpg', anh_san_phams: [{ duong_dan_anh: '/front.jpg' }],
  chi_tiet_san_phams: [
    { id: 90, id_mau_sac: 1, id_kich_thuoc: 1, trang_thai: 1, so_luong_ton: 4, gia_chenh_lech: 20000, mau_sac: { ten_mau: 'Đen' }, kich_thuoc: { ten_kich_thuoc: 'M' } },
    { id: 91, id_mau_sac: 1, id_kich_thuoc: 2, trang_thai: 1, so_luong_ton: 0, gia_chenh_lech: 0, mau_sac: { ten_mau: 'Đen' }, kich_thuoc: { ten_kich_thuoc: 'L' } },
    { id: 92, id_mau_sac: 2, id_kich_thuoc: 3, trang_thai: 1, so_luong_ton: 2, gia_chenh_lech: -10000, mau_sac: { ten_mau: 'Trắng' }, kich_thuoc: { ten_kich_thuoc: 'S' } }
  ]
};

test('đổi màu chỉ cung cấp size có trong đúng màu, không giữ tổ hợp size cũ không tồn tại', () => {
  assert.equal(colorGroups(product).length, 2);
  assert.equal(sizesFor(product, '2').length, 1);
  assert.deepEqual(initialSelection(product, '2'), { colorKey: '2', sizeKey: '3' });
  assert.equal(findVariant(product, '2', '1'), undefined);
  assert.equal(findVariant(product, '2', '3').id, 92);
});

test('giá gốc và khuyến mãi cùng áp dụng chênh lệch; không tạo phần trăm giảm giả', () => {
  assert.deepEqual(discountFor(product, product.chi_tiet_san_phams[0]), { price: 310000, original: 370000, saving: 60000, percent: 16 });
  assert.equal(discountFor({ ...product, gia_khuyen_mai: null }).saving, 0);
  assert.equal(discountFor({ ...product, gia_khuyen_mai: 999999 }).percent, 0);
});

test('một ảnh không bị nhân bản và sản phẩm không có size vẫn chọn được ID biến thể thực', () => {
  assert.deepEqual(galleryOf(product), ['/front.jpg']);
  const simple = { ...product, chi_tiet_san_phams: [{ id: 100, id_mau_sac: null, id_kich_thuoc: null, trang_thai: 1, so_luong_ton: 2 }] };
  const selection = initialSelection(simple);
  assert.deepEqual(selection, { colorKey: 'none', sizeKey: 'none' });
  assert.equal(findVariant(simple, selection.colorKey, selection.sizeKey).id, 100);
});

test('tồn kho tính cả số lượng đã có trong giỏ cũ và giỏ có variant ID', () => {
  const items = [
    { id: 9, id_chi_tiet_san_pham: 90, so_luong: 1 },
    { id: 9, mau_sac: 'Đen', kich_thuoc: 'M', so_luong: 2 },
    { id: 9, id_chi_tiet_san_pham: 92, so_luong: 1 }
  ];
  assert.equal(remainingQuantity(product, product.chi_tiet_san_phams[0], items), 1);
});

test('tái sử dụng cartStore thật: giữ ID biến thể, lưu local, tính tiền và mở checkout Mua ngay', async () => {
  const saved = new Map();
  globalThis.localStorage = { getItem: key => saved.get(key), setItem: (key, value) => saved.set(key, value) };
  try {
    const { cartStore } = await import('../src/stores/cartStore.js?demo-regression');
    assert.equal(purchaseSelection(cartStore, product, product.chi_tiet_san_phams[0], 2, true), true);
    assert.equal(cartStore.items[0].id_chi_tiet_san_pham, 90);
    assert.equal(cartStore.items[0].gia, 310000);
    assert.equal(cartStore.items[0].mau_sac, 'Đen');
    assert.equal(cartStore.subtotal, 620000);
    assert.equal(cartStore.isOpenCheckout, true);
    assert.equal(cartStore.isOpenDrawer, false);
    assert.equal(JSON.parse(saved.get('fashion_store_cart'))[0].id_chi_tiet_san_pham, 90);
    assert.equal(purchaseSelection(cartStore, product, product.chi_tiet_san_phams[0], 3), false);
    assert.equal(purchaseSelection(cartStore, product, product.chi_tiet_san_phams[1], 1), false);
    assert.equal(purchaseSelection(cartStore, product, { id: 999 }, 1), false);
    assert.equal(purchaseSelection(cartStore, product, product.chi_tiet_san_phams[0], 1.5), false);
    cartStore.addItem({ id: 20, ten_san_pham: 'Legacy', gia_ban: 100 }, 1);
    assert.equal(cartStore.items[1].itemKey, '20_Tiêu chuẩn_Freesize');
    assert.equal(cartStore.items[1].id_chi_tiet_san_pham, undefined);
  } finally { delete globalThis.localStorage; }
});


test('demo chặn ảnh stock chưa xác minh, giữ gallery riêng và không nhân bản', () => {
  assert.deepEqual(productGallery({ hinh_anh: 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500' }), []);
  assert.deepEqual(productGallery({ hinh_anh: '/sku-1.jpg', anh_san_phams: [{ duong_dan_anh: '/sku-1.jpg' }, { duong_dan_anh: '/sku-1-back.jpg' }] }), ['/sku-1.jpg', '/sku-1-back.jpg']);
  assert.deepEqual(productGallery(null), []);
});
test('sản phẩm liên quan chỉ từ catalog cùng danh mục và loại sản phẩm hiện tại', () => {
  const catalog = [{ id: 1, id_danh_muc: 2 }, { id: 2, id_danh_muc: 2 }, { id: 3, id_danh_muc: 3 }];
  assert.deepEqual(relatedProducts(catalog, catalog[0]), [catalog[1]]);
  assert.deepEqual(relatedProducts(catalog, null), []);
});
