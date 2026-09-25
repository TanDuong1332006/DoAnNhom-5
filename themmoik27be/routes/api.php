<?php

use App\Http\Controllers\Admin\DanhMucController;
use App\Http\Controllers\Admin\ThuongHieuController;
use App\Http\Controllers\Admin\MauSacController;
use App\Http\Controllers\Admin\KichThuocController;
use App\Http\Controllers\Admin\SanPhamController;
use App\Http\Controllers\Admin\ChiTietSanPhamController;
use App\Http\Controllers\Admin\KhachHangController;
use App\Http\Controllers\Admin\MaGiamGiaController;
use App\Http\Controllers\Admin\DonHangController;
use App\Http\Controllers\Admin\DanhGiaController;
use App\Http\Controllers\Admin\ThongKeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\giaoDichController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth (Đăng ký, Đăng nhập, Kiểm tra đăng nhập, Đăng xuất)
Route::post('/dang-ky',             [AuthController::class, 'dangKy']);
Route::post('/dang-nhap',           [AuthController::class, 'dangNhap']);
Route::get('/kiem-tra-dang-nhap',   [AuthController::class, 'kiemTraDangNhap']);
Route::post('/dang-xuat',           [AuthController::class, 'dangXuat']);
Route::post('/admin-login',         [AuthController::class, 'dangNhap']);
Route::post('/auth/admin-login',    [AuthController::class, 'dangNhap']);

// Giao Dịch MB Bank & Thanh Toán Tự Động
Route::any('/giao-dich',                         [giaoDichController::class, 'getData']);
Route::post('/giao-dich/mo-phong',               [giaoDichController::class, 'moPhongThanhToan']);
Route::get('/kiem-tra-thanh-toan/{ma_hoa_don}',  [giaoDichController::class, 'kiemTraThanhToan']);

// Public Client API
Route::prefix('client')->group(function () {
    Route::get('/home-data',              [ClientController::class, 'getHomeData']);
    Route::get('/san-phams',              [ClientController::class, 'getSanPhams']);
    Route::get('/san-pham/{slug}',        [ClientController::class, 'getChiTietSanPham']);
    Route::post('/dat-hang',              [ClientController::class, 'datHang']);
});

// ─── ADMIN ROUTES (Đúng chuẩn style be_k27_esport02) ───────────────────
Route::prefix('admin')->group(function () {

    // Danh Mục
    Route::prefix('danh-muc')->group(function () {
        Route::get('/get-data',       [DanhMucController::class, 'getData']);
        Route::post('/create',        [DanhMucController::class, 'create']);
        Route::post('/update',        [DanhMucController::class, 'capNhat']);
        Route::post('/destroy',       [DanhMucController::class, 'destroy']);
        Route::post('/change-status', [DanhMucController::class, 'doiTrangThai']);
    });

    // Thương Hiệu
    Route::prefix('thuong-hieu')->group(function () {
        Route::get('/get-data',       [ThuongHieuController::class, 'getData']);
        Route::post('/create',        [ThuongHieuController::class, 'create']);
        Route::post('/update',        [ThuongHieuController::class, 'capNhat']);
        Route::post('/destroy',       [ThuongHieuController::class, 'destroy']);
        Route::post('/change-status', [ThuongHieuController::class, 'doiTrangThai']);
    });

    // Màu Sắc
    Route::prefix('mau-sac')->group(function () {
        Route::get('/get-data',       [MauSacController::class, 'getData']);
        Route::post('/create',        [MauSacController::class, 'create']);
        Route::post('/update',        [MauSacController::class, 'capNhat']);
        Route::post('/destroy',       [MauSacController::class, 'destroy']);
        Route::post('/change-status', [MauSacController::class, 'doiTrangThai']);
    });

    // Kích Thước
    Route::prefix('kich-thuoc')->group(function () {
        Route::get('/get-data',       [KichThuocController::class, 'getData']);
        Route::post('/create',        [KichThuocController::class, 'create']);
        Route::post('/update',        [KichThuocController::class, 'capNhat']);
        Route::post('/destroy',       [KichThuocController::class, 'destroy']);
        Route::post('/change-status', [KichThuocController::class, 'doiTrangThai']);
    });

    // Sản Phẩm
    Route::prefix('san-pham')->group(function () {
        Route::get('/get-data',          [SanPhamController::class, 'getData']);
        Route::post('/create',           [SanPhamController::class, 'create']);
        Route::post('/update',           [SanPhamController::class, 'capNhat']);
        Route::post('/destroy',          [SanPhamController::class, 'destroy']);
        Route::post('/change-status',    [SanPhamController::class, 'doiTrangThai']);
        Route::post('/change-noi-bat',   [SanPhamController::class, 'doiNoiBat']);
    });

    // Biến Thể Sản Phẩm
    Route::prefix('chi-tiet-san-pham')->group(function () {
        Route::get('/get-data',       [ChiTietSanPhamController::class, 'getData']);
        Route::post('/create',        [ChiTietSanPhamController::class, 'create']);
        Route::post('/update',        [ChiTietSanPhamController::class, 'capNhat']);
        Route::post('/destroy',       [ChiTietSanPhamController::class, 'destroy']);
    });

    // Khách Hàng
    Route::prefix('khach-hang')->group(function () {
        Route::get('/get-data',       [KhachHangController::class, 'getData']);
        Route::post('/change-status', [KhachHangController::class, 'doiTrangThai']);
        Route::post('/toggle-block',  [KhachHangController::class, 'khoaTaiKhoan']);
    });

    // Mã Giảm Giá
    Route::prefix('ma-giam-gia')->group(function () {
        Route::get('/get-data',       [MaGiamGiaController::class, 'getData']);
        Route::post('/create',        [MaGiamGiaController::class, 'create']);
        Route::post('/update',        [MaGiamGiaController::class, 'capNhat']);
        Route::post('/destroy',       [MaGiamGiaController::class, 'destroy']);
        Route::post('/change-status', [MaGiamGiaController::class, 'doiTrangThai']);
    });

    // Đơn Hàng
    Route::prefix('don-hang')->group(function () {
        Route::get('/get-data',       [DonHangController::class, 'getData']);
        Route::post('/update-status', [DonHangController::class, 'capNhatTrangThai']);
        Route::post('/destroy',       [DonHangController::class, 'destroy']);
    });

    // Đánh Giá
    Route::prefix('danh-gia')->group(function () {
        Route::get('/get-data',       [DanhGiaController::class, 'getData']);
        Route::post('/change-status', [DanhGiaController::class, 'doiTrangThai']);
        Route::post('/destroy',       [DanhGiaController::class, 'destroy']);
    });

    // Thống Kê & Chart Views
    Route::prefix('thong-ke')->group(function () {
        Route::get('/tong-quan',             [ThongKeController::class, 'getTongQuan']);
        Route::get('/doanh-thu-theo-thang',  [ThongKeController::class, 'getDoanhThuTheoThang']);
        Route::get('/doanh-thu-theo-nam',    [ThongKeController::class, 'getDoanhThuTheoNam']);
        Route::get('/san-pham-theo-danh-muc',[ThongKeController::class, 'getSanPhamTheoDanhMuc']);
    });

    // Lịch Sử Giao Dịch MB Bank
    Route::prefix('giao-dich')->group(function () {
        Route::get('/get-data',       [giaoDichController::class, 'layDanhSachGiaoDich']);
        Route::post('/sync',          [giaoDichController::class, 'getData']);
    });
});
