<template>
  <div class="sofep-atelier-search-page bg-light-subtle min-vh-100 pb-5">

    <!-- 1. Search Header Hero Banner -->
    <div class="search-hero-bar py-4 mb-4 border-bottom shadow-xs">
      <div class="container">

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-2">
          <ol class="breadcrumb mb-0 small d-flex align-items-center gap-1 text-muted">
            <li class="breadcrumb-item">
              <router-link to="/" class="text-decoration-none text-muted hover-gold">Trang Chủ</router-link>
            </li>
            <li class="breadcrumb-item">
              <span class="text-muted">Bộ Sưu Tập</span>
            </li>
            <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">
              {{ breadcrumbLabel }}
            </li>
          </ol>
        </nav>

        <!-- Main Search Title with Count -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <div>
            <h1 class="fs-3 fw-extrabold text-dark text-uppercase font-streetwear mb-1">
              <span class="text-gradient-gold">{{ pageTitle }}</span>
            </h1>
            <p class="text-secondary small mb-0">
              {{ pageSubtitle }}
            </p>
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill px-3 py-2 fw-bold text-dark border bg-white shadow-xs" style="border-color: rgba(180, 131, 37, 0.25) !important;">
              <i class='bx bx-check-double text-warning me-1'></i>
              <span>{{ filteredProducts.length }} Tác Phẩm Phù Hợp</span>
            </span>
          </div>
        </div>

        <!-- Trending Suggestion Chips -->
        <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-2 border-top border-light">
          <span class="text-muted small fw-semibold" style="font-size: 11.5px;">Gợi ý xu hướng:</span>
          <button v-for="tag in trendingTags" :key="tag"
                  @click="timKiemGoiY(tag)"
                  class="btn btn-sm btn-white border rounded-pill px-3 py-0.5 small text-secondary trending-chip hover-gold shadow-xs">
            ✦ {{ tag }}
          </button>
        </div>

      </div>
    </div>

    <!-- 2. Main Search Body: 2-Column Desktop Web Layout -->
    <div class="container mb-4">
      <div class="row g-4">

        <!-- LEFT SIDEBAR: Boutique Filter Panel (col-lg-3 col-xl-3) -->
        <div class="col-lg-3 col-xl-3">
          <div class="card border-0 rounded-4 shadow-sm bg-white p-3 p-xl-4 filter-sidebar sticky-top"
               style="top: 85px; z-index: 10; border: 1px solid rgba(180, 131, 37, 0.14) !important;">

            <!-- Sidebar Header -->
            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
              <h6 class="fw-bold text-dark text-uppercase small mb-0 d-flex align-items-center gap-2 font-streetwear">
                <i class='bx bx-slider-alt fs-5' style="color: #b48325;"></i>
                <span>Bộ Lọc Tuyển Chọn</span>
              </h6>
              <button @click="resetFilters" class="btn btn-link btn-sm text-decoration-none text-muted p-0 small hover-gold" title="Xóa tất cả bộ lọc">
                Đặt lại
              </button>
            </div>

            <!-- Filter: Danh Mục Phong Cách -->
            <div class="filter-section-group">
              <div class="filter-section-title">
                <span>Danh Mục Thiết Kế</span>
                <span class="text-muted" style="font-size: 10px; font-weight: normal; text-transform: none;">({{ danh_mucs.length }} nhóm)</span>
              </div>
              <div v-if="categoryError" class="alert alert-warning small">{{ categoryError }}</div>
              <div class="category-filter-list">
                <a href="javascript:;"
                   @click="chonDanhMuc('tat-ca')"
                   :class="{ 'atelier-cat-active': selectedDanhMuc === 'tat-ca', 'atelier-cat-item': selectedDanhMuc !== 'tat-ca' }"
                   class="atelier-cat-link">
                  <span class="d-flex align-items-center gap-2">
                    <i class='bx bxs-grid-alt fs-6' style="opacity: 0.85;"></i>
                    <span>Tất cả thiết kế</span>
                  </span>
                  <span class="atelier-cat-count">{{ products.length }}</span>
                </a>
                <a v-for="dm in danh_mucs" :key="dm.id"
                   href="javascript:;"
                   @click="chonDanhMuc(dm.id)"
                   :class="{ 'atelier-cat-active': selectedDanhMuc === dm.id, 'atelier-cat-item': selectedDanhMuc !== dm.id }"
                   class="atelier-cat-link">
                  <span class="text-truncate">{{ dm.ten_danh_muc }}</span>
                  <span class="atelier-cat-count">{{ dm.san_phams_count || 0 }}</span>
                </a>
              </div>
            </div>

            <!-- Filter: Đặc Quyền Dịch Vụ -->


            <!-- Filter: Khoảng Giá Thông Minh -->
            <div class="filter-section-group">
              <div class="filter-section-title">
                <span>Khoảng Giá (₫)</span>
              </div>

              <!-- Quick Price Chips -->
              <div class="quick-price-chips mb-3">
                <button type="button" @click="chonGiaNhanh(null, 300000)" class="quick-price-chip-btn">
                  &lt; 300k
                </button>
                <button type="button" @click="chonGiaNhanh(300000, 500000)" class="quick-price-chip-btn">
                  300k - 500k
                </button>
                <button type="button" @click="chonGiaNhanh(500000, 1000000)" class="quick-price-chip-btn">
                  500k - 1tr
                </button>
                <button type="button" @click="chonGiaNhanh(1000000, null)" class="quick-price-chip-btn">
                  &gt; 1tr
                </button>
              </div>

              <!-- Inputs Min / Max -->
              <div class="price-input-row mb-3">
                <input type="number" v-model.number="priceRange.min" class="form-control form-control-sm price-field text-center" placeholder="TỪ (₫)">
                <span class="text-muted fw-bold">-</span>
                <input type="number" v-model.number="priceRange.max" class="form-control form-control-sm price-field text-center" placeholder="ĐẾN (₫)">
              </div>
              <button @click="apDungKhoangGia" class="btn btn-urbn-primary btn-sm w-100 fw-bold rounded-pill shadow-xs py-2">
                Áp Dụng Khoảng Giá
              </button>
            </div>

            <!-- Filter: Khu Vực Xuất Xưởng -->


            <!-- Filter: Đánh Giá Tuyển Chọn -->


          </div>
        </div>

        <!-- RIGHT AREA: Banner, Sort Bar & Editorial Product Grid (col-lg-9 col-xl-9) -->
        <div class="col-lg-9 col-xl-9">

          <!-- 1. SOFEP Flagship Atelier Showcase Card -->


          <!-- 2. Modern Segmented Sort Control Bar -->
          <div class="card border-0 rounded-4 shadow-sm bg-white p-3 mb-3.5" style="border: 1px solid rgba(180, 131, 37, 0.14) !important;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

              <!-- Sort Buttons -->
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="small text-secondary fw-semibold me-1">Sắp xếp theo:</span>

                <button @click="setSort('lien_quan')"
                        :class="sortBy === 'lien_quan' ? 'active-sort-pill' : 'inactive-sort-pill'"
                        class="btn btn-sm px-3 py-1.5 rounded-pill fw-semibold">
                  ✦ Mặc Định
                </button>
                <button @click="setSort('moi_nhat')"
                        :class="sortBy === 'moi_nhat' ? 'active-sort-pill' : 'inactive-sort-pill'"
                        class="btn btn-sm px-3 py-1.5 rounded-pill fw-semibold">
                  Mới Ra Mắt
                </button>
                <button @click="setSort('ban_chay')"
                        :class="sortBy === 'ban_chay' ? 'active-sort-pill' : 'inactive-sort-pill'"
                        class="btn btn-sm px-3 py-1.5 rounded-pill fw-semibold">
                  Lượt Xem Nhất
                </button>

                <!-- Sort by Price dropdown (Vue Reactive State & Click-outside) -->
                <div class="dropdown position-relative" ref="priceDropdownRef">
                  <button class="btn btn-sm px-3 py-1.5 rounded-pill d-flex align-items-center gap-2 fw-semibold cursor-pointer"
                          :class="(sortBy === 'gia_tang' || sortBy === 'gia_giam') ? 'active-sort-pill' : 'inactive-sort-pill'"
                          @click="togglePriceDropdown"
                          type="button">
                    <span class="small fw-semibold">{{ sortPriceLabel }}</span>
                    <i class='bx bx-chevron-down ms-0.5 transition-transform' :class="{ 'rotate-180': isPriceDropdownOpen }"></i>
                  </button>
                  <ul class="dropdown-menu shadow-lg border-0 rounded-4 p-2 custom-price-dropdown"
                      :class="{ 'show': isPriceDropdownOpen }"
                      :style="isPriceDropdownOpen ? 'display: block; position: absolute; left: 0; top: 100%; z-index: 1050; min-width: 210px;' : 'display: none;'">
                    <li>
                      <a class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center justify-content-between small fw-medium"
                         :class="{ 'fw-bold text-dark bg-light': sortBy === 'gia_tang' }"
                         href="javascript:;"
                         @click="setSort('gia_tang')">
                        <span class="d-flex align-items-center gap-2">
                          <i class='bx bx-trending-up' style="color: #b48325;"></i>
                          <span>Giá: Thấp đến Cao</span>
                        </span>
                        <i v-if="sortBy === 'gia_tang'" class='bx bx-check fs-5 fw-bold' style="color: #b48325;"></i>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center justify-content-between small fw-medium"
                         :class="{ 'fw-bold text-dark bg-light': sortBy === 'gia_giam' }"
                         href="javascript:;"
                         @click="setSort('gia_giam')">
                        <span class="d-flex align-items-center gap-2">
                          <i class='bx bx-trending-down' style="color: #b48325;"></i>
                          <span>Giá: Cao đến Thấp</span>
                        </span>
                        <i v-if="sortBy === 'gia_giam'" class='bx bx-check fs-5 fw-bold' style="color: #b48325;"></i>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Keyword & Count Indicator -->
              <div class="small text-muted">
                Tìm thấy <strong style="color: #b48325;">{{ filteredProducts.length }}</strong> tác phẩm
              </div>

            </div>
          </div>

          <!-- 3. Editorial Fashion Products Grid (Khoảng cách thoáng đãng, sang trọng) -->
          <div v-if="loading" class="alert alert-light" role="status">Đang tải sản phẩm…</div>
          <div v-else-if="error" class="alert alert-warning" role="alert">{{ error }} <button class="btn btn-sm btn-outline-dark" @click="taiSanPham">Thử lại</button></div>
          <div v-else-if="filteredProducts.length > 0" class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-3 editorial-grid-row">
            <div v-for="item in filteredProducts" :key="item.id" class="col">
              <div @click="chuyenToiChiTiet(item)"
                   class="card border-0 shadow-sm bg-white h-100 overflow-hidden cursor-pointer editorial-product-card hover-lift position-relative">

                <!-- Product Image Frame (Clean, Generous Studio Stage) -->
                <div class="position-relative overflow-hidden atelier-img-box" style="padding-top: 110%;">
                  <ProductImage :src="item.hinh_anh"
                       class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover editorial-img"
                       :alt="item.ten_san_pham" />

                  <!-- Floating Top-Left Luxury Tag -->
                  <div class="position-absolute top-0 start-0 m-2.5 z-2">
                    <span class="badge rounded-pill px-2.5 py-1 text-white fw-bold small shadow-xs" style="background: var(--sofep-gold-gradient); font-size: 10px;">
                      ✦ ATELIER
                    </span>
                  </div>

                  <!-- Floating Top-Right Wishlist Heart -->
                  <button class="position-absolute top-0 end-0 m-2.5 btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs z-2 card-wishlist-btn"
                          @click.stop="$toast ? $toast.success('Đã lưu vào danh sách yêu thích!') : null">
                    <i class='bx bx-heart text-secondary fs-6'></i>
                  </button>

                  <!-- Hover Action Bar: Slide up on hover (Desktop) -->
                  <div class="editorial-hover-bar position-absolute bottom-0 start-0 end-0 p-2.5 d-flex gap-2 z-2">
                    <button @click.stop="themNhanhVaoGio(item, $event)" class="btn btn-sm btn-white flex-grow-1 rounded-pill fw-bold text-dark shadow-sm d-flex align-items-center justify-content-center gap-1" style="font-size: 11.5px;">
                      <i class='bx bx-cart-add fs-6' style="color: #b48325;"></i>
                      <span>Chọn biến thể</span>
                    </button>
                    <button class="btn btn-sm btn-dark rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                      <i class='bx bx-right-arrow-alt fs-5 text-white'></i>
                    </button>
                  </div>
                </div>

                <!-- Product Info Body -->
                <div class="p-3 d-flex flex-column flex-grow-1">

                  <!-- Brand Sub-tag -->
                  <span class="text-uppercase tracking-wider small fw-bold mb-1" style="font-size: 10px; color: #b48325;">
                    SOFEP ATELIER · 2026
                  </span>

                  <!-- Title -->
                  <h6 class="product-title-clamp small fw-bold text-dark mb-2 lh-sm font-streetwear" :title="item.ten_san_pham">
                    {{ item.ten_san_pham }}
                  </h6>

                  <!-- Price & Rating Row -->
                  <div class="d-flex align-items-baseline gap-2 flex-wrap mt-auto">
                    <span class="fs-6 fw-extrabold font-streetwear" style="color: #b48325;">
                      ₫{{ formatMoney(getEffectivePrice(item)) }}
                    </span>
                    <span v-if="item.gia_khuyen_mai" class="text-muted text-decoration-line-through small" style="font-size: 11px;">
                      ₫{{ formatMoney(item.gia_ban) }}
                    </span>
                  </div>

                  <!-- Ratings & Sold Count -->


                </div>

              </div>
            </div>
          </div>

          <!-- Empty State (No Products Found) -->
          <div v-else class="card border-0 rounded-4 shadow-sm bg-white p-5 text-center my-4" style="border: 1px solid rgba(180, 131, 37, 0.14) !important;">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-xs"
                 style="width: 72px; height: 72px; background: rgba(180, 131, 37, 0.12); color: #b48325;">
              <i class='bx bx-search-alt fs-1'></i>
            </div>
            <h5 class="fw-bold text-dark mb-1 font-streetwear">Không Tìm Thấy Tác Phẩm Phù Hợp</h5>
            <p class="text-muted small mb-3">
              Không có sản phẩm nào khớp với từ khóa "{{ keyword }}" trong bộ lọc hiện tại.
            </p>
            <div>
              <button @click="resetFilters" class="btn btn-urbn-primary btn-sm rounded-pill px-4 fw-bold">
                Xóa Bộ Lọc &amp; Xem Tất Cả
              </button>
            </div>
          </div>

        </div>

      </div>
    </div>

  </div>
</template>

<script>
import { cartStore } from '../../stores/cartStore';
import axios from 'axios';
import ProductImage from '../shared/ProductImage.vue';
import { basePrice } from '../../services/productCatalog';

export default {
  components: { ProductImage },
  name: 'TimKiem',
  data() {
    return {
      cartStore,
      keyword: '',
      products: [],
      danh_mucs: [],
      selectedDanhMuc: 'tat-ca',
      isFlashSaleFilter: false,
      isNewArrivalFilter: false,
      loading: false,
      error: '',
      categoryError: '',
      requestId: 0,
      sortBy: 'lien_quan',
      sortPriceLabel: 'Giá: Thấp đến Cao',
      isPriceDropdownOpen: false,
      priceRange: {
        min: null,
        max: null
      },
      starFilter: 0,
      activeFilters: {
        hoa_toc: false,
        mall: false,
        yeu_thich: false
      },
      trendingTags: [
        'Áo thun boxy',
        'Polo dệt kim',
        'Túi xách da',
        'Blazer relaxed',
        'Quần parachute',
        'Bộ sưu tập Xuân Hè'
      ],
    };
  },
  computed: {
    currentCategoryObj() {
      if (!this.selectedDanhMuc || this.selectedDanhMuc === 'tat-ca') return null;
      return this.danh_mucs.find(d => d.id == this.selectedDanhMuc || d.slug_danh_muc == this.selectedDanhMuc);
    },
    pageTitle() {
      if (this.isFlashSaleFilter) return 'Giờ Vàng Sản phẩm ưu đãi';
      if (this.isNewArrivalFilter) return 'Bộ Sưu Tập Mới Drop 04';
      if (this.currentCategoryObj) return 'Danh Mục: ' + this.currentCategoryObj.ten_danh_muc;
      if (this.$route.query.ten) return 'Danh Mục: ' + this.$route.query.ten;
      if (this.keyword && this.keyword.trim()) return 'Kết Quả Tuyển Chọn: "' + this.keyword + '"';
      return 'Tất Cả Tác Phẩm Thời Trang';
    },
    pageSubtitle() {
      if (this.isFlashSaleFilter) return 'Các sản phẩm có ưu đãi tại SOFEP.';
      if (this.isNewArrivalFilter) return 'Các sáng tạo thời trang đương đại hoàn thiện trên chất liệu cao cấp mới nhất mùa này.';
      if (this.currentCategoryObj) return `Khám phá các thiết kế ${this.currentCategoryObj.ten_danh_muc.toLowerCase()} cao cấp tại SOFEP.`;
      if (this.keyword && this.keyword.trim()) return `Danh sách các sản phẩm phù hợp với từ khóa "${this.keyword}" tại SOFEP Studio.`;
      return 'Khám phá toàn bộ bộ sưu tập thời trang đương đại từ SOFEP.';
    },
    breadcrumbLabel() {
      if (this.isFlashSaleFilter) return 'Sản phẩm ưu đãi';
      if (this.isNewArrivalFilter) return 'Sản Phẩm Nổi Bật';
      if (this.currentCategoryObj) return this.currentCategoryObj.ten_danh_muc;
      if (this.$route.query.ten) return this.$route.query.ten;
      if (this.keyword && this.keyword.trim()) return `"${this.keyword}"`;
      return 'Tất Cả Tác Phẩm';
    },
    filteredProducts() {
      let list = [...this.products];

      // Lọc Flash Sale
      if (this.isFlashSaleFilter) {
        list = list.filter(p => p.is_flash_sale == 1 || (p.gia_khuyen_mai && Number(p.gia_khuyen_mai) > 0));
      }

      // Lọc Sản Phẩm Mới Drop 04
      if (this.isNewArrivalFilter) {
        list = list.filter(p => p.is_noi_bat == 1);
      }

      // Lọc theo Danh mục
      if (this.selectedDanhMuc && this.selectedDanhMuc !== 'tat-ca') {
        list = list.filter(p => {
          return p.id_danh_muc == this.selectedDanhMuc ||
                 (p.danh_muc && (p.danh_muc.id == this.selectedDanhMuc || p.danh_muc.slug_danh_muc == this.selectedDanhMuc));
        });
      }

      // Lọc theo từ khóa tìm kiếm (nếu có)
      if (this.keyword && this.keyword.trim()) {
        const kw = this.keyword.trim().toLowerCase();
        list = list.filter(p => p.ten_san_pham && p.ten_san_pham.toLowerCase().includes(kw));
      }

      // Lọc theo khoảng giá
      if (this.priceRange.min) {
        list = list.filter(p => this.getEffectivePrice(p) >= this.priceRange.min);
      }
      if (this.priceRange.max) {
        list = list.filter(p => this.getEffectivePrice(p) <= this.priceRange.max);
      }

      // Lọc đặc quyền dịch vụ
      if (this.activeFilters.mall) {
        list = list.filter(p => p.is_noi_bat);
      }
      if (this.activeFilters.yeu_thich) {
        list = list.filter(p => (p.luot_xem || 0) >= 100 || p.is_noi_bat);
      }

      return this.sapXepDanhSach(list);
    }
  },
  watch: {
    '$route': {
      immediate: true,
      deep: true,
      handler(route) {
        const query = route.query || {};
        const params = route.params || {};
        this.keyword = query.q || '';

        const catParam = params.id_or_slug || query.danh_muc;
        if (catParam) {
          this.selectedDanhMuc = isNaN(catParam) ? catParam : Number(catParam);
        } else {
          this.selectedDanhMuc = 'tat-ca';
        }

        this.isFlashSaleFilter = query.flash_sale === '1' || route.path === '/flash-sale';
        this.isNewArrivalFilter = query.moi_nhat === '1';

        this.taiSanPham();
      }
    }
  },
  mounted() {
    this.taiDanhMucs();
    document.addEventListener('click', this.handlePriceClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handlePriceClickOutside);
  },
  methods: {
    getEffectivePrice(p) { return basePrice(p); },
    togglePriceDropdown(e) {
      if (e) e.stopPropagation();
      this.isPriceDropdownOpen = !this.isPriceDropdownOpen;
      // Nếu chưa chọn sắp xếp theo giá thì tự động kích hoạt Thấp đến Cao và mở menu
      if (this.sortBy !== 'gia_tang' && this.sortBy !== 'gia_giam') {
        this.setSort('gia_tang');
        this.isPriceDropdownOpen = true;
      }
    },
    handlePriceClickOutside(e) {
      if (this.$refs.priceDropdownRef && !this.$refs.priceDropdownRef.contains(e.target)) {
        this.isPriceDropdownOpen = false;
      }
    },
    taiDanhMucs() {
      axios.get('http://127.0.0.1:8000/api/client/danh-mucs')
        .then(res => {
          if (res.data.status) {
            this.danh_mucs = res.data.data;
          }
        })
        .catch(() => { this.categoryError = 'Không thể tải danh mục.'; });
    },
    async taiSanPham() {
      const request=++this.requestId;
      this.loading=true;this.error='';this.products=[];
      try {
        const response=await axios.get('http://127.0.0.1:8000/api/client/san-phams?all=1');
        if(request!==this.requestId)return;
        if(!response.data.status||!Array.isArray(response.data.data))throw new Error('Invalid response');
        this.products=response.data.data;
      }catch(error){if(request===this.requestId)this.error='Không thể tải sản phẩm. Vui lòng thử lại.';}
      finally{if(request===this.requestId)this.loading=false;}
    },
    thucHienTimKiem() {
      this.$router.push({ path: '/tim-kiem', query: { q: this.keyword } }).catch(() => {});
    },
    timKiemGoiY(tag) {
      this.keyword = tag;
      this.thucHienTimKiem();
    },
    setSort(sortType) {
      this.sortBy = sortType;
      if (sortType === 'gia_tang') this.sortPriceLabel = 'Giá: Thấp đến Cao';
      if (sortType === 'gia_giam') this.sortPriceLabel = 'Giá: Cao đến Thấp';
      this.isPriceDropdownOpen = false;
    },
    sapXepDanhSach(list) {
      const arr = [...list];
      if (this.sortBy === 'gia_tang') {
        return arr.sort((a, b) => this.getEffectivePrice(a) - this.getEffectivePrice(b));
      }
      if (this.sortBy === 'gia_giam') {
        return arr.sort((a, b) => this.getEffectivePrice(b) - this.getEffectivePrice(a));
      }
      if (this.sortBy === 'ban_chay') {
        return arr.sort((a, b) => (b.luot_xem || 0) - (a.luot_xem || 0));
      }
      if (this.sortBy === 'moi_nhat') {
        return arr.sort((a, b) => (b.id || 0) - (a.id || 0));
      }
      return arr;
    },
    chonDanhMuc(dmId) {
      this.selectedDanhMuc = dmId;
      const query = { ...this.$route.query };
      if (dmId === 'tat-ca') {
        delete query.danh_muc;
        delete query.ten;
        delete query.slug;
      } else {
        query.danh_muc = dmId;
        const cat = this.danh_mucs.find(d => d.id == dmId);
        if (cat) {
          query.ten = cat.ten_danh_muc;
          query.slug = cat.slug_danh_muc;
        }
      }
      this.$router.push({ path: '/tim-kiem', query }).catch(() => {});
    },
    chonGiaNhanh(min, max) {
      this.priceRange.min = min;
      this.priceRange.max = max;
      if (this.$toast) {
        this.$toast.info('Đã chọn mức giá nhanh!');
      }
    },
    apDungKhoangGia() {
      if (this.$toast) {
        this.$toast.info('Đã áp dụng khoảng giá đã chọn!');
      }
    },
    resetFilters() {
      this.selectedDanhMuc = 'tat-ca';
      this.priceRange.min = null;
      this.priceRange.max = null;
      this.starFilter = 0;
      this.activeFilters = { hoa_toc: false, mall: false, yeu_thich: false };
      this.sortBy = 'lien_quan';
      this.isFlashSaleFilter = false;
      this.isNewArrivalFilter = false;
      this.keyword = '';
      this.$router.push({ path: '/tim-kiem' }).catch(() => {});
    },
    chuyenToiChiTiet(item) {
      this.$router.push(`/san-pham/${item.slug_san_pham || item.id}`);
    },
    themNhanhVaoGio(item,event){event?.stopPropagation();this.chuyenToiChiTiet(item);},
    formatMoney(val) {
      if (!val) return '0';
      return Number(val).toLocaleString('vi-VN');
    },
    tinhPhanTramGiam(giaBan, giaKM) {
      if (!giaBan || !giaKM || Number(giaBan) <= Number(giaKM)) return 0;
      return Math.round(((giaBan - giaKM) / giaBan) * 100);
    },

  }
};
</script>

<style scoped>
.sofep-atelier-search-page {
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.search-hero-bar {
  background: linear-gradient(135deg, #fdfbf7 0%, #fef8ee 50%, #fef3c7 100%);
  border-color: rgba(180, 131, 37, 0.15) !important;
}

.text-gradient-gold {
  background: linear-gradient(135deg, #b45309 0%, #d97706 45%, #b48325 75%, #f59e0b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.trending-chip {
  background-color: #ffffff;
  transition: all 0.2s ease;
}

.trending-chip:hover {
  background-color: #fef8ee;
  border-color: #b48325 !important;
  color: #b48325 !important;
}

/* Filter Sidebar Structure & Spacing - Tách biệt thoáng đãng, sang trọng */
.filter-section-group {
  margin-bottom: 1.5rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid rgba(226, 232, 240, 0.85);
}

.filter-section-title {
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: #1e293b;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* Category Filter List - Khoảng cách tách biệt rõ ràng giữa các danh mục */
.category-filter-list {
  display: flex;
  flex-direction: column;
  gap: 8px; /* Tách biệt rõ ràng, không dính sát */
}

.atelier-cat-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.22s cubic-bezier(0.2, 0.8, 0.2, 1);
  border: 1px solid transparent;
}

.atelier-cat-item {
  color: #475569;
  background-color: #fafbfc;
  border-color: #f1f5f9;
}

.atelier-cat-item:hover {
  background-color: #fef8ee;
  color: #b48325;
  border-color: rgba(180, 131, 37, 0.28);
  transform: translateX(4px);
}

.atelier-cat-active {
  background: linear-gradient(135deg, #fffdf9 0%, #fef3c7 100%);
  color: #78350f !important;
  font-weight: 700;
  border-color: rgba(180, 131, 37, 0.38) !important;
  box-shadow: 0 3px 10px rgba(180, 131, 37, 0.12);
}

.atelier-cat-count {
  font-size: 10.5px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 8px;
  background-color: #ffffff;
  color: #64748b;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.atelier-cat-active .atelier-cat-count {
  background-color: #ffffff;
  color: #b45309;
  border-color: rgba(180, 131, 37, 0.35);
  font-weight: 700;
}

/* Filter Checkbox List */
.filter-checkbox-list {
  display: flex;
  flex-direction: column;
  gap: 7px; /* Tách rời các dòng checkbox */
}

.filter-check-item {
  display: flex;
  align-items: center;
  padding: 6px 8px;
  border-radius: 8px;
  cursor: pointer;
  margin-bottom: 0;
  transition: background-color 0.15s ease;
}

.filter-check-item:hover {
  background-color: #f8fafc;
}

.filter-custom-checkbox {
  cursor: pointer;
  width: 15px;
  height: 15px;
  accent-color: #b48325;
}

.filter-check-label {
  font-size: 12.5px;
  font-weight: 500;
  user-select: none;
  cursor: pointer;
}

/* Quick Price Chips */
.quick-price-chips {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 7px;
}

.quick-price-chip-btn {
  padding: 6px 8px;
  font-size: 11px;
  font-weight: 600;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background-color: #ffffff;
  color: #475569;
  text-align: center;
  transition: all 0.2s ease;
  cursor: pointer;
}

.quick-price-chip-btn:hover {
  background-color: #fef8ee;
  border-color: #b48325;
  color: #b48325;
  transform: translateY(-1px);
}

.price-input-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.price-field {
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  font-size: 12px;
  padding: 6px 8px;
}

.price-field:focus {
  border-color: #b48325;
  box-shadow: 0 0 0 3px rgba(180, 131, 37, 0.12);
}

/* Rating filter items */
.filter-rating-group {
  margin-bottom: 0.5rem;
}

.rating-filter-list {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.rating-filter-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 8px;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.rating-filter-item:hover {
  background-color: #fef8ee;
  transform: translateX(3px);
}

.voucher-mini-atelier {
  border: 1.5px dashed rgba(180, 131, 37, 0.4);
  background-color: #fefdfb;
}

.active-sort-pill {
  background: var(--sofep-gold-gradient);
  color: #ffffff !important;
  border: none;
  box-shadow: 0 4px 12px rgba(180, 131, 37, 0.25);
}

.inactive-sort-pill {
  background-color: #f8fafc;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.inactive-sort-pill:hover {
  background-color: #fef8ee;
  color: #b48325;
  border-color: #b48325;
}

.custom-price-dropdown {
  border: 1px solid rgba(180, 131, 37, 0.2) !important;
  background-color: #ffffff;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08) !important;
}

.custom-price-dropdown .dropdown-item {
  transition: all 0.2s ease;
}

.custom-price-dropdown .dropdown-item:hover {
  background-color: #fef8ee;
  color: #b48325;
}

/* Editorial Product Grid - Khoảng cách tách biệt rộng rãi, sang trọng */
.editorial-grid-row {
  --bs-gutter-x: 1.5rem; /* 24px khoảng cách ngang */
  --bs-gutter-y: 2.25rem; /* 36px khoảng cách dọc giữa các hàng */
}

@media (min-width: 992px) {
  .editorial-grid-row {
    --bs-gutter-x: 1.75rem; /* 28px khoảng cách ngang trên desktop */
    --bs-gutter-y: 2.5rem;  /* 40px khoảng cách dọc giữa các hàng trên desktop */
  }
}

.editorial-product-card {
  border: 1px solid rgba(180, 131, 37, 0.14) !important;
  border-radius: 20px !important;
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04) !important;
  transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
  background-color: #ffffff;
}

.editorial-product-card:hover {
  border-color: rgba(180, 131, 37, 0.4) !important;
  box-shadow: 0 18px 40px -6px rgba(180, 131, 37, 0.18) !important;
  transform: translateY(-6px);
}

.atelier-img-box {
  background: radial-gradient(circle at 50% 50%, #ffffff 0%, #fbfaf8 100%);
}

.editorial-img {
  transition: transform 0.4s ease;
}

.editorial-product-card:hover .editorial-img {
  transform: scale(1.05);
}

.card-wishlist-btn {
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(0, 0, 0, 0.06);
  transition: all 0.2s ease;
}

.card-wishlist-btn:hover {
  transform: scale(1.1);
  color: #dc2626 !important;
}

.editorial-hover-bar {
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.25s ease;
  background: linear-gradient(to top, rgba(0,0,0,0.3) 0%, transparent 100%);
}

.editorial-product-card:hover .editorial-hover-bar {
  opacity: 1;
  transform: translateY(0);
}

.product-title-clamp {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 36px;
}

.hover-gold:hover {
  color: #b48325 !important;
}

.hover-lift {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.hover-lift:hover {
  transform: translateY(-4px);
}

.scroll-no-bar::-webkit-scrollbar {
  display: none;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
