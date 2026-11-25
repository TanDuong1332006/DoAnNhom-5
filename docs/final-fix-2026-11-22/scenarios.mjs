import fs from 'node:fs';import {randomUUID} from 'node:crypto';
const dir=new URL('.',import.meta.url),loc=JSON.parse(fs.readFileSync(new URL('uat-location.json',dir))),secret=JSON.parse(fs.readFileSync(loc.root+'/themmoik27be/storage/app/private/demo-access.json')),base='http://127.0.0.1:8047',checks=[];
async function api(label,path,body,token,expected=200){const r=await fetch(base+'/api'+path,{method:body?'POST':'GET',headers:{Accept:'application/json',...(body?{'Content-Type':'application/json'}:{}),...(token?{Authorization:'Bearer '+token}:{})},body:body?JSON.stringify(body):undefined});const j=await r.json();checks.push({label,path,status:r.status,expected,pass:r.status===expected});return j;}
try{
const c=(await api('customer login','/dang-nhap',{email:secret.customer.email,mat_khau:secret.customer.password})).token,a=(await api('admin login','/dang-nhap',{email:secret.admin.email,mat_khau:secret.admin.password})).token;
const email='final-audit-'+randomUUID()+'@example.test',password=randomUUID();
await api('registration creates inactive demo account','/dang-ky',{email,ho_va_ten:'SOFEP Audit Registration',so_dien_thoai:'0900000000',mat_khau:password,xac_nhan_mk:password,role:'customer'},null,201);
await api('unverified registration cannot login','/dang-nhap',{email,mat_khau:password},null,403);
const code='AUDIT_'+randomUUID().slice(0,8).toUpperCase();
await api('create synthetic voucher','/admin/ma-giam-gia/create',{ma_code:code,ten_giam_gia:'SOFEP Final Audit Demo',loai_giam:'co_dinh',gia_tri:10000,gia_tri_toi_thieu:0,so_luong:10,trang_thai:1,doi_tuong:'all'},a,201);
const cart={ho_ten:'SOFEP Audit Voucher',so_dien_thoai:'0900000000',dia_chi_giao:'Dia chi tong hop UAT',phuong_thuc_thanh_toan:'COD',items:[{id:1,id_chi_tiet_san_pham:1,quantity:1}],ma_giam_gia_su_dung:code};
const quote=await api('quote valid voucher','/client/kiem-tra-voucher',cart,c);checks.push({label:'quote amount reduced',pass:JSON.stringify(quote).includes('10000')});
await api('quote invalid voucher','/client/kiem-tra-voucher',{...cart,ma_giam_gia_su_dung:'AUDIT_NOT_EXIST'},c,422);
const order=(await api('checkout valid voucher','/client/dat-hang',cart,c,201)).don_hang;
if(order){await api('invalid order jump blocked','/admin/don-hang/update-status',{id:order.id,trang_thai_don_hang:'da_giao'},a,409);await api('cancel unpaid synthetic order','/admin/don-hang/update-status',{id:order.id,trang_thai_don_hang:'da_huy'},a);await api('repeat cancel idempotent','/admin/don-hang/update-status',{id:order.id,trang_thai_don_hang:'da_huy'},a);}
const all=(await api('variants for out of stock','/admin/chi-tiet-san-pham/get-data',null,a)).data;
const zero=all.find(x=>x.so_luong_ton===0&&x.trang_thai===1);if(zero)await api('out of stock rejected','/client/dat-hang',{...cart,ma_giam_gia_su_dung:null,items:[{id:zero.id_san_pham,id_chi_tiet_san_pham:zero.id,quantity:1}]},c,422);else checks.push({label:'existing out of stock fixture',pass:null,status:'NOT TESTED',reason:'No active zero-stock variant; no catalog changes for audit.'});
}catch(e){checks.push({label:'runner',pass:false,error:e.message});}
const result={passed:checks.every(x=>x.pass!==false),checks,scope:'Synthetic UAT fixtures only; array mailer; real delivery/positive browser OTP not tested.'};fs.writeFileSync(new URL('scenarios.json',dir),JSON.stringify(result,null,2));console.log(JSON.stringify(result));


