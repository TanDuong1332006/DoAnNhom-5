<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header fw-bold bg-dark text-white"><i class="bi bi-person me-2"></i>Hồ Sơ Cá Nhân</div>
          <div class="card-body">
            <div class="mb-3"><label class="form-label fw-semibold">Họ và tên</label>
              <input v-model="form.name" type="text" class="form-control">
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">Số điện thoại</label>
              <input v-model="form.phone" type="tel" class="form-control">
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">Địa chỉ</label>
              <textarea v-model="form.address" class="form-control" rows="2"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Ngày sinh</label>
                <input v-model="form.birthday" type="date" class="form-control">
              </div>
              <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Giới tính</label>
                <select v-model="form.gender" class="form-select">
                  <option value="1">Nam</option><option value="2">Nữ</option>
                </select>
              </div>
            </div>
            <button @click="save" :disabled="saving" class="btn btn-warning w-100">
              <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>Lưu thông tin
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axiosClient from '@/services/axiosClient'
import { authStore } from '@/stores/authStore'

export default {
  name: 'Profile',
  data() {
    const u = authStore.user || {}
    return { form: { name: u.name||'', phone: u.phone||'', address: u.address||'', birthday: u.birthday||'', gender: u.gender||1 }, saving: false }
  },
  methods: {
    async save() {
      this.saving = true
      const res = await axiosClient.post('/profile/update', this.form)
      if (res.data.status === 1) { this.$toast.success('Cập nhật thành công!'); authStore.user = { ...authStore.user, ...this.form }; localStorage.setItem('shop_user', JSON.stringify(authStore.user)) }
      else this.$toast.error(res.data.message)
      this.saving = false
    },
  },
}
</script>
