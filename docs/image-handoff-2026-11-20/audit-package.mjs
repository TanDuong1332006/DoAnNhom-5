import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath,pathToFileURL} from 'node:url';
import {createHash} from 'node:crypto';
const dir=path.dirname(fileURLToPath(import.meta.url)),repo=path.resolve(dir,'../..');
const input=JSON.parse(fs.readFileSync(path.join(dir,'inputs.json'))),bundle=input.extracted;
const manifest=JSON.parse(fs.readFileSync(path.join(bundle,'manifest.json'))),catalog=JSON.parse(fs.readFileSync(path.join(bundle,'catalog.json')));
const sha=b=>createHash('sha256').update(b).digest('hex');
const allowed=new Set(['README.md','boot.php','catalog.json','import.php','manifest.json','product-placeholder.svg','server.php','verify.mjs',...manifest.images.map(i=>'media/'+i.path)]);
function list(folder,prefix=''){return fs.readdirSync(folder,{withFileTypes:true}).flatMap(e=>e.isDirectory()?list(path.join(folder,e.name),prefix+e.name+'/'):[prefix+e.name]);}
const files=list(bundle),unexpected=files.filter(f=>!allowed.has(f)),missing=[...allowed].filter(f=>!files.includes(f));
const mismatches=manifest.images.filter(i=>sha(fs.readFileSync(path.join(bundle,'media',i.path)))!==i.sha256).map(i=>i.path);
const columns={danh_mucs:['id','ten_danh_muc','slug_danh_muc','mo_ta','trang_thai'],thuong_hieus:['id','ten_thuong_hieu','slug_thuong_hieu','trang_thai'],mau_sacs:['id','ten_mau','ma_mau','trang_thai'],kich_thuocs:['id','ten_kich_thuoc','mo_ta','trang_thai'],san_phams:['id','id_danh_muc','id_thuong_hieu','ma_san_pham','ten_san_pham','slug_san_pham','gia_ban','gia_khuyen_mai','hinh_anh','mo_ta_ngan','mo_ta_chi_tiet','so_luong_ton','is_noi_bat','is_flash_sale','trang_thai'],anh_san_phams:['id','id_san_pham','duong_dan_anh','thu_tu'],chi_tiet_san_phams:['id','id_san_pham','id_mau_sac','id_kich_thuoc','gia_chenh_lech','so_luong_ton','trang_thai','id_anh_san_pham']};
const badColumns=[];for(const [table,rows] of Object.entries(catalog)){if(!columns[table])badColumns.push(table);else for(const row of rows)for(const key of Object.keys(row))if(!columns[table].includes(key))badColumns.push(`${table}.${key}`);}
const text=JSON.stringify({catalog,manifest});
const riskyData=/127\.0\.0\.1|localhost|[A-Z]:\\|[\w.+-]+@[\w.-]+\.[a-z]{2,}|Bearer\s+\S+|-----BEGIN .*PRIVATE KEY|sk-[A-Za-z0-9]{20,}/i.test(text);
const {productImageUrl,imagePlaceholder}=await import(pathToFileURL(path.join(repo,'Fe/src/services/productImages.js')));
const headImages=await import(pathToFileURL(path.join(input.source,'Fe/src/services/productImages.js')));
const placeholders=catalog.san_phams.filter(p=>productImageUrl(p.hinh_anh,'http://127.0.0.1:8042/api')===imagePlaceholder).length;
const headPlaceholders=catalog.san_phams.filter(p=>headImages.productImageUrl(p.hinh_anh,'http://127.0.0.1:8042/api')===headImages.imagePlaceholder).length;
const mainSnapshot=JSON.parse(fs.readFileSync(path.join(repo,'docs/image-clone-2026-11-20/source-catalog.json'))).catalog;
const mainPlaceholders=mainSnapshot.san_phams.filter(p=>productImageUrl(p.hinh_anh)===imagePlaceholder).length;
const result={passed:!unexpected.length&&!missing.length&&!mismatches.length&&!badColumns.length&&!riskyData,zip_sha256:input.sha256,file_count:files.length,unexpected,missing,checksum_mismatches:mismatches,bad_catalog_columns:badColumns,private_data_patterns_in_catalog_manifest:riskyData,catalog_tables:Object.keys(catalog),data_scope:'catalog only; no account/order/auth rows',images:manifest.images.length,main_catalog_snapshot_products:mainSnapshot.san_phams.length,working_tree_main_snapshot_placeholders:mainPlaceholders,demo_products:catalog.san_phams.length,working_tree_demo_placeholders:placeholders,clean_HEAD_demo_placeholders:headPlaceholders,clean_HEAD_note:'HEAD accepts illustration filenames; working-tree safety patch is not in HEAD',demo_id_172:catalog.san_phams.find(p=>Number(p.id)===172),fixture_variant:manifest.fixture_variant,external_image_urls:catalog.san_phams.filter(p=>/^https?:/.test(p.hinh_anh)).length};
fs.writeFileSync(path.join(dir,'package-audit.json'),JSON.stringify(result,null,2));
console.log(JSON.stringify({...result,demo_id_172:{id:172,hinh_anh:result.demo_id_172.hinh_anh}},null,2));
