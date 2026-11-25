import fs from 'node:fs';
import {createHash,randomUUID} from 'node:crypto';
const dir=new URL('.',import.meta.url),loc=JSON.parse(fs.readFileSync(new URL('uat-location.json',dir))),base='http://127.0.0.1:8047',origin='http://127.0.0.1:5200';
const access=JSON.parse(fs.readFileSync(loc.root+'/themmoik27be/storage/app/private/demo-access.json')),manifest=JSON.parse(fs.readFileSync(loc.root+'/demo/manifest.json'));
const checks=[];let customer,admin;
async function req(label,path,{method='GET',body,token,expected=200,headers={}}={}){try{const r=await fetch(base+path,{method,headers:{Accept:'application/json',Origin:origin,...(body?{'Content-Type':'application/json'}:{}),...(token?{Authorization:'Bearer '+token}:{}),...headers},body:body?JSON.stringify(body):undefined,signal:AbortSignal.timeout(60000)});const bytes=Buffer.from(await r.arrayBuffer());const json=r.headers.get('content-type')?.includes('json')?JSON.parse(bytes):null;checks.push({label,path,status:r.status,expected,pass:r.status===expected});return {json,bytes,headers:r.headers};}catch(e){checks.push({label,path,pass:false,error:e.message});return {};}}
try{
 const all=(await req('catalog 168','/api/client/san-phams?all=1')).json?.data;
 checks.push({label:'catalog count and placeholder',pass:all?.length===168&&all.find(x=>x.id===172)?.hinh_anh==='/product-placeholder.svg'});
 for(const i of manifest.images){const r=await req('image '+i.product_id,i.url);checks.push({label:'image checksum '+i.product_id,pass:!!r.bytes&&createHash('sha256').update(r.bytes).digest('hex')===i.sha256});}
 await req('placeholder','/product-placeholder.svg');await req('missing image','/api/product-images/1/illustration-00000000-0000-0000-0000-000000000000.jpg',{expected:404});
 const d=(await req('detail gallery variant','/api/client/demo/san-pham/1')).json?.data?.san_pham;
 checks.push({label:'gallery 436 and variant 510 mapping',pass:d?.anh_san_phams.some(x=>x.id===436)&&d.chi_tiet_san_phams.some(x=>x.id===510&&x.id_anh_san_pham===436)});
 for(const role of ['customer','admin']){const r=await req(role+' login','/api/dang-nhap',{method:'POST',body:{email:access[role].email,mat_khau:access[role].password}});if(role==='customer')customer=r.json?.token;else admin=r.json?.token;}
 await req('invalid login','/api/dang-nhap',{method:'POST',body:{email:'absent-final-audit@example.test',mat_khau:randomUUID()},expected:401});
 await req('invalid registration','/api/dang-ky',{method:'POST',body:{email:'invalid'},expected:422});
 await req('invalid OTP','/api/xac-thuc-otp',{method:'POST',body:{email:access.customer.email,otp:'x'},expected:422});
 await req('session','/api/kiem-tra-dang-nhap',{token:customer});
 for(const p of ['san-pham','chi-tiet-san-pham','danh-muc','khach-hang','ma-giam-gia','don-hang'])await req('admin list '+p,'/api/admin/'+p+'/get-data',{token:admin});
 await req('dashboard','/api/admin/thong-ke/tong-quan',{token:admin});
 for(const who of [{name:'guest',token:null,expected:401},{name:'customer',token:customer,expected:403}])await req(who.name+' admin denied','/api/admin/san-pham/get-data',who);
 await req('admin customer checkout denied','/api/client/dat-hang',{method:'POST',token:admin,body:{},expected:403});
 await req('invalid product create','/api/admin/san-pham/create',{method:'POST',token:admin,body:{},expected:422});
 await req('invalid category create','/api/admin/danh-muc/create',{method:'POST',token:admin,body:{},expected:422});
 await req('invalid variant create','/api/admin/chi-tiet-san-pham/create',{method:'POST',token:admin,body:{},expected:422});
 await req('invalid voucher create','/api/admin/ma-giam-gia/create',{method:'POST',token:admin,body:{},expected:422});
 await req('invalid upload','/api/admin/san-pham/1/images',{method:'POST',token:admin,body:{},expected:422});
 for(const p of ['thong-bao/count','thong-bao','vi-uu-dai','tuy-chon-thong-bao','don-hang'])await req('customer '+p,'/api/client/'+p,{token:customer});
 const payload={ho_ten:'SOFEP Final Audit',so_dien_thoai:'0900000000',dia_chi_giao:'Dia chi demo final audit',phuong_thuc_thanh_toan:'COD',items:[{id:1,id_chi_tiet_san_pham:1,quantity:1}]};
 await req('cart quote','/api/client/kiem-tra-voucher',{method:'POST',body:payload,token:customer});
 await req('invalid checkout','/api/client/dat-hang',{method:'POST',body:{},token:customer,expected:422});
 await req('oversell rejected','/api/client/dat-hang',{method:'POST',body:{...payload,items:[{id:1,id_chi_tiet_san_pham:1,quantity:999999}]},token:customer,expected:422});
 for(const type of ['COD','MB_BANK']){
  const o=(await req(type+' checkout','/api/client/dat-hang',{method:'POST',body:{...payload,phuong_thuc_thanh_toan:type},token:customer,expected:201,headers:{'Idempotency-Key':randomUUID()}})).json?.don_hang;
  if(o){await req(type+' own detail','/api/client/don-hang/'+o.id,{token:customer});if(type==='MB_BANK'){
   await req('payment simulation failed','/api/giao-dich/mo-phong',{method:'POST',token:customer,body:{ma_hoa_don:o.ma_don_hang,ket_qua:'failed'}});
   await req('payment simulation success','/api/giao-dich/mo-phong',{method:'POST',token:customer,body:{ma_hoa_don:o.ma_don_hang,ket_qua:'success'}});
   const p=(await req('payment persisted','/api/kiem-tra-thanh-toan/'+o.ma_don_hang,{token:customer})).json;checks.push({label:'payment paid state',pass:p?.da_thanh_toan===true});
  }}
 }
 const allow=await req('CORS allow','/api/client/san-phams',{method:'OPTIONS',expected:204,headers:{'Access-Control-Request-Method':'GET'}});checks.push({label:'CORS allowed origin reflected',pass:allow.headers?.get('access-control-allow-origin')===origin});
 const deny=await req('CORS deny','/api/client/san-phams',{method:'OPTIONS',expected:204,headers:{Origin:'http://untrusted.example','Access-Control-Request-Method':'GET'}});checks.push({label:'CORS untrusted origin not allowed',pass:!deny.headers?.get('access-control-allow-origin')});
}finally{const result={passed:checks.every(x=>x.pass),checks,totals:{checks:checks.length,passed:checks.filter(x=>x.pass).length,failed:checks.filter(x=>!x.pass).length},scope:'Real Laravel/MySQL UAT 8047 only; synthetic orders; no external email/bank; no PO database/storage writes.'};fs.writeFileSync(new URL('http-results.json',dir),JSON.stringify(result,null,2));console.log(JSON.stringify({...result,checks:checks.filter(x=>!x.pass)}));}



