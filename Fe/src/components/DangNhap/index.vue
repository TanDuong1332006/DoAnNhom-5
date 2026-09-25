<template>
  <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center py-5"
       style="background: radial-gradient(circle at 50% 10%, #fffdfa 0%, #fef8ee 40%, #f9f8f5 100%);">

    <!-- Brand Logo Top -->
    <div class="text-center mb-4">
      <router-link to="/" class="d-inline-flex align-items-center text-decoration-none">
        <SofepLogo size="lg" variant="light" subtitle="STUDIO 2026" />
      </router-link>
    </div>

    <!-- Login Card -->
    <div class="card border-0 shadow-xl rounded-4 p-2 bg-white" style="width: 100%; max-width: 420px; border: 1px solid rgba(180, 131, 37, 0.16) !important;">
      <div class="card-body px-4 py-4">

        <!-- Icon + Title -->
        <div class="text-center mb-4">
          <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-xs"
               style="width: 64px; height: 64px; background: rgba(180, 131, 37, 0.12); color: #b48325;">
            <i class="bx bx-user" style="font-size: 2rem;"></i>
          </div>
          <h4 class="fw-extrabold text-dark mb-1 font-streetwear">Đăng Nhập Hệ Thống</h4>
          <p class="text-muted small mb-0">
            Chào mừng trở lại! Vui lòng nhập thông tin tài khoản.
          </p>
        </div>

        <form @submit.prevent="dangnhapTK">
          
          <!-- Email -->
          <div class="mb-3">
            <label for="loginEmail" class="form-label fw-semibold small text-dark mb-1">Địa Chỉ Email</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bx bx-envelope"></i>
              </span>
              <input v-model="dangNhap.email" type="email" class="form-control border-start-0 ps-0"
                     id="loginEmail" placeholder="nhapemail@domain.com" required />
            </div>
          </div>

          <!-- Mật khẩu -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label for="loginPassword" class="form-label fw-semibold small text-dark mb-0">Mật Khẩu</label>
              <router-link to="/quen-mat-khau" class="text-urbn-primary small text-decoration-none" style="font-size: 11px;">Quên mật khẩu?</router-link>
            </div>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bx bx-lock-alt"></i>
              </span>
              <input v-model="dangNhap.mat_khau" :type="hienMK ? 'text' : 'password'"
                     class="form-control border-start-0 border-end-0 ps-0" id="loginPassword"
                     placeholder="••••••••" required />
              <span class="input-group-text bg-light border-start-0 text-muted"
                    style="cursor:pointer;" @click="hienMK = !hienMK">
                <i :class="hienMK ? 'bx bx-hide' : 'bx bx-show'"></i>
              </span>
            </div>
          </div>

          <!-- Quick Test Roles Demo Buttons -->
          <div class="mb-3 p-2 bg-light rounded-3 border small">
            <div class="text-muted mb-1" style="font-size: 11px;">Chọn nhanh tài khoản mẫu:</div>
            <div class="d-flex gap-2">
              <button type="button" @click="chonMau('admin')" class="btn btn-sm btn-outline-dark py-0 px-2" style="font-size: 11px;">
                Admin (Quản trị)
              </button>
              <button type="button" @click="chonMau('customer')" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;">
                Khách hàng mẫu
              </button>
            </div>
          </div>

          <!-- Nút đăng nhập -->
          <div class="d-grid mt-4 mb-2">
            <button type="submit" :disabled="loading" class="btn btn-urbn-primary py-2 fw-bold text-white shadow-sm">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bx bx-log-in-circle me-1 fs-5"></i>
              <span>ĐĂNG NHẬP</span>
            </button>
          </div>

        </form>
      </div>

      <!-- Footer chuyển sang Đăng ký -->
      <div class="card-footer bg-transparent border-0 text-center pb-3 pt-0">
        <span class="text-muted small">Chưa có tài khoản? </span>
        <router-link to="/dang-ky" class="text-urbn-primary small text-decoration-none fw-bold">
          Đăng ký ngay <i class="bx bx-right-arrow-alt"></i>
        </router-link>
      </div>
    </div>

    <!-- Back to store link -->
    <div class="text-center mt-3">
      <router-link to="/" class="text-muted small text-decoration-none">
        <i class="bx bx-arrow-back me-1"></i> Quay về trang chủ cửa hàng
      </router-link>
    </div>

  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "DangNhap",
  data() {
    return {
      hienMK: false,
      loading: false,
      dangNhap: {
        email: "admin@shop.com",
        mat_khau: "Admin@123"
      }
    };
  },
  methods: {
    chonMau(type) {
      if (type === 'admin') {
        this.dangNhap.email = 'admin@shop.com';
        this.dangNhap.mat_khau = 'Admin@123';
      } else {
        this.dangNhap.email = 'testuser123@gmail.com';
        this.dangNhap.mat_khau = '123456';
      }
    },
    dangnhapTK() {
      this.loading = true;
      axios.post('http://127.0.0.1:8000/api/dang-nhap', this.dangNhap)
        .then((res) => {
          if (res.data.status === 1) {
            this.$toast.success(res.data.message || 'Đăng nhập thành công!');
            // Lưu token vào localStorage (hỗ trợ cả các tên key tương thích)
            localStorage.setItem('auth_token', res.data.token);
            localStorage.setItem('nguoi_dung_token', res.data.token);
            localStorage.setItem('admin_token', res.data.token);
            
            // Lưu thông tin người dùng
            localStorage.setItem('user', JSON.stringify(res.data.nguoi_dung));
            localStorage.setItem('nguoi_dung', JSON.stringify(res.data.nguoi_dung));

            // Nếu là admin thì vào trang quản trị /admin, nếu là khách hàng thì về trang chủ /
            if (res.data.nguoi_dung && res.data.nguoi_dung.role === 'admin') {
              this.$router.push('/admin');
            } else {
              this.$router.push('/');
            }
          } else {
            this.$toast.error(res.data.message || 'Email hoặc mật khẩu không chính xác!');
          }
        })
        .catch((error) => {
          if (error.response && error.response.data) {
            const errors = error.response.data.errors;
            if (errors) {
              const firstKey = Object.keys(errors)[0];
              this.$toast.error(errors[firstKey][0]);
            } else {
              this.$toast.error(error.response.data.message || 'Đăng nhập thất bại!');
            }
          } else {
            this.$toast.error('Không thể kết nối đến máy chủ Backend!');
          }
        })
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>

<style scoped>
.font-streetwear {
  font-family: 'Plus Jakarta Sans', sans-serif;
  letter-spacing: -0.02em;
}
</style>
