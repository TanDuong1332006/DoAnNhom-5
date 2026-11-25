import fs from 'node:fs';import {pathToFileURL} from 'node:url';
const s=JSON.parse(fs.readFileSync(new URL('./state.json',import.meta.url)));
const {default:config}=await import(pathToFileURL(s.fe_root+'/vite.config.js'));
process.env.VITE_API_URL='http://127.0.0.1:8044/api';process.env.VITE_DEMO_ILLUSTRATIONS='true';
export default {...config,root:s.fe_root,server:{...config.server,host:'127.0.0.1',port:5196,strictPort:true,headers:{'Content-Security-Policy':"default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; connect-src 'self' ws://127.0.0.1:5196 http://127.0.0.1:8044; img-src 'self' data: blob: http://127.0.0.1:8044; font-src 'self' data:"}}};
