
import assert from 'node:assert/strict';
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { randomUUID } from 'node:crypto';
const session=JSON.parse(readFileSync(readFileSync(tmpdir()+'/sofep-final-uat-session-path.txt','utf8').trim(),'utf8'));
assert.equal(session.task,'Final142143');
const out=new URL('./',import.meta.url), base='http://127.0.0.1:8030';
const checks=[];let ticket='N5-142',passed=false,summary={};
function check(name,condition,details={}){checks.push({ticket,case:name,passed:!!condition,...details});assert.ok(condition,name);}
async function api(name,path,body,token,status=200,method=body?'POST':'GET',key){
 const r=await fetch(base+path,{method,headers:{Accept:'application/json',...(body instanceof FormData?{}:body?{'Content-Type':'application/json'}:{}),...(token?{Authorization:'Bearer '+token}:{}),...(key?{'Idempotency-Key':key}:{})},body:body instanceof FormData?body:body?JSON.stringify(body):undefined,signal:AbortSignal.timeout(20000)});
 const data=await r.json();check(name,r.status===status,{path,expected:status,actual:r.status});
 return data;
}
let admin,a,b,publicCoupon,privateCoupon;
try{
 const status=await api('UAT identity','/__uat/status');
 check('Database/cache/connection isolated',status.database===session.database&&status.cache_prefix===session.cache_prefix&&status.connections.length===1&&status.connections[0]==='security_browser');
 admin=(await api('Admin login','/api/admin-login',{email:session.accounts.admin.email,mat_khau:session.password})).token;
 a=(await api('Customer A login','/api/dang-nhap',{email:session.accounts.customer.email,mat_khau:session.password})).token;
 b=(await api('Customer B login','/api/dang-nhap',{email:session.accounts.other.email,mat_khau:session.password})).token;
 const catalog=(await api('Guest search catalog','/api/client/san-phams?all=1&tu_khoa='+session.tag)).data;
 check('Search matches fixture',catalog.length===1&&catalog[0].id===session.product);
 const detail=await api('Guest product detail','/api/client/san-pham/'+session.slug);
 check('Variant price and initial stock',detail.data.san_pham.chi_tiet_san_phams.some(v=>v.id===session.variant&&Number(v.so_luong_ton)===30&&Number(v.gia_chenh_lech)===20000));
 const basket={ho_ten:'Final UAT Customer',so_dien_thoai:'0900000143',dia_chi_giao:'Địa chỉ UAT, không giao hàng',phuong_thuc_thanh_toan:'COD',tong_thanh_toan:1,items:[{id:session.product,id_chi_tiet_san_pham:session.variant,quantity:1,gia_ban:1}]};
 await api('Guest order denied','/api/client/dat-hang',basket,null,401);
 await api('Customer admin denied','/api/admin/don-hang/get-data',null,a,403);
 for(let i=0;i<2;i++){
  const order=(await api('Create COD '+i,'/api/client/dat-hang',basket,a,201,'POST',randomUUID())).don_hang;
  check('COD unpaid '+i,order.trang_thai_thanh_toan==='chua_thanh_toan'&&Number(order.tong_thanh_toan)===250000);
  for(const state of ['da_xac_nhan','dang_giao','da_giao']){
   await api('Admin transition COD '+i+' '+state,'/api/admin/don-hang/update-status',{id:order.id,trang_thai_don_hang:state,...(state==='da_giao'?{trang_thai_thanh_toan:'da_thanh_toan'}:{})},admin);
  }
 }
 async function coupon(suffix){
  const code=('F'+session.tag+suffix).toUpperCase();
  await api('Admin creates '+suffix,'/api/admin/ma-giam-gia/create',{ma_code:code,ten_giam_gia:'Final UAT '+suffix,loai_giam:'phan_tram',gia_tri:10,giam_toi_da:50000,gia_tri_toi_thieu:100000,so_luong:20,gioi_han_moi_khach:1,doi_tuong:'all',trang_thai:1},admin,201);
  const list=await api('Read '+suffix,'/api/admin/ma-giam-gia/get-data',null,admin);
  const c=list.data.find(c=>c.ma_code===code);check('Voucher exists '+suffix,!!c);return c;
 }
 publicCoupon=await coupon('PUBLIC');
 const sim={...basket,phuong_thuc_thanh_toan:'MB_BANK',ma_giam_gia_su_dung:publicCoupon.ma_code,items:[{...basket.items[0],quantity:2}]};
 await api('Invalid voucher rejected','/api/client/kiem-tra-voucher',{...sim,ma_giam_gia_su_dung:'INVALID_FINAL'},a,422);
 const quote=await api('Server quote','/api/client/kiem-tra-voucher',sim,a);
 check('Server total ignores forged prices',Number(quote.data.tong_thanh_toan)===426000&&Number(quote.data.tien_giam_gia)===44000);
 const key=randomUUID();
 const order=(await api('Create simulated order','/api/client/dat-hang',sim,a,201,'POST',key)).don_hang;
 const repeat=(await api('Repeat checkout same key','/api/client/dat-hang',sim,a,201,'POST',key)).don_hang;
 check('No duplicate order',repeat.id===order.id);
 await api('Other customer payment status forbidden','/api/kiem-tra-thanh-toan/'+order.ma_don_hang,null,b,403);
 await api('Payment failure simulation','/api/giao-dich/mo-phong',{ma_hoa_don:order.ma_don_hang,ket_qua:'failed'},a);
 check('Failed simulation remains unpaid',(await api('Read failed payment','/api/kiem-tra-thanh-toan/'+order.ma_don_hang,null,a)).don_hang.trang_thai_thanh_toan==='chua_thanh_toan');
 await api('Payment success','/api/giao-dich/mo-phong',{ma_hoa_don:order.ma_don_hang},a);
 await api('Payment success retry','/api/giao-dich/mo-phong',{ma_hoa_don:order.ma_don_hang},a);
 for(const state of ['da_xac_nhan','dang_giao','da_giao'])await api('Admin simulated lifecycle '+state,'/api/admin/don-hang/update-status',{id:order.id,trang_thai_don_hang:state},admin);
 const history=await api('Customer history','/api/client/don-hang',null,a);
 check('History exactly three owned orders',history.data.total===3);
 const own=await api('Owner detail latest status','/api/client/don-hang/'+order.id,null,a);
 check('History reflects delivered and paid',own.data.trang_thai_don_hang==='da_giao'&&own.data.trang_thai_thanh_toan==='da_thanh_toan'&&own.data.chi_tiets[0].so_luong===2);
 await api('Other customer detail 404','/api/client/don-hang/'+order.id,null,b,404);
 check('Other history empty',(await api('Other customer own list','/api/client/don-hang',null,b)).data.total===0);
 const finalProduct=(await api('Stock after checkout retries','/api/client/san-pham/'+session.slug)).data.san_pham;
 check('Stock decremented exactly four',Number(finalProduct.chi_tiet_san_phams.find(v=>v.id===session.variant).so_luong_ton)===26);
 summary.N5142={order:order.id,code:order.ma_don_hang,total:426000,stock:26};
 ticket='N5-143';
 const form=new FormData();form.append('topic','service');form.append('body','Customer Final UAT gửi góp ý riêng tư cần phản hồi.');
 form.append('images[]',new Blob([Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=','base64')],{type:'image/png'}),'fixture.png');
 const feedback=await api('Customer private feedback with attachment','/api/client/gop-y',form,a,201);
 const f=await api('Admin reads feedback','/api/admin/gop-y/'+feedback.reference,null,admin);
 check('Feedback belongs to A and attachment exists',f.data.customer_id===session.accounts.customer.id&&f.images.length===1);
 for(const token of [null,a,b]){
  await api('Private feedback access denied '+(token===null?'Guest':token===a?'A':'B'),'/api/admin/gop-y/'+feedback.reference,null,token,token?403:401);
  const image=await fetch(base+'/api/admin/gop-y/'+feedback.reference+'/anh/'+f.images[0].id,{headers:{Accept:'application/json',...(token?{Authorization:'Bearer '+token}:{})}});
  check('Private attachment denied '+(token===null?'Guest':token===a?'A':'B'),image.status===(token?403:401));
 }
 const image=await fetch(base+'/api/admin/gop-y/'+feedback.reference+'/anh/'+f.images[0].id,{headers:{Authorization:'Bearer '+admin}});
 check('Admin image private response',image.status===200&&image.headers.get('cache-control').includes('no-store'));
 await api('Admin processes feedback','/api/admin/gop-y/'+feedback.reference,{status:'processing',internal_note:'Ghi chú riêng Admin UAT'},admin,200,'PATCH');
 await api('Admin resolves feedback','/api/admin/gop-y/'+feedback.reference,{status:'resolved',internal_note:'Đã xử lý UAT'},admin,200,'PATCH');
 await api('Admin reply in-app','/api/admin/gop-y/'+feedback.reference+'/phan-hoi',{body:'SOFEP đã nhận và xử lý góp ý UAT.',channel:'in_app'},admin);
 const replies=(await api('A inbox private reply','/api/client/thong-bao',null,a)).data.data;
 check('Reply delivered to A',replies.some(n=>n.title.includes('#'+feedback.reference)));
 check('B cannot see reply',!(await api('B inbox no private reply','/api/client/thong-bao',null,b)).data.data.some(n=>n.title.includes('#'+feedback.reference)));
 privateCoupon=await coupon('PRIVATE');
 const campaign={title:'Tri ân Final UAT '+session.tag,body:'Voucher nội bộ cho Customer UAT đủ điều kiện.',kind:'voucher',voucher_id:privateCoupon.id,filters:{period:'all',orders:{min:3,max:3},spend:{min:926000,max:926000}},recheck:true,request_key:randomUUID()};
 const preview=await api('Preview eligible audience','/api/admin/chien-dich/xem-truoc',campaign,admin);
 check('Preview selects exactly one fixture',preview.data.count===1);
 const stored=await api('Create campaign','/api/admin/chien-dich',{...campaign,preview_hash:preview.data.preview_hash},admin,201);
 check('Recipient count one',stored.data.recipient_count===1);
 check('Create retry same campaign',(await api('Campaign idempotency','/api/admin/chien-dich',{...campaign,preview_hash:preview.data.preview_hash},admin,201)).data.id===stored.data.id);
 await api('Customer cannot dispatch','/api/admin/chien-dich/giao-lo',{},a,403);
 check('Dispatch one eligible recipient',(await api('Dispatch campaign','/api/admin/chien-dich/giao-lo',{},admin)).delivered===1);
 check('Dispatch retry no duplicates',(await api('Dispatch retry','/api/admin/chien-dich/giao-lo',{},admin)).delivered===0);
 const notes=(await api('A receives campaign','/api/client/thong-bao',null,a)).data.data;
 const note=notes.find(n=>n.voucher_id===privateCoupon.id);check('Private voucher notification exists',!!note);
 check('B receives no private voucher',!(await api('B notifications isolated','/api/client/thong-bao',null,b)).data.data.some(n=>n.voucher_id===privateCoupon.id));
 await api('B cannot mark A notification read','/api/client/thong-bao/'+note.id+'/da-doc',{},b,404);
 await api('B cannot save private voucher','/api/client/vi-uu-dai/'+privateCoupon.id+'/luu',{},b,422);
 await api('A saves voucher','/api/client/vi-uu-dai/'+privateCoupon.id+'/luu',{},a);
 await api('A saves voucher retry','/api/client/vi-uu-dai/'+privateCoupon.id+'/luu',{},a);
 const wallet=(await api('A wallet','/api/client/vi-uu-dai',null,a)).data.data;
 check('Saved once and available',wallet.filter(v=>v.id===privateCoupon.id).length===1&&wallet.find(v=>v.id===privateCoupon.id).state==='available');
 check('B wallet isolated',(await api('B wallet','/api/client/vi-uu-dai',null,b)).data.total===0);
 const privateBasket={...basket,ma_giam_gia_su_dung:privateCoupon.ma_code};
 await api('B private quote rejected','/api/client/kiem-tra-voucher',privateBasket,b,422);
 const privateQuote=await api('A private quote valid','/api/client/kiem-tra-voucher',privateBasket,a);
 check('Private server quote 228000',Number(privateQuote.data.tong_thanh_toan)===228000);
 const privateOrder=(await api('A uses granted voucher','/api/client/dat-hang',privateBasket,a,201,'POST',randomUUID())).don_hang;
 check('Wallet used state',(await api('Wallet after use','/api/client/vi-uu-dai',null,a)).data.data.find(v=>v.id===privateCoupon.id).state==='used');
 await api('Per customer private quota rejected','/api/client/kiem-tra-voucher',privateBasket,a,422);
 for(let i=0;i<2;i++)await api('Cancel private order '+i,'/api/admin/don-hang/update-status',{id:privateOrder.id,trang_thai_don_hang:'da_huy'},admin);
 check('Wallet available after cancellation',(await api('Wallet restored','/api/client/vi-uu-dai',null,a)).data.data.find(v=>v.id===privateCoupon.id).state==='available');
 const coupons=(await api('Voucher counters final','/api/admin/ma-giam-gia/get-data',null,admin)).data;
 check('Public usage once/private usage restored',Number(coupons.find(c=>c.id===publicCoupon.id).so_luong_da_dung)===1&&Number(coupons.find(c=>c.id===privateCoupon.id).so_luong_da_dung)===0);
 await api('A read all notifications','/api/client/thong-bao/doc-tat-ca',{},a);
 check('A unread zero',(await api('A unread count','/api/client/thong-bao/count',null,a)).unread===0);
 await api('B opt-out','/api/client/tuy-chon-thong-bao',{marketing_enabled:false},b,200,'PUT');
 check('Opt-out persisted',(await api('B marketing preference','/api/client/tuy-chon-thong-bao',null,b)).marketing_enabled===false);
 summary.N5143={feedback:feedback.reference,campaign:stored.data.id,voucher:privateCoupon.id,notification:note.id,privateOrder:privateOrder.id};
 passed=true;
}finally{
 const result={checked_at:new Date().toISOString(),database:session.database,cache_prefix:session.cache_prefix,frontend:'http://127.0.0.1:5190',backend:base,passed,checks,summary,external_mail_sent:false,type:'HTTP API MySQL UAT; not browser'};
 writeFileSync(new URL('http-uat.json',out),JSON.stringify(result,null,2));
 console.log(JSON.stringify({passed,checks:checks.length,summary}));
}

