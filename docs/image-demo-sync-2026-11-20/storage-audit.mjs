import fs from 'node:fs';import path from 'node:path';import {createHash} from 'node:crypto';
import {fileURLToPath} from 'node:url';
const dir=new URL('.',import.meta.url),repo=new URL('../../',dir),read=p=>JSON.parse(fs.readFileSync(new URL(p,repo)));
const data=read('docs/product-photo-approval-2026-11-20/catalog-snapshot.json').data,prior=read('docs/product-photo-approval-2026-11-20/review-data.json');
const hash=f=>createHash('sha256').update(fs.readFileSync(f)).digest('hex');
const local=url=>{if(!url)return null;const p=new URL(url,'http://localhost').pathname;const match=p.match(/^\/api\/product-images\/(\d+)\/([^/]+)$/);return match?`products/${match[1]}/${match[2]}`:p.startsWith('/storage/')?p.slice(9):null;};
const refs=new Set([...data.products.map(p=>p.hinh_anh),...data.gallery.map(g=>g.duong_dan_anh),...data.order_image_references.map(o=>o.hinh_anh)].map(local).filter(Boolean));
const root=new URL('themmoik27be/storage/app/public/',repo),files=[];
function walk(folder){if(!fs.existsSync(folder))return;for(const f of fs.readdirSync(folder,{withFileTypes:true})){const p=path.join(folder,f.name);if(f.isDirectory())walk(p);else if(/\.(jpg|jpeg|png|webp|gif|svg)$/i.test(f.name))files.push(p);}}
walk(fileURLToPath(new URL('products/',root)));
const rows=files.map(f=>{const rel=path.relative(root.pathname.replace(/^\/([A-Za-z]:)/,'$1'),f).replaceAll('\\','/');return {file:rel,sha256:hash(f),referenced:refs.has(rel)};});
const unchanged=[...new Map(prior.images.filter(i=>i.file).map(i=>[i.file,i])).values()].map(i=>({file:i.file,unchanged:fs.existsSync(new URL(i.file,root))&&hash(new URL(i.file,root))===i.sha256}));
if(unchanged.some(i=>!i.unchanged))throw Error('Main file changed');
fs.writeFileSync(new URL('storage-audit.json',dir),JSON.stringify({scope:'storage/app/public/products image files; read only',files:rows,referenced_paths:refs.size,verified_unchanged:unchanged.length,orphan_files:rows.filter(r=>!r.referenced),missing_paths:[...refs].filter(r=>!fs.existsSync(new URL(r,root)))},null,2));
console.log(JSON.stringify({physical:rows.length,unchanged:unchanged.length,orphans:rows.filter(r=>!r.referenced).length}));
