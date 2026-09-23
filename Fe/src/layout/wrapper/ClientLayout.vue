<template>
  <div class="client-layout-wrapper light-theme bg-white text-dark min-vh-100 d-flex flex-column">

    <!-- 1. Top Announcement Marquee Ticker (URBN WEAR Header Style) -->
    <div class="top-announcement-bar bg-urbn-primary text-white py-2 px-3 small fw-medium">
      <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-3 overflow-hidden">
          <span class="d-flex align-items-center gap-1.5 font-medium tracking-wide">
            <i class='bx bxs-truck fs-5'></i>
            <span>MIỄN PHÍ VẬN CHUYỂN ĐƠN TỪ 500.000₫ TOÀN QUỐC</span>
          </span>
          <span class="d-none d-md-inline opacity-50">|</span>
          <span class="d-none d-md-flex align-items-center gap-1.5 opacity-90">
            <i class='bx bx-refresh fs-5'></i>
            <span>ĐỔI TRẢ 30 NGÀY TẬN NHÀ MIỄN PHÍ</span>
          </span>
          <span class="d-none d-lg-inline opacity-50">|</span>
          <span class="d-none d-lg-flex align-items-center gap-1.5 text-warning font-bold">
            MÃ: <code class="bg-white bg-opacity-25 text-white px-2 py-0.5 rounded font-monospace small">URBNNEW</code> GIẢM 50.000₫
          </span>
        </div>
        
        <!-- Size Guide Quick Button -->
        <button @click="cartStore.toggleSizeAdvisor(true)"
                class="btn btn-sm btn-light bg-white bg-opacity-20 text-white border-0 rounded-pill px-3 py-1 d-flex align-items-center gap-1 ms-auto ms-sm-0 hover-opacity">
          <i class='bx bx-ruler'></i>
          <span class="small fw-semibold">Bảng Hướng Dẫn Chọn Size</span>
        </button>
      </div>
    </div>

    <!-- 2. Main Navigation Header Bar -->
    <header class="main-header sticky-top bg-white border-bottom shadow-sm">
      <div class="container py-3">
        <div class="row align-items-center g-3">

          <!-- Brand Logo & Mobile Toggle -->
          <div class="col-6 col-lg-3 d-flex align-items-center gap-2">
            <!-- Mobile Menu Toggle Button -->
            <button class="btn btn-light d-lg-none p-2 border-0 me-1" type="button" @click="isMobileMenuOpen = !isMobileMenuOpen">
              <i class='bx bx-menu fs-3 text-dark'></i>
            </button>

            <!-- URBN WEAR Logo -->
            <router-link to="/" class="d-flex align-items-center gap-2 text-decoration-none group-logo">
              <div class="logo-box bg-urbn-dark text-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                <i class='bx bxs-layer fs-4 text-urbn-primary'></i>
              </div>
              <div>
                <span class="logo-brand fs-4 fw-extrabold text-dark tracking-tight font-streetwear">
                  URBN<span class="text-urbn-primary">.</span>WEAR
                </span>
                <span class="d-block text-muted tracking-widest" style="font-size: 9px; font-weight: 700; letter-spacing: 2px;">
                  METROPOLIS 2026
                </span>
              </div>
            </router-link>
          </div>

          <!-- Live Search Bar -->
          <div class="col-12 col-lg-5 order-3 order-lg-2">
            <div class="position-relative search-box-wrapper">
              <input v-model="searchQuery" @keyup.enter="handleSearch" type="text"
                class="form-control rounded-pill bg-light border ps-4 pe-5 py-2 search-input shadow-none"
                placeholder="Tìm áo thun boxy, hoodie utility, quần parachute cargo...">
              <button @click="handleSearch" class="btn btn-urbn-dark text-white rounded-circle position-absolute top-50 end-0 translate-middle-y me-1 p-0 d-flex align-items-center justify-content-center search-btn">
                <i class='bx bx-search fs-5'></i>
              </button>
            </div>
          </div>

          <!-- Right Action Buttons -->
          <div class="col-6 col-lg-4 order-2 order-lg-3 text-end d-flex align-items-center justify-content-end gap-2 gap-sm-3">
            
            <!-- Size Advisor Button (Desktop) -->
            <button @click="cartStore.toggleSizeAdvisor(true)"
                    class="btn btn-light btn-sm rounded-pill px-3 py-1.5 fw-semibold d-none d-md-inline-flex align-items-center gap-1 border">
              <i class='bx bx-ruler text-urbn-primary'></i> Bảng Size
            </button>

            <!-- Admin Link Button -->
            <router-link to="/admin" class="btn btn-outline-dark btn-sm rounded-pill fw-semibold px-3 py-1.5 d-none d-sm-inline-flex align-items-center gap-1">
              <i class='bx bx-shield-quarter text-urbn-primary'></i> Quản Trị Admin
            </router-link>

            <!-- Cart Trigger Button -->
            <button @click="cartStore.toggleDrawer(true)" class="btn btn-light rounded-circle position-relative border-0 shadow-sm p-2 action-icon-btn" title="Giỏ hàng">
              <i class='bx bx-shopping-bag fs-4 text-dark'></i>
              <span v-if="cartStore.count > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-urbn-primary border border-white cart-badge-pulse">
                {{ cartStore.count }}
              </span>
            </button>

            <!-- Account User Dropdown -->
            <div class="dropdown">
              <button class="btn btn-light rounded-pill border-0 shadow-sm px-2 py-1 d-flex align-items-center gap-1" data-bs-toggle="dropdown">
                <i class='bx bx-user-circle fs-3 text-urbn-primary'></i>
                <span v-if="currentUser" class="small fw-bold text-dark d-none d-md-inline ms-1 text-truncate" style="max-width: 110px;">
                  {{ currentUser.ho_va_ten }}
                </span>
                <i class='bx bx-chevron-down text-muted small'></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 py-2" style="min-width: 230px;">
                <li v-if="currentUser" class="px-3 py-2 border-bottom mb-1">
                  <div class="small fw-bold text-dark text-truncate">{{ currentUser.ho_va_ten }}</div>
                  <div class="text-muted small mt-1" style="font-size: 11px;">
                    <span class="badge" :class="currentUser.role === 'admin' ? 'bg-danger' : 'bg-primary'">
                      {{ currentUser.role === 'admin' ? 'Quản Trị Viên' : 'Thành Viên' }}
                    </span>
                    <span class="d-block text-truncate mt-0.5">{{ currentUser.email }}</span>
                  </div>
                </li>
                <li v-else class="px-3 py-2 border-bottom mb-1">
                  <div class="small fw-bold text-dark">Tài Khoản URBN</div>
                  <div class="text-muted small" style="font-size: 11px;">Đăng nhập để nhận ưu đãi</div>
                </li>

                <!-- Logged in items -->
                <template v-if="currentUser">
                  <li v-if="currentUser.role === 'admin'">
                    <router-link class="dropdown-item py-2 small fw-semibold text-danger" to="/admin">
                      <i class='bx bx-shield-quarter me-2'></i>Bảng Điều Khiển Admin
                    </router-link>
                  </li>
                  <li v-else>
                    <a class="dropdown-item py-2 small fw-semibold" href="javascript:;" @click="showUserModal = true">
                      <i class='bx bx-user me-2 text-primary'></i>Thông Tin Tài Khoản
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item py-2 small text-danger" href="javascript:;" @click="dangXuat">
                      <i class='bx bx-log-out me-2'></i>Đăng Xuất
                    </a>
                  </li>
                </template>

                <!-- Not logged in items -->
                <template v-else>
                  <li>
                    <router-link class="dropdown-item py-2 small fw-semibold" to="/dang-nhap">
                      <i class='bx bx-log-in me-2 text-primary'></i>Đăng Nhập
                    </router-link>
                  </li>
                  <li>
                    <router-link class="dropdown-item py-2 small fw-semibold" to="/dang-ky">
                      <i class='bx bx-user-plus me-2 text-success'></i>Đăng Ký Mới
                    </router-link>
                  </li>
                  <li><hr class="dropdown-divider my-1"></li>
                  <li>
                    <router-link class="dropdown-item py-2 small text-muted" to="/admin/login">
                      <i class='bx bx-shield-quarter me-2 text-warning'></i>Cổng Quản Trị Admin
                    </router-link>
                  </li>
                </template>

                <li><hr class="dropdown-divider my-1"></li>
                <li><a class="dropdown-item py-2 small text-muted" href="tel:19006868"><i class='bx bx-help-circle me-2'></i>Hotline Hỗ Trợ 1900 6868</a></li>
              </ul>
            </div>

          </div>

        </div>
      </div>

      <!-- 3. Desktop Sub-Navbar Navigation Menu -->
      <nav class="sub-navbar border-top bg-white d-none d-lg-block">
        <div class="container">
          <ul class="nav nav-pills align-items-center gap-1 py-1">
            <li class="nav-item">
              <router-link to="/" class="nav-link text-dark fw-bold px-3 py-2 rounded-pill nav-hover-pill" active-class="active bg-urbn-primary text-white">
                <i class='bx bx-home-alt me-1'></i> Trang Chủ
              </router-link>
            </li>

            <!-- Category Dropdown with Vue State -->
            <li class="nav-item dropdown position-relative"
                @mouseenter="isCategoryDropdownOpen = true"
                @mouseleave="isCategoryDropdownOpen = false">
              <button class="nav-link text-dark fw-bold px-3 py-2 dropdown-toggle border-0 bg-transparent d-flex align-items-center cursor-pointer"
                      @click="isCategoryDropdownOpen = !isCategoryDropdownOpen"
                      type="button">
                <i class='bx bx-category me-1 text-urbn-primary fs-5'></i> Danh Mục Thời Trang
                <i class='bx bx-chevron-down ms-1 transition-transform' :class="{ 'rotate-180': isCategoryDropdownOpen }"></i>
              </button>
              
              <!-- Dropdown Menu -->
              <div class="dropdown-menu shadow-lg border-0 rounded-4 py-2 custom-category-dropdown"
                   :class="{ 'show': isCategoryDropdownOpen }"
                   style="min-width: 280px;">
                <div class="dropdown-header small text-muted text-uppercase fw-bold pb-1">
                  Chọn phân loại trang phục
                </div>
                <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2 rounded-3 mx-2 my-1"
                   href="javascript:;"
                   @click="chonDanhMuc('tat-ca')">
                  <span class="rounded-circle bg-urbn-primary bg-opacity-10 text-urbn-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class='bx bx-grid-alt fs-5'></i>
                  </span>
                  <span class="fw-bold text-dark flex-grow-1">Tất Cả Bộ Sưu Tập</span>
                  <i class='bx bx-chevron-right text-muted'></i>
                </a>
                <div class="dropdown-divider my-1"></div>
                <a v-for="dm in danh_mucs" :key="dm.id"
                   class="dropdown-item py-2 px-3 d-flex align-items-center gap-2 rounded-3 mx-2 my-1 category-dropdown-item"
                   href="javascript:;"
                   @click="chonDanhMuc(dm.id)">
                  <img :src="dm.hinh_anh || 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=80'" class="rounded-circle border object-fit-cover" style="width: 32px; height: 32px; object-fit: cover;">
                  <span class="fw-bold text-dark flex-grow-1 small">{{ dm.ten_danh_muc }}</span>
                  <span class="badge bg-light text-muted border rounded-pill small" style="font-size: 10px;">
                    {{ dm.san_phams_count || 0 }} mẫu
                  </span>
                </a>
              </div>
            </li>


            <li class="nav-item">
              <a class="nav-link text-danger fw-bold px-3 py-2" href="#flash-sale-section">
                <i class='bx bxs-zap text-danger me-1'></i> Flash Sale -30%
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-dark fw-semibold px-3 py-2" href="#products-section">
                <i class='bx bx-star text-warning me-1'></i> Sản Phẩm Mới Drop 04
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-dark fw-semibold px-3 py-2" href="#reviews-section">
                <i class='bx bx-chat text-info me-1'></i> Đánh Giá Cộng Đồng
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Mobile Offcanvas Menu Drawer -->
      <div v-if="isMobileMenuOpen" class="mobile-menu-drawer bg-white border-bottom shadow p-3 d-lg-none animate-in">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="fw-bold text-uppercase small text-muted">Menu Điều Hướng</span>
          <button @click="isMobileMenuOpen = false" class="btn btn-sm btn-light rounded-circle">
            <i class='bx bx-x fs-5'></i>
          </button>
        </div>
        <div class="d-flex flex-column gap-2">
          <router-link to="/" @click="isMobileMenuOpen = false" class="btn btn-light text-start fw-bold py-2 rounded-3">
            <i class='bx bx-home-alt me-2 text-urbn-primary'></i> Trang Chủ
          </router-link>
          <a href="#flash-sale-section" @click="isMobileMenuOpen = false" class="btn btn-light text-start fw-bold py-2 rounded-3 text-danger">
            <i class='bx bxs-zap me-2 text-danger'></i> Flash Sale -30%
          </a>
          <a href="#products-section" @click="isMobileMenuOpen = false" class="btn btn-light text-start fw-bold py-2 rounded-3">
            <i class='bx bx-grid-alt me-2 text-urbn-primary'></i> Khám Phá Sản Phẩm
          </a>
          <button @click="cartStore.toggleSizeAdvisor(true); isMobileMenuOpen = false;" class="btn btn-light text-start fw-bold py-2 rounded-3">
            <i class='bx bx-ruler me-2 text-urbn-primary'></i> Hướng Dẫn Chọn Size
          </button>
          <router-link to="/admin" @click="isMobileMenuOpen = false" class="btn btn-dark text-start fw-bold py-2 rounded-3 text-white">
            <i class='bx bx-shield-quarter me-2 text-warning'></i> Vào Quản Trị Admin
          </router-link>
        </div>
      </div>
    </header>

    <!-- Main View Content Area -->
    <main class="flex-grow-1">
      <router-view></router-view>
    </main>

    <!-- 4. Slide-in Cart Drawer (URBN WEAR Style) -->
    <div v-if="cartStore.isOpenDrawer" class="offcanvas-backdrop-custom" @click="cartStore.toggleDrawer(false)"></div>
    <div class="cart-drawer-container shadow-2xl d-flex flex-column" :class="{ 'open': cartStore.isOpenDrawer }">
      
      <!-- Drawer Header -->
      <div class="cart-drawer-header p-3 bg-urbn-dark text-white d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <i class='bx bx-shopping-bag fs-4 text-urbn-primary'></i>
          <h5 class="mb-0 fw-bold font-streetwear small tracking-wide">GIỎ HÀNG CỦA BẠN ({{ cartStore.count }})</h5>
        </div>
        <button @click="cartStore.toggleDrawer(false)" class="btn btn-link text-white p-0 text-decoration-none">
          <i class='bx bx-x fs-3'></i>
        </button>
      </div>

      <!-- Drawer Body Items -->
      <div class="cart-drawer-body flex-grow-1 p-3 overflow-auto">
        <div v-if="cartStore.items.length === 0" class="text-center py-5 text-muted">
          <div class="mb-3">
            <i class='bx bx-shopping-bag text-muted' style="font-size: 4rem; opacity: 0.3;"></i>
          </div>
          <h6 class="fw-bold text-dark">Giỏ hàng của bạn đang trống</h6>
          <p class="small text-secondary mb-3">Hãy chọn thêm những sản phẩm thời trang đường phố ưng ý nhé!</p>
          <button @click="cartStore.toggleDrawer(false)" class="btn btn-urbn-primary btn-sm rounded-pill px-4">
            Khám Phá Drop Mới
          </button>
        </div>

        <div v-else class="cart-item-list d-flex flex-column gap-3">
          <div v-for="item in cartStore.items" :key="item.itemKey" class="cart-item-row p-2 rounded-3 border bg-light d-flex gap-3 align-items-center position-relative">
            <img :src="item.hinh_anh || 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=100'" class="rounded-3 border object-fit-cover flex-shrink-0" style="width: 65px; height: 75px; object-fit: cover;">
            <div class="flex-grow-1 overflow-hidden">
              <h6 class="text-truncate mb-1 fw-bold text-dark small" :title="item.ten_san_pham">{{ item.ten_san_pham }}</h6>
              <div class="text-muted small mb-1" style="font-size: 11px;">
                Màu: <span class="fw-semibold text-dark">{{ item.mau_sac }}</span> | Size: <span class="fw-semibold text-dark">{{ item.kich_thuoc }}</span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="fw-bold text-urbn-primary small">{{ formatMoney(item.gia) }}₫</span>
                <!-- Quantity Controls -->
                <div class="btn-group btn-group-sm border rounded-pill bg-white overflow-hidden" role="group">
                  <button @click="cartStore.updateQuantity(item.itemKey, -1)" class="btn btn-light px-2 py-0 border-0">-</button>
                  <span class="px-2 py-0 small fw-bold d-flex align-items-center">{{ item.so_luong }}</span>
                  <button @click="cartStore.updateQuantity(item.itemKey, 1)" class="btn btn-light px-2 py-0 border-0">+</button>
                </div>
              </div>
            </div>
            <!-- Remove Button -->
            <button @click="cartStore.removeItem(item.itemKey)" class="btn btn-link text-danger p-0 ms-1" title="Xóa món này">
              <i class='bx bx-x fs-4'></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Drawer Footer Checkout -->
      <div v-if="cartStore.items.length > 0" class="p-3 border-top bg-light">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="text-secondary small">Tạm tính:</span>
          <h5 class="fw-bold text-urbn-primary mb-0">{{ formatMoney(cartStore.subtotal) }}₫</h5>
        </div>
        <div class="alert alert-success py-1 px-2 border-0 mb-3 small text-center" style="font-size: 11px;">
          <i class='bx bxs-check-circle me-1'></i> Đã đủ điều kiện được <strong>Miễn Phí Vận Chuyển</strong>!
        </div>
        <div class="d-grid gap-2">
          <button @click="tienHanhDatHang" class="btn btn-urbn-primary py-2 fw-bold text-white rounded-pill shadow-sm">
            <i class='bx bx-credit-card me-1'></i> TIẾN HÀNH ĐẶT HÀNG ({{ formatMoney(cartStore.subtotal) }}₫)
          </button>
          <button @click="cartStore.clearCart" class="btn btn-link btn-sm text-muted text-decoration-none">
            Xóa toàn bộ giỏ hàng
          </button>
        </div>
      </div>
    </div>

    <!-- 5. Interactive Size Advisor Modal Component -->
    <SizeAdvisorModal />

    <!-- 6. Footer (URBN WEAR Obsidian Dark Theme) -->
    <footer class="bg-urbn-dark text-white pt-5 pb-4 mt-5 border-top border-secondary border-opacity-25">
      <div class="container">
        
        <!-- Newsletter Box -->
        <div class="p-4 p-md-5 rounded-4 mb-5 border border-secondary border-opacity-25" style="background: linear-gradient(135deg, #111315 0%, #0a0d12 100%);">
          <div class="row align-items-center gy-3">
            <div class="col-lg-6">
              <span class="badge bg-urbn-primary text-white rounded-pill px-3 py-1 mb-2 fw-bold small">URBN METROPOLIS CLUB</span>
              <h3 class="fw-extrabold mb-1 font-streetwear text-white">Đăng Ký Nhận Voucher 50.000₫</h3>
              <p class="mb-0 text-secondary small">Nhận thông báo sớm nhất về các đợt Drop mới, mã giảm giá độc quyền và sự kiện thời trang đường phố.</p>
            </div>
            <div class="col-lg-6">
              <div class="input-group input-group-lg bg-dark rounded-pill p-1 border border-secondary">
                <input type="email" class="form-control border-0 bg-transparent text-white ps-3 shadow-none small" placeholder="Nhập địa chỉ email của bạn...">
                <button class="btn btn-urbn-primary rounded-pill px-4 fw-bold text-white text-nowrap">Đăng Ký</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Columns -->
        <div class="row g-4 mb-4">
          <div class="col-lg-4 col-md-6">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="bg-urbn-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class='bx bxs-layer fs-4'></i>
              </div>
              <span class="fs-4 fw-extrabold text-white font-streetwear">URBN<span class="text-urbn-primary">.</span>WEAR</span>
            </div>
            <p class="text-secondary small mb-3">
              Thương hiệu thời trang đường phố định hình phong cách đô thị đương đại. Mỗi sản phẩm được phát triển với chất lượng hoàn thiện chuẩn mực cao nhất trên nền vải Heavyweight Cotton Compact.
            </p>
            <div class="d-flex gap-2">
              <a href="#" class="btn btn-dark rounded-circle text-white social-btn border border-secondary"><i class='bx bxl-facebook fs-5'></i></a>
              <a href="#" class="btn btn-dark rounded-circle text-white social-btn border border-secondary"><i class='bx bxl-instagram fs-5'></i></a>
              <a href="#" class="btn btn-dark rounded-circle text-white social-btn border border-secondary"><i class='bx bxl-tiktok fs-5'></i></a>
            </div>
          </div>

          <div class="col-lg-2 col-md-6 col-6">
            <h6 class="fw-bold text-white text-uppercase mb-3 small tracking-wide font-streetwear">Danh Mục Drop</h6>
            <ul class="list-unstyled small d-flex flex-column gap-2 text-secondary">
              <li><a href="#products-section" class="text-decoration-none text-secondary footer-link">Áo Thun Boxy</a></li>
              <li><a href="#products-section" class="text-decoration-none text-secondary footer-link">Hoodie Utility Zip-up</a></li>
              <li><a href="#products-section" class="text-decoration-none text-secondary footer-link">Quần Parachute Cargo</a></li>
              <li><a href="#products-section" class="text-decoration-none text-secondary footer-link">Áo Khoác Bomber MA-1</a></li>
              <li><a href="#products-section" class="text-decoration-none text-secondary footer-link">Phụ Kiện Tactical</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 col-6">
            <h6 class="fw-bold text-white text-uppercase mb-3 small tracking-wide font-streetwear">Dịch Vụ Khách Hàng</h6>
            <ul class="list-unstyled small d-flex flex-column gap-2 text-secondary">
              <li><a href="javascript:;" @click="cartStore.toggleSizeAdvisor(true)" class="text-decoration-none text-secondary footer-link">Bảng Hướng Dẫn Chọn Size</a></li>
              <li><a href="#" class="text-decoration-none text-secondary footer-link">Chính Sách Đổi Trả 30 Ngày</a></li>
              <li><a href="#" class="text-decoration-none text-secondary footer-link">Quy Định Bảo Hành Form Áo</a></li>
              <li><a href="#" class="text-decoration-none text-secondary footer-link">Tra Cứu Tiến Độ Đơn Hàng</a></li>
              <li><a href="#" class="text-decoration-none text-secondary footer-link">Hệ Thống Flagship Store</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6">
            <h6 class="fw-bold text-white text-uppercase mb-3 small tracking-wide font-streetwear">Showroom Đô Thị</h6>
            <ul class="list-unstyled small text-secondary d-flex flex-column gap-2">
              <li><i class='bx bx-map-pin text-urbn-primary me-2'></i> 124 Phố Huế, Hoàn Kiếm, Hà Nội</li>
              <li><i class='bx bx-map-pin text-urbn-primary me-2'></i> 285 Cách Mạng Tháng 8, Q.10, TP.HCM</li>
              <li><i class='bx bx-phone text-urbn-primary me-2'></i> Hotline: 1900 6868 (8:30 - 22:00)</li>
              <li><i class='bx bx-envelope text-urbn-primary me-2'></i> contact@urbnwear.vn</li>
            </ul>
          </div>
        </div>

        <hr class="border-secondary border-opacity-25 my-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center small text-secondary">
          <div>© 2026 URBN WEAR STUDIO. Bản quyền thuộc về thương hiệu thời trang đương đại.</div>
          <div class="d-flex gap-3 mt-2 mt-sm-0">
            <span>Bảo mật</span>
            <span>Điều khoản dịch vụ</span>
            <span>Vận chuyển</span>
          </div>
        </div>

      </div>
    </footer>

    <!-- User Info Modal -->
    <div v-if="showUserModal && currentUser" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
              <i class='bx bxs-user-detail text-urbn-primary fs-4'></i> Thông Tin Tài Khoản
            </h5>
            <button type="button" class="btn-close" @click="showUserModal = false"></button>
          </div>
          <div class="modal-body py-4">
            <div class="text-center mb-4">
              <div class="rounded-circle bg-urbn-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width: 72px; height: 72px;">
                <i class='bx bx-user fs-1 text-urbn-primary'></i>
              </div>
              <h5 class="fw-bold text-dark mb-0">{{ currentUser.ho_va_ten }}</h5>
              <span class="badge rounded-pill mt-1" :class="currentUser.role === 'admin' ? 'bg-danger' : 'bg-primary'">
                {{ currentUser.role === 'admin' ? 'Quản Trị Viên' : 'Thành Viên Chính Thức' }}
              </span>
            </div>

            <div class="list-group list-group-flush rounded-3 border">
              <div class="list-group-item d-flex justify-content-between align-items-center py-2.5">
                <span class="text-muted small"><i class='bx bx-envelope me-2'></i>Email</span>
                <span class="fw-semibold small text-dark">{{ currentUser.email }}</span>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center py-2.5">
                <span class="text-muted small"><i class='bx bx-phone me-2'></i>Số điện thoại</span>
                <span class="fw-semibold small text-dark">{{ currentUser.so_dien_thoai || 'Chưa cập nhật' }}</span>
              </div>
              <div class="list-group-item d-flex justify-content-between align-items-center py-2.5">
                <span class="text-muted small"><i class='bx bx-check-shield me-2'></i>Trạng thái</span>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold">Đang Hoạt Động</span>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" @click="showUserModal = false">Đóng</button>
            <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-semibold" @click="dangXuat">Đăng Xuất</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { cartStore } from '../../stores/cartStore';
import SizeAdvisorModal from '../../components/Client/SizeAdvisorModal.vue';
import axios from 'axios';

export default {
  name: 'ClientLayout',
  components: {
    SizeAdvisorModal
  },
  data() {
    return {
      cartStore,
      searchQuery: '',
      danh_mucs: [],
      isCategoryDropdownOpen: false,
      isMobileMenuOpen: false,
      showUserModal: false,
      currentUser: null
    }
  },
  mounted() {
    this.loadDanhMucs();
    this.checkLoginUser();
  },
  methods: {
    checkLoginUser() {
      const userStr = localStorage.getItem('user') || localStorage.getItem('nguoi_dung');
      if (userStr) {
        try {
          this.currentUser = JSON.parse(userStr);
        } catch (e) {
          this.currentUser = null;
        }
      } else {
        this.currentUser = null;
      }
    },
    dangXuat() {
      const token = localStorage.getItem('auth_token') || localStorage.getItem('nguoi_dung_token') || localStorage.getItem('admin_token');
      axios.post('http://127.0.0.1:8000/api/dang-xuat', {}, {
        headers: { Authorization: `Bearer ${token}` }
      }).finally(() => {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('nguoi_dung_token');
        localStorage.removeItem('admin_token');
        localStorage.removeItem('user');
        localStorage.removeItem('nguoi_dung');
        this.currentUser = null;
        this.$toast.success('Đã đăng xuất thành công!');
        if (this.$route.path.startsWith('/admin')) {
          this.$router.push('/dang-nhap');
        }
      });
    },
    formatMoney(val) {
      if (!val) return '0';
      return Number(val).toLocaleString('vi-VN');
    },
    loadDanhMucs() {
      axios.get('http://127.0.0.1:8000/api/admin/danh-muc/get-data')
        .then(res => {
          if (res.data.status) {
            this.danh_mucs = res.data.data;
          }
        })
        .catch(e => console.error(e));
    },
    chonDanhMuc(dmId) {
      this.isCategoryDropdownOpen = false;
      this.isMobileMenuOpen = false;
      this.$router.push({ path: '/', query: { danh_muc: dmId } }).catch(() => {});
      this.$nextTick(() => {
        const el = document.getElementById('products-section');
        if (el) {
          el.scrollIntoView({ behavior: 'smooth' });
        }
      });
    },
    handleSearch() {
      if (!this.searchQuery.trim()) return;
      this.$router.push({ path: '/', query: { q: this.searchQuery } });
      this.$nextTick(() => {
        const el = document.getElementById('products-section');
        if (el) {
          el.scrollIntoView({ behavior: 'smooth' });
        }
      });
    },
    tienHanhDatHang() {
      this.$toast.success('Đã ghi nhận đơn hàng cho ' + this.cartStore.count + ' sản phẩm!');
      this.cartStore.toggleDrawer(false);
    }
  }
}
</script>

<style scoped>
.top-announcement-bar {
  background-color: var(--urbn-primary);
  letter-spacing: 0.3px;
}
.logo-box {
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.group-logo:hover .logo-box {
  transform: rotate(6deg) scale(1.06);
}
.search-box-wrapper .search-input {
  transition: all 0.25s ease;
  font-size: 14px;
}
.search-box-wrapper .search-input:focus {
  background-color: #ffffff !important;
  border-color: var(--urbn-primary) !important;
  box-shadow: 0 0 0 4px rgba(0, 47, 218, 0.15) !important;
}
.search-btn {
  width: 34px;
  height: 34px;
  transition: transform 0.2s ease;
}
.search-btn:hover {
  transform: translateY(-50%) scale(1.06);
}
.cart-badge-pulse {
  animation: badgePulse 1.8s infinite;
}
@keyframes badgePulse {
  0% { transform: translate(-50%, -50%) scale(1); }
  50% { transform: translate(-50%, -50%) scale(1.25); }
  100% { transform: translate(-50%, -50%) scale(1); }
}

/* Category Dropdown Styling */
.custom-category-dropdown {
  display: block;
  opacity: 0;
  visibility: hidden;
  transform: translateY(8px);
  transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
}
.custom-category-dropdown.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}
.category-dropdown-item {
  transition: all 0.2s ease;
}
.category-dropdown-item:hover {
  background-color: #f1f3f5;
  transform: translateX(4px);
}
.rotate-180 {
  transform: rotate(180deg);
}
.transition-transform {
  transition: transform 0.2s ease;
}
.cursor-pointer {
  cursor: pointer;
}
.hover-opacity:hover {
  opacity: 0.9;
}
.social-btn {
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.social-btn:hover {
  background-color: var(--urbn-primary) !important;
  border-color: var(--urbn-primary) !important;
}
.footer-link:hover {
  color: #ffffff !important;
  padding-left: 4px;
  transition: all 0.2s ease;
}
</style>
