<template>
  <div class="urbn-home-container">

    <!-- 1. HERO BANNER SECTION (CYBER METROPOLIS 2026) -->
    <section class="hero-banner-section position-relative bg-urbn-dark text-white overflow-hidden">
      <!-- Background Image with Dark Vignette Gradient -->
      <div class="position-absolute top-0 start-0 w-100 h-100 z-0">
        <img src="https://images.unsplash.com/photo-1509967419530-da38b4704bc6?auto=format&fit=crop&w=1800&q=80"
             class="w-100 h-100 object-fit-cover filter-hero"
             alt="URBN WEAR Hero">
        <div class="hero-gradient-overlay position-absolute top-0 start-0 w-100 h-100"></div>
      </div>

      <div class="container position-relative z-1 py-5 py-lg-6">
        <div class="row align-items-center min-vh-75 py-4">
          <div class="col-lg-8 col-xl-7">
            
            <!-- Capsule Tag -->
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-20 text-white small fw-bold mb-3 backdrop-blur">
              <i class='bx bxs-zap text-urbn-primary fs-5'></i>
              <span>BỘ SƯU TẬP XUÂN HÈ 2026 • DROP 04</span>
            </div>

            <!-- Big Bold Streetwear Title -->
            <h1 class="display-3 fw-extrabold text-uppercase tracking-tighter lh-1 mb-3 font-streetwear hero-main-title">
              CYBER <br>
              <span class="text-gradient-electric">METROPOLIS</span>
            </h1>

            <!-- Subtitle -->
            <p class="lead text-light opacity-80 mb-4 max-w-550 fw-normal" style="font-size: 1.05rem; line-height: 1.6;">
              Cấu trúc vải Heavyweight 320–460GSM kết hợp đường may raw-edge và phom dáng boxy drop-shoulder chuẩn tỉ lệ hình học đô thị hiện đại.
            </p>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap align-items-center gap-3 pt-2">
              <a href="#products-section" class="btn btn-urbn-primary btn-lg shadow-lg">
                <span>Khám Phá Drop Mới</span>
                <i class='bx bx-right-arrow-alt fs-4'></i>
              </a>

              <button @click="cartStore.toggleSizeAdvisor(true)" class="btn btn-link text-white-50 text-decoration-none small hover-white p-2">
                <i class='bx bx-ruler text-urbn-primary me-1'></i> Bảng Chọn Size
              </button>
            </div>

            <!-- Key Metric Highlights -->
            <div class="row g-3 pt-5 mt-2 border-top border-white border-opacity-15 text-white-50 small">
              <div class="col-4">
                <div class="fs-4 fw-extrabold text-white font-monospace">320+ GSM</div>
                <div class="small opacity-75">Cotton Compact 2 mặt</div>
              </div>
              <div class="col-4">
                <div class="fs-4 fw-extrabold text-white font-monospace">30 NGÀY</div>
                <div class="small opacity-75">Đổi trả tận nhà miễn phí</div>
              </div>
              <div class="col-4">
                <div class="fs-4 fw-extrabold text-white font-monospace">4.9 / 5.0</div>
                <div class="small opacity-75">+12.000 khách hài lòng</div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>

    <!-- 2. QUICK CATEGORY CHIPS BAR (5 Contemporary Streetwear Cards) -->
    <section class="quick-categories py-5 bg-white border-bottom">
      <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-4">
          <div>
            <span class="text-urbn-primary fw-bold text-uppercase small tracking-wide">PHÂN LOẠI TRANG PHỤC</span>
            <h2 class="fw-extrabold text-dark text-uppercase font-streetwear mb-0">Danh Mục Nổi Bật</h2>
          </div>
          <a href="#products-section" class="btn btn-outline-dark btn-sm rounded-pill px-3">
            Xem Tất Cả ({{ san_phams.length }}) <i class='bx bx-right-arrow-alt'></i>
          </a>
        </div>

        <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-5">
          <div v-for="cat in quickCategories" :key="cat.id" class="col">
            <div @click="locTheoTenDanhMuc(cat.name)"
                 class="category-street-card rounded-4 overflow-hidden position-relative cursor-pointer group-cat shadow-sm border">
              <div class="cat-img-box" style="padding-top: 100%;">
                <img :src="cat.img" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover cat-zoom" :alt="cat.label">
              </div>
              <div class="cat-gradient-overlay position-absolute bottom-0 start-0 end-0 p-3 text-white">
                <h6 class="fw-extrabold mb-0 text-uppercase small font-streetwear">{{ cat.label }}</h6>
                <span class="small text-white-50" style="font-size: 11px;">{{ cat.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. FLASH SALE COUNTDOWN SECTION (Giờ vàng giá sốc) -->
    <section id="flash-sale-section" class="flash-sale-section py-5 bg-light">
      <div class="container">
        
        <!-- Radiant Banner Card -->
        <div class="flash-banner-card p-4 p-md-5 rounded-4 text-white shadow-lg mb-5"
             style="background: linear-gradient(135deg, #dc2626 0%, #ea580c 50%, #f59e0b 100%);">
          <div class="row align-items-center gy-4">
            <div class="col-lg-7">
              <div class="d-inline-flex align-items-center gap-2 bg-black bg-opacity-25 px-3 py-1 rounded-pill small fw-bold text-uppercase tracking-wider mb-2">
                <i class='bx bxs-flame text-warning fs-5'></i>
                <span>GIỜ VÀNG GIÁ SỐC • FLASH SALE ĐỘC QUYỀN</span>
              </div>
              <h2 class="display-6 fw-extrabold text-uppercase font-streetwear mb-1">
                GIẢM ĐẾN 30% BỘ SƯU TẬP METROPOLIS
              </h2>
              <p class="mb-0 text-white-50 small">
                Số lượng giới hạn chỉ 50 chiếc mỗi size/màu. Miễn phí đổi trả trong 30 ngày tại nhà.
              </p>
            </div>

            <!-- Digital Countdown Timer -->
            <div class="col-lg-5 text-lg-end">
              <div class="d-inline-flex align-items-center gap-2 bg-black bg-opacity-40 backdrop-blur px-4 py-2.5 rounded-4 border border-white border-opacity-20 shadow">
                <i class='bx bx-time-five fs-4 text-warning'></i>
                <div class="text-center px-1">
                  <span class="fs-4 fw-bolder font-monospace d-block lh-1">{{ timer.hours }}</span>
                  <span class="text-white-50 font-monospace" style="font-size: 10px;">GIỜ</span>
                </div>
                <span class="fs-4 fw-bold">:</span>
                <div class="text-center px-1">
                  <span class="fs-4 fw-bolder font-monospace d-block lh-1">{{ timer.minutes }}</span>
                  <span class="text-white-50 font-monospace" style="font-size: 10px;">PHÚT</span>
                </div>
                <span class="fs-4 fw-bold">:</span>
                <div class="text-center px-1">
                  <span class="fs-4 fw-bolder font-monospace text-warning d-block lh-1">{{ timer.seconds }}</span>
                  <span class="text-white-50 font-monospace" style="font-size: 10px;">GIÂY</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Flash Sale Product Grid -->
        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4">
          <div v-for="sp in flash_sale_products" :key="'fs_' + sp.id" class="col">
            <div class="card-urbn h-100 d-flex flex-column position-relative">
              
              <!-- Sale Badge -->
              <span class="badge-urbn-sale position-absolute top-0 start-0 m-3 z-2">
                -{{ tinhPhanTramGiam(sp.gia_ban, sp.gia_khuyen_mai) }}%
              </span>

              <!-- Aspect Ratio 4:5 Image Box -->
              <div class="position-relative overflow-hidden" style="padding-top: 120%;">
                <img :src="sp.hinh_anh || 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500'"
                     class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover img-hover-zoom"
                     :alt="sp.ten_san_pham">
                <!-- Action Hover Buttons -->
                <div class="product-hover-overlay position-absolute bottom-0 start-0 end-0 p-2 d-flex justify-content-center gap-2">
                  <button @click="moQuickView(sp)" class="btn btn-light btn-sm rounded-circle shadow" title="Xem nhanh">
                    <i class='bx bx-show fs-5'></i>
                  </button>
                  <button @click="themNhanhVaoGio(sp)" class="btn btn-urbn-dark btn-sm rounded-pill px-3 shadow" title="Thêm vào giỏ">
                    <i class='bx bx-shopping-bag me-1'></i> Thêm Giỏ
                  </button>
                </div>
              </div>

              <!-- Product Info -->
              <div class="p-3 d-flex flex-column flex-grow-1">
                <span class="text-muted small text-uppercase mb-1" style="font-size: 11px;">
                  {{ sp.danh_muc ? sp.danh_muc.ten_danh_muc : 'Streetwear Drop' }}
                </span>
                <h6 class="fw-bold text-dark text-truncate mb-2" :title="sp.ten_san_pham">
                  {{ sp.ten_san_pham }}
                </h6>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                  <span class="fw-extrabold text-urbn-primary fs-5">{{ formatMoney(sp.gia_khuyen_mai || sp.gia_ban) }}₫</span>
                  <span v-if="sp.gia_khuyen_mai" class="text-muted text-decoration-line-through small">{{ formatMoney(sp.gia_ban) }}₫</span>
                </div>
                <button @click="themNhanhVaoGio(sp)" class="btn btn-dark w-100 btn-sm rounded-pill fw-bold mt-3">
                  <i class='bx bx-cart-add me-1'></i> Thêm Vào Giỏ
                </button>
              </div>

            </div>
          </div>
        </div>

      </div>
    </section>


    <!-- 5. MAIN PRODUCT CATALOG SECTION (Filter Tabs & Grid) -->
    <section id="products-section" class="products-catalog-section py-5">
      <div class="container">
        
        <!-- Section Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
          <div>
            <span class="text-urbn-primary fw-bold text-uppercase small tracking-wide">BỘ SƯU TẬP THỜI TRANG ĐÔ THỊ</span>
            <h2 class="fw-extrabold text-dark text-uppercase font-streetwear mb-0">Sản Phẩm Mới Drop 04</h2>
          </div>
          
          <!-- Category Filter Tabs -->
          <div class="d-flex flex-wrap gap-2">
            <button @click="tabActive = 'tat-ca'"
                    :class="{ 'btn-dark': tabActive === 'tat-ca', 'btn-light border': tabActive !== 'tat-ca' }"
                    class="btn btn-sm rounded-pill px-3 fw-bold">
              Tất Cả
            </button>
            <button @click="tabActive = 'hot'"
                    :class="{ 'btn-dark': tabActive === 'hot', 'btn-light border': tabActive !== 'hot' }"
                    class="btn btn-sm rounded-pill px-3 fw-bold">
              <i class='bx bxs-hot text-danger me-1'></i> Bán Chạy Nhất
            </button>
            <button v-for="dm in danh_mucs" :key="dm.id"
                    @click="tabActive = dm.id"
                    :class="{ 'btn-dark': tabActive === dm.id, 'btn-light border': tabActive !== dm.id }"
                    class="btn btn-sm rounded-pill px-3 fw-semibold">
              {{ dm.ten_danh_muc }}
            </button>
          </div>
        </div>

        <!-- Search Alert Notification if filtering by keyword -->
        <div v-if="searchKeyword" class="alert alert-light border rounded-4 p-3 mb-4 d-flex justify-content-between align-items-center">
          <div>
            Kết quả tìm kiếm cho từ khóa: <strong>"{{ searchKeyword }}"</strong> ({{ filteredProducts.length }} sản phẩm)
          </div>
          <button @click="searchKeyword = ''; $router.push('/')" class="btn btn-sm btn-outline-secondary rounded-pill">
            Xóa tìm kiếm
          </button>
        </div>

        <!-- Product Cards Grid (Aspect Ratio 4:5 Streetwear Cards) -->
        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4">
          <div v-for="sp in filteredProducts" :key="sp.id" class="col">
            <div class="card-urbn h-100 d-flex flex-column position-relative group-product">
              
              <!-- Badges -->
              <div class="position-absolute top-0 start-0 m-3 z-2 d-flex flex-column gap-1">
                <span v-if="sp.gia_khuyen_mai" class="badge-urbn-sale">
                  -{{ tinhPhanTramGiam(sp.gia_ban, sp.gia_khuyen_mai) }}%
                </span>
                <span v-if="sp.is_noi_bat" class="badge-urbn-hot">
                  HOT DROP
                </span>
              </div>

              <!-- Product Image Box -->
              <div class="position-relative overflow-hidden" style="padding-top: 125%;">
                <img :src="sp.hinh_anh || 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500'"
                     class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover img-hover-zoom"
                     :alt="sp.ten_san_pham">
                
                <!-- Hover Overlay Action Buttons -->
                <div class="product-hover-overlay position-absolute bottom-0 start-0 end-0 p-2 d-flex justify-content-center gap-2">
                  <button @click="moQuickView(sp)" class="btn btn-light btn-sm rounded-circle shadow" title="Xem nhanh">
                    <i class='bx bx-show fs-5'></i>
                  </button>
                  <button @click="themNhanhVaoGio(sp)" class="btn btn-urbn-dark btn-sm rounded-pill px-3 shadow" title="Thêm vào giỏ">
                    <i class='bx bx-shopping-bag me-1'></i> Thêm Giỏ
                  </button>
                </div>
              </div>

              <!-- Product Information -->
              <div class="p-3 d-flex flex-column flex-grow-1">
                <span class="text-muted small text-uppercase mb-1" style="font-size: 11px;">
                  {{ sp.danh_muc ? sp.danh_muc.ten_danh_muc : 'Áo Thun & Streetwear' }}
                </span>
                <h6 class="fw-bold text-dark text-truncate mb-2" :title="sp.ten_san_pham">
                  {{ sp.ten_san_pham }}
                </h6>
                
                <!-- Pricing -->
                <div class="d-flex align-items-baseline gap-2 mb-2">
                  <span class="fw-extrabold text-urbn-primary fs-5">{{ formatMoney(sp.gia_khuyen_mai || sp.gia_ban) }}₫</span>
                  <span v-if="sp.gia_khuyen_mai" class="text-muted text-decoration-line-through small">{{ formatMoney(sp.gia_ban) }}₫</span>
                </div>

                <!-- Sizes & Color swatches preview -->
                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                  <div class="d-flex gap-1">
                    <span v-for="c in ['#111315', '#4b5563', '#002fda']" :key="c"
                          class="rounded-circle border"
                          :style="{ backgroundColor: c, width: '10px', height: '10px' }"></span>
                  </div>
                  <span class="text-muted small" style="font-size: 11px;">Size: S, M, L, XL</span>
                </div>

                <!-- Action Button -->
                <button @click="themNhanhVaoGio(sp)" class="btn btn-dark w-100 btn-sm rounded-pill fw-bold mt-2">
                  <i class='bx bx-bag-plus me-1'></i> Thêm Vào Giỏ
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- Empty State if no product found -->
        <div v-if="filteredProducts.length === 0" class="text-center py-5">
          <i class='bx bx-search-alt text-muted' style="font-size: 3rem;"></i>
          <h5 class="fw-bold mt-3">Không tìm thấy sản phẩm phù hợp</h5>
          <p class="text-secondary small">Vui lòng thử chọn danh mục khác hoặc kiểm tra lại từ khóa tìm kiếm.</p>
          <button @click="tabActive = 'tat-ca'; searchKeyword = ''" class="btn btn-urbn-primary btn-sm rounded-pill px-4">
            Xem Tất Cả Sản Phẩm
          </button>
        </div>

      </div>
    </section>

    <!-- 6. STREETWEAR HIGHLIGHTS & BRAND VALUES -->
    <section class="py-5 bg-light border-top border-bottom">
      <div class="container">
        <div class="row g-4">
          <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 border h-100 d-flex align-items-center gap-3 shadow-sm">
              <div class="rounded-circle bg-urbn-primary bg-opacity-10 text-urbn-primary p-3 d-flex align-items-center justify-content-center">
                <i class='bx bx-badge-check fs-2'></i>
              </div>
              <div>
                <h6 class="fw-bold mb-1 small text-uppercase font-streetwear">Cotton 320–460GSM</h6>
                <p class="text-muted small mb-0" style="font-size: 11px;">Chải kỹ 2 mặt đứng phom không xù</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 border h-100 d-flex align-items-center gap-3 shadow-sm">
              <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 d-flex align-items-center justify-content-center">
                <i class='bx bx-refresh fs-2'></i>
              </div>
              <div>
                <h6 class="fw-bold mb-1 small text-uppercase font-streetwear">Đổi Trả 30 Ngày</h6>
                <p class="text-muted small mb-0" style="font-size: 11px;">Đổi tận nhà hoàn toàn miễn phí</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 border h-100 d-flex align-items-center gap-3 shadow-sm">
              <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center">
                <i class='bx bx-package fs-2'></i>
              </div>
              <div>
                <h6 class="fw-bold mb-1 small text-uppercase font-streetwear">Giao Hàng Siêu Tốc</h6>
                <p class="text-muted small mb-0" style="font-size: 11px;">Nội thành 2h, toàn quốc 2-3 ngày</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="p-4 bg-white rounded-4 border h-100 d-flex align-items-center gap-3 shadow-sm">
              <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center">
                <i class='bx bx-ruler fs-2'></i>
              </div>
              <div>
                <h6 class="fw-bold mb-1 small text-uppercase font-streetwear">Tư Vấn Size Chuẩn</h6>
                <p class="text-muted small mb-0" style="font-size: 11px;">Hệ thống tính size theo vóc dáng</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 7. COMMUNITY REVIEWS SECTION -->
    <section id="reviews-section" class="py-5 bg-white">
      <div class="container">
        <div class="text-center max-w-600 mx-auto mb-5">
          <span class="badge bg-urbn-primary bg-opacity-10 text-urbn-primary rounded-pill px-3 py-1 small fw-bold mb-2">URBN COMMUNITY</span>
          <h2 class="fw-extrabold text-dark text-uppercase font-streetwear">Cộng Đồng Đánh Giá</h2>
          <p class="text-secondary small">Hơn 12.000 khách hàng đã trải nghiệm chất lượng vải và phom dáng đương đại của chúng tôi.</p>
        </div>

        <div class="row g-4">
          <div v-for="(rv, idx) in customerReviews" :key="idx" class="col-md-4">
            <div class="p-4 rounded-4 bg-light border h-100 d-flex flex-column shadow-sm">
              <div class="d-flex align-items-center gap-1 text-warning mb-2">
                <i v-for="s in 5" :key="s" class='bx bxs-star'></i>
              </div>
              <p class="text-dark small fst-italic flex-grow-1 mb-3">
                "{{ rv.comment }}"
              </p>
              <div class="d-flex align-items-center gap-3 pt-3 border-top">
                <img :src="rv.avatar" class="rounded-circle border" style="width: 44px; height: 44px; object-fit: cover;" :alt="rv.name">
                <div>
                  <h6 class="fw-bold text-dark mb-0 small">{{ rv.name }}</h6>
                  <span class="text-muted" style="font-size: 11px;">
                    <i class='bx bxs-badge-check text-urbn-primary me-1'></i>{{ rv.meta }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 8. BRAND PARTNERS TICKER -->
    <section class="py-4 border-top bg-light">
      <div class="container text-center">
        <span class="text-muted small fw-bold text-uppercase tracking-wider d-block mb-3" style="font-size: 11px;">
          ĐỒNG HÀNH VÀ PHÁT TRIỂN CÙNG CÁC THƯƠNG HIỆU QUỐC TẾ
        </span>
        <div class="d-flex flex-wrap justify-content-center align-items-center gap-4 gap-md-5 opacity-75">
          <span class="fw-extrabold fs-4 text-dark font-streetwear">URBN STUDIO</span>
          <span class="fw-extrabold fs-4 text-dark font-streetwear">Y-3</span>
          <span class="fw-extrabold fs-4 text-dark font-streetwear">ACW*</span>
          <span class="fw-extrabold fs-4 text-dark font-streetwear">MISBHV</span>
          <span class="fw-extrabold fs-4 text-dark font-streetwear">BALENCIAGA</span>
        </div>
      </div>
    </section>

    <!-- 9. QUICK VIEW PRODUCT MODAL -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-2xl overflow-hidden" v-if="selectedProduct">
          <div class="modal-header border-0 pb-0">
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4 pt-0">
            <div class="row g-4 align-items-center">
              <div class="col-md-6 text-center">
                <img :src="selectedProduct.hinh_anh" class="img-fluid rounded-4 shadow-sm" style="max-height: 420px; object-fit: cover;" :alt="selectedProduct.ten_san_pham">
              </div>
              <div class="col-md-6">
                <span class="badge bg-urbn-primary bg-opacity-10 text-urbn-primary rounded-pill px-3 py-1 small fw-bold mb-2">
                  {{ selectedProduct.danh_muc ? selectedProduct.danh_muc.ten_danh_muc : 'Streetwear Drop' }}
                </span>
                <h4 class="fw-extrabold text-dark mb-2 font-streetwear">{{ selectedProduct.ten_san_pham }}</h4>
                <div class="d-flex align-items-baseline gap-2 mb-3">
                  <span class="text-urbn-primary fw-extrabold fs-4">{{ formatMoney(selectedProduct.gia_khuyen_mai || selectedProduct.gia_ban) }}₫</span>
                  <span v-if="selectedProduct.gia_khuyen_mai" class="text-muted text-decoration-line-through small">{{ formatMoney(selectedProduct.gia_ban) }}₫</span>
                </div>
                <p class="text-secondary small mb-3">
                  {{ selectedProduct.mo_ta_ngan || 'Sản phẩm hoàn thiện trên chất liệu Cotton Compact 320GSM siêu bền phom, thiết kế Boxy Drop-Shoulder tỉ lệ chuẩn đô thị đương đại.' }}
                </p>

                <!-- Color Options -->
                <div class="mb-3">
                  <label class="form-label small fw-bold text-dark mb-1">Màu Sắc:</label>
                  <div class="d-flex gap-2">
                    <button v-for="ms in mau_sacs" :key="ms.id" @click="selectedColor = ms"
                      :class="{ 'border-primary ring-2': selectedColor && selectedColor.id === ms.id }"
                      class="btn btn-sm btn-light border rounded-pill px-3 small d-flex align-items-center gap-1">
                      <span class="rounded-circle border" :style="{ backgroundColor: ms.ma_mau, width: '12px', height: '12px' }"></span>
                      {{ ms.ten_mau }}
                    </button>
                  </div>
                </div>

                <!-- Size Options -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">Chọn Kích Thước (Size):</label>
                    <button @click="cartStore.toggleSizeAdvisor(true)" class="btn btn-link p-0 text-urbn-primary small text-decoration-none" style="font-size: 11px;">
                      <i class='bx bx-ruler me-1'></i>Hướng dẫn size
                    </button>
                  </div>
                  <div class="d-flex gap-2">
                    <button v-for="kt in kich_thuocs" :key="kt.id" @click="selectedSize = kt"
                      :class="{ 'btn-dark text-white': selectedSize && selectedSize.id === kt.id, 'btn-light text-dark': !selectedSize || selectedSize.id !== kt.id }"
                      class="btn btn-sm rounded-pill px-3 fw-bold">
                      {{ kt.ten_kich_thuoc }}
                    </button>
                  </div>
                </div>

                <!-- Quantity & Add to Cart -->
                <div class="d-flex align-items-center gap-3 pt-2">
                  <div class="btn-group border rounded-pill overflow-hidden bg-light" role="group">
                    <button @click="quantityModal = Math.max(1, quantityModal - 1)" class="btn btn-light px-3 py-1 border-0">-</button>
                    <span class="px-3 py-1 small fw-bold d-flex align-items-center bg-white">{{ quantityModal }}</span>
                    <button @click="quantityModal++" class="btn btn-light px-3 py-1 border-0">+</button>
                  </div>
                  <button @click="themTuModal" class="btn btn-urbn-primary rounded-pill px-4 fw-bold text-white flex-grow-1 shadow-sm" data-bs-dismiss="modal">
                    <i class='bx bx-cart-add me-1'></i> Thêm Vào Giỏ
                  </button>
                </div>

              </div>
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

export default {
  name: 'ClientTrangChu',
  data() {
    return {
      cartStore,
      danh_mucs: [],
      san_phams: [],
      mau_sacs: [],
      kich_thuocs: [],
      tabActive: 'tat-ca',
      searchKeyword: '',
      selectedProduct: null,
      selectedColor: null,
      selectedSize: null,
      quantityModal: 1,

      // Countdown Timer for Flash Sale
      timer: {
        hours: '04',
        minutes: '28',
        seconds: '45'
      },
      timerInterval: null,

      // Quick Categories Showcase
      quickCategories: [
        { id: 't-shirts', name: 'Áo Thun', label: 'Áo Thun Boxy', count: '48 mẫu', img: 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=400&q=80' },
        { id: 'hoodies', name: 'Hoodie', label: 'Hoodie & Sweater', count: '32 mẫu', img: 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?auto=format&fit=crop&w=400&q=80' },
        { id: 'pants', name: 'Quần', label: 'Quần Cargo & Dù', count: '26 mẫu', img: 'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?auto=format&fit=crop&w=400&q=80' },
        { id: 'jackets', name: 'Áo Khoác', label: 'Áo Khoác Bomber', count: '14 mẫu', img: 'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=400&q=80' },
        { id: 'accessories', name: 'Phụ Kiện', label: 'Phụ Kiện & Túi', count: '18 mẫu', img: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=400&q=80' }
      ],

      // Customer Reviews
      customerReviews: [
        {
          name: 'Hoàng Long Vũ',
          avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200',
          meta: '1m76 • 68kg (Đã mua Size L)',
          comment: 'Chất vải thun 320GSM siêu dày nhưng mặc rất thoáng mát, cổ áo dệt gân chắc chắn không hề bị nhão sau khi giặt máy. Dáng boxy rũ vai chuẩn streetwear!'
        },
        {
          name: 'Trần Minh Quang',
          avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200',
          meta: '1m80 • 74kg (Đã mua Size XL)',
          comment: 'Màu xanh electric cobalt ở ngoài rất nổi bật. Form áo đứng vải, bảng tính size gợi ý chuẩn xác 100%. Đóng gói hộp rất chỉn chu và thơm.'
        },
        {
          name: 'Đặng Thảo My',
          avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200',
          meta: '1m62 • 50kg (Đã mua Size M Oversized)',
          comment: 'Hoodie 460GSM form cực dày dặn, mặc che khuyết điểm siêu đỉnh. Khóa kéo 2 chiều trơn tru, đi xe máy chống gió bụi xuất sắc!'
        }
      ]
    }
  },
  computed: {
    flash_sale_products() {
      const sale = this.san_phams.filter(p => p.gia_khuyen_mai || p.is_flash_sale);
      return sale.length > 0 ? sale.slice(0, 4) : this.san_phams.slice(0, 4);
    },
    filteredProducts() {
      let list = this.san_phams;
      if (this.searchKeyword) {
        list = list.filter(p => p.ten_san_pham && p.ten_san_pham.toLowerCase().includes(this.searchKeyword.toLowerCase()));
      }
      if (this.tabActive === 'tat-ca') return list;
      if (this.tabActive === 'hot') return list.filter(p => p.is_noi_bat);
      return list.filter(p => p.id_danh_muc == this.tabActive);
    }
  },
  watch: {
    '$route.query.danh_muc': {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.tabActive = newVal === 'tat-ca' ? 'tat-ca' : (Number(newVal) || newVal);
          this.$nextTick(() => {
            const el = document.getElementById('products-section');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
          });
        }
      }
    },
    '$route.query.q': {
      immediate: true,
      handler(newVal) {
        if (newVal && newVal.trim()) {
          this.searchKeyword = newVal.trim();
          this.$nextTick(() => {
            const el = document.getElementById('products-section');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
          });
        } else {
          this.searchKeyword = '';
        }
      }
    }
  },
  mounted() {
    this.loadData();
    this.startCountdown();
  },
  beforeUnmount() {
    if (this.timerInterval) clearInterval(this.timerInterval);
  },
  methods: {
    formatMoney(val) {
      if (!val) return '0';
      return Number(val).toLocaleString('vi-VN');
    },
    tinhPhanTramGiam(goc, km) {
      if (!km || km >= goc) return 20;
      return Math.round(((goc - km) / goc) * 100);
    },
    startCountdown() {
      let totalSeconds = 4 * 3600 + 28 * 60 + 45;
      this.timerInterval = setInterval(() => {
        if (totalSeconds <= 0) totalSeconds = 8 * 3600;
        totalSeconds--;
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;
        this.timer.hours = String(h).padStart(2, '0');
        this.timer.minutes = String(m).padStart(2, '0');
        this.timer.seconds = String(s).padStart(2, '0');
      }, 1000);
    },
    loadData() {
      axios.get('http://127.0.0.1:8000/api/admin/danh-muc/get-data')
        .then(res => { if (res.data.status) this.danh_mucs = res.data.data; });

      axios.get('http://127.0.0.1:8000/api/admin/san-pham/get-data')
        .then(res => { if (res.data.status) this.san_phams = res.data.data; });

      axios.get('http://127.0.0.1:8000/api/admin/mau-sac/get-data')
        .then(res => {
          if (res.data.status) {
            this.mau_sacs = res.data.data;
            if (this.mau_sacs.length > 0) this.selectedColor = this.mau_sacs[0];
          }
        });

      axios.get('http://127.0.0.1:8000/api/admin/kich-thuoc/get-data')
        .then(res => {
          if (res.data.status) {
            this.kich_thuocs = res.data.data;
            if (this.kich_thuocs.length > 0) this.selectedSize = this.kich_thuocs[1] || this.kich_thuocs[0];
          }
        });
    },
    locTheoTenDanhMuc(catName) {
      const match = this.danh_mucs.find(d => d.ten_danh_muc && d.ten_danh_muc.toLowerCase().includes(catName.toLowerCase()));
      if (match) {
        this.tabActive = match.id;
      } else {
        this.searchKeyword = catName;
      }
      const el = document.getElementById('products-section');
      if (el) el.scrollIntoView({ behavior: 'smooth' });
    },
    themNhanhVaoGio(product) {
      this.cartStore.addItem(product, 1);
      this.$toast.success('Đã thêm "' + product.ten_san_pham + '" vào giỏ hàng!');
    },
    moQuickView(product) {
      this.selectedProduct = product;
      this.quantityModal = 1;
      const modalEl = document.getElementById('quickViewModal');
      if (modalEl && window.bootstrap) {
        const modal = new window.bootstrap.Modal(modalEl);
        modal.show();
      }
    },
    themTuModal() {
      if (this.selectedProduct) {
        this.cartStore.addItem(this.selectedProduct, this.quantityModal, this.selectedColor, this.selectedSize);
        this.$toast.success('Đã thêm ' + this.quantityModal + ' sản phẩm vào giỏ hàng!');
      }
    }
  }
}
</script>

<style scoped>
.hero-banner-section {
  min-height: 80vh;
}
.filter-hero {
  filter: brightness(0.65) contrast(1.15);
}
.hero-gradient-overlay {
  background: linear-gradient(90deg, rgba(10, 13, 18, 0.95) 0%, rgba(10, 13, 18, 0.75) 50%, rgba(10, 13, 18, 0.3) 100%);
}
.backdrop-blur {
  backdrop-filter: blur(8px);
}
.text-gradient-electric {
  background: linear-gradient(90deg, #ffffff 0%, #cbd5e1 40%, #002fda 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.max-w-550 {
  max-width: 550px;
}
.max-w-600 {
  max-width: 600px;
}
.cat-img-box {
  background-color: #f1f3f5;
  transition: transform 0.4s ease;
}
.cat-zoom {
  transition: transform 0.4s ease;
}
.group-cat:hover .cat-zoom {
  transform: scale(1.08);
}
.cat-gradient-overlay {
  background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
}
.img-hover-zoom {
  transition: transform 0.4s ease;
}
.group-product:hover .img-hover-zoom {
  transform: scale(1.06);
}
.product-hover-overlay {
  background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, transparent 100%);
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.25s ease;
}
.group-product:hover .product-hover-overlay {
  opacity: 1;
  transform: translateY(0);
}
.hover-white:hover {
  color: #ffffff !important;
}
</style>
