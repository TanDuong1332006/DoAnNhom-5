<template>
    <div class="pb-4">
        <!-- Hero Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-3 border-primary border-top shadow rounded-4 overflow-hidden text-white">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center gy-3">
                            <div class="col-lg-8">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge bg-white text-primary fw-bold rounded-pill px-3 py-2">
                                        <i class='bx bx-shopping-bag me-1'></i> FASHION STORE ADMIN
                                    </span>
                                    <span class="badge bg-success rounded-pill px-3 py-2 fw-semibold">
                                        <i class='bx bx-check-circle me-1'></i> Hệ Thống Hoạt Động BÌNH THƯỜNG
                                    </span>
                                </div>
                                <h2 class="fw-bold mb-2 text-dark">Bảng Điều Khiển &amp; Thống Kê Tổng Quan</h2>
                                <p class="mb-0 opacity-75 text-secondary">
                                    Quản lý Bán hàng &amp; Thống kê Doanh thu Thời trang — Theo dõi
                                    <strong>Đơn hàng</strong>, <strong>Sản phẩm</strong>,
                                    <strong>Khách hàng</strong> &amp; <strong>Doanh số</strong> trong thời gian thực.
                                </p>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                    <router-link to="/admin/san-pham"
                                        class="btn btn-primary fw-bold rounded-3 px-4 py-2 shadow-sm text-white">
                                        <i class='bx bx-plus-circle me-2'></i> Thêm Sản Phẩm
                                    </router-link>
                                    <router-link to="/admin/don-hang"
                                        class="btn btn-secondary fw-semibold rounded-3 px-4 py-2 text-white">
                                        <i class='bx bx-receipt me-2'></i> Xem Đơn Hàng
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 border-top border-primary border-3 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <p class="text-muted small fw-semibold text-uppercase mb-1">Tổng Doanh Thu</p>
                                <h3 class="fw-bold text-primary mb-0">{{ formatTien(thong_ke.tong_doanh_thu) }} đ</h3>
                            </div>
                            <i class='bx bx-dollar-circle text-primary' style="font-size:2.5rem"></i>
                        </div>
                        <div class="progress rounded-pill mb-2" style="height:4px">
                            <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: 85%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class='bx bx-trending-up text-success me-1'></i>Thực thu đơn hoàn thành</span>
                            <router-link to="/admin/thong-ke/doanh-thu-theo-thang" class="text-primary small fw-semibold text-decoration-none">
                                Chi tiết <i class='bx bx-right-arrow-alt'></i>
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 border-top border-success border-3 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <p class="text-muted small fw-semibold text-uppercase mb-1">Tổng Đơn Hàng</p>
                                <h3 class="fw-bold text-success mb-0">{{ thong_ke.tong_don_hang }}</h3>
                            </div>
                            <i class='bx bx-cart-alt text-success' style="font-size:2.5rem"></i>
                        </div>
                        <div class="progress rounded-pill mb-2" style="height:4px">
                            <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: 75%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class='bx bx-bell text-warning me-1'></i>{{ thong_ke.don_hang_moi }} đơn mới</span>
                            <router-link to="/admin/don-hang" class="text-success small fw-semibold text-decoration-none">
                                Xem <i class='bx bx-right-arrow-alt'></i>
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 border-top border-warning border-3 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <p class="text-muted small fw-semibold text-uppercase mb-1">Tổng Sản Phẩm</p>
                                <h3 class="fw-bold text-warning mb-0">{{ thong_ke.tong_san_pham }}</h3>
                            </div>
                            <i class='bx bx-closet text-warning' style="font-size:2.5rem"></i>
                        </div>
                        <div class="progress rounded-pill mb-2" style="height:4px">
                            <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: 90%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class='bx bx-check text-success me-1'></i>Đang mở bán</span>
                            <router-link to="/admin/san-pham" class="text-warning small fw-semibold text-decoration-none">
                                Xem <i class='bx bx-right-arrow-alt'></i>
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 border-top border-info border-3 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <p class="text-muted small fw-semibold text-uppercase mb-1">Khách Hàng</p>
                                <h3 class="fw-bold text-info mb-0">{{ thong_ke.tong_khach_hang }}</h3>
                            </div>
                            <i class='bx bx-user-check text-info' style="font-size:2.5rem"></i>
                        </div>
                        <div class="progress rounded-pill mb-2" style="height:4px">
                            <div class="progress-bar bg-info rounded-pill" role="progressbar" style="width: 65%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class='bx bx-user text-primary me-1'></i>Tài khoản hoạt động</span>
                            <router-link to="/admin/khach-hang" class="text-info small fw-semibold text-decoration-none">
                                Xem <i class='bx bx-right-arrow-alt'></i>
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chart and Tables -->
        <div class="row g-4">
            <!-- Left Side: Quick Links & Top Products -->
            <div class="col-xl-4">
                <!-- Don Hang Gan Day -->
                <div class="card border-3 border-primary border-top border-start shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center px-4 py-3">
                        <span class="fw-bold text-dark d-flex align-items-center gap-2">
                            <i class='bx bx-receipt text-primary fs-5'></i> ĐƠN HÀNG GẦN ĐÂY
                        </span>
                        <router-link to="/admin/don-hang" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold text-white">Tất cả</router-link>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="!thong_ke.don_hang_gan_day || thong_ke.don_hang_gan_day.length === 0" class="text-center py-5 text-muted">
                            <i class='bx bx-package d-block mb-2' style="font-size:2.5rem;opacity:0.25"></i>
                            <small>Chưa có đơn hàng nào</small>
                        </div>
                        <div v-else class="list-group list-group-flush">
                            <div v-for="(dh, idx) in thong_ke.don_hang_gan_day" :key="idx" class="list-group-item px-4 py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-primary small">{{ dh.ma_don_hang }}</span>
                                    <span class="badge" :class="badgeTrangThai(dh.trang_thai_don_hang)">
                                        {{ textTrangThai(dh.trang_thai_don_hang) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-dark small fw-semibold">{{ dh.ho_ten }}</span>
                                    <span class="text-danger fw-bold small">{{ formatTien(dh.tong_thanh_toan) }} đ</span>
                                </div>
                                <small class="text-muted"><i class='bx bx-time me-1'></i>{{ dh.created_at ? dh.created_at.slice(0, 10) : '' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fast Links -->
                <div class="card border-3 border-primary border-start border-bottom shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom px-4 py-3">
                        <span class="fw-bold text-dark d-flex align-items-center gap-2">
                            <i class='bx bx-line-chart text-info fs-5'></i> BÁO CÁO THỐNG KÊ
                        </span>
                    </div>
                    <div class="list-group list-group-flush">
                        <router-link to="/admin/thong-ke/doanh-thu-theo-thang"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="d-flex align-items-center gap-2 text-dark fw-medium small">
                                <i class='bx bx-bar-chart-alt-2 text-primary fs-5'></i> Doanh Thu 12 Tháng
                            </span>
                            <i class='bx bx-chevron-right text-muted'></i>
                        </router-link>
                        <router-link to="/admin/thong-ke/doanh-thu-theo-nam"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="d-flex align-items-center gap-2 text-dark fw-medium small">
                                <i class='bx bx-trending-up text-success fs-5'></i> Tăng Trưởng Doanh Thu Theo Năm
                            </span>
                            <i class='bx bx-chevron-right text-muted'></i>
                        </router-link>
                        <router-link to="/admin/thong-ke/san-pham-theo-danh-muc"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-4 py-3">
                            <span class="d-flex align-items-center gap-2 text-dark fw-medium small">
                                <i class='bx bx-pie-chart-alt-2 text-warning fs-5'></i> Cơ Cấu Sản Phẩm / Danh Mục
                            </span>
                            <i class='bx bx-chevron-right text-muted'></i>
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Right Side: Chart Doanh Thu Theo Thang & Top Products -->
            <div class="col-xl-8">
                <!-- Bar Chart Preview -->
                <div class="card border-3 border-primary border-top border-end shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center px-4 py-3">
                        <span class="fw-bold text-dark d-flex align-items-center gap-2">
                            <i class='bx bx-bar-chart-alt-2 text-primary fs-5'></i> BIỂU ĐỒ DOANH THU 12 THÁNG (NĂM 2026)
                        </span>
                        <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold text-white">
                            <i class='bx bx-data me-1'></i> Dữ liệu thực
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="bg-light rounded-4 p-4">
                            <div v-if="chart_thang.length === 0" class="text-center py-5 text-muted">
                                <i class='bx bx-loader-circle bx-spin text-primary d-block mb-2' style="font-size:2.5rem"></i>
                                <small class="fw-semibold">Đang tải biểu đồ doanh thu...</small>
                            </div>
                            <div v-else class="d-flex align-items-end justify-content-around gap-2" style="height:230px">
                                <div v-for="(item, idx) in chart_thang" :key="idx"
                                    class="flex-fill text-center d-flex flex-column align-items-center justify-content-end h-100">
                                    <span v-if="item.tong_doanh_thu > 0" class="badge text-white fw-bold rounded-pill px-1 py-1 mb-2 small shadow-sm"
                                        style="font-size: 10px; background-color: #0d6efd;">
                                        {{ (item.tong_doanh_thu / 1000).toFixed(0) }}k
                                    </span>
                                    <div class="rounded-top-3 w-100" style="max-width:38px"
                                        :style="{ height: getBarHeight(item.tong_doanh_thu) + '%', backgroundColor: item.tong_doanh_thu > 0 ? getCanvaColor(idx) : '#dee2e6' }">
                                    </div>
                                    <div class="fw-semibold text-secondary small mt-2 pt-2 w-100 border-top text-center" style="font-size: 11px;">
                                        {{ item.thang }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 5 Best Selling Products -->
                <div class="card border-3 border-primary border-end shadow-sm rounded-4 h-auto overflow-hidden">
                    <div class="card-header bg-white border-bottom px-4 py-3">
                        <span class="fw-bold text-dark d-flex align-items-center gap-2">
                            <i class='bx bx-crown text-warning fs-5'></i> TOP SẢN PHẨM BÁN CHẠY NHẤT
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div v-if="!thong_ke.top_san_pham || thong_ke.top_san_pham.length === 0" class="text-center py-5 text-muted">
                            <i class='bx bx-box d-block mb-2' style="font-size:2.5rem;opacity:0.25"></i>
                            <small>Chưa có dữ liệu bán hàng</small>
                        </div>
                        <div v-else class="list-group list-group-flush">
                            <div v-for="(sp, idx) in thong_ke.top_san_pham" :key="idx" class="list-group-item d-flex align-items-center gap-3 px-4 py-3">
                                <span class="badge rounded-pill text-white fw-bold"
                                    :style="{ backgroundColor: getCanvaColor(idx), minWidth: '28px' }">
                                    {{ idx + 1 }}
                                </span>
                                <span class="flex-grow-1 fw-semibold text-dark small text-truncate">
                                    {{ sp.ten_san_pham }}
                                </span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small fw-bold">
                                    Đã bán: {{ sp.tong_so_luong }} cái
                                </span>
                                <span class="fw-bold text-danger small">
                                    {{ formatTien(sp.tong_doanh_thu) }} đ
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'DashboardOverview',
    data() {
        return {
            thong_ke: {
                tong_doanh_thu: 0,
                tong_don_hang: 0,
                don_hang_moi: 0,
                tong_san_pham: 0,
                tong_khach_hang: 0,
                don_hang_gan_day: [],
                top_san_pham: []
            },
            chart_thang: [],
            canvaPalette: ['#0d6efd', '#0dcaf0', '#ffc107', '#198754', '#dc3545', '#6f42c1', '#20c997', '#fd7e14', '#6610f2', '#d63384', '#0d6efd', '#198754']
        }
    },
    mounted() {
        this.loadDashboardData();
    },
    methods: {
        getCanvaColor(idx) {
            return this.canvaPalette[idx % this.canvaPalette.length];
        },
        formatTien(val) {
            if (!val) return '0';
            return Number(val).toLocaleString('vi-VN');
        },
        getBarHeight(val) {
            if (!val || val <= 0) return 4;
            const max = Math.max(...this.chart_thang.map(i => i.tong_doanh_thu), 1);
            return Math.max(10, Math.min(100, (val / max) * 100));
        },
        badgeTrangThai(status) {
            switch(status) {
                case 'cho_xac_nhan': return 'bg-warning text-dark';
                case 'da_xac_nhan': return 'bg-info text-white';
                case 'dang_giao': return 'bg-primary text-white';
                case 'da_giao': return 'bg-success text-white';
                case 'da_huy': return 'bg-danger text-white';
                default: return 'bg-secondary text-white';
            }
        },
        textTrangThai(status) {
            switch(status) {
                case 'cho_xac_nhan': return 'Chờ xác nhận';
                case 'da_xac_nhan': return 'Đã xác nhận';
                case 'dang_giao': return 'Đang giao';
                case 'da_giao': return 'Đã giao hàng';
                case 'da_huy': return 'Đã hủy';
                default: return status;
            }
        },
        loadDashboardData() {
            axios.get('http://127.0.0.1:8000/api/admin/thong-ke/tong-quan')
                .then(res => {
                    if (res.data.status) {
                        this.thong_ke = res.data.data;
                    }
                })
                .catch(err => console.error('Lỗi tải tổng quan:', err));

            axios.get('http://127.0.0.1:8000/api/admin/thong-ke/doanh-thu-theo-thang?nam=2026')
                .then(res => {
                    if (res.data.status) {
                        this.chart_thang = res.data.data;
                    }
                })
                .catch(err => console.error('Lỗi tải doanh thu tháng:', err));
        }
    }
}
</script>

<style scoped></style>
