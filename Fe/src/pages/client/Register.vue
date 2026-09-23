<template>
  <div class="min-vh-100 d-flex align-items-center bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="text-center mb-4"><h2 class="fw-bold"><i class="bi bi-bag-heart-fill text-warning me-2"></i>ShopVN</h2></div>
          <div class="card border-0 shadow">
            <div class="card-body p-4">
              <h4 class="fw-bold mb-4 text-center">Tạo Tài Khoản</h4>
              <div class="mb-3"><label class="form-label fw-semibold">Họ và tên *</label>
                <input v-model="form.name" type="text" class="form-control" placeholder="Nguyễn Văn A">
              </div>
              <div class="mb-3"><label class="form-label fw-semibold">Email *</label>
                <input v-model="form.email" type="email" class="form-control" placeholder="email@example.com">
              </div>
              <div class="mb-3"><label class="form-label fw-semibold">Số điện thoại *</label>
                <input v-model="form.phone" type="tel" class="form-control" placeholder="0909123456">
              </div>
              <div class="mb-4"><label class="form-label fw-semibold">Mật khẩu *</label>
                <input v-model="form.password" type="password" class="form-control" placeholder="Ít nhất 6 ký tự">
              </div>
              <button @click="register" :disabled="loading" class="btn btn-warning w-100 fw-bold btn-lg">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>Đăng Ký
              </button>
              <div class="text-center mt-3">
                <small>Đã có tài khoản? <router-link to="/login" class="text-warning fw-semibold">Đăng nhập</router-link></small>
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

export default {
  name: 'Register',
  data() { return { form: { name:'', email:'', password:'', phone:'' }, loading: false } },
  methods: {
    async register() {
      this.loading = true
      try {
        const res = await axiosClient.post('/auth/register', this.form)
        if (res.data.status === 1) { this.$toast.success(res.data.message); this.$router.push('/login') }
        else this.$toast.error(res.data.message)
      } catch (e) {
        if (e.response?.data?.errors) {
          const firstKey = Object.keys(e.response.data.errors)[0]
          this.$toast.error(e.response.data.errors[firstKey][0])
        } else this.$toast.error('Có lỗi xảy ra!')
      }
      this.loading = false
    },
  },
}
</script>
