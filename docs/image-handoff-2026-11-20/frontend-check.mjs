import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const dir=path.dirname(fileURLToPath(import.meta.url));
const page=await fetch('http://127.0.0.1:5192/',{headers:{Accept:'text/html'}});
const components={};
for(const name of ['TrangChu','TimKiem','ChiTietSanPham']){
 const response=await fetch(`http://127.0.0.1:5192/src/components/Client/${name}.vue`);
 const code=await response.text();components[name]={http:response.status,hardcoded_old_origin:code.includes('http://127.0.0.1:8000/api')};
}
const imageCode=await(await fetch('http://127.0.0.1:5192/src/services/productImages.js')).text();
const api=await fetch('http://127.0.0.1:8042/api/client/san-phams?all=1',{headers:{Origin:'http://127.0.0.1:5192',Connection:'close'}});await api.arrayBuffer();
const result={frontend_html_http:page.status,uat_csp:page.headers.get('content-security-policy'),vite_api_url_8042_in_transformed_image_service:imageCode.includes('http://127.0.0.1:8042/api'),components,laravel_catalog_http:api.status,cors_for_frontend_5192:api.headers.get('access-control-allow-origin'),browser_observation:'IAB localhost:5192 loaded app then showed Không thể tải sản phẩm. Vui lòng thử lại.',isolation:'CSP forbids port 8000 and external resource access; no old/main backend requests allowed by this test page',dynamic_frontend_result:'FAIL',offline_browser_test:'NOT RUN; browser API has no network-offline control; CSP is not proof of Internet disconnect'};
fs.writeFileSync(path.join(dir,'frontend-check.json'),JSON.stringify(result,null,2));console.log(JSON.stringify(result,null,2));
