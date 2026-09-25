<template>
  <div class="client-layout-wrapper light-theme bg-white text-dark min-vh-100 d-flex flex-column">

    <!-- 1. Top Announcement Marquee Ticker (SOFEP Luxury Header Style) -->
    <div class="top-announcement-bar py-2.5 px-3 small fw-medium">
      <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center overflow-hidden flex-wrap">
          <span class="d-flex align-items-center gap-2 font-medium tracking-wide">
            <i class='bx bxs-truck fs-5' style="color: #b48325;"></i>
            <span>MIỄN PHÍ VẬN CHUYỂN ĐƠN TỪ 500.000₫ TOÀN QUỐC</span>
          </span>
          <span class="d-none d-md-inline topbar-separator">|</span>
          <span class="d-none d-md-flex align-items-center gap-2">
            <i class='bx bx-refresh fs-5' style="color: #b48325;"></i>
            <span>ĐỔI TRẢ 30 NGÀY TẬN NHÀ MIỄN PHÍ</span>
          </span>
          <span class="d-none d-lg-inline topbar-separator">|</span>
          <span class="d-none d-lg-flex align-items-center gap-2 font-bold" style="color: #92400e;">
            <span>MÃ:</span>
            <code class="px-2.5 py-0.5 rounded font-monospace small topbar-coupon-code">SOFEPNEW</code>
            <span>GIẢM 50.000₫</span>
          </span>
        </div>
        
        <!-- Size Guide Quick Button -->
        <button @click="cartStore.toggleSizeAdvisor(true)"
                class="btn btn-sm btn-light bg-white border border-warning border-opacity-25 rounded-pill px-3.5 py-1.5 d-flex align-items-center gap-1.5 ms-auto ms-sm-0 shadow-2xs hover-gold text-dark">
          <i class='bx bx-ruler' style="color: #b48325;"></i>
          <span class="small fw-semibold">Bảng Chọn Size</span>
        </button>
      </div>
    </div>

    <!-- 2. Main Navigation Header Bar -->
    <header class="main-header sticky-top bg-white border-bottom shadow-sm">
      <div class="container py-3">
        <div class="d-flex align-items-center justify-content-between gap-3 gap-lg-4">

          <!-- Brand Logo & Mobile Toggle -->
          <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <!-- Mobile Menu Toggle Button -->
            <button class="btn btn-light d-lg-none p-2 border-0 me-1" type="button" @click="isMobileMenuOpen = !isMobileMenuOpen">
              <i class='bx bx-menu fs-3 text-dark'></i>
            </button>

            <!-- SOFEP Luxury Logo -->
            <router-link to="/" class="d-flex align-items-center text-decoration-none group-logo">
              <SofepLogo size="md" variant="light" subtitle="STUDIO 2026" />
            </router-link>
          </div>

          <!-- Live Search Bar (Co giãn linh hoạt ở giữa) -->
          <div class="flex-grow-1 mx-2 mx-md-4 d-none d-sm-block" style="max-width: 540px;">
            <div class="position-relative search-box-wrapper">
              <input v-model="searchQuery" @keyup.enter="handleSearch" type="text"
                class="form-control rounded-pill bg-light border ps-4 pe-5 py-2 search-input shadow-none"
                placeholder="Tìm áo thun boxy, hoodie utility, quần parachute cargo...">
              <button @click="handleSearch" class="btn btn-urbn-dark text-white rounded-circle position-absolute top-50 end-0 translate-middle-y me-1.5 p-0 d-flex align-items-center justify-content-center search-btn">
                <i class='bx bx-search fs-5'></i>
              </button>
            </div>
          </div>

          <!-- Right Action Buttons (Cực kỳ gọn gàng trên 1 hàng duy nhất) -->
          <div class="d-flex align-items-center justify-content-end gap-3 flex-shrink-0">
            
            <!-- Cart Trigger Button -->
            <button @click="cartStore.toggleDrawer(true)" class="btn btn-light rounded-circle position-relative border-0 shadow-sm p-2 action-icon-btn" title="Giỏ hàng">
              <i class='bx bx-shopping-bag fs-4 text-dark'></i>
              <span v-if="cartStore.count > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-urbn-primary border border-white cart-badge-pulse">
                {{ cartStore.count }}
              </span>
            </button>

            <!-- Account User Dropdown (Bấm vào Avatar hiện Đăng Ký, Đăng Nhập, Đăng Xuất) -->
            <div class="dropdown d-inline-block position-relative" ref="userDropdownRef">
              <button class="btn btn-light rounded-pill border-0 shadow-sm px-3 py-1.5 d-flex align-items-center gap-2 cursor-pointer user-dropdown-btn"
                      @click="toggleUserDropdown"
                      type="button">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                  <i class='bx bx-user fs-5'></i>
                </div>
                <span v-if="currentUser" class="small fw-bold text-dark d-none d-sm-inline text-truncate" style="max-width: 120px;">
                  {{ currentUser.ho_va_ten }}
                </span>
                <span v-else class="small fw-bold text-dark d-none d-sm-inline">
                  Tài khoản
                </span>
                <i class='bx bx-chevron-down text-muted small ms-1 transition-transform' :class="{ 'rotate-180': isUserDropdownOpen }"></i>
              </button>

              <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2 custom-user-dropdown"
                  :class="{ 'show': isUserDropdownOpen }"
                  :style="isUserDropdownOpen ? 'display: block; position: absolute; right: 0; top: 100%; z-index: 1060;' : 'display: none;'"
                  style="min-width: 250px;">
                <!-- Header thông tin tài khoản -->
                <li v-if="currentUser" class="px-3 py-2 border-bottom mb-2 bg-light rounded-3">
                  <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                      <i class='bx bx-user fs-5'></i>
                    </div>
                    <div class="overflow-hidden">
                      <div class="small fw-bold text-dark text-truncate">{{ currentUser.ho_va_ten }}</div>
                      <span class="badge" :class="currentUser.role === 'admin' ? 'bg-danger' : 'bg-primary'" style="font-size: 10px;">
                        {{ currentUser.role === 'admin' ? 'Quản Trị Viên' : 'Thành Viên' }}
                      </span>
                    </div>
                  </div>
                  <div class="text-muted small text-truncate mt-1" style="font-size: 11px;">{{ currentUser.email }}</div>
                </li>
                <li v-else class="px-3 py-2 border-bottom mb-2 bg-light rounded-3">
                  <div class="small fw-bold text-dark">Tài Khoản SOFEP</div>
                  <div class="text-muted small" style="font-size: 11px;">Đăng nhập hoặc đăng ký tài khoản mới</div>
                </li>

                <!-- Admin & Profile Links -->
                <li v-if="currentUser && currentUser.role === 'admin'">
                  <router-link class="dropdown-item py-2 small fw-semibold text-danger rounded-2" to="/admin" @click="isUserDropdownOpen = false">
                    <i class='bx bx-shield-quarter me-2 fs-5 align-middle'></i>Bảng Điều Khiển Admin
                  </router-link>
                </li>
                <li v-if="currentUser && currentUser.role !== 'admin'">
                  <a class="dropdown-item py-2 small fw-semibold text-dark rounded-2" href="javascript:;" @click="showUserModal = true; isUserDropdownOpen = false;">
                    <i class='bx bx-id-card me-2 text-primary fs-5 align-middle'></i>Thông Tin Tài Khoản
                  </a>
                </li>

                <li><hr class="dropdown-divider my-1"></li>

                <!-- Nút ĐĂNG NHẬP (Luôn hiển thị) -->
                <li>
                  <router-link class="dropdown-item py-2 small fw-semibold text-dark rounded-2" to="/dang-nhap" @click="isUserDropdownOpen = false">
                    <i class='bx bx-log-in me-2 text-primary fs-5 align-middle'></i>
                    <span>{{ currentUser ? 'Đăng Nhập (Tài Khoản Khác)' : 'Đăng Nhập' }}</span>
                  </router-link>
                </li>

                <!-- Nút ĐĂNG KÝ (Luôn hiển thị) -->
                <li>
                  <router-link class="dropdown-item py-2 small fw-semibold text-dark rounded-2" to="/dang-ky" @click="isUserDropdownOpen = false">
                    <i class='bx bx-user-plus me-2 text-success fs-5 align-middle'></i>
                    <span>Đăng Ký Tài Khoản Mới</span>
                  </router-link>
                </li>

                <!-- Nút ĐĂNG XUẤT (khi đang đăng nhập) -->
                <template v-if="currentUser">
                  <li><hr class="dropdown-divider my-1"></li>
                  <li>
                    <a class="dropdown-item py-2 small fw-bold text-danger bg-danger bg-opacity-10 rounded-2" href="javascript:;" @click="dangXuat(); isUserDropdownOpen = false;">
                      <i class='bx bx-log-out me-2 fs-5 align-middle'></i>Đăng Xuất
                    </a>
                  </li>
                </template>

                <li><hr class="dropdown-divider my-1"></li>
                <li>
                  <a class="dropdown-item py-1.5 small text-muted rounded-2" href="tel:19006868" @click="isUserDropdownOpen = false">
                    <i class='bx bx-help-circle me-2 align-middle'></i>Hotline 1900 6868
                  </a>
                </li>
              </ul>
            </div>

          </div>

        </div>

        <!-- Search bar mobile -->
        <div class="d-block d-sm-none mt-2">
          <div class="position-relative search-box-wrapper">
            <input v-model="searchQuery" @keyup.enter="handleSearch" type="text"
              class="form-control form-control-sm rounded-pill bg-light border ps-3 pe-5 py-1.5 search-input shadow-none"
              placeholder="Tìm kiếm sản phẩm...">
            <button @click="handleSearch" class="btn btn-urbn-dark text-white rounded-circle position-absolute top-50 end-0 translate-middle-y me-1 p-0 d-flex align-items-center justify-content-center search-btn" style="width: 28px; height: 28px;">
              <i class='bx bx-search small'></i>
            </button>
          </div>
        </div>

      </div>

      <!-- 3. Desktop Sub-Navbar Navigation Menu -->
      <nav class="sub-navbar border-top bg-white d-none d-lg-block">
        <div class="container">
          <ul class="nav nav-pills align-items-center gap-2 gap-xl-3 py-2">
            <li class="nav-item">
              <router-link to="/" class="nav-link sub-nav-pill text-dark fw-bold" active-class="active-sub-nav">
                <i class='bx bx-home-alt me-1.5'></i> Trang Chủ
              </router-link>
            </li>

            <!-- Category Dropdown with Vue State (NO dropdown-toggle to eliminate duplicate arrow) -->
            <li class="nav-item dropdown position-relative"
                @mouseenter="isCategoryDropdownOpen = true"
                @mouseleave="isCategoryDropdownOpen = false">
              <button class="nav-link sub-nav-pill text-dark fw-bold border-0 bg-transparent d-flex align-items-center cursor-pointer"
                      @click="dieuHuongTatCaDanhMuc"
                      type="button">
                <i class='bx bx-category me-1.5 text-urbn-primary fs-5'></i>
                <span>Danh Mục Thời Trang</span>
                <i class='bx bx-chevron-down ms-1.5 transition-transform' :class="{ 'rotate-180': isCategoryDropdownOpen }"></i>
              </button>
              
              <!-- Dropdown Menu -->
              <div class="dropdown-menu shadow-lg border-0 rounded-4 py-2 custom-category-dropdown"
                   :class="{ 'show': isCategoryDropdownOpen }"
                   style="min-width: 290px;">
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
                   @click="chonDanhMuc(dm)">
                  <img :src="dm.hinh_anh || 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=80'" class="rounded-circle border object-fit-cover" style="width: 32px; height: 32px; object-fit: cover;">
                  <span class="fw-bold text-dark flex-grow-1 small">{{ dm.ten_danh_muc }}</span>
                  <span class="badge bg-light text-muted border rounded-pill small" style="font-size: 10px;">
                    {{ dm.san_phams_count || 0 }} mẫu
                  </span>
                </a>
              </div>
            </li>

            <li class="nav-item">
              <a class="nav-link sub-nav-pill text-danger fw-bold cursor-pointer" href="javascript:;" @click="dieuHuongFlashSale">
                <i class='bx bxs-zap text-danger me-1.5'></i> Flash Sale -30%
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link sub-nav-pill text-dark fw-semibold cursor-pointer" href="javascript:;" @click="dieuHuongSanPhamMoi">
                <i class='bx bx-star text-warning me-1.5'></i> Sản Phẩm Mới Drop 04
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link sub-nav-pill text-dark fw-semibold cursor-pointer" href="javascript:;" @click="dieuHuongDanhGia">
                <i class='bx bx-chat text-info me-1.5'></i> Đánh Giá Cộng Đồng
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
          <!-- User info & logout on mobile -->
          <div v-if="currentUser" class="p-2.5 bg-light rounded-3 border mb-1 d-flex align-items-center justify-content-between">
            <div class="overflow-hidden">
              <div class="fw-bold text-dark text-truncate small">{{ currentUser.ho_va_ten }}</div>
              <span class="badge" :class="currentUser.role === 'admin' ? 'bg-danger' : 'bg-primary'" style="font-size: 10px;">
                {{ currentUser.role === 'admin' ? 'Quản Trị Viên' : 'Thành Viên' }}
              </span>
            </div>
            <button @click="dangXuat(); isMobileMenuOpen = false;" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1">
              <i class='bx bx-log-out me-1'></i>Đăng Xuất
            </button>
          </div>

          <!-- Direct mobile auth buttons if guest -->
          <div v-else class="d-flex gap-2 mb-1">
            <router-link to="/dang-nhap" @click="isMobileMenuOpen = false" class="btn btn-outline-dark btn-sm rounded-pill flex-grow-1 fw-bold py-1.5">
              <i class='bx bx-log-in me-1'></i>Đăng Nhập
            </router-link>
            <router-link to="/dang-ky" @click="isMobileMenuOpen = false" class="btn btn-urbn-primary btn-sm rounded-pill flex-grow-1 fw-bold text-white py-1.5 shadow-sm">
              <i class='bx bx-user-plus me-1'></i>Đăng Ký
            </router-link>
          </div>
          <router-link to="/" @click="isMobileMenuOpen = false" class="btn btn-light text-start fw-bold py-2 rounded-3">
            <i class='bx bx-home-alt me-2 text-urbn-primary'></i> Trang Chủ
          </router-link>
          <a href="javascript:;" @click="dieuHuongTatCaDanhMuc" class="btn btn-light text-start fw-bold py-2 rounded-3">
            <i class='bx bx-category me-2 text-urbn-primary'></i> Danh Mục Thời Trang
          </a>
          <a href="javascript:;" @click="dieuHuongFlashSale" class="btn btn-light text-start fw-bold py-2 rounded-3 text-danger">
            <i class='bx bxs-zap me-2 text-danger'></i> Flash Sale -30%
          </a>
          <a href="javascript:;" @click="dieuHuongSanPhamMoi" class="btn btn-light text-start fw-bold py-2 rounded-3">
            <i class='bx bx-star me-2 text-warning'></i> Sản Phẩm Mới Drop 04
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
      
      <!-- Drawer Header (Bright Luxury Style) -->
      <div class="cart-drawer-header p-3 bg-white border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: rgba(180, 131, 37, 0.12);">
            <i class='bx bx-shopping-bag fs-5' style="color: #b48325;"></i>
          </div>
          <h5 class="mb-0 fw-bold font-streetwear small tracking-wide text-dark">GIỎ HÀNG CỦA BẠN ({{ cartStore.count }})</h5>
        </div>
        <button @click="cartStore.toggleDrawer(false)" class="btn btn-light rounded-circle p-1 text-decoration-none shadow-none">
          <i class='bx bx-x fs-4 text-dark'></i>
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
          <button @click="xoaToanBoGioHang" class="btn btn-link btn-sm text-muted text-decoration-none">
            Xóa toàn bộ giỏ hàng
          </button>
        </div>
      </div>
    </div>

    <!-- 5. Interactive Size Advisor Modal Component -->
    <SizeAdvisorModal />

    <!-- 5b. Checkout & VietQR MB Bank Payment Modal Component -->
    <CheckoutModal />

    <!-- 6. Footer (SOFEP Luxury Light Atelier Style) -->
    <footer class="pt-5 pb-4 mt-5 border-top" style="background-color: #f9f8f5; border-color: #f1ede6 !important;">
      <div class="container">
        
        <!-- Newsletter Box (Bright Pearl White with Champagne Border) -->
        <div class="p-4 p-md-5 rounded-4 mb-5 border shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #fffdfa 60%, #fef8eb 100%); border-color: rgba(180, 131, 37, 0.25) !important;">
          <div class="row align-items-center gy-3">
            <div class="col-lg-6">
              <span class="badge bg-warning bg-opacity-15 text-dark rounded-pill px-3 py-1 mb-2 fw-bold small border border-warning border-opacity-25" style="color: #78350f !important;">
                <i class='bx bxs-crown me-1' style="color: #b48325;"></i>SOFEP CLUB VIP
              </span>
              <h3 class="fw-extrabold mb-1 font-streetwear text-dark">Đăng Ký Nhận Voucher 50.000₫</h3>
              <p class="mb-0 text-secondary small">Nhận thông báo sớm nhất về các bộ sưu tập Haute Couture mới, mã giảm giá độc quyền và sự kiện thời trang.</p>
            </div>
            <div class="col-lg-6">
              <div class="input-group input-group-lg bg-white rounded-pill p-1 border shadow-xs" style="border-color: rgba(180, 131, 37, 0.25) !important;">
                <input type="email" class="form-control border-0 bg-transparent text-dark ps-3 shadow-none small" placeholder="Nhập địa chỉ email của bạn...">
                <button class="btn btn-urbn-primary rounded-pill px-4 fw-bold text-white text-nowrap">Đăng Ký</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Columns -->
        <div class="row g-4 mb-4">
          <div class="col-lg-4 col-md-6">
            <router-link to="/" class="d-inline-flex align-items-center text-decoration-none mb-3">
              <SofepLogo size="md" variant="light" subtitle="STUDIO 2026" />
            </router-link>
            <p class="text-secondary small mb-3" style="line-height: 1.6;">
              Thương hiệu thời trang cao cấp đương đại SOFEP định hình phong cách sang trọng, thời thượng. Mỗi sản phẩm được phát triển với chất lượng hoàn thiện chuẩn mực cao nhất.
            </p>
            <div class="d-flex align-items-center gap-2.5 mt-2 social-btn-group">
              <!-- Facebook -->
              <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" 
                 class="social-btn social-fb" title="Facebook SOFEP Haute Atelier">
                <i class='bx bxl-facebook fs-5'></i>
              </a>
              <!-- Instagram -->
              <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" 
                 class="social-btn social-insta" title="Instagram @sofep.studio">
                <i class='bx bxl-instagram fs-5'></i>
              </a>
              <!-- Zalo -->
              <a href="https://zalo.me" target="_blank" rel="noopener noreferrer" 
                 class="social-btn social-zalo" title="Chat Zalo Tư Vấn SOFEP">
                <svg viewBox="0 0 32 32" width="22" height="22" xmlns="http://www.w3.org/2000/svg">
                  <path class="zalo-bubble" d="M16 3.5C8.82 3.5 3 8.87 3 15.5c0 3.5 1.63 6.64 4.25 8.85L5.8 28.8a.7.7 0 00.95.87l5.22-2.19c1.28.34 2.63.52 4.03.52 7.18 0 13-5.37 13-12S23.18 3.5 16 3.5z"/>
                  <text class="zalo-text" x="16" y="19" font-family="'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, Arial, sans-serif" font-weight="900" font-size="8.8" text-anchor="middle" letter-spacing="-0.4">Zalo</text>
                </svg>
              </a>
            </div>
          </div>

          <div class="col-lg-2 col-md-6 col-6">
            <h6 class="fw-bold text-dark text-uppercase mb-3 small tracking-wide font-streetwear">Bộ Sưu Tập</h6>
            <ul class="list-unstyled small d-flex flex-column gap-2 text-secondary">
              <li><router-link to="/tim-kiem?danh_muc=1&ten=Áo+Nam" class="text-decoration-none text-secondary footer-link">Áo Nam Cao Cấp</router-link></li>
              <li><router-link to="/tim-kiem?danh_muc=2&ten=Quần+Nam" class="text-decoration-none text-secondary footer-link">Quần Nam Savile Row</router-link></li>
              <li><router-link to="/tim-kiem?danh_muc=3&ten=Váy+%26+Đầm+Nữ" class="text-decoration-none text-secondary footer-link">Váy &amp; Đầm Dạ Hội</router-link></li>
              <li><router-link to="/tim-kiem?danh_muc=5&ten=Áo+Khoác+%26+Blazer" class="text-decoration-none text-secondary footer-link">Áo Khoác &amp; Blazer</router-link></li>
              <li><router-link to="/tim-kiem?danh_muc=8&ten=Phụ+Kiện+Thời+Trang" class="text-decoration-none text-secondary footer-link">Phụ Kiện Thời Trang</router-link></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 col-6">
            <h6 class="fw-bold text-dark text-uppercase mb-3 small tracking-wide font-streetwear">Dịch Vụ Khách Hàng</h6>
            <ul class="list-unstyled small d-flex flex-column gap-2 text-secondary">
              <li><a href="javascript:;" @click="cartStore.toggleSizeAdvisor(true)" class="text-decoration-none text-secondary footer-link">Bảng Hướng Dẫn Chọn Size</a></li>
              <li><a href="#" class="text-decoration-none text-secondary footer-link">Chính Sách Đổi Trả 30 Ngày</a></li>
              <li><a href="#" class="text-decoration-none text-secondary footer-link">Quy Định Bảo Hành Form Áo</a></li>
              <li><a href="#" class="text-decoration-none text-secondary footer-link">Tra Cứu Tiến Độ Đơn Hàng</a></li>
              <li><a href="#" class="text-decoration-none text-secondary footer-link">Hệ Thống Flagship Store</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6">
            <h6 class="fw-bold text-dark text-uppercase mb-3 small tracking-wide font-streetwear">Showroom Đô Thị</h6>
            <ul class="list-unstyled small text-secondary d-flex flex-column gap-2">
              <li><i class='bx bx-map-pin me-2' style="color: #b48325;"></i> 124 Phố Huế, Hoàn Kiếm, Hà Nội</li>
              <li><i class='bx bx-map-pin me-2' style="color: #b48325;"></i> 285 Cách Mạng Tháng 8, Q.10, TP.HCM</li>
              <li><i class='bx bx-phone me-2' style="color: #b48325;"></i> Hotline: 1900 6868 (8:30 - 22:00)</li>
              <li><i class='bx bx-envelope me-2' style="color: #b48325;"></i> contact@sofep.vn</li>
            </ul>
          </div>
        </div>

        <hr class="my-4" style="border-color: #f1ede6;">

        <div class="d-flex flex-wrap justify-content-between align-items-center small text-secondary">
          <div>© 2026 SOFEP STUDIO. Bản quyền thuộc về thương hiệu thời trang cao cấp.</div>
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
import CheckoutModal from '../../components/Client/CheckoutModal.vue';
import axios from 'axios';

export default {
  name: 'ClientLayout',
  components: {
    SizeAdvisorModal,
    CheckoutModal
  },
  data() {
    return {
      cartStore,
      searchQuery: '',
      danh_mucs: [],
      isCategoryDropdownOpen: false,
      isMobileMenuOpen: false,
      showUserModal: false,
      isUserDropdownOpen: false,
      currentUser: null
    }
  },
  mounted() {
    this.loadDanhMucs();
    this.checkLoginUser();
    document.addEventListener('click', this.handleUserDropdownClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleUserDropdownClickOutside);
  },
  methods: {
    toggleUserDropdown(e) {
      if (e) e.stopPropagation();
      this.isUserDropdownOpen = !this.isUserDropdownOpen;
    },
    handleUserDropdownClickOutside(event) {
      if (this.$refs.userDropdownRef && !this.$refs.userDropdownRef.contains(event.target)) {
        this.isUserDropdownOpen = false;
      }
    },
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
    chonDanhMuc(dm) {
      this.isCategoryDropdownOpen = false;
      this.isMobileMenuOpen = false;
      if (!dm || dm === 'tat-ca') {
        this.$router.push({ path: '/tim-kiem' }).catch(() => {});
      } else {
        const id = (typeof dm === 'object') ? dm.id : dm;
        const name = (typeof dm === 'object') ? dm.ten_danh_muc : '';
        const slug = (typeof dm === 'object') ? dm.slug_danh_muc : '';
        this.$router.push({
          path: '/tim-kiem',
          query: { danh_muc: id, ten: name, slug: slug }
        }).catch(() => {});
      }
    },
    dieuHuongTatCaDanhMuc() {
      this.isCategoryDropdownOpen = false;
      this.isMobileMenuOpen = false;
      this.$router.push({ path: '/tim-kiem' }).catch(() => {});
    },
    dieuHuongFlashSale() {
      this.isCategoryDropdownOpen = false;
      this.isMobileMenuOpen = false;
      this.$router.push({ path: '/tim-kiem', query: { flash_sale: '1' } }).catch(() => {});
    },
    dieuHuongSanPhamMoi() {
      this.isCategoryDropdownOpen = false;
      this.isMobileMenuOpen = false;
      this.$router.push({ path: '/tim-kiem', query: { moi_nhat: '1' } }).catch(() => {});
    },
    dieuHuongDanhGia() {
      this.isCategoryDropdownOpen = false;
      this.isMobileMenuOpen = false;
      if (this.$route.path !== '/') {
        this.$router.push({ path: '/', hash: '#reviews-section' }).catch(() => {});
      } else {
        const el = document.getElementById('reviews-section');
        if (el) el.scrollIntoView({ behavior: 'smooth' });
      }
    },
    handleSearch() {
      if (!this.searchQuery.trim()) return;
      this.$router.push({ path: '/tim-kiem', query: { q: this.searchQuery.trim() } });
    },
    tienHanhDatHang() {
      if (this.cartStore.items.length === 0) {
        if (this.$toast) {
          this.$toast.warning('Giỏ hàng của bạn đang trống!');
        } else {
          alert('Giỏ hàng trống!');
        }
        return;
      }
      this.cartStore.toggleDrawer(false);
      this.cartStore.toggleCheckout(true);
    },
    async xoaToanBoGioHang() {
      const confirmed = await this.$confirmDelete('Bạn có chắc muốn xóa sạch toàn bộ sản phẩm khỏi giỏ hàng?', 'Xóa Giỏ Hàng');
      if (confirmed) {
        this.cartStore.clearCart();
        if (this.$toast) this.$toast.info('Đã xóa toàn bộ giỏ hàng');
      }
    }
  }
}
</script>

<style scoped>
.top-announcement-bar {
  background: var(--sofep-topbar-gradient);
  border-bottom: 1px solid rgba(180, 131, 37, 0.18);
  color: #78350f;
  letter-spacing: 0.3px;
}

.topbar-separator {
  margin: 0 12px;
  opacity: 0.35;
  color: #b48325;
}

.topbar-coupon-code {
  background-color: rgba(180, 131, 37, 0.15);
  color: #78350f;
  font-weight: 700;
  border: 1px dashed rgba(180, 131, 37, 0.4);
}

.user-dropdown-btn {
  transition: all 0.2s ease;
}

.user-dropdown-btn:hover {
  background-color: #fef8ee !important;
  color: #b48325 !important;
}

/* Sub-Navbar Navigation Pills - Tách biệt rộng rãi, sang xịn mịn */
.sub-nav-pill {
  padding: 8px 18px !important;
  border-radius: 999px !important;
  transition: all 0.22s ease !important;
  font-size: 13.5px;
  display: inline-flex;
  align-items: center;
  border: 1px solid transparent;
}

.sub-nav-pill:hover {
  background-color: #fef8ee !important;
  color: #b48325 !important;
  border-color: rgba(180, 131, 37, 0.25) !important;
  transform: translateY(-1px);
}

.active-sub-nav {
  background: var(--sofep-gold-gradient) !important;
  color: #ffffff !important;
  border-color: transparent !important;
  box-shadow: 0 4px 12px rgba(180, 131, 37, 0.25) !important;
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
  border-color: var(--sofep-gold) !important;
  box-shadow: 0 0 0 4px rgba(180, 131, 37, 0.15) !important;
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
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
  border: 1px solid #f1ede6 !important;
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
  background-color: #fef8eb;
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
.social-btn-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.social-btn {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #ffffff;
  border: 1.5px solid rgba(180, 131, 37, 0.22);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  transition: all 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
  cursor: pointer;
}

.social-btn:hover {
  transform: translateY(-3px) scale(1.08);
}

/* Facebook Styling */
.social-fb {
  color: #1877f2;
}
.social-fb:hover {
  background: #1877f2 !important;
  color: #ffffff !important;
  border-color: #1877f2 !important;
  box-shadow: 0 6px 18px rgba(24, 119, 242, 0.35);
}

/* Instagram Styling */
.social-insta {
  color: #e1306c;
}
.social-insta:hover {
  background: linear-gradient(135deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%) !important;
  color: #ffffff !important;
  border-color: transparent !important;
  box-shadow: 0 6px 18px rgba(225, 48, 108, 0.35);
}

/* Zalo Styling */
.social-zalo {
  color: #0068ff;
}
.social-zalo:hover {
  background: #0068ff !important;
  border-color: #0068ff !important;
  box-shadow: 0 6px 18px rgba(0, 104, 255, 0.35);
}
.social-zalo .zalo-bubble {
  fill: #0068ff;
  transition: fill 0.25s ease;
}
.social-zalo .zalo-text {
  fill: #ffffff;
  transition: fill 0.25s ease;
}
.social-zalo:hover .zalo-bubble {
  fill: #ffffff;
}
.social-zalo:hover .zalo-text {
  fill: #0068ff;
}
.footer-link:hover {
  color: var(--sofep-gold) !important;
  padding-left: 4px;
  transition: all 0.2s ease;
}
</style>
