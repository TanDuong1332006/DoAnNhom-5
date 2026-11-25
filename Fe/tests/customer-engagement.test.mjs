import { apiUrl } from '../src/services/apiConfig.js';
import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
import { randomUUID } from 'node:crypto';
import { topicLabels, engagementError, engagementDate, money } from '../src/services/engagement.js';
function component(file, extra = {}) {
  const source=readFileSync(new URL('../src/components/Engagement/'+file+'.vue',import.meta.url),'utf8');
  const script=source.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/^import .*;\r?\n/gm,'').replace('export default','globalThis.definition =');
  const context={apiUrl, ProductImage: {}, topicLabels,engagementError,engagementDate,money,FormData,crypto:{randomUUID},CampaignVoucherForm:{},customerSession:()=>({token:'qa'}),clearCustomerSession(){},engagementApi:async()=>({data:{}}),window:{dispatchEvent(){}},Event,cartStore:{items:[{id:7,id_chi_tiet_san_pham:21,so_luong:2}],toggleCheckout(value){this.open=value;}},...extra};
  vm.runInNewContext(script,context);
  const state={...context.definition.data(),$route:{path:'/thong-bao'},$router:{replace:async()=>{}},$nextTick:fn=>fn()};
  for(const [key,fn] of Object.entries(context.definition.methods||{}))state[key]=fn.bind(state);
  for(const [key,fn] of Object.entries(context.definition.computed||{}))Object.defineProperty(state,key,{get:fn.bind(state)});
  return {state,definition:context.definition,context,source};
}
const settle=()=>new Promise(resolve=>setImmediate(resolve));
test('góp ý riêng chặn ảnh sai định dạng, quá 5 ảnh hoặc quá 1MB trước khi gửi',()=>{
  const {state}=component('FeedbackPage');
  for(const files of [[{type:'image/svg+xml',size:1}],Array.from({length:6},()=>({type:'image/png',size:1})),[{type:'image/jpeg',size:1024*1024+1}]]){
    const event={target:{files,value:'selected'}};state.selectImages(event);assert.ok(state.error);assert.equal(state.files.length,0);assert.equal(event.target.value,'');
  }
});
test('gửi góp ý khóa bấm lặp, chỉ gửi API private và giữ lỗi backend để sửa',async()=>{
  let finish;const calls=[];
  const {state}=component('FeedbackPage',{engagementApi:async(path,options)=>{calls.push({path,options});return new Promise(resolve=>finish=resolve);}});
  state.form={topic:'website',body:'Góp ý đủ dài và riêng tư.',email:'',phone:'',website:''};
  const pending=state.submit();state.submit();assert.equal(calls.length,1);assert.equal(calls[0].path,'client/gop-y');
  assert.equal(calls[0].options.data.get('body'),state.form.body);
  finish({data:{message:'Đã nhận góp ý riêng.'}});await pending;assert.equal(state.loading,false);assert.equal(state.success,'Đã nhận góp ý riêng.');assert.equal(state.form.body,'');
});
test('Guest không gọi API hộp thư và chọn voucher không tạo đơn hoặc mất giỏ',async()=>{
  let calls=0;const guest=component('CustomerCenter',{customerSession:()=>null,engagementApi:async()=>{calls++;}});await guest.state.load();assert.equal(calls,0);assert.equal(guest.state.needsLogin,true);
  const {state,context}=component('CustomerCenter');const before=JSON.stringify(context.cartStore.items);state.use({ma_code:'THANKYOU'});assert.equal(context.cartStore.selectedVoucherCode,'THANKYOU');assert.equal(context.cartStore.open,true);assert.equal(JSON.stringify(context.cartStore.items),before);
});
test('lưu voucher chống bấm lặp, không tự checkout và hiển thị thông báo server',async()=>{
  let finish;let calls=0;const {state,context}=component('CustomerCenter',{engagementApi:()=>{calls++;return new Promise(resolve=>finish=resolve);}});
  const pending=state.save({id:8});state.save({id:8});assert.equal(calls,1);finish({data:{message:'Lưu mã không trừ lượt.'}});await pending;assert.deepEqual([...state.saved],[8]);assert.equal(context.cartStore.open,undefined);assert.equal(state.success,'Lưu mã không trừ lượt.');
});
test('chuông Guest không gọi API; response của phiên Customer cũ không ghi đè phiên mới',async()=>{
  let calls=0,token=null,finish;const {state}=component('NotificationBell',{customerSession:()=>token?{token}:null,engagementApi:()=>{calls++;return new Promise(resolve=>finish=resolve);}});
  await state.refresh();assert.equal(calls,0);token='A';const pending=state.refresh();token='B';finish({data:{unread:9}});await pending;assert.equal(state.unread,0);
});
test('Admin sửa điều kiện trong lúc xem trước không dùng response cũ để xác nhận gửi',async()=>{
  let finish;const {state}=component('AdminCampaigns',{engagementApi:()=>new Promise(resolve=>finish=resolve)});
  state.form.title='Tri ân';state.form.body='Nội dung';const pending=state.preview();state.form.ordersMin=2;finish({data:{data:{count:5,preview_hash:'a'.repeat(64)}}});await pending;assert.equal(state.previewData,null);assert.equal(state.busy,false);
});
test('Admin phải xác nhận trước khi gửi; lỗi mạng giữ request_key để retry cùng chiến dịch',async()=>{
  const calls=[];const {state}=component('AdminCampaigns',{engagementApi:async(path,options)=>{calls.push(options.data);throw {response:{data:{message:'Lỗi mạng'}}};}});
  state.previewData={count:2,preview_hash:'x'.repeat(64)};state.sendKey=randomUUID();await state.send();assert.equal(calls.length,0);
  state.confirmSend=true;await state.send();await state.send();assert.equal(calls.length,2);assert.equal(calls[0].request_key,calls[1].request_key);
});
test('giao diện không render HTML từ nội dung khách hoặc chiến dịch',()=>{
  for(const file of ['FeedbackPage','AdminFeedback','AdminCampaigns','CustomerCenter'])assert.doesNotMatch(component(file).source,/v-html\s*=/);
});


test('góp ý hết phiên giữ nội dung và cho phép gửi lại với tư cách Guest', async () => {
  let cleared;
  const {state}=component('FeedbackPage',{clearCustomerSession:token=>{cleared=token;},engagementApi:async()=>{throw {response:{status:401}};}});
  state.form={topic:'service',body:'Nội dung riêng cần được giữ nguyên.',email:'',phone:'',website:''};
  await state.submit();
  assert.equal(cleared,'qa');
  assert.equal(state.form.body,'Nội dung riêng cần được giữ nguyên.');
  assert.match(state.error,/gửi lại với tư cách khách/);
});
