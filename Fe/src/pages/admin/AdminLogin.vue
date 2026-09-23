<template>
  <div class="min-vh-100 d-flex align-items-center" style="background: #1a1a2e;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-4">
          <div class="text-center mb-4">
            <h3 class="text-warning fw-bold"><i class="bi bi-shield-check me-2"></i>Admin Panel</h3>
            <p class="text-muted">ShopVN Management System</p>
          </div>
          <div class="card border-0 shadow-lg">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-4 text-center">Đăng Nhập Admin</h5>
              <div class="mb-3"><label class="form-label fw-semibold">Email</label>
                <input v-model="form.email" type="email" class="form-control" placeholder="admin@shop.com">
              </div>
              <div class="mb-4"><label class="form-label fw-semibold">Mật khẩu</label>
                <input v-model="form.password" type="password" class="form-control" placeholder="••••••••" @keyup.enter="login">
              </div>
              <button @click="login" :disabled="loading" class="btn btn-warning w-100 fw-bold">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>Đăng Nhập
              </button>
              <div class="text-center mt-3">
                <router-link to="/login" class="text-muted small">← Về trang khách hàng</router-link>
              </div>
            </div>
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
  name: 'AdminLogin',
  data() { return { form: { email: '', password: '' }, loading: false } },
  methods: {
    async login() {
      this.loading = true
      try {
        const res = await axiosClient.post('/auth/admin-login', this.form)
        if (res.data.status === 1) {
          authStore.login(res.data.token, res.data.user)
          this.$toast.success('Đăng nhập admin thành công!')
          this.$router.push('/admin')
        } else {
          this.$toast.error(res.data.message)
        }
      } catch (e) { this.$toast.error('Có lỗi xảy ra!') }
      this.loading = false
    },
  },
}
</script>
