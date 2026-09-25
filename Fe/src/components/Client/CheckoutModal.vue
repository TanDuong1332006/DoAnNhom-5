<template>
  <div v-if="cartStore.isOpenCheckout" class="checkout-modal-backdrop position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3" style="background-color: rgba(0,0,0,0.65); z-index: 1080;">
    <div class="card border-0 rounded-4 shadow-lg bg-white overflow-hidden checkout-modal-card" style="width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-column;">
      
      <!-- Modal Header -->
      <div class="modal-header border-bottom px-4 py-3 bg-white d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class='bx bx-credit-card-front fs-4'></i>
          </div>
          <div>
            <h5 class="fw-bold text-dark mb-0 font-streetwear fs-6">
              {{ currentStep === 'payment' ? 'Thanh Toán VietQR - MB Bank' : (currentStep === 'success' ? 'Đặt Hàng Thành Công' : 'Xác Nhận Đặt Hàng & Thanh Toán') }}
            </h5>
            <span class="small text-muted" style="font-size: 11px;">
              {{ currentStep === 'payment' ? 'Quét mã QR để hoàn tất thanh toán tự động' : 'Hệ thống mua sắm trực tuyến chính hãng SOFEP STUDIO' }}
            </span>
          </div>
        </div>
        <button type="button" class="btn-close shadow-none" @click="dongModal"></button>
      </div>

      <!-- Modal Body (Scrollable) -->
      <div class="modal-body p-4 overflow-y-auto">

        <!-- ══════════════════════════════════════════════════════ -->
        <!-- BƯỚC 1: NHẬP THÔNG TIN GIAO HÀNG & CHỌN PHƯƠNG THỨC    -->
        <!-- ══════════════════════════════════════════════════════ -->
        <div v-if="currentStep === 'form'">
          
          <!-- Receiver Form -->
          <h6 class="fw-bold text-dark small text-uppercase tracking-wider mb-3 d-flex align-items-center gap-1.5">
            <i class='bx bx-map-pin text-danger fs-5'></i>
            <span>1. Địa Chỉ Nhận Hàng</span>
          </h6>

          <div class="row g-2.5 mb-3">
            <div class="col-12 col-sm-6">
              <label class="form-label small fw-semibold text-secondary mb-1">Họ và tên người nhận *</label>
              <input type="text" v-model="form.ho_ten" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Ví dụ: Nguyễn Văn An">
            </div>
            <div class="col-12 col-sm-6">
              <label class="form-label small fw-semibold text-secondary mb-1">Số điện thoại liên hệ *</label>
              <input type="tel" v-model="form.so_dien_thoai" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Ví dụ: 0987654321">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold text-secondary mb-1">Địa chỉ giao hàng chi tiết *</label>
              <input type="text" v-model="form.dia_chi_giao" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố">
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold text-secondary mb-1">Ghi chú đơn hàng (Tùy chọn)</label>
              <input type="text" v-model="form.ghi_chu" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến...">
            </div>
          </div>

          <!-- Payment Method Options -->
          <h6 class="fw-bold text-dark small text-uppercase tracking-wider mb-2.5 d-flex align-items-center gap-1.5">
            <i class='bx bx-wallet text-danger fs-5'></i>
            <span>2. Chọn Phương Thức Thanh Toán</span>
          </h6>

          <div class="d-flex flex-column gap-2 mb-4">
            
            <!-- Option 1: MB Bank VietQR (Khuyên dùng) -->
            <label class="payment-option-card rounded-3 border p-3 cursor-pointer d-flex align-items-center justify-content-between"
                   :class="{ 'active border-danger bg-danger bg-opacity-10': form.phuong_thuc === 'MB_BANK' }">
              <div class="d-flex align-items-center gap-2.5">
                <input type="radio" v-model="form.phuong_thuc" value="MB_BANK" class="form-check-input mt-0 me-1">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/25/Logo_MB_new.png" style="height: 24px; width: auto;" alt="MB Bank Logo">
                <div>
                  <div class="fw-bold text-dark small d-flex align-items-center gap-1">
                    <span>Chuyển Khoản MB Bank (VietQR)</span>
                    <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.5" style="font-size: 9px;">Tự Động 24/7</span>
                  </div>
                  <div class="text-muted small" style="font-size: 11px;">Quét mã QR qua app ngân hàng, hệ thống tự động xác nhận đơn sau 1-3 giây.</div>
                </div>
              </div>
              <i class='bx bxs-check-circle text-danger fs-5' v-if="form.phuong_thuc === 'MB_BANK'"></i>
            </label>

            <!-- Option 2: COD -->
            <label class="payment-option-card rounded-3 border p-3 cursor-pointer d-flex align-items-center justify-content-between"
                   :class="{ 'active border-dark bg-light': form.phuong_thuc === 'COD' }">
              <div class="d-flex align-items-center gap-2.5">
                <input type="radio" v-model="form.phuong_thuc" value="COD" class="form-check-input mt-0 me-1">
                <div class="rounded-circle bg-light border p-1 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                  <i class='bx bx-money fs-5 text-dark'></i>
                </div>
                <div>
                  <div class="fw-bold text-dark small">Thanh Toán Khi Nhận Hàng (COD)</div>
                  <div class="text-muted small" style="font-size: 11px;">Kiểm tra hàng rồi thanh toán tiền mặt trực tiếp cho nhân viên giao vận.</div>
                </div>
              </div>
              <i class='bx bxs-check-circle text-dark fs-5' v-if="form.phuong_thuc === 'COD'"></i>
            </label>

          </div>

          <!-- Order Summary Card -->
          <div class="bg-light rounded-3 p-3 border mb-3">
            <div class="d-flex justify-content-between small text-secondary mb-1">
              <span>Tổng số lượng sản phẩm:</span>
              <strong class="text-dark">{{ cartStore.count }} món</strong>
            </div>
            <div class="d-flex justify-content-between small text-secondary mb-1">
              <span>Tạm tính tiền hàng:</span>
              <span>₫{{ formatMoney(cartStore.subtotal) }}</span>
            </div>
            <div class="d-flex justify-content-between small text-secondary mb-1">
              <span>Phí vận chuyển:</span>
              <span :class="phiVanChuyen === 0 ? 'text-success fw-bold' : ''">
                {{ phiVanChuyen === 0 ? 'Miễn phí' : '₫' + formatMoney(phiVanChuyen) }}
              </span>
            </div>
            <div class="d-flex justify-content-between align-items-baseline pt-2 mt-2 border-top">
              <span class="fw-bold text-dark">Tổng thanh toán:</span>
              <span class="fs-4 fw-black text-danger font-streetwear">₫{{ formatMoney(tongThanhToan) }}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <button @click="taoDonHang"
                  :disabled="loadingTaoDon"
                  class="btn btn-danger btn-lg w-100 rounded-pill fw-bold text-white shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2">
            <span v-if="loadingTaoDon" class="spinner-border spinner-border-sm"></span>
            <span v-else><i class='bx bx-check-shield fs-5'></i></span>
            <span>XÁC NHẬN ĐẶT HÀNG (₫{{ formatMoney(tongThanhToan) }})</span>
          </button>

        </div>


        <!-- ══════════════════════════════════════════════════════ -->
        <!-- BƯỚC 2: MÀN HÌNH THANH TOÁN VIETQR MB BANK (TỰ ĐỘNG)    -->
        <!-- ══════════════════════════════════════════════════════ -->
        <div v-else-if="currentStep === 'payment'" class="text-center">
          
          <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 rounded-3 text-start small mb-3 p-2.5">
            <div class="d-flex align-items-center gap-1.5 text-danger fw-bold mb-1">
              <i class='bx bxs-bell-ring fs-5'></i>
              <span>Vui lòng không thay đổi NỘI DUNG CHUYỂN KHOẢN:</span>
            </div>
            <div class="text-muted">
              Nội dung chuyển khoản chuẩn: <strong class="badge bg-danger fs-6 text-white">{{ donHangData?.ma_don_hang }}</strong> để hệ thống tự động xác nhận đơn hàng ngay khi nhận được tiền!
            </div>
          </div>

          <!-- VietQR Code Image Card -->
          <div class="vietqr-box bg-white p-3 rounded-4 border shadow-sm mx-auto mb-3" style="max-width: 320px;">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <img src="https://upload.wikimedia.org/wikipedia/commons/2/25/Logo_MB_new.png" style="height: 22px;" alt="MB Bank">
              <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 10px;">VietQR 24/7</span>
            </div>
            
            <div class="position-relative overflow-hidden rounded-3 border mb-2" style="background-color: #fafafa;">
              <img :src="vietqrUrl" class="img-fluid w-100" alt="VietQR MB Bank">
            </div>

            <div class="small fw-semibold text-danger">Quét mã bằng mọi App Ngân Hàng</div>
          </div>

          <!-- Bank Details Table with 1-click Copy -->
          <div class="card border rounded-3 p-3 bg-light text-start mb-3 small">
            <div class="row gy-2">
              <div class="col-6 text-muted">Ngân hàng:</div>
              <div class="col-6 text-end fw-bold text-dark">MB Bank (Quân Đội)</div>

              <div class="col-6 text-muted">Số tài khoản:</div>
              <div class="col-6 text-end">
                <span class="fw-black text-danger font-monospace fs-6 me-1">{{ bankInfo.so_tai_khoan }}</span>
                <button @click="copyText(bankInfo.so_tai_khoan, 'Đã sao chép số tài khoản!')" class="btn btn-outline-secondary btn-sm py-0 px-1.5" title="Copy">
                  <i class='bx bx-copy'></i>
                </button>
              </div>

              <div class="col-6 text-muted">Chủ tài khoản:</div>
              <div class="col-6 text-end fw-bold text-dark">{{ bankInfo.chu_tai_khoan }}</div>

              <div class="col-6 text-muted">Số tiền chuyển:</div>
              <div class="col-6 text-end">
                <span class="fw-black text-danger fs-6 me-1">₫{{ formatMoney(bankInfo.so_tien) }}</span>
                <button @click="copyText(bankInfo.so_tien, 'Đã sao chép số tiền!')" class="btn btn-outline-secondary btn-sm py-0 px-1.5" title="Copy">
                  <i class='bx bx-copy'></i>
                </button>
              </div>

              <div class="col-6 text-muted">Nội dung chuyển khoản:</div>
              <div class="col-6 text-end">
                <span class="badge bg-danger text-white fs-6 font-monospace me-1">{{ bankInfo.noi_dung_chuyen_khoan }}</span>
                <button @click="copyText(bankInfo.noi_dung_chuyen_khoan, 'Đã sao chép nội dung chuyển khoản!')" class="btn btn-danger btn-sm py-0 px-1.5" title="Copy">
                  <i class='bx bx-copy'></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Live Automatic Polling Indicator -->
          <div class="p-3 bg-white rounded-3 border mb-3 d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
              <div class="spinner-grow spinner-grow-sm text-danger" role="status"></div>
              <div class="text-start">
                <div class="small fw-bold text-dark">Đang tự động kiểm tra giao dịch MB Bank...</div>
                <div class="text-muted" style="font-size: 11px;">Hệ thống quét định kỳ mỗi 3 giây hoặc bạn có thể bấm kiểm tra ngay.</div>
              </div>
            </div>
            <button @click="kiemTraGiaoDichNgay" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold text-nowrap" :disabled="loadingCheck">
              <span v-if="loadingCheck" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class='bx bx-refresh me-1'></i> Kiểm tra ngay
            </button>
          </div>

          <!-- Quick Test Simulation (Dành cho chấm điểm & Test Demo) -->
          <div class="p-2.5 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 text-start small mb-2 d-flex align-items-center justify-content-between">
            <div>
              <span class="fw-bold text-dark">🧪 Chế độ Demo / Chấm điểm:</span>
              <div class="text-muted" style="font-size: 11px;">Mô phỏng khách vừa chuyển khoản thành công vào tài khoản MB Bank.</div>
            </div>
            <button @click="moPhongChuyenKhoan" class="btn btn-warning btn-sm fw-bold text-dark rounded-pill px-3 shadow-xs">
              Mô Phỏng Đã Chuyển
            </button>
          </div>

        </div>


        <!-- ══════════════════════════════════════════════════════ -->
        <!-- BƯỚC 3: THÔNG BÁO ĐẶT HÀNG / THANH TOÁN THÀNH CÔNG     -->
        <!-- ══════════════════════════════════════════════════════ -->
        <div v-else-if="currentStep === 'success'" class="text-center py-4">
          <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 80px; height: 80px;">
            <i class='bx bx-check-double display-4'></i>
          </div>
          <h4 class="fw-bold text-dark mb-1 font-streetwear">Đặt Hàng Thành Công!</h4>
          <p class="text-muted small mb-3">
            Mã đơn hàng: <strong class="text-danger font-monospace fs-6">{{ donHangData?.ma_don_hang }}</strong>
          </p>

          <div class="p-3 bg-light rounded-3 border text-start small mx-auto mb-4" style="max-width: 480px;">
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Trạng thái thanh toán:</span>
              <span class="badge bg-success text-white fw-bold">Đã Thanh Toán Thành Công</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Phương thức:</span>
              <span class="fw-semibold text-dark">{{ form.phuong_thuc === 'MB_BANK' ? 'Chuyển khoản MB Bank (VietQR)' : 'Thanh toán COD' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Người nhận:</span>
              <span class="fw-semibold text-dark">{{ form.ho_ten }} ({{ form.so_dien_thoai }})</span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted">Địa chỉ nhận hàng:</span>
              <span class="fw-semibold text-dark text-truncate ms-2" style="max-width: 280px;">{{ form.dia_chi_giao }}</span>
            </div>
          </div>

          <div class="d-flex justify-content-center gap-2">
            <button @click="hoanTatMuaHang" class="btn btn-danger rounded-pill px-4 fw-bold">
              Tiếp Tục Mua Sắm
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script>
import { cartStore } from '../../stores/cartStore';
import axios from 'axios';

export default {
  name: 'CheckoutModal',
  data() {
    return {
      cartStore,
      currentStep: 'form', // 'form', 'payment', 'success'
      loadingTaoDon: false,
      loadingCheck: false,
      pollInterval: null,
      form: {
        ho_ten: '',
        so_dien_thoai: '',
        dia_chi_giao: '',
        ghi_chu: '',
        phuong_thuc: 'MB_BANK'
      },
      donHangData: null,
      vietqrUrl: '',
      bankInfo: {
        ten_ngan_hang: 'MB Bank',
        so_tai_khoan: '0862296097',
        chu_tai_khoan: 'MB BANK OFFICIAL',
        noi_dung_chuyen_khoan: '',
        so_tien: 0
      }
    };
  },
  computed: {
    phiVanChuyen() {
      return this.cartStore.subtotal >= 500000 ? 0 : 30000;
    },
    tongThanhToan() {
      return this.cartStore.subtotal + this.phiVanChuyen;
    }
  },
  watch: {
    'cartStore.isOpenCheckout'(isOpen) {
      if (isOpen) {
        this.currentStep = 'form';
        this.fillUserInfo();
      } else {
        this.dungPolling();
      }
    }
  },
  beforeUnmount() {
    this.dungPolling();
  },
  methods: {
    fillUserInfo() {
      const userStr = localStorage.getItem('user') || localStorage.getItem('nguoi_dung');
      if (userStr) {
        try {
          const user = JSON.parse(userStr);
          this.form.ho_ten = user.ho_va_ten || user.name || '';
          this.form.so_dien_thoai = user.so_dien_thoai || user.phone || '';
          this.form.dia_chi_giao = user.dia_chi || '';
        } catch (e) {}
      }
    },
    dongModal() {
      this.dungPolling();
      this.cartStore.toggleCheckout(false);
    },
    formatMoney(val) {
      if (!val) return '0';
      return Number(val).toLocaleString('vi-VN');
    },
    copyText(text, successMsg = 'Đã sao chép thành công!') {
      if (!text) return;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(String(text));
        this.$toast ? this.$toast.info(successMsg) : alert(successMsg);
      }
    },
    taoDonHang() {
      if (!this.form.ho_ten.trim()) {
        this.$toast ? this.$toast.error('Vui lòng nhập họ và tên người nhận!') : alert('Vui lòng nhập họ tên!');
        return;
      }
      if (!this.form.so_dien_thoai.trim()) {
        this.$toast ? this.$toast.error('Vui lòng nhập số điện thoại người nhận!') : alert('Vui lòng nhập SĐT!');
        return;
      }
      if (!this.form.dia_chi_giao.trim()) {
        this.$toast ? this.$toast.error('Vui lòng nhập địa chỉ nhận hàng!') : alert('Vui lòng nhập địa chỉ!');
        return;
      }
      if (this.cartStore.items.length === 0) {
        this.$toast ? this.$toast.warning('Giỏ hàng đang trống!') : alert('Giỏ hàng trống!');
        return;
      }

      this.loadingTaoDon = true;
      const payload = {
        ho_ten: this.form.ho_ten,
        so_dien_thoai: this.form.so_dien_thoai,
        dia_chi_giao: this.form.dia_chi_giao,
        ghi_chu: this.form.ghi_chu,
        phuong_thuc_thanh_toan: this.form.phuong_thuc,
        tong_tien_hang: this.cartStore.subtotal,
        phi_van_chuyen: this.phiVanChuyen,
        tong_thanh_toan: this.tongThanhToan,
        items: this.cartStore.items.map(i => ({
          id: i.id,
          ten_san_pham: i.ten_san_pham,
          hinh_anh: i.hinh_anh,
          quantity: i.so_luong,
          gia_ban: i.gia,
          size: i.kich_thuoc,
          color: i.mau_sac
        }))
      };

      axios.post('http://127.0.0.1:8000/api/client/dat-hang', payload)
        .then(res => {
          if (res.data.status) {
            this.donHangData = res.data.don_hang;
            this.vietqrUrl = res.data.vietqr_url;
            if (res.data.ngan_hang) {
              this.bankInfo = res.data.ngan_hang;
            }

            if (this.form.phuong_thuc === 'MB_BANK') {
              this.currentStep = 'payment';
              this.batDauPolling(this.donHangData.ma_don_hang);
            } else {
              this.currentStep = 'success';
              this.cartStore.clearCart();
            }
          }
        })
        .catch(err => {
          console.error(err);
          this.$toast ? this.$toast.error('Có lỗi xảy ra khi tạo đơn hàng!') : alert('Lỗi tạo đơn hàng!');
        })
        .finally(() => {
          this.loadingTaoDon = false;
        });
    },
    batDauPolling(maHoaDon) {
      this.dungPolling();
      // Polling mỗi 3 giây kiểm tra xem đã chuyển khoản thành công chưa
      this.pollInterval = setInterval(() => {
        this.kiemTraTrangThai(maHoaDon);
      }, 3000);
    },
    dungPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval);
        this.pollInterval = null;
      }
    },
    kiemTraTrangThai(maHoaDon) {
      if (!maHoaDon) return;
      axios.get(`http://127.0.0.1:8000/api/kiem-tra-thanh-toan/${maHoaDon}`)
        .then(res => {
          if (res.data.status && res.data.da_thanh_toan) {
            this.dungPolling();
            this.currentStep = 'success';
            this.cartStore.clearCart();
            if (this.$toast) {
              this.$toast.success('Hệ thống MB Bank đã ghi nhận thanh toán thành công!');
            }
          }
        })
        .catch(e => console.warn(e));
    },
    kiemTraGiaoDichNgay() {
      this.loadingCheck = true;
      // Gọi API giao-dich quét MB Bank
      axios.get('http://127.0.0.1:8000/api/giao-dich')
        .then(() => {
          if (this.donHangData?.ma_don_hang) {
            this.kiemTraTrangThai(this.donHangData.ma_don_hang);
          }
        })
        .catch(() => {})
        .finally(() => {
          setTimeout(() => {
            this.loadingCheck = false;
          }, 800);
        });
    },
    moPhongChuyenKhoan() {
      if (!this.donHangData?.ma_don_hang) return;
      axios.post('http://127.0.0.1:8000/api/giao-dich/mo-phong', {
        ma_hoa_don: this.donHangData.ma_don_hang,
        so_tien: this.bankInfo.so_tien
      })
      .then(res => {
        if (res.data.status) {
          this.dungPolling();
          this.currentStep = 'success';
          this.cartStore.clearCart();
          if (this.$toast) {
            this.$toast.success('Mô phỏng MB Bank thành công!');
          }
        }
      })
      .catch(e => console.error(e));
    },
    hoanTatMuaHang() {
      this.dongModal();
      this.$router.push('/');
    }
  }
};
</script>

<style scoped>
.checkout-modal-card {
  animation: modalFadeIn 0.25s ease-out;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.payment-option-card {
  transition: all 0.2s ease;
  border-width: 1.5px !important;
}

.payment-option-card:hover {
  border-color: #ee4d2d !important;
}

.vietqr-box {
  border: 1px solid #dee2e6;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
