<template>
  <button v-if="!cartStore.isOpenCheckout && currentStep === 'payment' && donHangData" type="button"
    class="btn btn-dark position-fixed bottom-0 end-0 m-3 rounded-pill shadow" style="z-index:1070"
    @click="cartStore.toggleCheckout(true)">Tiếp tục thanh toán demo</button>
  <div v-if="cartStore.isOpenCheckout" role="dialog" aria-modal="true" :aria-labelledby="currentStep === 'auth' ? 'checkout-auth-heading' : 'checkout-heading'" @keydown.esc.stop.prevent="dongModal" class="checkout-modal-backdrop position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3" style="background-color: rgba(0,0,0,0.65); z-index: 1080;">
    <div :class="{ 'checkout-auth-card': currentStep === 'auth' }" class="card border-0 rounded-4 shadow-lg bg-white overflow-hidden checkout-modal-card" style="width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column;">
      
      <!-- Modal Header -->
      <div v-if="currentStep !== 'auth'" class="modal-header border-bottom px-4 py-3 bg-white d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class='bx bx-credit-card-front fs-4'></i>
          </div>
          <div>
            <h5 id="checkout-heading" class="fw-bold text-dark mb-0 font-streetwear fs-6">
              {{ currentStep === 'payment' ? 'Thanh toán mô phỏng' : (currentStep === 'success' ? tieuDeKetQuaDonHang : 'Xác Nhận Đặt Hàng & Thanh Toán') }}
            </h5>
            <span class="small text-muted" style="font-size: 11px;">
              {{ currentStep === 'payment' ? 'Demo SOFEP — không chuyển tiền thật' : 'Hệ thống mua sắm trực tuyến chính hãng SOFEP STUDIO' }}
            </span>
          </div>
        </div>
        <button type="button" :disabled="loadingTaoDon" aria-label="Đóng thanh toán" class="btn-close shadow-none" @click="dongModal"></button>
      </div>

      <!-- Modal Body (Scrollable) -->
      <div class="modal-body p-4 overflow-y-auto">

        <!-- ══════════════════════════════════════════════════════ -->
        <!-- BƯỚC 1: NHẬP THÔNG TIN GIAO HÀNG & CHỌN PHƯƠNG THỨC    -->
        <!-- ══════════════════════════════════════════════════════ -->
        <div v-if="authChecking" class="text-center py-4" role="status">Đang xác minh phiên đăng nhập…</div>
        <div v-else-if="currentStep === 'auth'" class="checkout-auth-content">
          <svg class="checkout-auth-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M8 8V6a4 4 0 0 1 8 0v2M5 8h14l1 13H4L5 8Z" />
            <path d="M12 12v5m-2-3 2-2 2 2" />
          </svg>
          <h2 id="checkout-auth-heading">Đăng nhập để tiếp tục mua hàng</h2>
          <p>Giỏ hàng của bạn sẽ được giữ nguyên.</p>
          <div class="checkout-auth-actions">
            <button type="button" class="checkout-auth-login" @click="goAuth('/dang-nhap')">Đăng nhập</button>
            <button type="button" class="checkout-auth-register" @click="goAuth('/dang-ky')">Đăng ký</button>
            <button type="button" class="checkout-auth-return" @click="dongModal">Quay lại cửa hàng</button>
          </div>
        </div>
        <div v-else-if="currentStep === 'form'">
          
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
                    <span>Thanh toán mô phỏng (demo)</span>
                    <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.5" style="font-size: 9px;">DEMO</span>
                  </div>
                  <div class="text-muted small" style="font-size: 11px;">Dùng nút mô phỏng sau khi đặt hàng; không chuyển tiền thật.</div>
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

          <div class="mb-3">
            <label for="checkout-voucher" class="form-label small fw-semibold">Mã giảm giá (tùy chọn)</label>
            <select v-if="walletVouchers.length" v-model="voucherCode" class="form-select mb-2" aria-label="Chọn voucher từ ví"><option value="">Chọn mã đã lưu trong Ví ưu đãi</option><option v-for="voucher in walletVouchers" :key="voucher.id" :value="voucher.ma_code" :disabled="voucher.state !== 'available'">{{ voucher.ma_code }} · {{ voucher.ten_giam_gia }}{{ voucher.state !== 'available' ? ' — ' + voucher.reason : '' }}</option></select>
            <small v-if="walletMessage" class="text-muted d-block">{{ walletMessage }}</small>
            <input id="checkout-voucher" v-model="voucherCode" maxlength="50" class="form-control" placeholder="Nhập mã voucher">
            <button @click="checkVoucher" :disabled="loadingVoucher || loadingTaoDon" class="btn btn-outline-dark btn-sm mt-2">{{ loadingVoucher ? 'Đang kiểm tra…' : 'Kiểm tra mã và tổng tiền' }}</button>
            <div v-if="voucherMessage" class="small mt-2" role="status" :class="quote ? 'text-success' : 'text-danger'">{{ voucherMessage }}</div>
            <small class="text-muted">Mức giảm và tồn kho được xác nhận lại khi đặt hàng.</small>
          </div>
          <div v-if="cartIssue" class="alert alert-warning" role="alert">
            <strong>{{ cartIssue.item.ten_san_pham }}</strong>
            <p class="mb-2">{{ cartIssue.message }}</p>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-outline-dark btn-sm" @click="reviewCart">Chỉnh sửa giỏ hàng</button>
              <button type="button" class="btn btn-outline-danger btn-sm" @click="reselectItem">Bỏ dòng này và chọn lại sản phẩm</button>
            </div>
          </div>
          <!-- Order Summary Card -->
          <div class="bg-light rounded-3 p-3 border mb-3">
            <div class="d-flex justify-content-between small text-secondary mb-1">
              <span>Tổng số lượng sản phẩm:</span>
              <strong class="text-dark">{{ cartStore.count }} món</strong>
            </div>
            <div class="d-flex justify-content-between small text-secondary mb-1">
              <span>{{ quote ? 'Tiền hàng đã kiểm tra:' : 'Tạm tính tiền hàng:' }}</span>
              <span>₫{{ formatMoney(quote?.tong_tien_hang ?? cartStore.subtotal) }}</span>
            </div>
            <div class="d-flex justify-content-between small text-secondary mb-1">
              <span>Phí vận chuyển:</span>
              <span :class="phiVanChuyen === 0 ? 'text-success fw-bold' : ''">
                {{ phiVanChuyen === 0 ? 'Miễn phí' : '₫' + formatMoney(phiVanChuyen) }}
              </span>
            </div>
            <div v-if="quote" class="d-flex justify-content-between small text-success"><span>Giảm voucher:</span><span>-₫{{ formatMoney(quote.tien_giam_gia) }}</span></div>
            <div class="d-flex justify-content-between align-items-baseline pt-2 mt-2 border-top">
              <span class="fw-bold text-dark">Tổng thanh toán:</span>
              <span class="fs-4 fw-black text-danger font-streetwear">₫{{ formatMoney(tongThanhToan) }}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <button @click="taoDonHang"
                  :disabled="loadingTaoDon || loadingVoucher || !!cartIssue || !cartStore.items.length"
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
          
          <div class="alert alert-warning text-start">
            <strong>Thanh toán mô phỏng phục vụ demo.</strong> Không chuyển tiền thật.
            <div>Mã đơn: {{ donHangData?.ma_don_hang }}</div>
            <div>Tổng tiền đã được server xác nhận: ₫{{ formatMoney(donHangData?.tong_thanh_toan) }}</div>
          </div>

          <!-- Quick Test Simulation (Dành cho chấm điểm & Test Demo) -->
          <div class="p-2.5 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 text-start small mb-2 d-flex align-items-center justify-content-between">
            <div>
              <span class="fw-bold text-dark">🧪 Chế độ Demo / Chấm điểm:</span>
              <div class="text-muted" style="font-size: 11px;">Chỉ ghi nhận trạng thái demo, không có giao dịch tiền thật.</div>
            </div>
            <button :disabled="loadingSimulation" @click="moPhongChuyenKhoan('success')" class="btn btn-warning btn-sm fw-bold text-dark rounded-pill px-3 shadow-xs">
              Mô phỏng thanh toán
            </button>
            <button :disabled="loadingSimulation" @click="moPhongChuyenKhoan('failed')" class="btn btn-outline-dark btn-sm rounded-pill">Mô phỏng thất bại</button>
          </div>

        </div>


        <!-- ══════════════════════════════════════════════════════ -->
        <!-- BƯỚC 3: THÔNG BÁO ĐẶT HÀNG / THANH TOÁN THÀNH CÔNG     -->
        <!-- ══════════════════════════════════════════════════════ -->
        <div v-else-if="currentStep === 'success'" class="text-center py-4">
          <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 80px; height: 80px;">
            <i class='bx bx-check-double display-4'></i>
          </div>
          <h4 class="fw-bold text-dark mb-1 font-streetwear">{{ tieuDeKetQuaDonHang }}!</h4>
          <p class="text-muted small mb-3">
            Mã đơn hàng: <strong class="text-danger font-monospace fs-6">{{ donHangData?.ma_don_hang }}</strong>
          </p>

          <div class="p-3 bg-light rounded-3 border text-start small mx-auto mb-4" style="max-width: 480px;">
            <div class="d-flex justify-content-between mb-1" role="status">
              <span class="text-muted">Trạng thái đơn hàng:</span>
              <span class="fw-semibold text-dark">{{ trangThaiDonHang }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Trạng thái thanh toán:</span>
              <span class="badge bg-success text-white fw-bold">{{ trangThaiThanhToan }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span class="text-muted">Phương thức:</span>
              <span class="fw-semibold text-dark">{{ form.phuong_thuc === 'MB_BANK' ? 'Thanh toán mô phỏng (demo)' : 'Thanh toán COD' }}</span>
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
            <button type="button" :disabled="loadingCheck" @click="kiemTraGiaoDichNgay" class="btn btn-outline-dark rounded-pill px-3">
              {{ loadingCheck ? 'Đang cập nhật…' : 'Cập nhật trạng thái đơn' }}
            </button>
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
import { apiUrl } from '../../services/apiConfig.js';
import { cartStore } from '../../stores/cartStore';
import axios from 'axios';
import { customerSession, clearCustomerSession } from '../../services/authSessions';
import { isNavigationFailure, NavigationFailureType } from 'vue-router';

export default {
  name: 'CheckoutModal',
  data() {
    return {
      cartStore,
      currentStep: 'form', // 'form', 'payment', 'success'
      authChecking: false,
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
      paymentToken: '',
      walletVouchers: [], walletMessage: '',
      voucherCode: '', quote: null, voucherMessage: '', loadingVoucher: false, loadingSimulation: false,
      cartIssue: null,
      orderKey: '',
      orderFingerprint: '',
      vietqrUrl: '',
      bankInfo: {
        ten_ngan_hang: 'MB Bank',
        so_tai_khoan: '0362242553',
        chu_tai_khoan: 'MB BANK OFFICIAL',
        noi_dung_chuyen_khoan: '',
        so_tien: 0
      }
    };
  },
  computed: {
    tieuDeKetQuaDonHang() {
      return this.donHangData?.trang_thai_don_hang === 'da_huy' ? 'Đơn Hàng Đã Hủy' : 'Đặt Hàng Thành Công';
    },
    trangThaiThanhToan() {
      if (this.donHangData?.trang_thai_thanh_toan !== 'da_thanh_toan') return 'Chưa thanh toán';
      return this.donHangData?.phuong_thuc_thanh_toan === 'MB_BANK' ? 'Đã thanh toán mô phỏng' : 'Đã thanh toán';
    },
    trangThaiDonHang() {
      return { cho_xac_nhan: 'Chờ xác nhận', da_xac_nhan: 'Đã xác nhận', dang_giao: 'Đang giao', da_giao: 'Đã giao', da_huy: 'Đã hủy' }[this.donHangData?.trang_thai_don_hang] || 'Chưa có thông tin';
    },
    phiVanChuyen() {
      return this.quote?.phi_van_chuyen ?? (this.cartStore.subtotal <= 0 || this.cartStore.subtotal >= 500000 ? 0 : 30000);
    },
    tongThanhToan() {
      return this.quote?.tong_thanh_toan ?? this.cartStore.subtotal + this.phiVanChuyen;
    }
  },
  watch: {
    voucherCode() { this.quote = null; this.voucherMessage = ''; },
    'cartStore.items': { deep: true, handler() { this.quote = null; this.voucherMessage = ''; this.cartIssue = null; } },
    async 'cartStore.isOpenCheckout'(isOpen) {
      if (isOpen) {
        if (!await this.verifyCustomer()) return;
        if (this.donHangData?.phuong_thuc_thanh_toan === 'MB_BANK' && this.donHangData.trang_thai_thanh_toan !== 'da_thanh_toan') { this.currentStep = 'payment'; this.batDauPolling(this.donHangData.ma_don_hang); return; }
        this.currentStep = 'form';
        this.orderKey = ''; this.orderFingerprint = ''; this.voucherCode = this.cartStore.selectedVoucherCode || '';
        this.cartStore.selectedVoucherCode = '';
        await this.loadVoucherWallet();
        this.fillUserInfo();
      } else {
        this.dungPolling();
      }
    }
  },
  async mounted() {
    if (sessionStorage.getItem('sofep_checkout_return')) {
      sessionStorage.removeItem('sofep_checkout_return');
      this.cartStore.toggleCheckout(true);
    }
    try {
      const pending = JSON.parse(sessionStorage.getItem('sofep_pending_demo_order') || 'null');
      if (pending?.code) {
        this.donHangData = { ma_don_hang: pending.code, tong_thanh_toan: pending.total, phuong_thuc_thanh_toan: 'MB_BANK' };
        this.paymentToken = ''; this.form.phuong_thuc = 'MB_BANK';
        if (!await this.verifyCustomer()) return;
        this.currentStep = 'payment';
        await this.kiemTraTrangThai(pending.code);
      }
    } catch {}
  },
  beforeUnmount() {
    this.dungPolling();
  },
  methods: {
    async verifyCustomer() {
      this.authChecking = true;
      const session = customerSession();
      try {
        if (!session) { this.currentStep = 'auth'; return false; }
        const { data } = await axios.get(apiUrl('/kiem-tra-dang-nhap'), { headers: { Authorization: `Bearer ${session.token}` } });
        if (data.status !== 1 || data.nguoi_dung?.role !== 'customer') { this.currentStep = 'auth'; return false; }
        return true;
      } catch (error) {
        if (error.response?.status === 401) clearCustomerSession(session?.token);
        this.currentStep = 'auth';
        return false;
      } finally { this.authChecking = false; }
    },
    goAuth(path) {
      sessionStorage.setItem('sofep_checkout_return', this.$route.fullPath);
      this.dongModal();
      this.$router.push(path);
    },
    handleAuthError(error) {
      if (![401, 403].includes(error.response?.status)) return false;
      if (error.response.status === 401) clearCustomerSession(customerSession()?.token);
      this.dungPolling(); this.currentStep = 'auth';
      return true;
    },
    clearPendingDemo() {
      this.dungPolling(); sessionStorage.removeItem('sofep_pending_demo_order');
      this.currentStep = 'form'; this.donHangData = null; this.paymentToken = '';
      this.cartStore.toggleCheckout(false);
    },
    fillUserInfo() {
      const userStr = JSON.stringify(customerSession()?.user || null);
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
    reviewCart() {
      this.dongModal();
      this.cartStore.toggleDrawer(true);
    },
    async reselectItem() {
      const item = this.cartIssue?.item;
      if (!item) return;
      const failure = await this.$router.push('/san-pham/' + encodeURIComponent(item.id));
      // Khi đã ở trang sản phẩm này, Vue Router báo điều hướng trùng.
      if (failure && !isNavigationFailure(failure, NavigationFailureType.duplicated)) return;
      this.cartStore.removeItem(item.itemKey);
      this.dongModal();
    },
    showCartIssue(error, items) {
      const errors = error.response?.data?.errors || {};
      for (const [field, messages] of Object.entries(errors)) {
        const match = /^items\.(\d+)(?:\.|$)/.exec(field);
        const item = match && items[Number(match[1])];
        if (!item) continue;
        this.quote = null;
        this.voucherMessage = '';
        this.cartIssue = { item, message: [].concat(messages)[0] };
        return true;
      }
      return false;
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
    async loadVoucherWallet() {
      this.walletVouchers = []; this.walletMessage = '';
      try { const { data } = await axios.get(apiUrl('/client/vi-uu-dai')); this.walletVouchers = data.data?.data || []; }
      catch (error) { if ([401,403].includes(error.response?.status)) this.handleAuthError(error); else this.walletMessage = 'Có thể nhập mã trực tiếp; Ví ưu đãi hiện chưa sẵn sàng.'; }
    },
    async checkVoucher() {
      if (this.loadingVoucher || this.loadingTaoDon || !this.cartStore.items.length) return;
      const items = this.cartStore.items.slice();
      const version = JSON.stringify([this.voucherCode, this.cartStore.items]);
      this.loadingVoucher = true; this.quote = null; this.voucherMessage = ''; this.cartIssue = null;
      try {
        const { data } = await axios.post(apiUrl('/client/kiem-tra-voucher'), {
          ma_giam_gia_su_dung: this.voucherCode.trim(),
          items: items.map(i => ({ id: i.id, id_chi_tiet_san_pham: i.id_chi_tiet_san_pham || null, quantity: i.so_luong }))
        });
        if (version !== JSON.stringify([this.voucherCode, this.cartStore.items])) return;
        this.quote = data.data; this.voucherMessage = data.message + ' Giảm ₫' + this.formatMoney(data.data?.tien_giam_gia) + '.';
      } catch (error) {
        if (version !== JSON.stringify([this.voucherCode, this.cartStore.items]) || this.showCartIssue(error, items)) return;
        this.voucherMessage = Object.values(error.response?.data?.errors || {}).flat()[0] || error.response?.data?.message || 'Không thể kiểm tra voucher.';
      }
      finally { this.loadingVoucher = false; }
    },
    async taoDonHang() {
      if (this.loadingTaoDon || this.loadingVoucher || this.cartIssue || this.currentStep !== 'form') return;
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
      if (!await this.verifyCustomer()) { this.loadingTaoDon = false; return; }
      const items = this.cartStore.items.slice();
      const cartVersion = JSON.stringify(items);
      const payload = {
        ho_ten: this.form.ho_ten,
        so_dien_thoai: this.form.so_dien_thoai,
        dia_chi_giao: this.form.dia_chi_giao,
        ghi_chu: this.form.ghi_chu,
        phuong_thuc_thanh_toan: this.form.phuong_thuc,
        ma_giam_gia_su_dung: this.voucherCode.trim(),
        tong_tien_hang: this.cartStore.subtotal,
        phi_van_chuyen: this.phiVanChuyen,
        tong_thanh_toan: this.tongThanhToan,
        items: items.map(i => ({
          id: i.id,
          ...(i.id_chi_tiet_san_pham ? { id_chi_tiet_san_pham: i.id_chi_tiet_san_pham } : {}),
          ten_san_pham: i.ten_san_pham,
          hinh_anh: i.hinh_anh,
          quantity: i.so_luong,
          gia_ban: i.gia,
          size: i.kich_thuoc,
          color: i.mau_sac
        }))
      };

      const fingerprint = JSON.stringify(payload);
      if (!this.orderKey || fingerprint !== this.orderFingerprint) {
        this.orderKey = crypto.randomUUID(); this.orderFingerprint = fingerprint;
      }
      return axios.post(apiUrl('/client/dat-hang'), payload, { headers: { 'Idempotency-Key': this.orderKey } })
        .then(res => {
          if (res.data.status) {
            this.donHangData = res.data.don_hang;
            this.paymentToken = res.data.payment_token;
            this.cartStore.clearCart();
            this.vietqrUrl = res.data.vietqr_url;
            if (res.data.ngan_hang) {
              this.bankInfo = res.data.ngan_hang;
            }

            if (this.form.phuong_thuc === 'MB_BANK') {
              sessionStorage.setItem('sofep_pending_demo_order', JSON.stringify({ code: this.donHangData.ma_don_hang, total: this.donHangData.tong_thanh_toan }));
              this.currentStep = 'payment';
              this.batDauPolling(this.donHangData.ma_don_hang);
            } else {
              this.currentStep = 'success';

            }
          }
        })
        .catch(err => {
          if (this.handleAuthError(err)) return;
          if (cartVersion === JSON.stringify(this.cartStore.items) && this.showCartIssue(err, items)) return;
          const message = Object.values(err.response?.data?.errors || {}).flat()[0] || err.response?.data?.message || 'Không thể tạo đơn hàng.';
          this.$toast ? this.$toast.error(message) : alert(message);
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
      return axios.get(apiUrl(`/kiem-tra-thanh-toan/${encodeURIComponent(maHoaDon)}`))
        .then(res => {
          const wasPaid = this.donHangData?.trang_thai_thanh_toan === 'da_thanh_toan';
          if (res.data.don_hang) {
            this.donHangData = res.data.don_hang;
            for (const field of ['ho_ten', 'so_dien_thoai', 'dia_chi_giao']) this.form[field] = this.donHangData[field] || '';
            this.form.phuong_thuc = this.donHangData.phuong_thuc_thanh_toan || 'MB_BANK';
            if (this.donHangData.trang_thai_don_hang === 'da_huy') {
              this.dungPolling();
              if (this.currentStep !== 'success') this.currentStep = 'form';
              sessionStorage.removeItem('sofep_pending_demo_order'); return;
            }
          }
          if (res.data.status && res.data.da_thanh_toan) {
            this.dungPolling();
            this.currentStep = 'success';
            sessionStorage.removeItem('sofep_pending_demo_order');
            if (this.$toast && !wasPaid) {
              this.$toast.success('Đã ghi nhận thanh toán mô phỏng.');
            }
          }
        })
        .catch(e => { if (!this.handleAuthError(e)) this.$toast?.error('Không thể kiểm tra trạng thái thanh toán.'); });
    },
    kiemTraGiaoDichNgay() {
      if (this.loadingCheck || !this.donHangData?.ma_don_hang) return;
      this.loadingCheck = true;
      return this.kiemTraTrangThai(this.donHangData.ma_don_hang).finally(() => { this.loadingCheck = false; });
    },
    moPhongChuyenKhoan(result = 'success') {
      if (!this.donHangData?.ma_don_hang || this.loadingSimulation) return;
      this.loadingSimulation = true;
      return axios.post(apiUrl('/giao-dich/mo-phong'), {
        ma_hoa_don: this.donHangData.ma_don_hang,
        ket_qua: result
      })
      .then(res => {
        if (res.data.status) {
          this.dungPolling();
          return this.kiemTraTrangThai(this.donHangData.ma_don_hang);
        } else {
          this.$toast?.error(res.data.message || 'Thanh toán thất bại. Đơn chưa thanh toán.');
        }
      })
      .catch(error => { if (!this.handleAuthError(error)) this.$toast?.error(error.response?.data?.message || 'Không thể mô phỏng thanh toán.'); })
      .finally(() => { this.loadingSimulation = false; });
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
.checkout-auth-card {
  max-width: 460px !important;
  border: 1px solid #e1e1e1 !important;
  border-radius: 24px !important;
}
.checkout-auth-card .modal-body { padding: 30px 16px 16px !important; }
.checkout-auth-content { text-align: center; color: #111; }
.checkout-auth-icon { display: block; margin: 0 auto 16px; }
.checkout-auth-content h2 { margin: 0; font-size: 20px; font-weight: 600; line-height: 1.4; letter-spacing: -.35px; }
.checkout-auth-content p { margin: 8px 0 16px; color: #717171; font-size: 14px; line-height: 1.6; }
.checkout-auth-actions { display: flex; flex-direction: column; gap: 12px; }
.checkout-auth-actions button { width: 100%; min-height: 46px; border-radius: 999px; font: inherit; font-size: 16px; font-weight: 600; padding: 10px 20px; transition: background-color .18s, border-color .18s; }
.checkout-auth-login { background: #101010; border: 1px solid #101010; color: #fff; }
.checkout-auth-login:hover { background: #303030; border-color: #303030; }
.checkout-auth-register { background: #fff; border: 1px solid #d7d7d7; color: #646464; }
.checkout-auth-register:hover { background: #f6f6f6; border-color: #aaa; }
.checkout-auth-return { background: transparent; border: 1px solid transparent; color: #646464; }
.checkout-auth-return:hover { background: #f6f6f6; }
.checkout-auth-actions button:focus-visible { outline: 2px solid #111; outline-offset: 3px; }
@media (max-width: 480px) {
  .checkout-auth-card .modal-body { padding: 26px 16px 14px !important; }
  .checkout-auth-content h2 { font-size: 18px; }
}
@media (prefers-reduced-motion: reduce) {
  .checkout-modal-card { animation: none; }
  .checkout-auth-actions button { transition: none; }
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

<style scoped>
.payment-option-card.active { background-color: #fff4f1 !important; color: #222; }
</style>
