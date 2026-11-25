
import {readFileSync,writeFileSync} from 'node:fs';
import {tmpdir} from 'node:os';import assert from 'node:assert/strict';
const s=JSON.parse(readFileSync(readFileSync(tmpdir()+'/sofep-final-uat-session-path.txt','utf8').trim(),'utf8'));
const prior=JSON.parse(readFileSync(new URL('./http-uat.json',import.meta.url),'utf8')),checks=[];
async function api(name,path,body,token,status=200,method=body?'POST':'GET'){
 const r=await fetch('http://127.0.0.1:8030/api/'+path,{method,headers:{Accept:'application/json',...(body?{'Content-Type':'application/json'}:{}),...(token?{Authorization:'Bearer '+token}:{})},body:body?JSON.stringify(body):undefined,signal:AbortSignal.timeout(20000)});
 const d=await r.json();checks.push({case:name,expected:status,actual:r.status,passed:r.status===status});assert.equal(r.status,status);return d;
}
const a=(await api('Login fixture A','dang-nhap',{email:s.accounts.customer.email,mat_khau:s.password})).token;
const admin=(await api('Login fixture Admin','admin-login',{email:s.accounts.admin.email,mat_khau:s.password})).token;
const campaign={title:'Opt-out UAT '+s.tag,body:'Preview only; no dispatch.',kind:'promotion',filters:{period:'all',orders:{min:3,max:3},spend:{min:926000,max:926000}}};
await api('Eligible A opts out','client/tuy-chon-thong-bao',{marketing_enabled:false},a,200,'PUT');
const p=await api('Preview excludes opted-out eligible A','admin/chien-dich/xem-truoc',campaign,admin);
checks.push({case:'No recipient after eligible opt-out',passed:p.data.count===0});assert.equal(p.data.count,0);
await api('Restore fixture preference','client/tuy-chon-thong-bao',{marketing_enabled:true},a,200,'PUT');
const restored=await api('Preview restores eligible A','admin/chien-dich/xem-truoc',campaign,admin);
checks.push({case:'Exactly one recipient restored',passed:restored.data.count===1});assert.equal(restored.data.count,1);
const id=prior.summary.N5142.order;
await api('History Guest detail denied','client/don-hang/'+id,null,null,401);
await api('History Admin denied','client/don-hang',null,admin,403);
await api('Invalid history page rejected','client/don-hang?page=0',null,a,422);
await api('Logout fixture A','dang-xuat',{},a);
await api('Revoked token history denied','client/don-hang',null,a,401);
writeFileSync(new URL('./http-extra.json',import.meta.url),JSON.stringify({database:s.database,passed:true,checks,external_messages_sent:false},null,2));
console.log(JSON.stringify({passed:true,checks:checks.length}));

