import { createRouter, createWebHistory } from "vue-router";
import checkLogin from "./checkLogin";

const routes = [
    // ─── CLIENT SHOPPING ROUTES (Light Modern Theme) ─────────
    {
        path: '/',
        meta: { layout: 'client' },
        component: () => import('../components/Client/TrangChu.vue')
    },
    {
        path: '/shop',
        meta: { layout: 'client' },
        component: () => import('../components/Client/TrangChu.vue')
    },
    {
        path: '/san-pham/:id',
        meta: { layout: 'client' },
        component: () => import('../components/Client/ChiTietSanPham.vue')
    },
    {
        path: '/chi-tiet-san-pham/:id',
        meta: { layout: 'client' },
        component: () => import('../components/Client/ChiTietSanPham.vue')
    },
    {
        path: '/tim-kiem',
        meta: { layout: 'client' },
        component: () => import('../components/Client/TimKiem.vue')
    },
    {
        path: '/danh-muc/:id_or_slug?',
        name: 'DanhMucClient',
        meta: { layout: 'client' },
        component: () => import('../components/Client/TimKiem.vue')
    },
    {
        path: '/bo-suu-tap',
        name: 'BoSuuTapClient',
        meta: { layout: 'client' },
        component: () => import('../components/Client/TimKiem.vue')
    },
    {
        path: '/flash-sale',
        name: 'FlashSaleClient',
        meta: { layout: 'client' },
        component: () => import('../components/Client/TimKiem.vue')
    },

    // ─── AUTH (Đăng ký, Đăng nhập) ──────────────────────────
    {
        path: '/admin/login',
        meta: { layout: 'blank' },
        component: () => import('../components/DangNhap/index.vue')
    },
    {
        path: '/dang-nhap',
        meta: { layout: 'blank' },
        component: () => import('../components/DangNhap/index.vue')
    },
    {
        path: '/dang-ky',
        meta: { layout: 'blank' },
        component: () => import('../components/DangKy/index.vue')
    },
    {
        path: '/quen-mat-khau',
        meta: { layout: 'blank' },
        component: () => import('../components/QuenMatKhau/index.vue')
    },

    // ─── ADMIN ROUTES (Rocker Dashboard Default Layout) ─────
    {
        path: '/admin',
        component: () => import('../components/Admin/Dashboard/index.vue')
    },
    {
        path: '/admin/danh-muc',
        component: () => import('../components/Admin/DanhMuc/index.vue')
    },
    {
        path: '/admin/thuong-hieu',
        component: () => import('../components/Admin/ThuongHieu/index.vue')
    },
    {
        path: '/admin/mau-sac',
        component: () => import('../components/Admin/MauSac/index.vue')
    },
    {
        path: '/admin/kich-thuoc',
        component: () => import('../components/Admin/KichThuoc/index.vue')
    },
    {
        path: '/admin/san-pham',
        component: () => import('../components/Admin/SanPham/index.vue')
    },
    {
        path: '/admin/chi-tiet-san-pham',
        component: () => import('../components/Admin/ChiTietSanPham/index.vue')
    },
    {
        path: '/admin/khach-hang',
        component: () => import('../components/Admin/KhachHang/index.vue')
    },
    {
        path: '/admin/don-hang',
        component: () => import('../components/Admin/DonHang/index.vue')
    },
    {
        path: '/admin/ma-giam-gia',
        component: () => import('../components/Admin/MaGiamGia/index.vue')
    },
    {
        path: '/admin/danh-gia',
        component: () => import('../components/Admin/DanhGia/index.vue')
    },

    // ─── ADMIN CHART THỐNG KÊ ───────────────────────────────
    {
        path: '/admin/thong-ke/doanh-thu-theo-thang',
        component: () => import('../components/Admin/ThongKe/DoanhThuTheoThang.vue')
    },
    {
        path: '/admin/thong-ke/doanh-thu-theo-nam',
        component: () => import('../components/Admin/ThongKe/DoanhThuTheoNam.vue')
    },
    {
        path: '/admin/thong-ke/san-pham-theo-danh-muc',
        component: () => import('../components/Admin/ThongKe/SanPhamTheoDanhMuc.vue')
    },

    // Wildcard
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0, behavior: 'smooth' };
        }
    }
});

// Guard bảo vệ các trang Quản Trị Admin
router.beforeEach((to, from, next) => {
    if (to.path.startsWith('/admin') && to.path !== '/admin/login') {
        checkLogin(to, from, next);
    } else {
        next();
    }
});

export default router;