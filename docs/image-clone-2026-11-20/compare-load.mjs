import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const dir=path.dirname(fileURLToPath(import.meta.url));
const old=JSON.parse(fs.readFileSync(path.join(dir,'../image-audit-2026-11-20/inventory.json')));
const urls=Object.entries(old.checks).filter(([,c])=>c.error).slice(0,8).map(([u])=>u);
const batches=[];
for(const timeout of [8000,20000]){
 const checks=await Promise.all(urls.map(async url=>{const start=performance.now();try{const r=await fetch(url,{headers:{Connection:'close'},signal:AbortSignal.timeout(timeout)});const b=await r.arrayBuffer();return{url,status:r.status,bytes:b.byteLength,ms:Math.round(performance.now()-start)};}catch(e){return{url,error:e.name,ms:Math.round(performance.now()-start)};}}));
 batches.push({concurrency:8,timeout_ms:timeout,checks});
}
fs.writeFileSync(path.join(dir,'concurrency-comparison.json'),JSON.stringify(batches,null,2));
console.log(JSON.stringify(batches.map(b=>({concurrency:b.concurrency,timeout_ms:b.timeout_ms,success:b.checks.filter(c=>c.status===200).length,timeout:b.checks.filter(c=>c.error).length})),null,2));
