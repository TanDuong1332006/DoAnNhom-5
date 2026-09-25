<template>
  <div class="sofep-atelier-detail-page bg-light-subtle pb-5">
    
    <!-- 1. Atelier Breadcrumb Navigation -->
    <div class="container py-3">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small d-flex align-items-center gap-1 text-muted">
          <li class="breadcrumb-item">
            <router-link to="/" class="text-decoration-none text-muted hover-gold">Trang Chủ</router-link>
          </li>
          <li class="breadcrumb-item">
            <a href="javascript:;" class="text-decoration-none text-muted hover-gold">
              {{ product?.danh_muc?.ten_danh_muc || 'Thời Trang Atelier' }}
            </a>
          </li>
          <li class="breadcrumb-item active text-dark text-truncate fw-semibold" style="max-width: 450px;" aria-current="page">
            {{ product?.ten_san_pham || 'Chi Tiết Sản Phẩm' }}
          </li>
        </ol>
      </nav>
    </div>

    <!-- 2. Main Product Showcase Section (Bespoke 2-Column Studio Layout) -->
    <div class="container mb-4">
      <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden p-3 p-lg-4" style="border: 1px solid rgba(180, 131, 37, 0.14) !important;">
        <div class="row g-4 g-xl-5">

          <!-- LEFT COLUMN: Atelier Gallery Stage (col-12 col-md-5 col-lg-5) -->
          <div class="col-12 col-md-5 col-lg-5">
            
            <!-- Main Featured Image Frame -->
            <div class="position-relative bg-white rounded-4 border overflow-hidden atelier-gallery-frame d-flex align-items-center justify-content-center">
              <img :src="activeImage" :alt="product?.ten_san_pham" class="img-fluid w-100 h-100 object-fit-contain zoom-image">
              
              <!-- Floating Top-Left Luxury Badge -->
              <div class="position-absolute top-0 start-0 m-3 z-2">
                <span class="badge atelier-glass-badge rounded-pill px-3 py-1.5 small fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs">
                  <i class='bx bxs-crown fs-6' style="color: #b48325;"></i>
                  <span>SOFEP ATELIER · 2026</span>
                </span>
              </div>

              <!-- Floating Top-Right Wishlist Button -->
              <button @click="toggleYeuThich"
                      class="position-absolute top-0 end-0 m-3 btn atelier-round-glass-btn d-flex align-items-center justify-content-center shadow-xs z-2"
                      :title="isYeuThich ? 'Đã lưu yêu thích' : 'Lưu vào danh sách mong muốn'">
                <i class='bx fs-5' :class="isYeuThich ? 'bxs-heart text-danger' : 'bx-heart text-secondary'"></i>
              </button>

              <!-- Floating Lookbook Video Button Pill -->
              <div v-if="hasVideo"
                   @click="toggleVideoModal"
                   class="position-absolute bottom-0 start-50 translate-middle-x mb-3 atelier-video-pill rounded-pill px-3.5 py-1.5 shadow-sm d-flex align-items-center gap-2 cursor-pointer z-2">
                <div class="pulse-play-icon d-flex align-items-center justify-content-center rounded-circle text-white">
                  <i class='bx bx-play fs-5 ms-0.5'></i>
                </div>
                <span class="small fw-bold text-dark font-streetwear" style="font-size: 11.5px;">Video Lookbook HD</span>
              </div>

              <!-- Pagination Indicator 1/5 -->
              <div class="position-absolute end-0 bottom-0 m-3 bg-dark bg-opacity-70 text-white rounded-pill px-2.5 py-0.5 small fw-bold z-2" style="font-size: 11px;">
                {{ activeImageIndex + 1 }} / {{ galleryImages.length }}
              </div>
            </div>

            <!-- Thumbnails Horizontal Row -->
            <div class="d-flex gap-2.5 overflow-x-auto thumbnail-scroll-strip mt-3 py-1">
              <div v-for="(img, idx) in galleryImages" :key="idx"
                   @click="setActiveImage(idx)"
                   @mouseenter="setActiveImage(idx)"
                   :class="{ 'active-atelier-thumb': activeImageIndex === idx }"
                   class="atelier-thumb-item rounded-3 border overflow-hidden flex-shrink-0 cursor-pointer"
                   style="width: 72px; height: 72px;">
                <img :src="img" class="w-100 h-100 object-fit-cover" :alt="'Ảnh ' + (idx + 1)">
              </div>
            </div>

            <!-- Social Share & Wishlist Row -->
            <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top small text-muted">
              <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">Chia sẻ tác phẩm:</span>
                <a href="javascript:;" class="text-secondary fs-5 hover-gold" title="Facebook"><i class='bx bxl-facebook-circle'></i></a>
                <a href="javascript:;" class="text-secondary fs-5 hover-gold" title="Pinterest"><i class='bx bxl-pinterest'></i></a>
                <a href="javascript:;" class="text-secondary fs-5 hover-gold" title="Sao chép liên kết" @click="chiaSeSanPham"><i class='bx bx-link-alt'></i></a>
              </div>
              <div class="d-flex align-items-center gap-1.5 cursor-pointer text-muted" @click="toggleYeuThich">
                <i class='bx bxs-heart text-danger' v-if="isYeuThich"></i>
                <i class='bx bx-heart' v-else></i>
                <span :class="{ 'text-danger fw-bold': isYeuThich }">{{ soLuongDaBan + (isYeuThich ? 1 : 0) }} người thích</span>
              </div>
            </div>

          </div>

          <!-- RIGHT COLUMN: Bespoke Product Studio (col-12 col-md-7 col-lg-7) -->
          <div class="col-12 col-md-7 col-lg-7 d-flex flex-column">

            <!-- Category Capsule Tag -->
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
              <span class="badge rounded-pill px-3 py-1.5 text-uppercase fw-bold small" style="background-color: rgba(180, 131, 37, 0.12); color: #b48325;">
                ✦ BỘ SƯU TẬP CAO CẤP • SOFEP STUDIO
              </span>
              <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small fw-semibold">
                {{ product?.danh_muc?.ten_danh_muc || 'Thời Trang Đương Đại' }}
              </span>
            </div>

            <!-- Product Title -->
            <h1 class="fs-4 fw-extrabold text-dark lh-sm mb-2 font-streetwear">
              {{ product?.ten_san_pham }}
            </h1>

            <!-- Ratings, Sold Count, Guarantee Tag -->
            <div class="d-flex align-items-center gap-3 py-2 small border-bottom mb-3 flex-wrap">
              <div class="d-flex align-items-center gap-1">
                <span class="fw-extrabold fs-6" style="color: #b48325;">4.9</span>
                <span class="text-warning"><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star-half'></i></span>
              </div>
              <div class="vr"></div>
              <div class="d-flex align-items-center gap-1">
                <span class="fw-bold text-dark">{{ reviews.length * 37 }}</span>
                <span class="text-muted">Đánh Giá</span>
              </div>
              <div class="vr"></div>
              <div class="d-flex align-items-center gap-1">
                <span class="fw-bold text-dark">{{ soLuongDaBan }}</span>
                <span class="text-muted">Đã Sở Hữu</span>
              </div>
              <div class="ms-auto d-flex align-items-center gap-1 text-success fw-semibold small">
                <i class='bx bxs-badge-check fs-5 text-success'></i>
                <span>Bảo Hành 12 Tháng</span>
              </div>
            </div>

            <!-- Prestige Golden Hour Drop Timer Bar -->
            <div class="prestige-timer-bar p-3 rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
              <div class="d-flex align-items-center gap-2 small">
                <div class="p-1.5 rounded-circle d-flex align-items-center justify-content-center text-white" style="background: var(--sofep-gold-gradient); width: 32px; height: 32px;">
                  <i class='bx bxs-zap fs-6'></i>
                </div>
                <div>
                  <span class="fw-extrabold text-uppercase font-streetwear d-block" style="color: #78350f; font-size: 12px; letter-spacing: 0.5px;">
                    ƯU ĐÃI ĐẶC QUYỀN TRONG NGÀY
                  </span>
                  <span class="text-muted small" style="font-size: 11px;">Tiết kiệm ngay ₫{{ formatMoney(tinhTienGiam) }} khi đặt hôm nay</span>
                </div>
              </div>
              <div class="d-flex align-items-center gap-1 small fw-bold">
                <span class="text-secondary small me-1" style="font-size: 11px;">KẾT THÚC SAU:</span>
                <span class="countdown-digit-cube">{{ countdownHours }}</span>
                <span class="text-muted">:</span>
                <span class="countdown-digit-cube">{{ countdownMinutes }}</span>
                <span class="text-muted">:</span>
                <span class="countdown-digit-cube">{{ countdownSeconds }}</span>
              </div>
            </div>

            <!-- Price & Installment Container Box -->
            <div class="p-3.5 rounded-3 mb-3 border bg-white shadow-xs" style="background: linear-gradient(135deg, #ffffff 0%, #fdfbf7 100%); border-color: rgba(180, 131, 37, 0.2) !important;">
              <div class="d-flex align-items-baseline gap-3 flex-wrap">
                <!-- Strikethrough price -->
                <span v-if="product?.gia_ban && Number(product.gia_ban) > Number(giaHienTai)"
                      class="text-muted text-decoration-line-through fs-6">
                  ₫{{ formatMoney(product.gia_ban) }}
                </span>
                <!-- Main Bold Price -->
                <span class="fs-2 fw-extrabold font-streetwear" style="color: #b48325;">
                  ₫{{ formatMoney(giaHienTai) }}
                </span>
                <!-- Luxury Discount Badge -->
                <span class="badge rounded-pill px-2.5 py-1 small fw-bold text-white shadow-xs" style="background: var(--sofep-gold-gradient);">
                  -{{ tinhPhanTramGiam(product?.gia_ban, giaHienTai) }}% ATELIER SALE
                </span>
              </div>

              <!-- SPayLater Installment info -->
              <div class="small text-secondary mt-2.5 pt-2 border-top d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="background-color: rgba(180, 131, 37, 0.15); color: #78350f; font-size: 10px;">
                  SPayLater 0%
                </span>
                <span>Chỉ từ <strong>₫{{ formatMoney(Math.round(giaHienTai / 3)) }}</strong> x 3 kỳ không lãi suất qua thẻ hoặc ví trả sau</span>
              </div>
            </div>

            <!-- Exclusive Voucher Vault -->
            <div class="d-flex align-items-center gap-3 py-2 small mb-2">
              <span class="text-secondary fw-semibold" style="width: 105px; flex-shrink: 0;">Mã Đặc Quyền:</span>
              <div class="d-flex flex-wrap gap-2">
                <button @click="luuVoucher('SOFEP15K')" class="btn btn-sm atelier-voucher-ticket py-1 px-2.5 d-flex align-items-center gap-1.5" title="Bấm để lưu mã">
                  <i class='bx bx-purchase-tag' style="color: #b48325;"></i>
                  <span class="fw-bold">GIẢM ₫15K</span>
                  <span class="badge bg-white text-dark rounded-pill px-1.5 py-0.5 ms-0.5" style="font-size: 9px;">Lưu</span>
                </button>
                <button @click="luuVoucher('SOFEP50K')" class="btn btn-sm atelier-voucher-ticket py-1 px-2.5 d-flex align-items-center gap-1.5" title="Bấm để lưu mã">
                  <i class='bx bx-purchase-tag' style="color: #b48325;"></i>
                  <span class="fw-bold">GIẢM ₫50K</span>
                  <span class="badge bg-white text-dark rounded-pill px-1.5 py-0.5 ms-0.5" style="font-size: 9px;">Lưu</span>
                </button>
                <button @click="luuVoucher('SOFEP120K')" class="btn btn-sm atelier-voucher-ticket py-1 px-2.5 d-flex align-items-center gap-1.5" title="Bấm để lưu mã">
                  <i class='bx bx-purchase-tag' style="color: #b48325;"></i>
                  <span class="fw-bold">GIẢM ₫120K</span>
                  <span class="badge bg-white text-dark rounded-pill px-1.5 py-0.5 ms-0.5" style="font-size: 9px;">Lưu</span>
                </button>
              </div>
            </div>

            <!-- Shipping Information Row -->
            <div class="d-flex align-items-start gap-3 py-2 small mb-2">
              <span class="text-secondary fw-semibold" style="width: 105px; flex-shrink: 0;">Vận Chuyển:</span>
              <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class='bx bxs-truck text-success fs-5'></i>
                  <span>Miễn phí giao hàng toàn quốc cho đơn từ <strong>500.000₫</strong></span>
                </div>
                <div class="text-muted" style="font-size: 12px;">
                  Giao đến: <strong class="text-dark">Hà Nội / TP. Hồ Chí Minh</strong> (Dự kiến giao: {{ ngayGiaoDuKien }})
                </div>
              </div>
            </div>

            <!-- Color Options Selection -->
            <div class="d-flex align-items-center gap-3 py-2 small mb-2">
              <span class="text-secondary fw-semibold" style="width: 105px; flex-shrink: 0;">Màu Sắc:</span>
              <div class="d-flex flex-wrap gap-2">
                <button v-for="c in colorOptions" :key="c.name"
                        @click="selectedColor = c.name"
                        :class="{ 'atelier-variant-active': selectedColor === c.name }"
                        class="btn btn-sm px-3 py-1.5 d-flex align-items-center gap-2 atelier-variant-btn position-relative rounded-pill">
                  <span class="rounded-circle border" :style="{ backgroundColor: c.hex, width: '14px', height: '14px' }"></span>
                  <span class="fw-semibold">{{ c.name }}</span>
                  <i v-if="selectedColor === c.name" class='bx bx-check position-absolute top-0 end-0' style="color: #b48325; font-size: 13px; transform: translate(25%, -25%);"></i>
                </button>
              </div>
            </div>

            <!-- Size Options Selection -->
            <div class="d-flex align-items-center gap-3 py-2 small mb-2">
              <span class="text-secondary fw-semibold" style="width: 105px; flex-shrink: 0;">Kích Cỡ:</span>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <button v-for="s in sizeOptions" :key="s"
                        @click="selectedSize = s"
                        :class="{ 'atelier-size-active': selectedSize === s }"
                        class="btn btn-sm px-3.5 py-1.5 fw-bold atelier-size-btn rounded-pill position-relative">
                  {{ s }}
                  <i v-if="selectedSize === s" class='bx bx-check position-absolute top-0 end-0' style="color: #b48325; font-size: 13px; transform: translate(25%, -25%);"></i>
                </button>
                <button @click="cartStore.toggleSizeAdvisor(true)" class="btn btn-link btn-sm text-decoration-none p-0 ms-2 fw-semibold" style="color: #b48325;">
                  <i class='bx bx-ruler me-0.5'></i> Bảng hướng dẫn chọn size
                </button>
              </div>
            </div>

            <!-- Quantity Selector -->
            <div class="d-flex align-items-center gap-3 py-2 small mb-4">
              <span class="text-secondary fw-semibold" style="width: 105px; flex-shrink: 0;">Số Lượng:</span>
              <div class="d-flex align-items-center gap-3">
                <div class="input-group input-group-sm quantity-group rounded-pill overflow-hidden border" style="width: 125px;">
                  <button @click="giamSoLuong" class="btn btn-light border-0 px-3 fw-bold" type="button">-</button>
                  <input type="number" v-model.number="quantity" min="1" :max="product?.so_luong_ton || 99" class="form-control text-center border-0 fw-bold shadow-none">
                  <button @click="tangSoLuong" class="btn btn-light border-0 px-3 fw-bold" type="button">+</button>
                </div>
                <span class="text-muted small">{{ product?.so_luong_ton || 120 }} tác phẩm sẵn có</span>
              </div>
            </div>

            <!-- MAIN ACTION BUTTONS -->
            <div class="d-flex flex-wrap align-items-center gap-3 mt-auto pt-3 border-top">
              
              <!-- Nút 1: Thêm Vào Giỏ Hàng -->
              <button @click="themVaoGioHang(false)"
                      class="btn btn-urbn-outline btn-lg px-4 py-2.5 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2 flex-grow-1 flex-sm-grow-0">
                <i class='bx bx-cart-add fs-4'></i>
                <span>Thêm Vào Giỏ Hàng</span>
              </button>

              <!-- Nút 2: Mua Ngay -->
              <button @click="themVaoGioHang(true)"
                      class="btn btn-urbn-primary btn-lg px-5 py-2.5 rounded-pill fw-bold text-white shadow-sm flex-grow-1">
                <span class="d-block text-uppercase font-streetwear">Đặt Mua Ngay</span>
                <span class="small opacity-90" style="font-size: 12px;">Giao tận nhà · Chỉ ₫{{ formatMoney(giaHienTai) }}</span>
              </button>

            </div>

            <!-- SOFEP Haute Guarantees -->
            <div class="d-flex align-items-center justify-content-between pt-3 mt-3 border-top small text-secondary">
              <div class="d-flex align-items-center gap-1.5">
                <i class='bx bx-check-shield fs-5' style="color: #b48325;"></i>
                <span class="fw-semibold">SOFEP Cam Kết</span>
              </div>
              <div class="d-flex align-items-center gap-1">
                <i class='bx bx-refresh text-success fs-5'></i>
                <span>Đổi trả tận nhà 30 ngày</span>
              </div>
              <div class="d-flex align-items-center gap-1">
                <i class='bx bx-gift fs-5' style="color: #b48325;"></i>
                <span>Hộp quà &amp; Thiệp cao cấp</span>
              </div>
            </div>

          </div>

        </div>
      </div>
    </div>

    <!-- 3. Official Atelier Store Showcase Strip -->
    <div class="container mb-4">
      <div class="card border-0 rounded-4 shadow-sm bg-white p-3 p-lg-4" style="border: 1px solid rgba(180, 131, 37, 0.12) !important;">
        <div class="row align-items-center gy-3">
          
          <div class="col-12 col-md-5 col-lg-4 border-md-end">
            <div class="d-flex align-items-center gap-3">
              <div class="position-relative">
                <div class="rounded-circle border p-1 bg-white shadow-xs d-flex align-items-center justify-content-center overflow-hidden" style="width: 72px; height: 72px;">
                  <SofepLogo circle size="md" variant="light" />
                </div>
                <span class="badge position-absolute bottom-0 start-50 translate-middle-x rounded-pill px-2 py-0.5 fw-bold text-white" style="font-size: 9px; background: var(--sofep-gold-gradient);">Atelier</span>
              </div>
              <div>
                <h6 class="fw-bold text-dark mb-0 font-streetwear">{{ product?.thuong_hieu?.ten_thuong_hieu || 'SOFEP Flagship Atelier' }}</h6>
                <div class="small text-muted mt-0.5">Trực tuyến phục vụ 24/7</div>
                <div class="d-flex gap-2 mt-2">
                  <a href="tel:19006868" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1 fw-semibold small">
                    <i class='bx bx-message-rounded-dots me-1'></i> Tư Vấn Trực Tiếp
                  </a>
                  <router-link to="/" class="btn btn-light border btn-sm rounded-pill px-3 py-1 fw-semibold small text-dark">
                    <i class='bx bx-store me-1'></i> Xem Lookbook
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-7 col-lg-8">
            <div class="row g-3 text-center text-sm-start">
              <div class="col-6 col-sm-4">
                <div class="small text-secondary">Đánh Giá Hài Lòng</div>
                <div class="fw-bold fs-6" style="color: #b48325;">4.9 / 5.0 (99%)</div>
              </div>
              <div class="col-6 col-sm-4">
                <div class="small text-secondary">Thời Gian Phản Hồi</div>
                <div class="fw-bold text-dark fs-6">Dưới 5 phút</div>
              </div>
              <div class="col-6 col-sm-4">
                <div class="small text-secondary">Chất Liệu Tuyển Chọn</div>
                <div class="fw-bold text-dark fs-6">100% Supima / Da Thật</div>
              </div>
              <div class="col-6 col-sm-4">
                <div class="small text-secondary">Sản Phẩm Lưu Hành</div>
                <div class="fw-bold text-dark fs-6">148 Tác phẩm</div>
              </div>
              <div class="col-6 col-sm-4">
                <div class="small text-secondary">Chính Sách Đổi Trả</div>
                <div class="fw-bold text-success fs-6">30 ngày tận nhà</div>
              </div>
              <div class="col-6 col-sm-4">
                <div class="small text-secondary">Thành Viên VIP</div>
                <div class="fw-bold text-dark fs-6">199,5k khách hàng</div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- 4. Specifications & Description Full Width Desktop Card (THÔNG SỐ & MÔ TẢ) -->
    <div class="container mb-4">
      <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden p-4" style="border: 1px solid rgba(180, 131, 37, 0.12) !important;">
        
        <!-- Header -->
        <h4 class="fw-bold text-dark fs-5 text-uppercase font-streetwear pb-3 border-bottom d-flex align-items-center gap-2">
          <i class='bx bx-detail' style="color: #b48325;"></i>
          <span>Chi Tiết &amp; Thông Số Kỹ Thuật Tác Phẩm</span>
        </h4>

        <!-- Specifications Table Section -->
        <div class="my-3">
          <h6 class="fw-bold small text-uppercase tracking-wider mb-3" style="color: #b48325;">
            I. Bảng Thông Số Chi Tiết
          </h6>
          
          <div class="table-responsive rounded-3 border">
            <table class="table table-striped table-hover mb-0 spec-desktop-table small">
              <tbody>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4" style="width: 28%;">Bộ Sưu Tập</th>
                  <td class="py-2.5 px-4 fw-semibold text-dark">
                    <router-link to="/" class="text-decoration-none me-1 hover-gold" style="color: #b48325;">SOFEP Studio</router-link> &gt; 
                    <span class="text-muted ms-1">{{ product?.danh_muc?.ten_danh_muc || 'Thời trang đương đại' }}</span>
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4">Nhà Mốt Thiết Kế</th>
                  <td class="py-2.5 px-4 fw-bold text-dark">
                    {{ product?.thuong_hieu?.ten_thuong_hieu || 'SOFEP Haute Atelier Milan' }}
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4">Mã Tác Phẩm (SKU)</th>
                  <td class="py-2.5 px-4 font-monospace fw-bold text-dark">
                    {{ product?.ma_san_pham || ('SFP-' + (product?.id || '01')) }}
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4">Chất Liệu Tuyển Chọn</th>
                  <td class="py-2.5 px-4 text-dark">
                    Heavyweight Supima Cotton 100% 320GSM hoặc Da Thuộc cao cấp xử lý chống bai dão, siêu êm mịn tiếp xúc da.
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4">Kiểu Dáng / Phom Dáng</th>
                  <td class="py-2.5 px-4 text-dark">
                    Boxy Drop-Shoulder / Contemporary Relaxed Fit chuẩn phom hình học đương đại.
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4">Bảng Màu Khả Dụng</th>
                  <td class="py-2.5 px-4 text-dark">
                    Đen Obsidian, Trắng Chalk, Xám Heather, Xanh Navy Cobalt
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4">Kích Cỡ Có Sẵn</th>
                  <td class="py-2.5 px-4 text-dark fw-semibold">
                    S (45-58kg), M (59-68kg), L (69-78kg), XL (79-90kg)
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4">Xuất Xứ &amp; Hoàn Thiện</th>
                  <td class="py-2.5 px-4 text-dark">
                    Dây chuyền tiêu chuẩn chất lượng xuất khẩu thị trường Milan &amp; Paris
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4">Chính Sách Hậu Mãi</th>
                  <td class="py-2.5 px-4 text-success fw-semibold">
                    Đổi mới trong 30 ngày tận nhà nếu có lỗi sợi vải, bảo dưỡng đường chỉ may trọn đời
                  </td>
                </tr>
                <tr>
                  <th class="bg-light text-secondary py-2.5 px-4">Tình Trạng Kho Hàng</th>
                  <td class="py-2.5 px-4 text-dark">
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1">
                      Còn hàng ({{ product?.so_luong_ton || 120 }} tác phẩm sẵn sàng giao nhanh toàn quốc)
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Rich Description Section -->
        <div class="mt-4 pt-3 border-top">
          <h6 class="fw-bold small text-uppercase tracking-wider mb-3" style="color: #b48325;">
            II. Mô Tả Chi Tiết &amp; Cảm Hứng Sáng Tạo
          </h6>
          <div class="product-rich-description text-secondary lh-lg small">
            <p class="fs-6 text-dark fw-medium">
              {{ product?.mo_ta_chi_tiet || 'Tác phẩm được gia công tỉ mỉ bằng kỹ nghệ dệt may tân tiến nhất, đáp ứng hoàn hảo cho cả môi trường làm việc sáng tạo, dạo phố hay các sự kiện sang trọng.' }}
            </p>
            <p>
              Được nghiên cứu và tạo hình nhằm định nghĩa lại phong cách may đo thường nhật, thiết kế mang lại sự giao thoa hài hòa giữa cấu trúc phom dáng chuẩn mực, bề mặt sợi êm ái cùng độ bền vượt thời gian.
            </p>
            <div class="row g-3 my-3">
              <div class="col-md-6">
                <div class="p-3.5 bg-light rounded-3 border-start border-3 h-100" style="border-color: #b48325 !important;">
                  <h6 class="fw-bold text-dark small mb-2"><i class='bx bx-check-circle me-1' style="color: #b48325;"></i> Ưu Điểm Độc Quyền:</h6>
                  <ul class="mb-0 ps-3 text-muted">
                    <li>Vải dệt công nghệ kép hạn chế nhăn nhàu sau khi giặt.</li>
                    <li>Đường may móc xích đôi chịu lực gia cố tại vai và sườn áo.</li>
                    <li>Màu nhuộm hoạt tính an toàn cho da, giữ độ sâu màu bền bỉ.</li>
                    <li>Bo viền tinh xảo giữ trọn vẹn dáng áo sau nhiều mùa sử dụng.</li>
                  </ul>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3.5 bg-light rounded-3 border-start border-3 border-secondary h-100">
                  <h6 class="fw-bold text-dark small mb-2"><i class='bx bx-info-circle text-secondary me-1'></i> Hướng Dẫn Giặt &amp; Bảo Quản:</h6>
                  <ul class="mb-0 ps-3 text-muted">
                    <li>Giặt ở nhiệt độ thường (dưới 30°C) với trang phục cùng tone màu.</li>
                    <li>Không ngâm chung với chất tẩy rửa nồng độ clo cao.</li>
                    <li>Lộn trái mặt vải trước khi phơi ở nơi thoáng gió râm mát.</li>
                    <li>Ủi nhiệt độ nhẹ hoặc trung bình, tránh ủi trực tiếp lên logo nhiệt.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- 5. Customer Reviews Full Width Desktop Card -->
    <div class="container mb-4">
      <div class="card border-0 rounded-4 shadow-sm bg-white p-4" style="border: 1px solid rgba(180, 131, 37, 0.12) !important;">
        
        <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom gap-2">
          <div>
            <h4 class="fw-bold text-dark fs-5 text-uppercase font-streetwear mb-1">
              Nhận Xét Từ Khách Hàng
            </h4>
            <div class="d-flex align-items-center gap-2 small">
              <span class="fs-4 fw-extrabold" style="color: #b48325;">4.9</span>
              <span class="text-warning fs-6"><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star-half'></i></span>
              <span class="text-muted">trên 5 sao ({{ reviews.length * 37 }} lượt cảm nhận thực tế)</span>
            </div>
          </div>

          <!-- Review Filter Badges -->
          <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-sm btn-dark px-3 py-1 rounded-pill fw-bold">Tất Cả</button>
            <button class="btn btn-sm btn-light border px-3 py-1 rounded-pill">5 Sao (112)</button>
            <button class="btn btn-sm btn-light border px-3 py-1 rounded-pill">4 Sao (18)</button>
            <button class="btn btn-sm btn-light border px-3 py-1 rounded-pill">Có Lookbook / Ảnh (45)</button>
          </div>
        </div>

        <!-- Review Comments List -->
        <div class="d-flex flex-column gap-3 mt-3">
          <div v-for="(rv, rIndex) in reviews" :key="rIndex" class="p-3.5 bg-light-subtle rounded-3 border">
            <div class="d-flex align-items-center justify-content-between mb-1.5">
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(180, 131, 37, 0.12); color: #b48325;">
                  {{ rv.ten[0] }}
                </div>
                <div>
                  <div class="fw-bold text-dark small">{{ rv.ten }}</div>
                  <div class="text-warning small" style="font-size: 11px;">
                    <i v-for="s in rv.sao" :key="s" class='bx bxs-star'></i>
                    <span class="text-muted ms-2">{{ rv.ngay }} | Phân loại: {{ rv.phan_loai }}</span>
                  </div>
                </div>
              </div>
              <span class="badge bg-success bg-opacity-10 text-success small fw-semibold">Đã mua tại Atelier</span>
            </div>
            <p class="small text-secondary mb-0 ps-sm-5 ms-sm-2">
              "{{ rv.noi_dung }}"
            </p>
          </div>
        </div>

      </div>
    </div>

    <!-- 6. Related Products Grid Across Desktop Width (Full width) -->
    <div v-if="relatedProducts && relatedProducts.length > 0" class="container">
      <div class="card border-0 rounded-4 shadow-sm bg-white p-4" style="border: 1px solid rgba(180, 131, 37, 0.12) !important;">
        <h4 class="fw-bold text-dark fs-5 text-uppercase font-streetwear pb-3 mb-3 border-bottom d-flex align-items-center gap-2">
          <i class='bx bxs-crown' style="color: #b48325;"></i>
          <span>Gợi Ý Phối Đồ Cùng Tác Phẩm</span>
        </h4>
        
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-4 g-3 g-md-4 gy-4">
          <div v-for="rel in relatedProducts" :key="rel.id" class="col">
            <div @click="chuyenSanPham(rel)" class="card border rounded-3 p-2 h-100 cursor-pointer hover-lift bg-white">
              <div class="position-relative overflow-hidden rounded-2 mb-2" style="padding-top: 100%;">
                <img :src="rel.hinh_anh" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" :alt="rel.ten_san_pham">
                <span v-if="rel.gia_khuyen_mai" class="badge rounded-pill position-absolute top-0 start-0 m-1.5 text-white fw-bold px-2 py-0.5" style="font-size: 9px; background: var(--sofep-gold-gradient);">
                  ATELIER
                </span>
              </div>
              <div class="small fw-bold text-truncate text-dark" :title="rel.ten_san_pham">{{ rel.ten_san_pham }}</div>
              <div class="d-flex align-items-baseline gap-1 mt-1">
                <span class="fw-extrabold small" style="color: #b48325;">₫{{ formatMoney(rel.gia_khuyen_mai || rel.gia_ban) }}</span>
                <span v-if="rel.gia_khuyen_mai" class="text-muted text-decoration-line-through text-truncate" style="font-size: 10px;">
                  ₫{{ formatMoney(rel.gia_ban) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 7. Sticky Bottom Action Bar (Chỉ hiển thị trên Mobile d-block d-md-none) -->
    <div class="sofep-sticky-bottom-bar fixed-bottom bg-white border-top shadow-lg d-block d-md-none">
      <div class="container d-flex align-items-center justify-content-between p-0" style="max-width: 600px;">
        <a href="tel:19006868" class="bottom-action-btn d-flex flex-column align-items-center justify-content-center text-dark text-decoration-none py-2 px-3 flex-shrink-0">
          <i class='bx bx-message-rounded-dots fs-4' style="color: #b48325;"></i>
          <span style="font-size: 10px;" class="fw-semibold mt-0.5">Tư vấn</span>
        </a>
        <div class="vr my-2"></div>
        <button @click="themVaoGioHang(false)" class="bottom-action-btn btn border-0 d-flex flex-column align-items-center justify-content-center text-dark py-2 px-3 flex-shrink-0">
          <i class='bx bx-cart-add fs-4' style="color: #b48325;"></i>
          <span style="font-size: 10px;" class="fw-semibold mt-0.5">Thêm Giỏ</span>
        </button>
        <button @click="themVaoGioHang(true)" class="btn buy-voucher-btn flex-grow-1 h-100 rounded-0 py-2 px-3 d-flex flex-column align-items-center justify-content-center text-white" style="background: var(--sofep-gold-gradient);">
          <span class="fw-bold fs-6 text-white text-uppercase tracking-wide">Đặt Mua Ngay</span>
          <span class="small opacity-90" style="font-size: 12px;">₫{{ formatMoney(giaHienTai) }}</span>
        </button>
      </div>
    </div>

    <!-- Video Modal Preview -->
    <div v-if="showVideoModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-white text-dark rounded-4 border-0 shadow-2xl overflow-hidden">
          <div class="modal-header border-bottom py-3 px-4">
            <h6 class="modal-title fw-bold font-streetwear d-flex align-items-center gap-2 mb-0">
              <i class='bx bx-video fs-5' style="color: #b48325;"></i>
              <span>Video Giới Thiệu Tác Phẩm • SOFEP ATELIER</span>
            </h6>
            <button type="button" class="btn-close shadow-none" @click="showVideoModal = false"></button>
          </div>
          <div class="modal-body text-center p-4">
            <div class="ratio ratio-16x9 rounded-3 overflow-hidden bg-black mb-3 shadow-sm">
              <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1" title="Product video" allowfullscreen></iframe>
            </div>
            <p class="small text-secondary mb-0">Video quay thực tế chất liệu vải cao cấp và chi tiết đường may hoàn thiện.</p>
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
  name: 'ChiTietSanPham',
  data() {
    return {
      cartStore,
      product: null,
      relatedProducts: [],
      activeImageIndex: 0,
      activeImage: '',
      galleryImages: [],
      hasVideo: true,
      showVideoModal: false,
      isYeuThich: false,
      quantity: 1,
      selectedColor: 'Đen Obsidian',
      selectedSize: 'L',
      soLuongDaBan: 488,
      colorOptions: [
        { name: 'Đen Obsidian', hex: '#121314' },
        { name: 'Xám Heather', hex: '#7a8288' },
        { name: 'Xanh Navy', hex: '#1e3a8a' },
        { name: 'Trắng Chalk', hex: '#f3f4f6' }
      ],
      sizeOptions: ['S', 'M', 'L', 'XL'],
      reviews: [
        {
          ten: 'Hoàng Nam (VIP)',
          sao: 5,
          ngay: '22/09/2026',
          phan_loai: 'Đen Obsidian, Size L',
          noi_dung: 'Chất vải cực kỳ dày dặn, form boxy đứng áo đúng chuẩn streetwear. Giao hàng hỏa tốc trong 2 tiếng rất ưng ý!'
        },
        {
          ten: 'Trần Minh Đức',
          sao: 5,
          ngay: '20/09/2026',
          phan_loai: 'Xám Heather, Size XL',
          noi_dung: 'Đóng gói hộp chắc chắn xịn sò. Mình 1m75 nặng 72kg mặc size L vừa vặn hoặc XL mặc rộng rất đẹp.'
        },
        {
          ten: 'Nguyễn Thu Trang',
          sao: 5,
          ngay: '18/09/2026',
          phan_loai: 'Trắng Chalk, Size M',
          noi_dung: 'Mua tặng bạn trai, anh ấy rất thích chất liệu Supima mát mịn, không bị xù lông khi giặt máy.'
        }
      ],
      countdownHours: '00',
      countdownMinutes: '59',
      countdownSeconds: '55',
      timerInterval: null
    };
  },
  computed: {
    giaHienTai() {
      if (!this.product) return 249000;
      return Number(this.product.gia_khuyen_mai || this.product.gia_ban || 249000);
    },
    tinhTienGiam() {
      if (!this.product) return 50000;
      const goc = Number(this.product.gia_ban || 0);
      const km = Number(this.product.gia_khuyen_mai || goc);
      return goc > km ? (goc - km) : 100000;
    },
    ngayGiaoDuKien() {
      const now = new Date();
      const d1 = new Date(now.getTime() + 2 * 24 * 60 * 60 * 1000);
      const d2 = new Date(now.getTime() + 3 * 24 * 60 * 60 * 1000);
      return `${d1.getDate()} Thg ${d1.getMonth() + 1} - ${d2.getDate()} Thg ${d2.getMonth() + 1}`;
    }
  },
  watch: {
    '$route.params.id': {
      immediate: true,
      handler(newId) {
        if (newId) {
          this.loadChiTietSanPham(newId);
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      }
    }
  },
  mounted() {
    this.startCountdown();
  },
  beforeUnmount() {
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
    }
  },
  methods: {
    loadChiTietSanPham(idOrSlug) {
      axios.get(`http://127.0.0.1:8000/api/client/san-pham/${idOrSlug}`)
        .then(res => {
          if (res.data.status && res.data.data) {
            const sp = res.data.data.san_pham;
            this.product = sp;
            this.relatedProducts = res.data.data.san_pham_lien_quan || [];

            const images = [];
            if (sp.hinh_anh) images.push(sp.hinh_anh);
            if (sp.anh_san_phams && sp.anh_san_phams.length > 0) {
              sp.anh_san_phams.forEach(item => {
                if (item.duong_dan_anh && !images.includes(item.duong_dan_anh)) {
                  images.push(item.duong_dan_anh);
                }
              });
            }
            if (images.length === 0) {
              images.push('https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=700');
            }
            while (images.length < 5) {
              images.push(images[0]);
            }
            this.galleryImages = images;
            this.activeImage = images[0];
            this.activeImageIndex = 0;

            if (sp.luot_xem) {
              this.soLuongDaBan = Math.max(488, Math.round(sp.luot_xem / 2));
            }

            if (sp.chi_tiet_san_phams && sp.chi_tiet_san_phams.length > 0) {
              const colors = [];
              sp.chi_tiet_san_phams.forEach(item => {
                if (item.mau_sac && !colors.some(c => c.name === item.mau_sac.ten_mau)) {
                  colors.push({
                    name: item.mau_sac.ten_mau,
                    hex: item.mau_sac.ma_mau || '#111315'
                  });
                }
              });
              if (colors.length > 0) {
                this.colorOptions = colors;
                this.selectedColor = colors[0].name;
              }
            }
          }
        })
        .catch(err => {
          console.warn('Fallback mock product data:', err);
          this.setupMockProduct(idOrSlug);
        });
    },
    setupMockProduct(id) {
      this.product = {
        id: id || 1,
        ma_san_pham: 'SFP-2026-LUX',
        ten_san_pham: 'Túi Xách Da Nữ Đeo Chéo Khóa Mạ Vàng Cao Cấp SOFEP Atelier',
        gia_ban: '550000',
        gia_khuyen_mai: '450000',
        hinh_anh: 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=700',
        mo_ta_ngan: 'Chất liệu da thuộc cao cấp nhập khẩu, khóa mạ vàng 18K tinh xảo.',
        mo_ta_chi_tiet: 'Thiết kế sang trọng, phom dáng cứng cáp chống gãy gập, đường may thủ công chuẩn mực Milan.',
        so_luong_ton: 120,
        danh_muc: { ten_danh_muc: 'Túi Xách & Ví' },
        thuong_hieu: { ten_thuong_hieu: 'SOFEP Flagship Atelier' }
      };
      this.galleryImages = [
        this.product.hinh_anh,
        'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=700',
        'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=700',
        'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=700'
      ];
      this.activeImage = this.galleryImages[0];
    },
    setActiveImage(idx) {
      this.activeImageIndex = idx;
      this.activeImage = this.galleryImages[idx];
    },
    tangSoLuong() {
      const max = this.product?.so_luong_ton || 99;
      if (this.quantity < max) this.quantity++;
    },
    giamSoLuong() {
      if (this.quantity > 1) this.quantity--;
    },
    formatMoney(val) {
      if (!val) return '0';
      return Number(val).toLocaleString('vi-VN');
    },
    tinhPhanTramGiam(giaBan, giaKM) {
      if (!giaBan || !giaKM || Number(giaBan) <= Number(giaKM)) return 18;
      return Math.round(((giaBan - giaKM) / giaBan) * 100);
    },
    toggleYeuThich() {
      this.isYeuThich = !this.isYeuThich;
      if (this.isYeuThich) {
        this.$toast ? this.$toast.success('Đã lưu vào danh sách mong muốn!') : alert('Đã lưu vào danh sách mong muốn!');
      }
    },
    chiaSeSanPham() {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href);
        this.$toast ? this.$toast.info('Đã sao chép liên kết sản phẩm!') : alert('Đã sao chép liên kết!');
      }
    },
    toggleVideoModal() {
      this.showVideoModal = !this.showVideoModal;
    },
    chuyenSanPham(sp) {
      this.$router.push(`/san-pham/${sp.slug_san_pham || sp.id}`);
    },
    luuVoucher(code) {
      if (this.$toast) {
        this.$toast.success(`Đã lưu mã giảm giá ${code} vào ví voucher của bạn!`);
      }
    },
    themVaoGioHang(moCheckout = false) {
      if (!this.product) return;
      const spItem = {
        id: this.product.id,
        ten_san_pham: this.product.ten_san_pham,
        gia_ban: this.giaHienTai,
        hinh_anh: this.activeImage || this.product.hinh_anh,
        size: this.selectedSize,
        color: this.selectedColor,
        quantity: this.quantity
      };
      
      this.cartStore.addItem(spItem, this.quantity);

      if (moCheckout) {
        this.cartStore.toggleDrawer(false);
        this.cartStore.toggleCheckout(true);
      } else {
        if (this.$toast) {
          this.$toast.success(`Đã thêm ${this.quantity} tác phẩm vào Giỏ Hàng!`);
        }
      }
    },
    startCountdown() {
      let totalSeconds = 59 * 60 + 55;
      this.timerInterval = setInterval(() => {
        if (totalSeconds <= 0) {
          totalSeconds = 60 * 60;
        }
        totalSeconds--;
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;

        this.countdownHours = String(h).padStart(2, '0');
        this.countdownMinutes = String(m).padStart(2, '0');
        this.countdownSeconds = String(s).padStart(2, '0');
      }, 1000);
    }
  }
};
</script>

<style scoped>
.sofep-atelier-detail-page {
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  min-height: 100vh;
}

.atelier-gallery-frame {
  height: 460px;
  background: radial-gradient(circle at 50% 50%, #ffffff 0%, #fcfbfa 100%);
  border-color: rgba(180, 131, 37, 0.15) !important;
}

.zoom-image {
  transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.atelier-gallery-frame:hover .zoom-image {
  transform: scale(1.04);
}

.atelier-glass-badge {
  background: rgba(255, 255, 255, 0.88);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(180, 131, 37, 0.3);
  color: #78350f;
  font-size: 11px;
  letter-spacing: 0.5px;
}

.atelier-round-glass-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(180, 131, 37, 0.2);
  transition: all 0.25s ease;
}

.atelier-round-glass-btn:hover {
  transform: scale(1.1);
  background: #ffffff;
  border-color: #b48325;
}

.atelier-video-pill {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(180, 131, 37, 0.25);
  transition: all 0.25s ease;
}

.atelier-video-pill:hover {
  transform: translate(-50%, -2px) scale(1.04);
  box-shadow: 0 8px 24px rgba(180, 131, 37, 0.2) !important;
  border-color: #b48325;
}

.pulse-play-icon {
  width: 26px;
  height: 26px;
  background: var(--sofep-gold-gradient);
}

.thumbnail-scroll-strip {
  scrollbar-width: thin;
}

.atelier-thumb-item {
  transition: all 0.25s ease;
  border-color: rgba(180, 131, 37, 0.15) !important;
}

.atelier-thumb-item:hover {
  border-color: #b48325 !important;
}

.active-atelier-thumb {
  border: 2px solid #b48325 !important;
  box-shadow: 0 4px 14px rgba(180, 131, 37, 0.25);
}

.prestige-timer-bar {
  background: linear-gradient(135deg, #fefdfb 0%, #fef8ee 50%, #fef3c7 100%);
  border: 1px solid rgba(180, 131, 37, 0.25);
}

.countdown-digit-cube {
  background: #1e293b;
  color: #fef08a;
  border-radius: 6px;
  padding: 2px 7px;
  font-family: monospace;
  font-weight: 800;
  font-size: 13px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.atelier-voucher-ticket {
  border: 1.5px dashed rgba(180, 131, 37, 0.45);
  background-color: #fefcf7;
  color: #78350f;
  border-radius: 9999px;
  transition: all 0.2s ease;
}

.atelier-voucher-ticket:hover {
  background-color: #fef3c7;
  border-color: #b48325;
  transform: translateY(-1px);
}

.atelier-variant-btn {
  border: 1px solid #e2e8f0;
  background-color: #ffffff;
  color: #1e293b;
  transition: all 0.2s ease;
}

.atelier-variant-btn:hover {
  border-color: #b48325;
}

.atelier-variant-active {
  border: 2px solid #b48325 !important;
  background-color: #fefcf7 !important;
  box-shadow: 0 2px 8px rgba(180, 131, 37, 0.15);
}

.atelier-size-btn {
  min-width: 44px;
  border: 1px solid #e2e8f0;
  background-color: #ffffff;
  color: #1e293b;
  transition: all 0.2s ease;
}

.atelier-size-btn:hover {
  border-color: #b48325;
}

.atelier-size-active {
  border: 2px solid #b48325 !important;
  background-color: #fefcf7 !important;
  color: #b48325 !important;
  box-shadow: 0 2px 8px rgba(180, 131, 37, 0.15);
}

.spec-desktop-table th {
  font-weight: 600;
  border-right: 1px solid #dee2e6;
}

.hover-gold:hover {
  color: #b48325 !important;
}

.hover-lift {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.hover-lift:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(180, 131, 37, 0.12);
}

.sofep-sticky-bottom-bar {
  z-index: 1050;
  height: 58px;
}

.bottom-action-btn {
  width: 90px;
  height: 58px;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
