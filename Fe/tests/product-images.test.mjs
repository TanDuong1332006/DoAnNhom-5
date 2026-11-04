import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
import { productImageUrl, productImageGallery, imagePlaceholder, isIllustrativeImage } from '../src/services/productImages.js';

test('ảnh thiếu, URL không an toàn và ảnh stock chưa xác minh dùng placeholder; storage theo host API', () => {
  for (const value of ['', null, 'javascript:alert(1)', 'data:image/svg+xml,bad', 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518']) assert.equal(productImageUrl(value), imagePlaceholder);
  assert.equal(productImageUrl('/storage/products/1/front.png', 'http://api.test:8000/api'), 'http://api.test:8000/storage/products/1/front.png');
  assert.equal(productImageUrl('https://assets.example.test/sku-1.png'), 'https://assets.example.test/sku-1.png');
  assert.deepEqual(productImageGallery({ hinh_anh: '/sku-front.png', anh_san_phams: [{ duong_dan_anh: '/sku-back.png' }, { duong_dan_anh: '/sku-front.png' }] }), ['/sku-front.png', '/sku-back.png']);
});

function loadComponent(path, globals) {
  let code = readFileSync(new URL('../src/components/'+path, import.meta.url),'utf8').match(/<script>([\s\S]*?)<\/script>/)[1];
  code = code.replace(/^import .*;\r?\n/gm,'').replace('export default','globalThis.definition =');
  const ctx = { ProductImage: {}, ...globals }; vm.runInNewContext(code,ctx);
  const definition = ctx.definition, state = { ...definition.data(), ...globals?.props };
  for (const [key, fn] of Object.entries(definition.methods || {})) state[key] = fn.bind(state);
  return { definition, state };
}

test('ảnh tải lỗi chuyển placeholder và được thử lại khi đổi source', () => {
  const { definition, state } = loadComponent('shared/ProductImage.vue', { productImageUrl, imagePlaceholder, isIllustrativeImage });
  state.failed = true;
  definition.watch.src.call(state);
  assert.equal(state.failed,false);
  assert.ok(readFileSync(new URL('../src/components/shared/ProductImage.vue',import.meta.url),'utf8').includes('@error="failed = true"'));
});

test('Admin sắp xếp, chọn ảnh chính và upload đúng sản phẩm bằng multipart', async () => {
  const calls = [], revoked = [];
  class Body { constructor() { this.fields = []; } append(key,value) { this.fields.push([key,value]); } }
  const { state } = loadComponent('Admin/SanPham/ProductImages.vue', {
    axios: { post: async (url, body) => { calls.push({url,body}); return { data: { status: 1, data: { id: 7 } } }; } },
    FormData: Body, URL: { createObjectURL: file => 'blob:'+file.name, revokeObjectURL: url => revoked.push(url) },
    props: { product: { id: 7, hinh_anh: '/front.png', anh_san_phams: [{ id: 10, thu_tu: 0, duong_dan_anh: '/front.png' },{ id: 11, thu_tu: 1, duong_dan_anh: '/back.png' }] } }
  });
  state.$emit = () => {}; state.$refs = { files: { value: 'files' } }; state.resetImages();
  state.move(1,-1); state.mainId = 11; await state.save();
  assert.deepEqual(Array.from(calls[0].body.ids),[11,10]); assert.equal(calls[0].body.main_id,11);
  assert.equal(calls[0].url,'http://127.0.0.1:8000/api/admin/san-pham/7/images/order');
  const file = { name: 'correct-sku.png', type: 'image/png', size: 128 };
  state.selectFiles({ target: { files: [file] } }); await state.upload();
  assert.equal(calls[1].url,'http://127.0.0.1:8000/api/admin/san-pham/7/images');
  assert.equal(calls[1].body.fields[0][0],'images[]'); assert.equal(calls[1].body.fields[0][1],file);
  assert.equal(state.pending.length,0); assert.deepEqual(revoked,['blob:correct-sku.png']);
});

test('nhãn minh họa bám filename upload, không gắn nhãn nhầm ảnh thật', () => {
 assert.equal(isIllustrativeImage('http://localhost:8000/api/product-images/1/illustration-12345678-1234-1234-1234-123456789abc.jpg'),true);
 assert.equal(isIllustrativeImage('/actual-product.jpg'),false);
});
