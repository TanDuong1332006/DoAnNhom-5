<template>
  <div v-if="cartStore.isOpenSizeAdvisor" class="fixed-modal-overlay d-flex align-items-center justify-content-center p-3" @click="cartStore.toggleSizeAdvisor(false)">
    <div class="modal-dialog-content bg-white rounded-4 shadow-2xl overflow-hidden border" @click.stop>
      
      <!-- Modal Header -->
      <div class="bg-urbn-dark text-white px-4 py-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <div class="p-2 bg-urbn-primary rounded-3 d-flex align-items-center justify-content-center text-white">
            <i class='bx bx-ruler fs-5'></i>
          </div>
          <div>
            <h5 class="fw-bold mb-0 text-uppercase small tracking-wide">
              Bảng Hướng Dẫn & Gợi Ý Chọn Size
            </h5>
            <span class="text-white-50" style="font-size: 11px;">
              Quy đổi kích cỡ chuẩn theo phom dáng Boxy Drop-Shoulder
            </span>
          </div>
        </div>
        <button @click="cartStore.toggleSizeAdvisor(false)" class="btn btn-link text-white-50 p-1 text-decoration-none hover-white">
          <i class='bx bx-x fs-3'></i>
        </button>
      </div>

      <!-- Tab Switcher -->
      <div class="d-flex border-bottom bg-light">
        <button @click="activeTab = 'advisor'"
                :class="{ 'border-primary text-primary bg-white font-bold': activeTab === 'advisor', 'text-secondary': activeTab !== 'advisor' }"
                class="btn flex-fill py-2 rounded-0 border-bottom border-2 small fw-bold">
          <i class='bx bx-brain me-1'></i> Gợi Ý Cỡ Thông Minh
        </button>
        <button @click="activeTab = 'chart'"
                :class="{ 'border-primary text-primary bg-white font-bold': activeTab === 'chart', 'text-secondary': activeTab !== 'chart' }"
                class="btn flex-fill py-2 rounded-0 border-bottom border-2 small fw-bold">
          <i class='bx bx-table me-1'></i> Bảng Thông Số Chi Tiết
        </button>
      </div>

      <!-- Tab 1: Smart Sizing Advisor -->
      <div v-if="activeTab === 'advisor'" class="p-4">
        
        <!-- Height Slider -->
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="small fw-bold text-dark">Chiều cao của bạn:</label>
            <span class="badge bg-light text-dark border font-monospace fs-6 px-2 py-1">{{ height }} cm</span>
          </div>
          <input type="range" class="form-range" min="150" max="195" step="1" v-model.number="height">
          <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
            <span>150 cm</span>
            <span>170 cm</span>
            <span>195 cm</span>
          </div>
        </div>

        <!-- Weight Slider -->
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="small fw-bold text-dark">Cân nặng của bạn:</label>
            <span class="badge bg-light text-dark border font-monospace fs-6 px-2 py-1">{{ weight }} kg</span>
          </div>
          <input type="range" class="form-range" min="45" max="95" step="1" v-model.number="weight">
          <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
            <span>45 kg</span>
            <span>70 kg</span>
            <span>95 kg</span>
          </div>
        </div>

        <!-- Fit Preference -->
        <div class="mb-4">
          <label class="small fw-bold text-dark mb-2 d-block">Sở thích phom dáng mặc:</label>
          <div class="row g-2">
            <div class="col-4">
              <button @click="fitPreference = 'fitted'"
                      :class="{ 'border-primary bg-primary bg-opacity-10 text-primary fw-bold': fitPreference === 'fitted', 'btn-light text-secondary': fitPreference !== 'fitted' }"
                      class="btn btn-sm w-100 p-2 rounded-3 border text-start h-100">
                <div class="small fw-bold">Vừa Vặn</div>
                <div style="font-size: 10px;" class="opacity-75">Gọn gàng vừa người</div>
              </button>
            </div>
            <div class="col-4">
              <button @click="fitPreference = 'boxy'"
                      :class="{ 'border-primary bg-primary bg-opacity-10 text-primary fw-bold': fitPreference === 'boxy', 'btn-light text-secondary': fitPreference !== 'boxy' }"
                      class="btn btn-sm w-100 p-2 rounded-3 border text-start h-100">
                <div class="small fw-bold">Boxy Standard</div>
                <div style="font-size: 10px;" class="opacity-75">Rũ vai streetwear</div>
              </button>
            </div>
            <div class="col-4">
              <button @click="fitPreference = 'oversized'"
                      :class="{ 'border-primary bg-primary bg-opacity-10 text-primary fw-bold': fitPreference === 'oversized', 'btn-light text-secondary': fitPreference !== 'oversized' }"
                      class="btn btn-sm w-100 p-2 rounded-3 border text-start h-100">
                <div class="small fw-bold">Oversized</div>
                <div style="font-size: 10px;" class="opacity-75">Thụng rộng Y2K</div>
              </button>
            </div>
          </div>
        </div>

        <!-- Recommendation Output Box -->
        <div class="p-3 rounded-4 bg-light border border-primary border-opacity-25 d-flex gap-3 align-items-center">
          <div class="rounded-circle bg-urbn-primary text-white d-flex flex-column align-items-center justify-content-center flex-shrink-0 shadow"
               style="width: 68px; height: 68px;">
            <span class="fs-3 fw-bolder lh-1">{{ recommendation.size }}</span>
            <span style="font-size: 9px;" class="opacity-75 font-monospace text-uppercase">Khuyên dùng</span>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 fw-bold" style="font-size: 11px;">
                <i class='bx bx-check-circle me-1'></i>Độ chính xác {{ recommendation.confidence }}%
              </span>
            </div>
            <p class="small text-dark mb-0 leading-tight">
              {{ recommendation.reason }}
            </p>
          </div>
        </div>

      </div>

      <!-- Tab 2: Detailed Size Chart Table -->
      <div v-else class="p-4">
        <div class="table-responsive">
          <table class="table table-bordered table-hover text-center align-middle mb-0 small">
            <thead class="table-light fw-bold text-uppercase" style="font-size: 11px;">
              <tr>
                <th>Size</th>
                <th>Dài Áo</th>
                <th>Rộng Ngực</th>
                <th>Rộng Vai</th>
                <th>Cân Nặng</th>
                <th>Chiều Cao</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in sizeChartData" :key="item.size" :class="{ 'table-primary fw-bold': item.size === recommendation.size }">
                <td><span class="badge bg-urbn-dark px-2 py-1">{{ item.size }}</span></td>
                <td>{{ item.length }}</td>
                <td>{{ item.chest }}</td>
                <td>{{ item.shoulder }}</td>
                <td>{{ item.fitWeight }}</td>
                <td>{{ item.fitHeight }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="text-muted mt-3 mb-0" style="font-size: 11px;">
          * Lưu ý: Thông số đo thực tế có thể chênh lệch 1-2cm do kỹ thuật may và độ co giãn tự nhiên của vải Cotton Compact.
        </p>
      </div>

      <!-- Modal Footer -->
      <div class="px-4 py-3 bg-light border-top d-flex justify-content-between align-items-center">
        <button @click="cartStore.toggleSizeAdvisor(false)" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
          Đóng
        </button>
        <button @click="chonSizeKhuyenNghi" class="btn btn-urbn-primary btn-sm rounded-pill px-4">
          <i class='bx bx-check me-1'></i> Áp Dụng Size {{ recommendation.size }}
        </button>
      </div>

    </div>
  </div>
</template>

<script>
import { cartStore } from '../../stores/cartStore';

export default {
  name: 'SizeAdvisorModal',
  data() {
    return {
      cartStore,
      activeTab: 'advisor',
      height: 175,
      weight: 68,
      fitPreference: 'boxy',
      sizeChartData: [
        { size: 'S', length: '70 cm', chest: '112 cm', shoulder: '53 cm', fitWeight: '48 - 58kg', fitHeight: '1m55 - 1m68' },
        { size: 'M', length: '72 cm', chest: '118 cm', shoulder: '55 cm', fitWeight: '58 - 66kg', fitHeight: '1m65 - 1m74' },
        { size: 'L', length: '74 cm', chest: '124 cm', shoulder: '57 cm', fitWeight: '65 - 75kg', fitHeight: '1m73 - 1m80' },
        { size: 'XL', length: '76 cm', chest: '130 cm', shoulder: '59 cm', fitWeight: '74 - 84kg', fitHeight: '1m78 - 1m86' },
        { size: 'XXL', length: '78 cm', chest: '136 cm', shoulder: '61 cm', fitWeight: '82 - 95kg', fitHeight: '1m82 - 1m92' },
      ]
    }
  },
  computed: {
    recommendation() {
      let score = this.height * 0.4 + this.weight * 0.6;
      if (this.fitPreference === 'fitted') score -= 4;
      if (this.fitPreference === 'oversized') score += 5;

      if (score < 98) {
        return { size: 'S', confidence: 96, reason: 'Phù hợp cho vóc dáng dưới 1m68, cân nặng dưới 58kg để có độ rơi vai chuẩn xác.' };
      } else if (score < 108) {
        return { size: 'M', confidence: 98, reason: 'Tối ưu cho chiều cao 1m65 - 1m74, cân nặng 58 - 66kg với phom đứng tự nhiên.' };
      } else if (score < 118) {
        return { size: 'L', confidence: 99, reason: 'Kích cỡ hoàn hảo cho thể hình 1m73 - 1m80, cân nặng 65 - 75kg, dáng boxy chuẩn streetwear.' };
      } else if (score < 128) {
        return { size: 'XL', confidence: 97, reason: 'Thích hợp cho 1m78 - 1m86, cân nặng 74 - 84kg hoặc người chuộng độ rủ phủ mông.' };
      } else {
        return { size: 'XXL', confidence: 95, reason: 'Thích hợp cho trên 1m82 và cân nặng trên 82kg hoặc người yêu thích phong cách Y2K Baggy.' };
      }
    }
  },
  methods: {
    chonSizeKhuyenNghi() {
      this.cartStore.selectedSizeAdvisorSize = this.recommendation.size;
      this.$toast.success('Đã chọn Size ' + this.recommendation.size + ' theo gợi ý!');
      this.cartStore.toggleSizeAdvisor(false);
    }
  }
}
</script>

<style scoped>
.fixed-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 2000;
  background-color: rgba(10, 13, 18, 0.75);
  backdrop-filter: blur(5px);
  animation: fadeIn 0.2s ease-out;
}
.modal-dialog-content {
  width: 100%;
  max-width: 540px;
  animation: scaleUp 0.2s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes scaleUp {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.hover-white:hover {
  color: #fff !important;
}
</style>
