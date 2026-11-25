// Chỉ cấu hình cho server frontend UAT; không sửa cấu hình source hoặc môi trường chính.
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath,pathToFileURL} from 'node:url';
const dir=path.dirname(fileURLToPath(import.meta.url));
const input=JSON.parse(fs.readFileSync(path.join(dir,'inputs.json')));
const fe=path.join(input.source,'Fe');
const {default:original}=await import(pathToFileURL(path.join(fe,'vite.config.js')).href);
export default {...original,root:fe,server:{...original.server,host:'127.0.0.1',port:5192,strictPort:true,headers:{
 'Content-Security-Policy':"default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; connect-src 'self' ws://127.0.0.1:5192 http://127.0.0.1:8042; img-src 'self' data: http://127.0.0.1:8042; font-src 'self' data:",
}}};
