<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold mb-0"><i class="bi bi-ticket-perforated me-2"></i>Quản Lý Mã Giảm Giá</h4>
      <button class="btn btn-warning" @click="openCreate"><i class="bi bi-plus-lg me-1"></i>Thêm Mã</button>
    </div>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr><th>Mã</th><th>Loại</th><th>Giá trị</th><th>ĐH tối thiểu</th><th>Đã dùng/Tối đa</th><th>Hạn</th><th>TT</th><th>Action</th></tr>
          </thead>
          <tbody>
            <tr v-for="c in coupons" :key="c.id">
              <td><strong class="text-warning">{{ c.code }}</strong></td>
              <td><span class="badge" :class="c.type === 'percent' ? 'bg-info text-dark' : 'bg-primary'">{{ c.type === 'percent' ? '%' : 'Cố định' }}</span></td>
              <td>{{ c.type === 'percent' ? c.value + '%' : formatPrice(c.value) + 'đ' }}</td>
              <td>{{ formatPrice(c.min_order) }}đ</td>
              <td>{{ c.used_count }}/{{ c.max_uses || '∞' }}</td>
              <td>{{ c.expires_at ? formatDate(c.expires_at) : 'Không hạn' }}</td>
              <td><span :class="c.is_active ? 'badge bg-success' : 'badge bg-secondary'">{{ c.is_active ? 'Hiệu lực' : 'Hết hạn' }}</span></td>
              <td>
                <button class="btn btn-sm btn-primary me-1" @click="openEdit(c)"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-danger" @click="deleteCoupon(c)"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="couponModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white"><h5 class="modal-title">{{ editMode ? 'Cập nhật' : 'Thêm' }} Mã Giảm Giá</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div v-if="!editMode" class="mb-3"><label class="form-label fw-semibold">Mã coupon *</label>
              <input v-model="form.code" type="text" class="form-control" placeholder="VD: SALE10" style="text-transform:uppercase;">
            </div>
            <div class="row">
              <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Loại *</label>
                <select v-model="form.type" class="form-select"><option value="percent">Phần trăm (%)</option><option value="fixed">Cố định (đ)</option></select>
              </div>
              <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Giá trị *</label>
                <input v-model.number="form.value" type="number" class="form-control" min="0">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3"><label class="form-label fw-semibold">ĐH tối thiểu</label>
                <input v-model.number="form.min_order" type="number" class="form-control" min="0">
              </div>
              <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Số lần tối đa</label>
                <input v-model.number="form.max_uses" type="number" class="form-control" min="1" placeholder="Để trống = không giới hạn">
              </div>
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">Ngày hết hạn</label>
              <input v-model="form.expires_at" type="datetime-local" class="form-control">
            </div>
            <div class="form-check form-switch"><input v-model="form.is_active" class="form-check-input" type="checkbox"><label class="form-check-label">Hiệu lực</label></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
            <button @click="save" :disabled="saving" class="btn btn-warning">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>Lưu
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axiosClient from '@/services/axiosClient'

export default {
  name: 'AdminCoupons',
  data() {
    return {
      coupons: [], editMode: false, saving: false,
      form: { id: null, code: '', type: 'percent', value: 10, min_order: 0, max_uses: null, expires_at: '', is_active: true },
      modal: null,
    }
  },
  mounted() { this.fetchCoupons() },
  methods: {
    async fetchCoupons() {
      const res = await axiosClient.get('/admin/coupons')
      if (res.data.status === 1) this.coupons = res.data.data
    },
    openCreate() { this.editMode = false; this.form = { id: null, code: '', type: 'percent', value: 10, min_order: 0, max_uses: null, expires_at: '', is_active: true }; this.showModal() },
    openEdit(c) { this.editMode = true; this.form = { id: c.id, code: c.code, type: c.type, value: c.value, min_order: c.min_order, max_uses: c.max_uses, expires_at: c.expires_at ? c.expires_at.replace(' ', 'T').slice(0, 16) : '', is_active: !!c.is_active }; this.showModal() },
    showModal() { if (!this.modal) this.modal = new bootstrap.Modal(document.getElementById('couponModal')); this.modal.show() },
    async save() {
      this.saving = true
      const url = this.editMode ? '/admin/coupons/update' : '/admin/coupons/create'
      const data = { ...this.form, is_active: this.form.is_active ? 1 : 0, expires_at: this.form.expires_at || null }
      if (!this.editMode) delete data.id
      try {
        const res = await axiosClient.post(url, data)
        if (res.data.status === 1) { this.$toast.success(res.data.message); this.modal?.hide(); this.fetchCoupons() }
        else this.$toast.error(res.data.message)
      } catch (e) {
        if (e.response?.data?.errors) { const k = Object.keys(e.response.data.errors)[0]; this.$toast.error(e.response.data.errors[k][0]) }
      }
      this.saving = false
    },
    async deleteCoupon(c) {
      if (!confirm(`Xoá mã "${c.code}"?`)) return
      const res = await axiosClient.post('/admin/coupons/delete', { id: c.id })
      if (res.data.status === 1) { this.$toast.success('Đã xoá!'); this.fetchCoupons() }
    },
    formatPrice(p) { return new Intl.NumberFormat('vi-VN').format(p || 0) },
    formatDate(d) { return new Date(d).toLocaleDateString('vi-VN') },
  },
}
</script>
