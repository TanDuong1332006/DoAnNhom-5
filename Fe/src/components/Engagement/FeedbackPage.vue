<template>
  <main class="eng-page">
    <span class="eng-eyebrow">SOFEP / LẮNG NGHE BẠN</span>
    <h1>Mỗi góp ý, một thay đổi tốt hơn.</h1>
    <p class="eng-muted">Chia sẻ trải nghiệm của bạn với SOFEP. Góp ý được gửi riêng đến đội ngũ quản trị, không đăng công khai.</p>
    <div class="eng-grid">
      <section class="eng-panel"><h2>Đóng góp ý kiến</h2>
        <div v-if="success" class="eng-success" role="status">{{ success }}<div class="eng-actions"><button class="eng-btn secondary" @click="success = ''">Gửi góp ý khác</button></div></div>
        <form v-else @submit.prevent="submit">
          <label for="feedback-topic">Chủ đề góp ý</label><select id="feedback-topic" v-model="form.topic" required><option value="">Chọn chủ đề</option><option v-for="(label,key) in topics" :key="key" :value="key">{{ label }}</option></select>
          <label for="feedback-body">Bạn muốn SOFEP cải thiện điều gì?</label><textarea id="feedback-body" v-model="form.body" required minlength="10" maxlength="5000" rows="7" placeholder="Một chi tiết nhỏ cũng có thể tạo nên trải nghiệm tốt hơn…"></textarea><small class="eng-muted">{{ form.body.length }}/5000 ký tự</small>
          <div class="eng-grid"><div><label for="feedback-email">Email liên hệ · tùy chọn</label><input id="feedback-email" v-model="form.email" type="email" maxlength="255" autocomplete="email"></div><div><label for="feedback-phone">Số điện thoại · tùy chọn</label><input id="feedback-phone" v-model="form.phone" type="tel" maxlength="25" autocomplete="tel"></div></div>
          <div class="eng-honey" aria-hidden="true"><label for="feedback-website">Website</label><input id="feedback-website" v-model="form.website" tabindex="-1" autocomplete="off"></div>
          <label for="feedback-images">Ảnh đính kèm · tối đa 5 ảnh</label><div class="eng-upload"><input id="feedback-images" ref="images" type="file" accept="image/jpeg,image/png,image/webp" multiple @change="selectImages"><p class="eng-muted">JPG, PNG, WebP · tối đa 1 MB/ảnh, 6000×6000 px. Ảnh được lưu riêng, chỉ Admin có quyền xem.</p><div v-for="(file,index) in files" :key="index" class="eng-actions"><span class="eng-muted">{{ file.name }}</span><button type="button" class="eng-btn secondary" @click="files.splice(index,1)">Bỏ ảnh</button></div></div>
          <div v-if="error" class="eng-error" role="alert">{{ error }}</div>
          <div class="eng-actions"><button type="submit" class="eng-btn" :disabled="loading">{{ loading ? 'Đang gửi…' : 'Gửi góp ý riêng ↗' }}</button></div>
        </form>
      </section>
      <aside class="eng-panel" style="align-self:start;background:#f8f6f1"><span class="eng-eyebrow">CẢM ƠN VÌ ĐÃ ĐỒNG HÀNH</span><h2 style="margin-top:18px">SOFEP luôn sẵn sàng lắng nghe.</h2><p class="eng-muted">Bạn không cần đăng nhập để góp ý. Nếu đã đăng nhập Customer, phản hồi của SOFEP có thể được gửi vào trung tâm thông báo của bạn.</p><p class="eng-muted">Vui lòng không gửi mật khẩu, thông tin thanh toán hoặc dữ liệu cá nhân của người khác. Để lại kênh liên hệ nếu bạn muốn được hỗ trợ riêng.</p><router-link class="eng-btn secondary" to="/">Quay lại cửa hàng</router-link></aside>
    </div>
  </main>
</template>
<script>
import { customerSession, clearCustomerSession } from '../../services/authSessions';
import { engagementApi, engagementError, topicLabels } from '../../services/engagement';
export default {
  data: () => ({ topics:topicLabels,form:{topic:'',body:'',email:'',phone:'',website:''},files:[],loading:false,error:'',success:'' }),
  methods: {
    selectImages(event) {
      const files=[...event.target.files];this.error='';
      if(files.length>5 || files.some(f=>!['image/jpeg','image/png','image/webp'].includes(f.type) || f.size>1024*1024)) {this.error='Chọn tối đa 5 ảnh JPG/PNG/WebP, mỗi ảnh không quá 1 MB.';event.target.value='';this.files=[];return;}
      this.files=files;
    },
    async submit() {
      if(this.loading) return; this.loading=true;this.error='';
      try {
        const payload=new FormData();for(const [key,value] of Object.entries(this.form)) if(value) payload.append(key,value);
        for(const file of this.files) payload.append('images[]',file);
        const {data}=await engagementApi('client/gop-y',{method:'post',data:payload});
        this.success=data.message;this.form={topic:'',body:'',email:'',phone:'',website:''};this.files=[];
      } catch(error){if(error.response?.status===401){clearCustomerSession(customerSession()?.token);this.error='Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập hoặc gửi lại với tư cách khách; nội dung được giữ nguyên.';}else this.error=engagementError(error);}finally{this.loading=false;}
    }
  }
};
</script>
<style src="../../assets/css/engagement.css"></style>
