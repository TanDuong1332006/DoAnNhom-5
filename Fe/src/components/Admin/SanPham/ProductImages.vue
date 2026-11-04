<template>
  <section class="product-images">
    <p class="text-muted small">Ảnh thuộc đúng sản phẩm <strong>{{ product.ma_san_pham || product.id }}</strong>. JPG, PNG, WebP · tối đa 2 MB/ảnh, 6000 × 6000 px · tối đa 20 ảnh (3 ảnh/lần).</p>
    <p class="small">Chọn ảnh chính để dùng ở trang chủ, tìm kiếm và Quick View. Khi gỡ ảnh, file được giữ để bảo toàn lịch sử.</p>
    <div class="mb-3"><span class="small text-muted">Ảnh chính hiện tại</span><ProductImage :src="product.hinh_anh" :alt="product.ten_san_pham" style="display:block;width:100px;height:110px;object-fit:contain;border-radius:10px" /></div>
    <div v-if="error" role="alert" class="alert alert-danger">{{ error }}</div>
    <label class="d-block mb-3"><input v-model="illustration" type="checkbox" :disabled="busy" /> Ảnh minh họa kiểu áo (không phải ảnh mẫu sản phẩm thực tế)</label>
    <label class="form-label" :for="'files-' + product.id">Chọn ảnh từ máy tính</label>
    <input :id="'files-' + product.id" ref="files" type="file" multiple accept="image/jpeg,image/png,image/webp" :disabled="busy" class="form-control" @change="selectFiles" />
    <div v-if="pending.length" class="image-grid my-3"><figure v-for="(item, index) in pending" :key="item.preview"><img :src="item.preview" :alt="'Xem trước ' + item.file.name" /><figcaption>{{ item.file.name }} <button type="button" :disabled="busy" @click="removePending(index)">Bỏ</button></figcaption></figure></div>
    <button type="button" class="btn btn-dark my-3" :disabled="busy || !pending.length" @click="upload">{{ busy ? 'Đang xử lý…' : 'Tải ảnh đã chọn lên' }}</button>
    <div class="image-grid"><figure v-for="(image, index) in images" :key="image.id">
      <ProductImage :src="image.duong_dan_anh" :alt="product.ten_san_pham + ' ảnh ' + (index + 1)" />
      <figcaption><label><input v-model="mainId" type="radio" :value="image.id" :disabled="busy" /> Ảnh chính</label><div class="d-flex gap-1 mt-2"><button type="button" :disabled="busy || index === 0" :aria-label="'Đưa ảnh ' + (index + 1) + ' lên trước'" @click="move(index,-1)">←</button><button type="button" :disabled="busy || index === images.length-1" :aria-label="'Đưa ảnh ' + (index + 1) + ' ra sau'" @click="move(index,1)">→</button><button type="button" :disabled="busy" @click="remove(image)">Gỡ ảnh</button></div></figcaption>
    </figure></div>
    <p v-if="!images.length" class="text-muted">Chưa có ảnh gallery. Ảnh URL chính hiện tại vẫn được giữ nếu có.</p>
    <button type="button" class="btn btn-outline-dark mt-3" :disabled="busy || !images.length" @click="save">Lưu thứ tự và ảnh chính</button>
    <p v-if="message" role="status" class="text-success mt-2">{{ message }}</p>
  </section>
</template>
<script>
import axios from 'axios';
import ProductImage from '../../shared/ProductImage.vue';
export default {
  components: { ProductImage }, props: { product: { type: Object, required: true } }, emits: ['changed'],
  data() { return { illustration: false, images: [], mainId: null, pending: [], busy: false, error: '', message: '' }; },
  watch: { product: { immediate: true, handler() { this.resetImages(); } } },
  beforeUnmount() { this.clearPending(); },
  methods: {
    resetImages() { this.images = [...(this.product.anh_san_phams || [])].sort((a,b) => a.thu_tu-b.thu_tu || a.id-b.id); this.mainId = this.images.find(image => image.duong_dan_anh === this.product.hinh_anh)?.id ?? null; },
    clearPending() { this.pending.forEach(item => URL.revokeObjectURL(item.preview)); this.pending = []; },
    selectFiles(event) {
      this.error = ''; this.message = ''; this.clearPending();
      const files = [...event.target.files];
      if (files.length > 3 || this.images.length + files.length > 20 || files.some(file => !['image/jpeg','image/png','image/webp'].includes(file.type) || file.size > 2048*1024)) { this.error = 'Chọn tối đa 3 ảnh/lần, JPG/PNG/WebP, không quá 2 MB/ảnh và 20 ảnh/sản phẩm.'; event.target.value = ''; return; }
      this.pending = files.map(file => ({ file, preview: URL.createObjectURL(file) }));
    },
    removePending(index) { URL.revokeObjectURL(this.pending[index].preview); this.pending.splice(index,1); },
    move(index, direction) { const next = index+direction; if (next < 0 || next >= this.images.length) return; [this.images[index],this.images[next]] = [this.images[next],this.images[index]]; },
    async perform(action) { if (this.busy) return; this.busy = true; this.error = ''; this.message = ''; try { const response = await action(); if (!response.data.status) throw new Error('Không thể lưu ảnh.'); this.$emit('changed', response.data.data); this.message = 'Đã lưu. Client sẽ nhận ảnh mới khi tải lại sản phẩm.'; return true; } catch(error) { this.error = Object.values(error.response?.data?.errors || {}).flat()[0] || error.response?.data?.message || error.message; return false; } finally { this.busy = false; } },
    async upload() { const body = new FormData(); this.pending.forEach(item => body.append('images[]',item.file)); if (this.illustration) body.append('image_kind','illustration'); if (await this.perform(() => axios.post('http://127.0.0.1:8000/api/admin/san-pham/'+this.product.id+'/images',body))) { this.clearPending(); this.$refs.files.value = ''; } },
    save() { const body = { ids: this.images.map(image => image.id) }; if (this.mainId !== null) body.main_id = this.mainId; return this.perform(() => axios.post('http://127.0.0.1:8000/api/admin/san-pham/'+this.product.id+'/images/order',body)); },
    remove(image) { return this.perform(() => axios.delete('http://127.0.0.1:8000/api/admin/san-pham/'+this.product.id+'/images/'+image.id)); }
  }
};
</script>
<style scoped>
.image-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:16px}.image-grid figure{margin:0;border:1px solid #e2ddd3;border-radius:12px;padding:10px;min-width:0}.image-grid :deep(.product-photo){width:100%;height:150px;object-fit:contain}.image-grid img{width:100%;height:150px;object-fit:contain;border-radius:8px;background:#f7f5f1}.image-grid figcaption{font-size:12px;overflow-wrap:anywhere;margin-top:8px}.image-grid button{border:1px solid #ddd5c5;background:#faf8f4;border-radius:6px;padding:4px 8px}.image-grid button:disabled{opacity:.35}.image-grid input{accent-color:#aa8956}.product-images button:focus-visible{outline:2px solid #aa8956;outline-offset:3px}
</style>
