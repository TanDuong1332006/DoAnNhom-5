<template>
  <main class="eng-page"><span class="eng-eyebrow">SOFEP / DÀNH RIÊNG CHO BẠN</span><h1>{{ wallet ? 'Ví ưu đãi của tôi' : 'Thông báo của tôi' }}</h1>
    <div class="eng-actions"><router-link class="eng-btn secondary" to="/thong-bao">Thông báo</router-link><router-link class="eng-btn secondary" to="/vi-uu-dai">Ví ưu đãi</router-link><button v-if="!needsLogin" class="eng-btn secondary" :disabled="loading || busy" @click="load">Làm mới</button></div>
    <div v-if="needsLogin" class="eng-panel"><h2>Đăng nhập Customer để tiếp tục</h2><p class="eng-muted">Thông báo và ưu đãi được bảo vệ theo tài khoản của bạn.</p><router-link class="eng-btn" to="/dang-nhap" @click="rememberReturn">Đăng nhập</router-link></div>
    <template v-else>
      <div v-if="error" class="eng-error" role="alert">{{ error }} <button class="eng-btn secondary" @click="load">Thử lại</button></div>
      <div v-if="success" class="eng-success" role="status">{{ success }}</div>
      <nav class="eng-tabs" aria-label="Lọc nội dung"><button v-for="tab in tabs" :key="tab[0]" :class="{active:filter===tab[0]}" @click="changeFilter(tab[0])">{{ tab[1] }}</button></nav>
      <p v-if="loading" class="eng-muted" role="status">Đang tải…</p>
      <template v-else>
        <div v-if="!wallet" class="eng-actions"><button class="eng-btn secondary" :disabled="busy" @click="readAll">Đánh dấu tất cả đã đọc</button><span class="eng-muted">{{ unread }} chưa đọc</span></div>
        <div class="eng-list" style="margin-top:20px">
          <article v-for="item in visibleItems" :key="item.id" class="eng-item" :class="{unread:!wallet && !item.read_at}">
            <template v-if="!wallet"><span class="eng-tag">{{ kindLabels[item.kind] }}</span><div class="eng-muted">{{ date(item.created_at) }}</div><button class="eng-title-btn" @click="open(item)">{{ item.title }}</button><p class="eng-body">{{ item.body }}</p><button v-if="!item.read_at" class="eng-btn secondary" :disabled="busy" @click="read(item)">Đánh dấu đã đọc</button></template>
            <template v-if="wallet || item.voucher"><div class="eng-divider" :style="wallet ? 'border:0;margin:0;padding:0' : ''"><span class="eng-tag">{{ voucher(item).ma_code }}</span><h3 style="margin-top:12px">{{ voucher(item).ten_giam_gia }}</h3><p>{{ voucher(item).loai_giam==='phan_tram' ? Number(voucher(item).gia_tri)+'%' : money(voucher(item).gia_tri) }} giảm giá <span v-if="voucher(item).giam_toi_da">· tối đa {{ money(voucher(item).giam_toi_da) }}</span></p><p class="eng-muted">{{ voucher(item).mo_ta }}<br>{{ voucher(item).conditions }}<br><span v-for="(condition,index) in voucher(item).grant_conditions" :key="index">{{ condition }}<br></span>Hiệu lực: {{ voucher(item).ngay_bat_dau || 'Ngay khi được cấp' }} → {{ voucher(item).ngay_ket_thuc || 'Không giới hạn ngày' }} (giờ Việt Nam)<br>Còn {{ Math.max(0,voucher(item).so_luong-voucher(item).so_luong_da_dung) }} lượt tổng</p><p :class="voucher(item).state==='available'?'text-success':'text-danger'">{{ voucher(item).reason }}</p><div class="eng-actions"><button v-if="!wallet" class="eng-btn" :disabled="busy || saved.includes(item.voucher.id) || item.voucher.state!=='available'" @click="save(item.voucher)">{{ saved.includes(item.voucher.id)?'Đã lưu':'Lưu mã' }}</button><button v-else class="eng-btn" :disabled="item.state!=='available'" @click="use(item)">Chọn mã để thanh toán</button></div></div></template>
          </article>
          <div v-if="!visibleItems.length" class="eng-panel eng-muted">{{ wallet ? 'Chưa có voucher trong nhóm này. Lưu mã từ thông báo để xem tại đây.' : 'Bạn chưa có thông báo trong nhóm này.' }}</div>
        </div>
        <div v-if="lastPage>1" class="eng-actions"><button :disabled="page<=1" class="eng-btn secondary" @click="page--;load()">Trang trước</button><span>{{ page }}/{{ lastPage }}</span><button :disabled="page>=lastPage" class="eng-btn secondary" @click="page++;load()">Trang sau</button></div>
      </template>
      <section class="eng-panel"><h2>Tùy chọn thông báo</h2><label class="eng-check"><input type="checkbox" v-model="marketing" :disabled="busy" @change="preferences"> Nhận thông báo khuyến mãi, tri ân và voucher</label><p class="eng-muted">Thông báo hệ thống và phản hồi góp ý riêng vẫn được gửi vào hộp thư. Không gửi marketing qua email/SMS.</p></section>
    </template>
  </main>
</template>
<script>
import { engagementApi, engagementError, engagementDate, money } from '../../services/engagement';
import { customerSession } from '../../services/authSessions';
import { cartStore } from '../../stores/cartStore';
export default {
  data:()=>({items:[],unread:0,loading:false,busy:false,error:'',success:'',filter:'all',page:1,lastPage:1,marketing:true,needsLogin:false,saved:[],requestId:0,kindLabels:{general:'Hệ thống',system:'Phản hồi riêng',promotion:'Khuyến mãi',thanks:'Tri ân',voucher:'Voucher'}}),
  computed:{wallet(){return this.$route.path==='/vi-uu-dai';},tabs(){return this.wallet ? [['all','Tất cả'],['available','Có thể dùng'],['ineligible','Chưa đủ điều kiện'],['used','Đã sử dụng'],['expired','Hết hạn']] : [['all','Tất cả'],['unread','Chưa đọc'],['promotion','Khuyến mãi/Voucher'],['system','Hệ thống']];},visibleItems(){return this.wallet && this.filter!=='all' ? this.items.filter(i=>i.state===this.filter) : this.items;}},
  watch:{'$route.path'(){this.filter='all';this.page=1;this.load();}},mounted(){this.load();},beforeUnmount(){this.requestId++;},
  methods:{money,date:engagementDate,rememberReturn(){sessionStorage.setItem('sofep_customer_return',this.$route.fullPath);},voucher(item){return this.wallet?item:item.voucher;},changeFilter(filter){this.filter=filter;this.page=1;if(!this.wallet)this.load();},
    async load(){const request=++this.requestId;const token=customerSession()?.token;this.items=[];this.needsLogin=!token;if(this.needsLogin)return;this.loading=true;this.error='';try{const {data}=await engagementApi((this.wallet?'client/vi-uu-dai':'client/thong-bao')+'?page='+this.page+'&filter='+this.filter);if(request!==this.requestId || customerSession()?.token!==token)return;this.items=data.data.data;this.lastPage=data.data.last_page;this.unread=data.unread||0;const pref=await engagementApi('client/tuy-chon-thong-bao');if(request===this.requestId && customerSession()?.token===token)this.marketing=pref.data.marketing_enabled;}catch(error){if(request!==this.requestId)return;this.error=engagementError(error);if([401,403].includes(error.response?.status))this.needsLogin=true;}finally{if(request===this.requestId)this.loading=false;}},
    async action(fn){if(this.busy)return;this.busy=true;this.error='';try{await fn();}catch(e){this.error=engagementError(e);}finally{this.busy=false;}},
    read(item){return this.action(async()=>{await engagementApi('client/thong-bao/'+item.id+'/da-doc',{method:'post'});item.read_at=new Date().toISOString();this.unread=Math.max(0,this.unread-1);window.dispatchEvent(new Event('sofep-inbox-changed'));});},
    async open(item){if(!item.read_at)await this.read(item);await this.$router.replace({path:'/thong-bao',query:{item:item.id}});},
    readAll(){return this.action(async()=>{await engagementApi('client/thong-bao/doc-tat-ca',{method:'post'});await this.load();window.dispatchEvent(new Event('sofep-inbox-changed'));});},
    save(v){return this.action(async()=>{const {data}=await engagementApi('client/vi-uu-dai/'+v.id+'/luu',{method:'post'});this.saved.push(v.id);this.success=data.message;});},
    use(v){if(!cartStore.items.length){this.error='Thêm sản phẩm vào giỏ trước khi áp dụng voucher.';return;}cartStore.selectedVoucherCode=v.ma_code;cartStore.toggleCheckout(true);},
    preferences(){const desired=this.marketing;return this.action(async()=>{try{await engagementApi('client/tuy-chon-thong-bao',{method:'put',data:{marketing_enabled:desired}});this.success='Đã lưu tùy chọn nhận thông báo.';}catch(error){this.marketing=!desired;throw error;}});}
  }
};
</script>
<style src="../../assets/css/engagement.css"></style>
