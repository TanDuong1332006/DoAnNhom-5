import fs from 'node:fs';
const dir=new URL('.',import.meta.url),loc=JSON.parse(fs.readFileSync(new URL('uat-location.json',dir))),secret=JSON.parse(fs.readFileSync(loc.root+'/themmoik27be/storage/app/private/demo-access.json')),base='http://127.0.0.1:8046/api',checks=[];
async function call(label,p,{body,token,expected=200}={}){const r=await fetch(base+p,{method:body?'POST':'GET',headers:{Accept:'application/json',...(body?{'Content-Type':'application/json'}:{}),...(token?{Authorization:'Bearer '+token}:{})},body:body?JSON.stringify(body):undefined,signal:AbortSignal.timeout(45000)});const j=await r.json();checks.push({label,path:p,status:r.status,pass:r.status===expected});return j;}
try{const tokens={};for(const role of ['customer','admin'])tokens[role]=(await call(role+' login','/dang-nhap',{body:{email:secret[role].email,mat_khau:secret[role].password}})).token;
const before=await call('legacy reviews empty','/admin/danh-gia/get-data',{token:tokens.admin});checks.push({label:'new install review count zero',pass:before.data?.length===0});
const f=await call('customer private platform feedback','/client/gop-y',{token:tokens.customer,expected:201,body:{topic:'website',body:'Gop y tong hop UAT final submission, khong co du lieu ca nhan.'}});
await call('admin sees private feedback','/admin/gop-y/'+f.reference,{token:tokens.admin});
await call('customer cannot read admin feedback','/admin/gop-y/'+f.reference,{token:tokens.customer,expected:403});
await call('admin in-app reply','/admin/gop-y/'+f.reference+'/phan-hoi',{token:tokens.admin,body:{body:'SOFEP da ghi nhan gop y UAT tong hop.',channel:'in_app'}});
const inbox=await call('customer receives reply','/client/thong-bao',{token:tokens.customer}),notification=inbox.data?.data?.find(n=>n.delivery_key?.startsWith('feedback:'+f.reference+':'));
checks.push({label:'reply linked to own inbox',pass:!!notification});if(notification)await call('customer marks reply read','/client/thong-bao/'+notification.id+'/da-doc',{token:tokens.customer,body:{}});
const after=await call('legacy review unaffected','/admin/danh-gia/get-data',{token:tokens.admin});checks.push({label:'feedback is not a product review',pass:after.data?.length===0});
const detail=await call('catalog detail contract remains','/client/demo/san-pham/1');checks.push({label:'reviews rating keys preserved',pass:Array.isArray(detail.data?.reviews)&&detail.data?.rating?.count===0});
}catch(e){checks.push({label:'runner',pass:false,error:e.message});}
const result={passed:checks.every(x=>x.pass),checks,scope:'New isolated MySQL UAT; synthetic feedback/reply/notification only, no real email'};fs.writeFileSync(new URL('feedback-results.json',dir),JSON.stringify(result,null,2));console.log(JSON.stringify(result));
