<template>
  <div class="auth-page">
    <!-- Logo -->
    <div class="logo-container">
      <h1 class="logo-text">
        <span class="logo-icon">S</span> SOFEP <span class="logo-sub">STUDIO 2026</span>
      </h1>
    </div>

    <!-- Main Card -->
    <div class="auth-card">
      <!-- Icon Header -->
      <div class="auth-icon">
        <i class="fas fa-lock" v-if="step === 1"></i>
        <i class="fas fa-key" v-else-if="step === 2"></i>
        <i class="fas fa-shield-alt" v-else></i>
      </div>

      <!-- Tiêu đề & Phụ đề -->
      <h2 class="auth-title">
        {{ step === 1 ? 'Quên Mật Khẩu?' : (step === 2 ? 'Nhập mã xác nhận' : 'Tạo mật khẩu mới') }}
      </h2>
      <p class="auth-subtitle">
        <span v-if="step === 1">Vui lòng nhập địa chỉ email đã đăng ký tài khoản để thiết lập lại mật khẩu.</span>
        <span v-if="step === 2">Mã 6 số đã được gửi tới <strong>{{ email }}</strong></span>
        <span v-if="step === 3">Vui lòng nhập mật khẩu mới để bảo mật tài khoản.</span>
      </p>

      <!-- Hiển thị lỗi chung -->
      <p v-if="errorMessage" class="error-text">
        <i class="fas fa-exclamation-triangle"></i> {{ errorMessage }}
      </p>

      <!-- BƯỚC 1: NHẬP EMAIL -->
      <form @submit.prevent="handleSendOtp" v-if="step === 1" class="auth-form">
        <div class="input-group">
          <label>Địa Chỉ Email</label>
          <div class="input-wrapper">
            <i class="far fa-envelope"></i>
            <input type="email" v-model="email" placeholder="name@example.com" required />
          </div>
        </div>
        <button type="submit" class="submit-btn" :disabled="isLoading">
          <i class="far fa-paper-plane" v-if="!isLoading"></i>
          {{ isLoading ? 'ĐANG XỬ LÝ...' : 'GỬI YÊU CẦU ĐẶT LẠI' }}
        </button>
      </form>

      <!-- BƯỚC 2: NHẬP OTP -->
      <form @submit.prevent="handleVerifyOtp" v-if="step === 2" class="auth-form">
        <div class="input-group">
          <label>Mã OTP (6 số)</label>
          <div class="input-wrapper">
            <i class="fas fa-hashtag"></i>
            <input type="text" v-model="otp" maxlength="6" placeholder="Nhập mã 6 số" required />
          </div>
        </div>
        <button type="submit" class="submit-btn" :disabled="isLoading">
          {{ isLoading ? 'ĐANG KIỂM TRA...' : 'XÁC NHẬN MÃ' }}
        </button>
      </form>

      <!-- BƯỚC 3: ĐỔI MẬT KHẨU MỚI -->
      <form @submit.prevent="handleResetPassword" v-if="step === 3" class="auth-form">
        <div class="input-group">
          <label>Mật khẩu mới</label>
          <div class="input-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" v-model="password" placeholder="Nhập mật khẩu mới" required />
          </div>
        </div>
        <div class="input-group">
          <label>Xác nhận mật khẩu</label>
          <div class="input-wrapper">
            <i class="fas fa-check-circle"></i>
            <input type="password" v-model="passwordConfirmation" placeholder="Nhập lại mật khẩu mới" required />
          </div>
        </div>
        <button type="submit" class="submit-btn" :disabled="isLoading">
          {{ isLoading ? 'ĐANG LƯU...' : 'LƯU MẬT KHẨU MỚI' }}
        </button>
      </form>

      <!-- Footer Links -->
      <div class="auth-links">
        <router-link to="/dang-nhap" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại Đăng Nhập</router-link>
        <span class="divider">|</span>
        <router-link to="/dang-ky" class="highlight">Đăng ký mới</router-link>
      </div>
    </div>

    <!-- Nút về trang chủ -->
    <div class="home-link">
      <router-link to="/"><i class="fas fa-store"></i> Về trang chủ mua sắm</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const step = ref(1);
const email = ref('');
const otp = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const isLoading = ref(false);
const errorMessage = ref('');

const API_BASE_URL = 'http://localhost:8000/api'; 

const handleSendOtp = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';
    await axios.post(`${API_BASE_URL}/forgot-password/send-otp`, { email: email.value });
    step.value = 2;
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi gửi mail.';
  } finally {
    isLoading.value = false;
  }
};

const handleVerifyOtp = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';
    await axios.post(`${API_BASE_URL}/forgot-password/verify-otp`, {
      email: email.value,
      otp: otp.value
    });
    step.value = 3;
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Mã xác nhận sai hoặc đã hết hạn.';
  } finally {
    isLoading.value = false;
  }
};

const handleResetPassword = async () => {
  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = "Mật khẩu xác nhận không khớp.";
    return;
  }
  try {
    isLoading.value = true;
    errorMessage.value = '';
    await axios.post(`${API_BASE_URL}/forgot-password/reset-password`, {
      email: email.value,
      otp: otp.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    });
    alert("Đổi mật khẩu thành công! Vui lòng đăng nhập lại.");
    router.push('/dang-nhap');
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không thể đổi mật khẩu.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* Reset cơ bản cho component để chống xung đột */
.auth-page *, .auth-page *::before, .auth-page *::after {
  box-sizing: border-box;
}

/* Nền toàn trang */
.auth-page {
  min-height: 100vh;
  background-color: #faf7f2;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  padding: 20px;
}

/* Phần Logo */
.logo-container {
  margin-bottom: 30px;
  text-align: center;
}
.logo-text {
  font-size: 26px;
  font-weight: 800;
  color: #2c2c2c;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin: 0;
}
.logo-icon {
  background-color: #fcefe3;
  color: #c88949;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 22px;
}
.logo-sub {
  font-size: 13px;
  font-weight: 500;
  color: #999;
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

/* Khung trắng chính (Card) */
.auth-card {
  background: #ffffff;
  width: 100%;
  max-width: 440px;
  border-radius: 20px;
  padding: 45px 40px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  text-align: center;
}

/* Icon Header */
.auth-icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 24px auto;
  background-color: #fafafa;
  border: 1px solid #eaeaea;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #c88949;
  font-size: 24px;
}

/* Tiêu đề & Text */
.auth-title {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 12px 0;
}
.auth-subtitle {
  font-size: 14.5px;
  color: #666;
  margin: 0 0 30px 0;
  line-height: 1.6;
}

/* Lỗi text */
.error-text {
  background-color: #fff2f2;
  color: #dc3545;
  font-size: 13.5px;
  text-align: left;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
  border-left: 4px solid #dc3545;
}

/* Form */
.auth-form {
  display: flex;
  flex-direction: column;
  width: 100%;
}

/* Ép các nhóm input hiển thị dọc */
.input-group {
  display: flex !important;
  flex-direction: column !important;
  align-items: flex-start !important;
  margin-bottom: 22px;
  width: 100%;
}
.input-group label {
  display: block !important;
  font-size: 14px;
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
}
.input-wrapper {
  position: relative;
  width: 100%;
}
.input-wrapper i {
  position: absolute;
  top: 50%;
  left: 16px;
  transform: translateY(-50%);
  color: #a0a0a0;
  font-size: 16px;
}
.input-wrapper input {
  width: 100% !important;
  display: block !important;
  padding: 14px 16px 14px 44px;
  border: 1px solid #e2e2e2;
  border-radius: 10px;
  font-size: 15px;
  outline: none;
  background-color: #fbfbfb;
  transition: all 0.3s ease;
  font-family: inherit;
}
.input-wrapper input:focus {
  border-color: #c88949;
  background-color: #ffffff;
  box-shadow: 0 0 0 4px rgba(200, 137, 73, 0.1);
}

/* Nút Submit */
.submit-btn {
  width: 100%;
  background: linear-gradient(135deg, #c88949, #b07335);
  color: #ffffff;
  border: none;
  padding: 15px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
  margin-top: 10px;
  box-shadow: 0 4px 12px rgba(200, 137, 73, 0.2);
}
.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(200, 137, 73, 0.3);
}
.submit-btn:disabled {
  background: #d4b595;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}

/* Footer Card Links */
.auth-links {
  margin-top: 30px;
  padding-top: 25px;
  border-top: 1px solid #f1f1f1;
  font-size: 14px;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #999;
}
.auth-links a {
  text-decoration: none;
  transition: color 0.2s;
  font-weight: 500;
}
.back-link {
  color: #666;
}
.back-link:hover {
  color: #111;
}
.divider {
  margin: 0 15px;
  color: #e0e0e0;
}
.highlight {
  color: #c88949;
  font-weight: 600;
}
.highlight:hover {
  color: #9e6933;
}

/* Link trang chủ */
.home-link {
  margin-top: 35px;
}
.home-link a {
  font-size: 14px;
  color: #888;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: color 0.2s;
  font-weight: 500;
}
.home-link a:hover {
  color: #444;
}
</style>