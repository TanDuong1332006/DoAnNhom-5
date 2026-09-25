<template>
  <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center py-5"
       style="background: radial-gradient(circle at 50% 10%, #fffdfa 0%, #fef8ee 40%, #f9f8f5 100%);">

    <!-- Brand Logo Top -->
    <div class="text-center mb-4">
      <router-link to="/" class="d-inline-flex align-items-center text-decoration-none">
        <SofepLogo size="lg" variant="light" subtitle="STUDIO 2026" />
      </router-link>
    </div>

    <!-- Registration Card -->
    <div class="card border-0 shadow-xl rounded-4 p-2 bg-white" style="width: 100%; max-width: 460px; border: 1px solid rgba(180, 131, 37, 0.16) !important;">
      <div class="card-body px-4 py-4">

        <!-- Icon + Title -->
        <div class="text-center mb-4">
          <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-xs"
               style="width: 64px; height: 64px; background: rgba(180, 131, 37, 0.12); color: #b48325;">
            <i class="bx bx-user-plus" style="font-size: 2rem;"></i>
          </div>
          <h4 class="fw-extrabold text-dark mb-1 font-streetwear">Tạo Tài Khoản Mới</h4>
          <p class="text-muted small mb-0">
            Gia nhập cộng đồng thời trang SOFEP &amp; nhận ngay voucher 50.000₫.
          </p>
        </div>

        <form @submit.prevent="dangkyTaiKhoan">
          <div class="row g-3">
            
            <!-- Họ và Tên -->
            <div class="col-12">
              <label for="regFullName" class="form-label fw-semibold small text-dark mb-1">Họ Và Tên</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bx bx-user"></i>
                </span>
                <input v-model="dangKy.ho_va_ten" type="text" class="form-control border-start-0 ps-0"
                       id="regFullName" placeholder="Nguyễn Văn A" required />
              </div>
            </div>

            <!-- Email -->
            <div class="col-12">
              <label for="regEmail" class="form-label fw-semibold small text-dark mb-1">Địa Chỉ Email</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bx bx-envelope"></i>
                </span>
                <input v-model="dangKy.email" type="email" class="form-control border-start-0 ps-0"
                       id="regEmail" placeholder="name@example.com" required />
              </div>
            </div>

            <!-- Số điện thoại -->
            <div class="col-12">
              <label for="regPhone" class="form-label fw-semibold small text-dark mb-1">Số Điện Thoại</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bx bx-phone"></i>
                </span>
                <input v-model="dangKy.so_dien_thoai" type="tel" class="form-control border-start-0 ps-0"
                       id="regPhone" placeholder="09xxxxxxxx" required />
              </div>
            </div>

            <!-- Mật khẩu -->
            <div class="col-6">
              <label for="regPassword" class="form-label fw-semibold small text-dark mb-1">Mật Khẩu</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bx bx-lock-alt"></i>
                </span>
                <input v-model="dangKy.mat_khau" :type="hienMK ? 'text' : 'password'"
                       class="form-control border-start-0 border-end-0 ps-0"
                       id="regPassword" placeholder="••••••••" required />
                <span class="input-group-text bg-light border-start-0 text-muted" style="cursor:pointer;" @click="hienMK = !hienMK">
                  <i :class="hienMK ? 'bx bx-hide' : 'bx bx-show'"></i>
                </span>
              </div>
            </div>

            <!-- Xác nhận mật khẩu -->
            <div class="col-6">
              <label for="regConfirmPassword" class="form-label fw-semibold small text-dark mb-1">Xác Nhận</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bx bx-lock-alt"></i>
                </span>
                <input v-model="dangKy.xac_nhan_mk" :type="hienMK ? 'text' : 'password'"
                       class="form-control border-start-0 ps-0"
                       id="regConfirmPassword" placeholder="••••••••" required />
              </div>
            </div>

            <!-- Điều khoản -->
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="agreeTerms" v-model="dongYDieuKhoan" required />
                <label class="form-check-label small text-muted" for="agreeTerms">
                  Tôi đồng ý với <a href="#" class="text-urbn-primary text-decoration-none fw-semibold">Điều khoản sử dụng</a> và <a href="#" class="text-urbn-primary text-decoration-none fw-semibold">Chính sách bảo mật</a>
                </label>
              </div>
            </div>

            <!-- Nút đăng ký -->
            <div class="col-12 mt-3">
              <div class="d-grid">
                <button type="submit" :disabled="loading" class="btn btn-urbn-primary py-2 fw-bold text-white shadow-sm">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bx bx-user-check me-1 fs-5"></i>
                  <span>ĐĂNG KÝ TÀI KHOẢN</span>
                </button>
              </div>
            </div>

          </div>
        </form>
      </div>

      <!-- Footer chuyển sang Đăng nhập -->
      <div class="card-footer bg-transparent border-0 text-center pb-3 pt-0">
        <span class="text-muted small">Đã có tài khoản? </span>
        <router-link to="/dang-nhap" class="text-urbn-primary small text-decoration-none fw-bold">
          Đăng nhập ngay <i class="bx bx-right-arrow-alt"></i>
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
  name: 'DangKy',
  data() {
    return {
      hienMK: false,
      loading: false,
      dongYDieuKhoan: true,
      dangKy: {
        ho_va_ten: '',
        email: '',
        so_dien_thoai: '',
        mat_khau: '',
        xac_nhan_mk: ''
      }
    };
  },
  methods: {
    dangkyTaiKhoan() {
      if (this.dangKy.mat_khau !== this.dangKy.xac_nhan_mk) {
        this.$toast.error('Xác nhận mật khẩu không khớp!');
        return;
      }
      this.loading = true;
      axios.post('http://127.0.0.1:8000/api/dang-ky', this.dangKy)
        .then(res => {
          if (res.data.status === 1) {
            this.$toast.success(res.data.message || 'Đăng ký tài khoản thành công!');
            this.$router.push('/dang-nhap');
          } else {
            this.$toast.error(res.data.message || 'Đăng ký không thành công!');
          }
        })
        .catch(err => {
          if (err.response && err.response.data) {
            const errors = err.response.data.errors;
            if (errors) {
              const firstKey = Object.keys(errors)[0];
              this.$toast.error(errors[firstKey][0]);
            } else {
              this.$toast.error(err.response.data.message || 'Lỗi dữ liệu đăng ký!');
            }
          } else {
            this.$toast.error('Không thể kết nối đến máy chủ!');
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
