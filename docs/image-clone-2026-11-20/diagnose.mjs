import fs from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';
const root=path.dirname(fileURLToPath(import.meta.url));
const previous=JSON.parse(fs.readFileSync(path.join(root,'../image-audit-2026-11-20/inventory.json')));
const failed=Object.entries(previous.checks).filter(([,c])=>c.status==='unreachable');
const groups={};for(const [u] of failed){const origin=new URL(u).origin;groups[origin]=(groups[origin]||0)+1;}
const all=process.argv.includes('--all');
const cases=[['known-good',previous.rows.find(r=>r.main).url],...(all?failed:failed.slice(0,5)).map(([u],i)=>['old-timeout-'+i,u]),['health','http://127.0.0.1:8000/up'],['missing-route','http://127.0.0.1:8000/__image_diagnostic_missing']];
const checks=[];
for(const [label,url] of cases){const start=performance.now();try{const res=await fetch(url,{headers:{Connection:'close'},signal:AbortSignal.timeout(20000)});const bytes=Buffer.from(await res.arrayBuffer());checks.push({label,url,status:res.status,ms:Math.round(performance.now()-start),bytes:bytes.length,sha256:(await import('node:crypto')).createHash('sha256').update(bytes).digest('hex')});}catch(e){checks.push({label,url,error:e.name,ms:Math.round(performance.now()-start)});}}
fs.writeFileSync(path.join(root,all?'timeout-diagnostics-all.json':'timeout-diagnostics.json'),JSON.stringify({old_timeout_origins:groups,old_concurrency:8,old_timeout_ms:8000,recheck_concurrency:1,recheck_timeout_ms:20000,checks},null,2));
console.log(JSON.stringify({groups,tested:checks.length,http_200:checks.filter(c=>c.status===200).length,errors:checks.filter(c=>c.error||c.status>=400)},null,2));
