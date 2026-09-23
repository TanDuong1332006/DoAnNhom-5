<template>
    <div class="nav-container primary-menu">
        <div class="mobile-topbar-header">
            <div>
                <img src="../../assets/images/logo-icon.png" class="logo-icon" alt="logo icon">
            </div>
            <div>
                <h4 class="logo-text">FashionStore</h4>
            </div>
            <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
            </div>
        </div>
        <nav class="navbar navbar-expand-xl w-100">
            <ul class="navbar-nav justify-content-start flex-grow-1 gap-1">

                <li class="nav-item">
                    <router-link class="nav-link" to="/admin">
                        <div class="parent-icon"><i class='bx bx-home-alt'></i>
                        </div>
                        <div class="menu-title">Dashboard</div>
                    </router-link>
                </li>

                <li class="nav-item dropdown">
                    <a href="javascript:;" class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                        <div class="parent-icon"><i class='bx bx-closet'></i>
                        </div>
                        <div class="menu-title">Quản Lý Sản Phẩm</div>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <router-link class="dropdown-item" to="/admin/danh-muc">
                                <i class="bx bx-right-arrow-alt"></i>Danh Mục Sản Phẩm
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/admin/thuong-hieu">
                                <i class="bx bx-right-arrow-alt"></i>Thương Hiệu
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/admin/mau-sac">
                                <i class="bx bx-right-arrow-alt"></i>Màu Sắc
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/admin/kich-thuoc">
                                <i class="bx bx-right-arrow-alt"></i>Kích Thước
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/admin/san-pham">
                                <i class="bx bx-right-arrow-alt"></i>Danh Sách Sản Phẩm
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/admin/chi-tiet-san-pham">
                                <i class="bx bx-right-arrow-alt"></i>Biến Thể &amp; Tồn Kho
                            </router-link>
                        </li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a href="javascript:;" class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                        <div class="parent-icon"><i class='bx bx-shopping-bag'></i>
                        </div>
                        <div class="menu-title">Đơn Hàng &amp; Ưu Đãi</div>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <router-link class="dropdown-item" to="/admin/don-hang">
                                <i class="bx bx-right-arrow-alt"></i>Quản Lý Đơn Hàng
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/admin/ma-giam-gia">
                                <i class="bx bx-right-arrow-alt"></i>Mã Giảm Giá (Coupon)
                            </router-link>
                        </li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a href="javascript:;" class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                        <div class="parent-icon"><i class='bx bx-user-pin'></i>
                        </div>
                        <div class="menu-title">Khách Hàng &amp; Phản Hồi</div>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <router-link class="dropdown-item" to="/admin/khach-hang">
                                <i class="bx bx-right-arrow-alt"></i>Danh Sách Khách Hàng
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/admin/danh-gia">
                                <i class="bx bx-right-arrow-alt"></i>Đánh Giá Sản Phẩm
                            </router-link>
                        </li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a href="javascript:;" class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                        <div class="parent-icon"><i class='bx bx-bar-chart-alt-2'></i>
                        </div>
                        <div class="menu-title">Báo Cáo &amp; Thống Kê</div>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <router-link class="dropdown-item" to="/admin/thong-ke/doanh-thu-theo-thang">
                                <i class="bx bx-right-arrow-alt"></i>Doanh Thu Theo Tháng
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/admin/thong-ke/doanh-thu-theo-nam">
                                <i class="bx bx-right-arrow-alt"></i>Doanh Thu Theo Năm
                            </router-link>
                        </li>
                        <li>
                            <router-link class="dropdown-item" to="/admin/thong-ke/san-pham-theo-danh-muc">
                                <i class="bx bx-right-arrow-alt"></i>Thống Kê Theo Danh Mục
                            </router-link>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
</template>

<script>
export default {
    name: 'MenuRocker'
}
</script>

<style></style>