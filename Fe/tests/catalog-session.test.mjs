import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { adminToken, clearAdminSession } from '../src/services/authSessions.js';
import { readFileSync } from 'node:fs';
import * as catalog from '../src/services/productCatalog.js';
import * as detail from '../src/services/productDetail.js';
import { isIllustrativeImage } from '../src/services/productImages.js';

function component(file, globals = {}) {
  let script = readFileSync(new URL('../src/' + file, import.meta.url), 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1];
  script = script.replace(/^import .*;\r?\n/gm, '').replace('export default', 'globalThis.component =');
  const context = { ...catalog, ...detail, isIllustrativeImage, $refs: {}, cartStore: { items: [] }, ProductPurchase: {}, ProductImage: {}, ...globals };
  vm.runInNewContext(script, context);
  const definition = context.component;
  const state = definition.data ? definition.data() : {};
  state.$refs = {};
  Object.assign(state, globals.props);
  for (const [key, value] of Object.entries(definition.methods || {})) state[key] = value.bind(state);
  for (const [key, value] of Object.entries(definition.computed || {})) Object.defineProperty(state, key, { get: value.bind(state) });
  return state;
}

const product = { id: 1, gia_ban: 200000, gia_khuyen_mai: 180000, so_luong_ton: 999, chi_tiet_san_phams_count: 2,
  hinh_anh: 'front.jpg', anh_san_phams: [{ duong_dan_anh: 'back.jpg' }, { duong_dan_anh: 'front.jpg' }],
  chi_tiet_san_phams: [
    { id: 10, id_mau_sac: 1, id_kich_thuoc: 2, trang_thai: 1, so_luong_ton: 3, gia_chenh_lech: 25000, mau_sac: { ten_mau: 'Đen' }, kich_thuoc: { ten_kich_thuoc: 'M' } },
    { id: 11, trang_thai: 0, so_luong_ton: 100, gia_chenh_lech: 0 }
  ] };

test('giá, ảnh và tồn kho lấy đúng biến thể; không dùng tồn kho tổng khi chưa chọn', () => {
  assert.equal(catalog.variantPrice(product, product.chi_tiet_san_phams[0]), 205000);
  assert.equal(catalog.availableStock(product), 0);
  assert.equal(catalog.availableStock(product, product.chi_tiet_san_phams[0]), 3);
  assert.equal(catalog.variantsOf(product).length, 1);
  assert.deepEqual(catalog.galleryOf(product), ['front.jpg', 'back.jpg']);
});

test('bộ chọn chặn biến thể ẩn, số lượng vượt kho và truyền đúng màu/size/giá vào giỏ', () => {
  const calls = [];
  const state = component('components/Client/ProductPurchase.vue', { props: { product }, cartStore: { addItem: (...args) => calls.push(args) } });
  assert.equal(state.canAdd, false);
  state.colorKey = '999';
  assert.equal(state.canAdd, false);
  state.colorKey = '1';
  state.sizeKey = '2';
  state.quantity = 4;
  assert.equal(state.canAdd, false);
  state.quantity = 2;
  assert.equal(state.canAdd, true);
  state.add();
  assert.equal(calls[0][0].gia_ban, 205000);
  assert.equal(calls[0][1], 2);
  assert.equal(calls[0][2].ten_mau, 'Đen');
  assert.equal(calls[0][3].ten_kich_thuoc, 'M');
  assert.equal(calls[0][4].id, 10);
});

test('API lỗi: trang chủ, tìm kiếm và chi tiết xóa dữ liệu cũ và hiển thị lỗi, không dựng sản phẩm', async () => {
  const axios = { get: async () => { throw { response: { status: 404 } }; } };
  const home = component('components/Client/TrangChu.vue', { axios });
  home.san_phams = [product];
  await home.loadData();
  assert.equal(home.san_phams.length, 0);
  assert.ok(home.error);
  const search = component('components/Client/TimKiem.vue', { axios });
  search.products = [product];
  await search.taiSanPham();
  assert.equal(search.products.length, 0);
  assert.ok(search.error);
  const detail = component('components/Client/ChiTietSanPham.vue', { axios });
  detail.product = product;
  await detail.loadProduct('hidden');
  assert.equal(detail.product, null);
  assert.equal(detail.images.length, 0);
  assert.match(detail.error, /không tồn tại|ngừng bán/);
});

test('API rỗng giữ danh sách rỗng, không báo lỗi', async () => {
  const axios = { get: async () => ({ data: { status: 1, data: [] } }) };
  const home = component('components/Client/TrangChu.vue', { axios });
  await home.loadData();
  assert.equal(home.san_phams.length, 0);
  assert.equal(home.error, '');
  assert.equal(home.flash_sale_products.length, 0);
  const search = component('components/Client/TimKiem.vue', { axios });
  await search.taiSanPham();
  assert.equal(search.products.length, 0);
  assert.equal(search.error, '');
});

test('Quick View tải chi tiết đúng sản phẩm và đặt lại lựa chọn khi đổi sản phẩm', async () => {
  const urls = [];
  const home = component('components/Client/TrangChu.vue', {
    axios: { get: async url => { urls.push(url); return { data: { status: 1, data: { san_pham: product } } }; } },
    document: { getElementById: () => null }, window: {}
  });
  home.$nextTick = async () => {};
  await home.moQuickView({ slug_san_pham: 'ao-sofep' });
  assert.equal(urls[0], 'http://127.0.0.1:8000/api/client/san-pham/ao-sofep');
  assert.equal(home.selectedProduct, product);
  assert.equal(home.selectedProduct.chi_tiet_san_phams[0].id, 10);
});

test('đổi URL nhanh không để response cũ ghi đè chi tiết sản phẩm mới', async () => {
  let resolveFirst;
  const state = component('components/Client/ChiTietSanPham.vue', {
    axios: { get: url => url.includes('/first?') ? new Promise(resolve => { resolveFirst = resolve; }) : Promise.resolve({ data: { status: 1, data: { san_pham: { id: 2 } } } }) }
  });
  const first = state.loadProduct('first');
  await state.loadProduct('second');
  resolveFirst({ data: { status: 1, data: { san_pham: { id: 1 } } } });
  await first;
  assert.equal(state.product.id, 2);
});

async function guardCase(response, reject = false) {
  const stored = new Map([['auth_token', 'fake-token'], ['user', JSON.stringify({ role: 'admin' })]]);
  const paths = [];
  const source = readFileSync(new URL('../src/router/checkLogin.js', import.meta.url), 'utf8')
    .replace(/^import .*;\r?\n/gm, '').replace('export default function checkLogin', 'globalThis.checkLogin = function');
  const context = {
    axios: { get: async () => { if (reject) throw response; return { data: response }; } },
    useToast: () => ({ error() {}, warning() {} }),
    localStorage: { getItem: key => stored.get(key), setItem: (key, value) => stored.set(key, value), removeItem: key => stored.delete(key) }
  };
  context.adminToken = () => adminToken(context.localStorage);
  context.clearAdminSession = token => clearAdminSession(token, context.localStorage);
  vm.runInNewContext(source, context);
  await context.checkLogin({}, {}, path => paths.push(path));
  return { stored, paths };
}

test('guard không tin role Admin trong localStorage khi API lỗi mạng/401/phiên hết hạn', async () => {
  for (const error of [new Error('Network'), { response: { status: 401 } }]) {
    const result = await guardCase(error, true);
    assert.deepEqual(result.paths, ['/dang-nhap']);
    assert.equal(result.stored.has('auth_token'), false);
    assert.equal(result.stored.has('user'), false);
  }
});

test('guard chỉ cho Admin được server xác nhận, từ chối Customer', async () => {
  assert.deepEqual((await guardCase({ status: 1, nguoi_dung: { role: 'admin' } })).paths, [undefined]);
  assert.deepEqual((await guardCase({ status: 1, nguoi_dung: { role: 'customer' } })).paths, ['/']);
});


test('trang chính tải theo slug, bỏ qua query demo và giữ dữ liệu từng sản phẩm', async () => {
  const urls = [];
  const state = component('components/Client/ChiTietSanPham.vue', { axios: { get: async url => { urls.push(url); return { data: { status: 1, data: { san_pham: { ...product, slug_san_pham: 'ao-thuc' }, rating: { count: 0, average: null }, sold_count: 3 } } }; } } });
  state.$route = { params: { slug: 'ao-thuc' }, query: { product: 1 } };
  assert.equal(state.currentId, 'ao-thuc');
  await state.loadProduct(state.currentId);
  assert.equal(urls[0], 'http://127.0.0.1:8000/api/client/san-pham/ao-thuc?presentation=2');
  assert.equal(state.product.slug_san_pham, 'ao-thuc');
  assert.equal(state.soldCount, 3);
  state.$route.params = {};
  assert.equal(state.currentId, undefined);
});

test('chi tiết không có size và hết hàng: giữ ID thực, chặn CTA khi hết tồn', () => {
  const state = component('components/Client/ChiTietSanPham.vue');
  state.product = { id: 4, gia_ban: 100, chi_tiet_san_phams: [{ id: 45, trang_thai: 1, id_mau_sac: null, id_kich_thuoc: null, so_luong_ton: 2 }] };
  Object.assign(state, detail.initialSelection(state.product));
  assert.equal(state.showSizes, false);
  assert.equal(state.selectedVariant.id, 45);
  assert.equal(state.canPurchase, true);
  state.product.chi_tiet_san_phams[0].so_luong_ton = 0;
  assert.equal(state.canPurchase, false);
  assert.equal(state.maximumQuantity, 0);
});

test('đổi màu chuyển ảnh và giữ size hợp lệ, không tự chọn size thay thế', () => {
  const state = component('components/Client/ChiTietSanPham.vue');
  const variant = { ...product.chi_tiet_san_phams[0], id_mau_sac: 1, id_kich_thuoc: 2 };
  state.product = { ...product, anh_san_phams: [{ id: 7, duong_dan_anh: '/storage/blue.jpg' }],
    chi_tiet_san_phams: [variant, { ...variant, id: 12, id_mau_sac: 3, id_kich_thuoc: 4, id_anh_san_pham: 7,
      mau_sac: { ten_mau: 'Xanh' }, kich_thuoc: { ten_kich_thuoc: 'L' } }] };
  state.colorKey = null; state.sizeKey = 'none';
  assert.equal(state.displayStock, 6);
  state.colorKey = '1'; state.sizeKey = '2';
  state.chooseColor('1');
  assert.equal(state.sizeKey, '2');
  state.chooseColor('3');
  assert.equal(state.sizeKey, null);
  assert.equal(state.displayStock, 6);
  assert.equal(state.sizes.length, 1);
  assert.equal(state.activeImage, 'http://127.0.0.1:8000/storage/blue.jpg');
  assert.equal(state.sizeAvailable(variant), false);
  assert.equal(state.canPurchase, false);
  state.chooseSize(state.sizes[0]);
  assert.equal(state.selectedVariant.id, 12);
  assert.equal(state.displayStock, 3);
  assert.equal(state.canPurchase, true);
  state.product.chi_tiet_san_phams[1].so_luong_ton = 0;
  assert.equal(state.sizes.length, 1);
  assert.equal(state.sizeAvailable(state.sizes[0]), false);
  assert.equal(state.canPurchase, false);
});

test('Admin lưu nhiều size của cùng một màu với tồn riêng và xóa đúng dòng đã xác nhận', async () => {
  const calls = [];
  const state = component('components/Admin/SanPham/ProductVariants.vue', {
    props: { product: { id: 9 } },
    axios: {
      get: async () => ({ data: { data: [], supports_color_image: false } }),
      post: async (url, payload) => { calls.push({ url, payload }); return { data: {} }; }
    }
  });
  state.$emit = () => {};
  state.draft.id_mau_sac = 3;
  state.draft.id_anh_san_pham = 7;
  state.selectedSizes = [4, 5];
  state.stockBySize = { 4: 12, 5: 0 };
  await state.save();
  assert.equal(calls.length, 2);
  assert.deepEqual(calls.map(({ payload }) => [payload.id_mau_sac, payload.id_kich_thuoc, payload.so_luong_ton]), [[3, 4, 12], [3, 5, 0]]);
  assert.ok(calls.every(({ payload }) => !Object.hasOwn(payload, 'id_anh_san_pham')));
  state.pendingDelete = { id: 21 };
  await state.remove();
  assert.match(calls[2].url, /\/destroy$/);
  assert.equal(calls[2].payload.id, 21);
});
