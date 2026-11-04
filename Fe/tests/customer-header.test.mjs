import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
import { customerSession, adminToken, saveSession, clearCustomerSession, clearAdminSession, requestToken } from '../src/services/authSessions.js';

function storage() {
  const map = new Map();
  return { map, getItem: key => map.get(key) || null, setItem: (key,value) => map.set(key,value), removeItem: key => map.delete(key) };
}
function header(axios = { get: async () => ({data:{status:1,nguoi_dung:{role:'customer',ho_va_ten:'Khách test'}}}), post: async () => ({data:{status:1}}) }) {
  const local = storage(), session = storage(), paths = [], messages = [];
  const code = readFileSync(new URL('../src/layout/wrapper/ClientLayout.vue',import.meta.url),'utf8').match(/<script>([\s\S]*?)<\/script>/)[1].replace(/^import .*;\r?\n/gm,'').replace('export default','globalThis.definition =');
  const context = { axios, localStorage:local, sessionStorage:session, cartStore:{items:[{id:1}],count:1}, SizeAdvisorModal:{}, CheckoutModal:{}, customerSession:()=>customerSession(local), clearCustomerSession:token=>clearCustomerSession(token,local), document:{activeElement:null} };
  vm.runInNewContext(code,context);
  const definition=context.definition, state={...definition.data(),$refs:{},$router:{push:async path=>paths.push(path)},$toast:{success:m=>messages.push(m),warning:m=>messages.push(m)},$nextTick:fn=>fn()};
  for(const [key,fn] of Object.entries(definition.methods)) state[key]=fn.bind(state);
  return {local,session,definition,state,paths,messages,context};
}

test('đăng nhập Customer giữ nguyên Admin; đăng xuất Customer không xóa Admin hoặc giỏ hàng',async()=>{
  const calls=[];
  const h=header({post:async(url,payload,config)=>{calls.push({url,config});return {data:{status:1}};}});
  saveSession('admin-token',{role:'admin',id:10},h.local);
  saveSession('customer-token',{role:'customer',id:20,ho_va_ten:'Nguyễn Thị Minh Anh Phương Thanh'},h.local);
  h.local.setItem('sofep_cart','preserved-cart'); h.session.setItem('unrelated','keep');
  h.state.currentUser=customerSession(h.local).user;
  assert.equal(h.definition.computed.accountName.call(h.state),'Nguyễn Thị Minh Anh Phương Thanh');
  await h.state.dangXuat();
  assert.equal(calls[0].config.headers.Authorization,'Bearer customer-token');
  assert.equal(customerSession(h.local),null); assert.equal(adminToken(h.local),'admin-token');
  assert.equal(JSON.parse(h.local.getItem('admin_user')).id,10);
  assert.equal(h.local.getItem('sofep_cart'),'preserved-cart'); assert.equal(h.session.getItem('unrelated'),'keep');
  assert.equal(h.state.cartStore.items.length,1); assert.equal(h.state.currentUser,null);
  assert.deepEqual(h.paths,['/']);
});

test('chọn token theo API, không trộn Admin với Customer và đọc phiên cũ đúng vai trò',()=>{
  const s=storage(); saveSession('customer',{role:'customer',id:1},s); saveSession('admin',{role:'admin',id:2},s);
  assert.equal(requestToken('http://localhost:8000/api/admin/san-pham/get-data',s),'admin');
  assert.equal(requestToken('http://localhost:8000/api/client/san-phams',s),'customer');
  clearAdminSession('admin',s); assert.equal(customerSession(s).token,'customer');
  s.setItem('admin_token','preserve-legacy-admin'); clearCustomerSession('customer',s);
  assert.equal(s.getItem('admin_token'),'preserve-legacy-admin');
  s.setItem('auth_token','legacy-customer');s.setItem('user',JSON.stringify({role:'customer',id:3}));
  assert.equal(customerSession(s).token,'legacy-customer');
});

test('logout lỗi mạng vẫn chỉ xóa phiên Customer và không báo đã thu hồi server thành công',async()=>{
  const h=header({post:async()=>{throw new Error('offline');}});
  saveSession('admin',{role:'admin'},h.local);saveSession('customer',{role:'customer'},h.local);
  await h.state.dangXuat();
  assert.equal(customerSession(h.local),null); assert.equal(adminToken(h.local),'admin');
  assert.match(h.messages[0],/chưa thể xác nhận/);
});

test('tìm kiếm rỗng không điều hướng; trim từ khóa và chặn gửi lặp khi đang điều hướng',async()=>{
  const h=header();h.state.searchQuery='   ';await h.state.handleSearch();assert.equal(h.paths.length,0);assert.ok(h.state.searchMessage);
  h.state.searchQuery='  Áo Thun  ';await h.state.handleSearch();
  assert.equal(h.paths[0].query.q,'Áo Thun');assert.equal(h.state.searching,false);
  h.state.searching=true;await h.state.handleSearch();assert.equal(h.paths.length,1);
});

test('menu bàn phím mở bằng mũi tên, di chuyển mục và Escape trả focus về nút tài khoản',()=>{
  const h=header();const focused=[];
  const items=[0,1,2].map(index=>({focus(){focused.push(index);h.context.document.activeElement=this;}}));
  h.state.$refs.userDropdownRef={querySelectorAll:()=>items};h.state.$refs.userMenuTrigger={focus:()=>focused.push('trigger')};
  const key=key=>h.state.handleUserMenuKeydown({key,preventDefault(){},stopPropagation(){}});
  key('ArrowDown');key('ArrowDown');key('End');key('Escape');
  assert.deepEqual(focused,[0,1,2,'trigger']);assert.equal(h.state.isUserDropdownOpen,false);
});

test('Axios giữ Authorization tường minh khi xác minh phiên hoặc đăng xuất đúng tài khoản',()=>{
  const source=readFileSync(new URL('../src/main.js',import.meta.url),'utf8');
  const body=source.match(/axios\.interceptors\.request\.use\(\(config\) => \{([\s\S]*?)\n\}\);/)[1].replaceAll('import.meta.env.VITE_API_URL',"'http://api.test/api'");
  const s=storage();saveSession('admin',{role:'admin'},s);saveSession('customer',{role:'customer'},s);
  const context={requestToken:url=>requestToken(url,s),resolveApiUrl:url=>url};vm.runInNewContext('globalThis.intercept = config => {'+body+'}',context);
  const result=context.intercept({url:'http://api.test/api/kiem-tra-dang-nhap',headers:{Authorization:'Bearer admin'}});
  assert.equal(result.headers.Authorization,'Bearer admin');
});
