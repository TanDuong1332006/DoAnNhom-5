<template>
  <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center py-5"
       style="background-color: #f1f3f7;">

    <!-- Brand Logo Top -->
    <div class="text-center mb-4">
      <router-link to="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
        <div class="rounded-3 bg-urbn-dark text-white d-flex align-items-center justify-content-center shadow-sm"
             style="width: 44px; height: 44px;">
          <i class="bx bxs-layer text-urbn-primary fs-3"></i>
        </div>
        <span class="fs-3 fw-extrabold text-dark tracking-tight font-streetwear">
          URBN<span class="text-urbn-primary">.</span>WEAR
        </span>
      </router-link>
      <div class="text-muted small fw-semibold tracking-wider text-uppercase" style="font-size: 10px; letter-spacing: 2px;">
        Contemporary Streetwear Studio
      </div>
    </div>

    <!-- Forgot Password Card -->
    <div class="card border-0 shadow-lg rounded-4 p-2 bg-white" style="width: 100%; max-width: 420px;">
      <div class="card-body px-4 py-4">

        <!-- Icon + Title -->
        <div class="text-center mb-4">
          <div class="rounded-circle bg-urbn-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2"
               style="width: 64px; height: 64px;">
            <i class="bx bx-lock-open-alt text-urbn-primary" style="font-size: 2rem;"></i>
          </div>
          <h4 class="fw-extrabold text-dark mb-1 font-streetwear">Quên Mật Khẩu?</h4>
          <p class="text-muted small mb-0">
            Vui lòng nhập địa chỉ email đã đăng ký tài khoản để thiết lập lại mật khẩu.
          </p>
        </div>

        <!-- Alert Notification -->
        <div class="alert alert-light border border-secondary border-opacity-25 py-2 px-3 rounded-3 mb-3 d-flex align-items-start gap-2 small text-muted">
          <i class="bx bx-info-circle text-urbn-primary fs-5 mt-0.5 flex-shrink-0"></i>
          <div>
            Hệ thống hỗ trợ đặt lại mật khẩu trực tiếp cho tài khoản đã có trên website URBN WEAR.
          </div>
        </div>

        <form @submit.prevent="guiYeuCau">
          <!-- Email Input -->
          <div class="mb-3">
            <label for="forgotEmail" class="form-label fw-semibold small text-dark mb-1">Địa Chỉ Email</label>
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-end-0 text-muted">
                <i class="bx bx-envelope"></i>
              </span>
              <input v-model="email" type="email" class="form-control border-start-0 ps-0"
                     id="forgotEmail" placeholder="name@example.com" required />
            </div>
          </div>

          <!-- Submit Button -->
          <div class="d-grid mt-4 mb-2">
            <button type="submit" :disabled="loading" class="btn btn-urbn-primary py-2 fw-bold text-white shadow-sm">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bx bx-paper-plane me-1 fs-5"></i>
              <span>GỬI YÊU CẦU ĐẶT LẠI</span>
            </button>
          </div>
        </form>

      </div>

      <!-- Footer Navigation -->
      <div class="card-footer bg-transparent border-0 text-center pb-3 pt-0">
        <div class="border-top pt-3 d-flex justify-content-center gap-3">
          <router-link to="/dang-nhap" class="text-muted small text-decoration-none fw-semibold">
            <i class="bx bx-arrow-back me-1"></i> Quay lại Đăng Nhập
          </router-link>
          <span class="text-muted small">|</span>
          <router-link to="/dang-ky" class="text-urbn-primary small text-decoration-none fw-bold">
            Đăng ký mới
          </router-link>
        </div>
      </div>
    </div>

    <!-- Back to Store -->
    <div class="text-center mt-3">
      <router-link to="/" class="text-muted small text-decoration-none">
        <i class="bx bx-store me-1"></i> Về trang chủ mua sắm
      </router-link>
    </div>

  </div>
</template>

<script>
export default {
  name: 'QuenMatKhau',
  data() {
    return {
      email: '',
      loading: false
    };
  },
  methods: {
    guiYeuCau() {
      if (!this.email) {
        this.$toast.warning('Vui lòng nhập địa chỉ email!');
        return;
      }
      this.loading = true;
      setTimeout(() => {
        this.loading = false;
        this.$toast.success('Yêu cầu đặt lại mật khẩu đã được tiếp nhận thành công!');
        this.$router.push('/dang-nhap');
      }, 1000);
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
