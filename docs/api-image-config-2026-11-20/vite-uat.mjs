import config from '../../Fe/vite.config.js';
import {fileURLToPath} from 'node:url';
export default {...config,root:fileURLToPath(new URL('../../Fe/',import.meta.url)),server:{...config.server,host:'127.0.0.1',port:5194,strictPort:true,headers:{'Content-Security-Policy':"default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; connect-src 'self' ws://127.0.0.1:5194 http://127.0.0.1:8042; img-src 'self' data: blob: http://127.0.0.1:8042; font-src 'self' data:"}}};
