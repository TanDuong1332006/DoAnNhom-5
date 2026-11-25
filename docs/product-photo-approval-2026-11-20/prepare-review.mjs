import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import {createHash} from 'node:crypto';
import {productImageUrl,imagePlaceholder,isIllustrativeImage} from '../../Fe/src/services/productImages.js';
const dir=path.dirname(fileURLToPath(import.meta.url)),repo=path.resolve(dir,'../..');
const read=name=>JSON.parse(fs.readFileSync(path.join(repo,name),'utf8'));
const snapshot=read('docs/product-photo-approval-2026-11-20/catalog-snapshot.json'),data=snapshot.data;
const ai=read('docs/product-content/full-catalog/manifest.json');
const stock=read('docs/image-handoff-2026-11-20/20-files-review.json').rows;
const prior=read('docs/image-audit-2026-11-20/inventory.json').checks;
const retry=read('docs/image-clone-2026-11-20/timeout-diagnostics-all.json').checks;
const aiMap=new Map(ai.map(p=>[p.published.url,p]));
const stockMap=new Map(stock.filter(p=>p.source_record).map(p=>['/api/product-images/'+p.file.slice('products/'.length),p]));
const history=new Map(data.order_image_references.map(r=>[r.hinh_anh,Number(r.reference_count)]));
const colors=new Map(data.colors.map(x=>[Number(x.id),x.ten_mau])),sizes=new Map(data.sizes.map(x=>[Number(x.id),x.ten_kich_thuoc]));
const products=[],variants=[],images=[],batches=[];
for(const category of data.categories){
 const group=data.products.filter(p=>Number(p.id_danh_muc)===Number(category.id));if(!group.length)continue;
 if(group.length<15||group.length>25)throw Error('Category batch outside requested 15–25 range');
 const batch='D'+String(batches.length+1).padStart(2,'0');batches.push({batch,category:category.ten_danh_muc,count:group.length,ids:group.map(p=>Number(p.id))});
 for(const p of group){
  const vv=data.variants.filter(v=>Number(v.id_san_pham)===Number(p.id)),gg=data.gallery.filter(g=>Number(g.id_san_pham)===Number(p.id));
  const colorNames=[...new Set(vv.map(v=>colors.get(Number(v.id_mau_sac))||(v.id_mau_sac?'Không tìm thấy màu ID '+v.id_mau_sac:'Không khai báo màu')))];
  const urls=[...new Set([p.hinh_anh,...gg.map(g=>g.duong_dan_anh)].filter(Boolean))];
  const rows=[];
  for(const url of urls){
   const parsed=new URL(url,'http://localhost');const matched=parsed.pathname.match(/^\/api\/product-images\/(\d+)\/([^/]+)$/);
   const relative=matched?`products/${matched[1]}/${matched[2]}`:parsed.pathname.startsWith('/storage/')?parsed.pathname.slice(9):null;
   const local=relative?path.join(repo,'themmoik27be/storage/app/public',relative):null;
   const exists=local?fs.existsSync(local):null,sha=exists?createHash('sha256').update(fs.readFileSync(local)).digest('hex'):'';
   const a=aiMap.get(url),s=stockMap.get(parsed.pathname);
   const source=a?'AI tạo cho demo':s?'Pexels (hồ sơ lịch sử)':/(^|\.)unsplash\.com$/.test(parsed.hostname)?'Unsplash':relative?'Upload, chưa rõ nguồn':'Website ngoài, chưa rõ quyền';
   const proof=a?'Manifest AI khớp SHA-256: '+(a.sha256===sha?'có':'không'):s?'Hồ sơ Pexels khớp SHA-256: '+(s.sha256===sha?'có':'không'):'Không có bằng chứng gắn ảnh với hàng thật SOFEP/SKU';
   const retryResult=retry.find(r=>r.url===url&&r.status===200),old=retryResult||prior[url];
   const http=old?.status===200?'HTTP 200 ở đợt kiểm tra trước, chưa đo lại':old?.status===404?'404 ở đợt kiểm tra trước, chưa đo lại':old?.error?'Lỗi/timeout cũ, chưa đo lại':'Chưa có kết quả HTTP';
   const galleryIds=gg.filter(g=>g.duong_dan_anh===url).map(g=>Number(g.id));
   const variantIds=vv.filter(v=>galleryIds.includes(Number(v.id_anh_san_pham))).map(v=>Number(v.id));
   const classification=exists===false?'Thiếu file upload':a||s||source==='Unsplash'||isIllustrativeImage(url)?'Minh họa/stock chưa xác minh SKU':'Chưa xác minh ảnh thật/SKU';
   const row={batch,product_id:Number(p.id),sku:p.ma_san_pham,url,roles:[p.hinh_anh===url?'Ảnh chính':null,galleryIds.length?'Gallery':null,variantIds.length?'Biến thể':null].filter(Boolean).join(', '),gallery_ids:galleryIds,variant_ids:variantIds,source,classification,file:relative,exists,sha256:sha,source_evidence:proof,source_url:s?.source_record.source_url||a?.source||url,historical_http:http,order_references:history.get(url)||0,display:productImageUrl(url)===imagePlaceholder?'Placeholder theo chính sách':'URL được chấp nhận, chưa chứng minh SKU',reason:a?'AI demo không phải bằng chứng đúng SKU':s||source==='Unsplash'?'Ảnh stock không chứng minh đúng mẫu SOFEP':'Thiếu hồ sơ SKU/quyền dùng; nguồn ID 172 đang HOLD'};
   images.push(row);rows.push(row);
  }
  const main=rows.find(i=>i.url===p.hinh_anh);
  products.push({batch,id:Number(p.id),name:p.ten_san_pham,sku:p.ma_san_pham,category:category.ten_danh_muc,colors:colorNames,variant_count:vv.length,variant_ids:vv.map(v=>Number(v.id)),main:p.hinh_anh||'',gallery:gg.map(g=>({id:Number(g.id),url:g.duong_dan_anh,order:g.thu_tu})),source:main?.source||'Thiếu ảnh chính',evidence:'Chưa có bằng chứng ảnh đúng SKU',display:productImageUrl(p.hinh_anh)===imagePlaceholder?'Placeholder':'Có URL ảnh, chưa xác minh SKU',reason:main?.reason||'Thiếu ảnh chính',description:p.mo_ta_ngan||'',action:'PO cung cấp ảnh chụp đúng SKU và từng màu, xác nhận quyền sử dụng'});
  for(const v of vv){const g=gg.find(g=>Number(g.id)===Number(v.id_anh_san_pham));variants.push({batch,product_id:Number(p.id),sku:p.ma_san_pham,variant_id:Number(v.id),variant_sku:snapshot.variant_sku_columns.map(k=>v[k]).filter(Boolean).join(', ')||'Không có trường SKU biến thể',color:colors.get(Number(v.id_mau_sac))||(v.id_mau_sac?'Màu ID '+v.id_mau_sac+' không tìm thấy':'Không khai báo màu'),color_id:v.id_mau_sac,size:sizes.get(Number(v.id_kich_thuoc))||(v.id_kich_thuoc?'Size ID '+v.id_kich_thuoc+' không tìm thấy':'Không khai báo size'),size_id:v.id_kich_thuoc,image_id:v.id_anh_san_pham,image_url:g?.duong_dan_anh||'',status:Number(v.trang_thai),reason:g?'Ảnh liên kết chưa chứng minh đúng SKU/màu':'Chưa gắn ảnh riêng cho biến thể; không giả lập ảnh theo màu'});}
 }
}
const unique=new Map(images.map(i=>[i.url,i]));
const sourceCounts={};for(const image of unique.values())sourceCounts[image.source]=(sourceCounts[image.source]||0)+1;
const summary={captured_at:snapshot.captured_at,products:products.length,variants:variants.length,gallery_records:data.gallery.length,product_image_rows:images.length,unique_urls:unique.size,unique_sources:sourceCounts,verified_real_sku_products:0,placeholder_products:products.filter(p=>p.display==='Placeholder').length,missing_local_files:[...unique.values()].filter(i=>i.exists===false).length,existing_local_files:[...unique.values()].filter(i=>i.exists===true).length,products_with_no_main:products.filter(p=>!p.main).length,variants_with_image:variants.filter(v=>v.image_id).length,order_image_urls:data.order_image_references.length,order_image_reference_count:data.order_image_references.reduce((n,r)=>n+Number(r.reference_count),0),http_check:'Không gọi HTTP mới, không tải ảnh Internet; chỉ dùng kết quả cũ có ghi nhãn',variant_sku_field_present:snapshot.variant_sku_columns.length>0};
if(products.length!==168||new Set(products.map(p=>p.id)).size!==products.length||variants.length!==data.variants.length||batches.reduce((n,b)=>n+b.count,0)!==products.length)throw Error('Review population mismatch');
fs.writeFileSync(path.join(dir,'review-data.json'),JSON.stringify({summary,batches,products,variants,images},null,2));
fs.mkdirSync(path.join(dir,'batches'),{recursive:true});
const esc=s=>String(s).replaceAll('|','/').replaceAll('\n',' ');
for(const batch of batches){
 const rows=products.filter(p=>p.batch===batch.batch);
 const text=`# ${batch.batch} — ${batch.category}\n\n${batch.count} sản phẩm. Tất cả đang chờ ảnh thật đáng tin cậy; không có ảnh được tự duyệt.\n\nLọc cột Đợt = ${batch.batch} trong Excel. URL gốc, gallery và bằng chứng ở sheet Anh; từng màu/size ở sheet Bien_the.\n\n| ID | SKU | Sản phẩm | Màu hiện có | ID biến thể | Ảnh chính hiện tại | Hiển thị |\n| --- | --- | --- | --- | --- | --- | --- |\n`+rows.map(p=>`| ${p.id} | ${esc(p.sku)} | ${esc(p.name)} | ${esc(p.colors.join(', '))} | ${p.variant_ids.join(', ')} | ${p.main?'[URL gốc]('+p.main+')':'Thiếu'} | ${p.display} |`).join('\n')+'\n\nPO cung cấp tên file gốc, ảnh mặt trước/sau/chi tiết, màu thực, ID biến thể áp dụng và bằng chứng quyền dùng. Không sửa URL hiện tại trong bảng nguồn.\n';
 fs.writeFileSync(path.join(dir,'batches',batch.batch+'.md'),text);
}
console.log(JSON.stringify(summary,null,2));
