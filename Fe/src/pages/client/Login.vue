<template>
  <div class="min-vh-100 d-flex align-items-center bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-5">
          <div class="text-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-bag-heart-fill text-warning me-2"></i>ShopVN</h2>
          </div>
          <div class="card border-0 shadow">
            <div class="card-body p-4">
              <h4 class="fw-bold mb-4 text-center">Đăng Nhập</h4>
              <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input v-model="form.email" type="email" class="form-control" placeholder="email@example.com" @keyup.enter="login">
              </div>
              <div class="mb-4">
                <label class="form-label fw-semibold">Mật khẩu</label>
                <input v-model="form.password" type="password" class="form-control" placeholder="••••••••" @keyup.enter="login">
              </div>
              <button @click="login" :disabled="loading" class="btn btn-warning w-100 fw-bold btn-lg">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>Đăng Nhập
              </button>
              <div class="text-center mt-3">
                <small class="text-muted">Chưa có tài khoản? <router-link to="/register" class="text-warning fw-semibold">Đăng ký</router-link></small>
              </div>
              <hr>
              <div class="text-center">
                <router-link to="/admin/login" class="text-muted small"><i class="bi bi-shield me-1"></i>Đăng nhập Admin</router-link>
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
import { cartStore } from '@/stores/cartStore'

export default {
  name: 'Login',
  data() { return { form: { email: '', password: '' }, loading: false } },
  methods: {
    async login() {
      if (!this.form.email || !this.form.password) { this.$toast.error('Vui lòng nhập đầy đủ thông tin!'); return }
      this.loading = true
      try {
        const res = await axiosClient.post('/auth/login', this.form)
        if (res.data.status === 1) {
          authStore.login(res.data.token, res.data.user)
          cartStore.fetchCart()
          this.$toast.success('Đăng nhập thành công!')
          this.$router.push('/')
        } else {
          this.$toast.error(res.data.message)
        }
      } catch (e) {
        if (e.response?.data?.errors) {
          const firstKey = Object.keys(e.response.data.errors)[0]
          this.$toast.error(e.response.data.errors[firstKey][0])
        } else {
          this.$toast.error('Có lỗi xảy ra!')
        }
      }
      this.loading = false
    },
  },
}
</script>
