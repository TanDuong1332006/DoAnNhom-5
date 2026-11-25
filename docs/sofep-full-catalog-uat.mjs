import { readFileSync, writeFileSync } from 'node:fs';
import { randomUUID, createHash } from 'node:crypto';
import assert from 'node:assert/strict';

const base = 'http://127.0.0.1:8012';
const session = JSON.parse(readFileSync(process.argv[2], 'utf8'));
assert.equal(session.purpose, 'SOFEP isolated MySQL UAT');
assert.equal(session.api_port, 8012);
async function api(path, payload, token, status = 200) {
  const response = await fetch(base + path, { method: payload ? 'POST' : 'GET', headers: { Accept: 'application/json', ...(payload ? {'Content-Type': 'application/json'} : {}), ...(token ? {Authorization: `Bearer ${token}`} : {}), ...(path === '/api/client/dat-hang' ? {'Idempotency-Key':randomUUID()} : {}) }, body: payload ? JSON.stringify(payload) : undefined, signal: AbortSignal.timeout(30000) });
  const data = await response.json();
  assert.equal(response.status, status, `${path}: HTTP ${response.status}, ${data.message || ''}`);
  return data;
}
assert.equal((await api('/__uat/status')).database, session.database);
const products = (await api('/api/client/san-phams?all=1')).data;
assert.equal(products.length, session.catalog_products);
assert.ok(!products.some(p => [170,171].includes(Number(p.id))));
let index = 0; const images = [];
await Promise.all(Array.from({length:4}, async () => {
  while (index < products.length) {
    const p=products[index++];
    assert.ok(p.hinh_anh.startsWith(base + '/api/product-images/'));
    const response=await fetch(p.hinh_anh, {signal:AbortSignal.timeout(20000)});
    const bytes=Buffer.from(await response.arrayBuffer());
    assert.equal(response.status,200);
    assert.ok(bytes.subarray(0,3).toString('hex')==='ffd8ff' || bytes.subarray(0,8).toString('hex')==='89504e470d0a1a0a' || bytes.toString('ascii',0,4)==='RIFF');
    images.push(createHash('sha256').update(bytes).digest('hex'));
  }
}));
const product=products.find(p=>p.chi_tiet_san_phams?.some(v=>Number(v.so_luong_ton)>0));
assert.ok(product, 'A product with available stock is required.');
const variant=product.chi_tiet_san_phams.find(v=>Number(v.so_luong_ton)>0);
const basket={ma_giam_gia_su_dung:'DEMO10',items:[{id:product.id,id_chi_tiet_san_pham:variant.id,quantity:1,gia_ban:1}]};
const quote=await api('/api/client/kiem-tra-voucher',basket);
const placed=await api('/api/client/dat-hang',{...basket,ho_ten:'Khách thử catalog demo',so_dien_thoai:'0900000000',dia_chi_giao:'UAT, không giao hàng thật',phuong_thuc_thanh_toan:'MB_BANK',tong_thanh_toan:1},null,201);
assert.equal(Number(placed.don_hang.tong_thanh_toan),Number(quote.data.tong_thanh_toan));
await api('/api/giao-dich/mo-phong',{ma_hoa_don:placed.don_hang.ma_don_hang,payment_token:placed.payment_token});
const login=await api('/api/admin-login',{email:'uat-admin@sofep.example.test',mat_khau:session.password});
assert.ok(login.token);
await api('/api/admin/don-hang/update-status',{id:placed.don_hang.id,trang_thai_don_hang:'da_huy'},login.token,409);
for(const state of ['dang_giao','da_giao']) await api('/api/admin/don-hang/update-status',{id:placed.don_hang.id,trang_thai_don_hang:state},login.token);
const stats=await api('/api/admin/thong-ke/tong-quan',null,login.token);
assert.equal(Number(stats.data.tong_doanh_thu),Number(placed.don_hang.tong_thanh_toan));
const detail=await api('/api/client/san-pham/'+product.slug_san_pham+'?presentation=2');
assert.equal(Number(detail.data.sold_count),1);
assert.equal(Number(detail.data.san_pham.chi_tiet_san_phams.find(v=>v.id===variant.id).so_luong_ton),Number(variant.so_luong_ton)-1);
await api('/api/dang-xuat',{},login.token);
const result={checked_at:new Date().toISOString(),database:session.database,products:products.length,valid_primary_images:images.length,distinct_primary_hashes:new Set(images).size,product_id:product.id,product_name:product.ten_san_pham,variant_id:variant.id,order_id:placed.don_hang.id,subtotal:Number(placed.don_hang.tong_tien_hang),discount:Number(placed.don_hang.tien_giam_gia),total:Number(placed.don_hang.tong_thanh_toan),checkout_simulation_admin_completion:true,paid_cancellation_rejected:true,stock_decremented_once:true,revenue_matches_completed_paid_order:true,passed:true};
writeFileSync(new URL('./sofep-full-catalog-uat.json',import.meta.url),JSON.stringify(result,null,2));
console.log(JSON.stringify(result,null,2));
