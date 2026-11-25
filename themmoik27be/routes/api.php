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
use App\Http\Controllers\Admin\ThongKeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\giaoDichController;
// THÊM CONTROLLER QUÊN MẬT KHẨU Ở ĐÂY:
use App\Http\Controllers\Api\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth (Đăng ký, Đăng nhập, Kiểm tra đăng nhập, Đăng xuất, Quên mật khẩu)
Route::post('/dang-ky',             [AuthController::class, 'dangKy'])->middleware('throttle:auth-register');
Route::post('/xac-thuc-otp',         [AuthController::class, 'xacThucOtp'])->middleware('throttle:auth-register-otp');
Route::post('/dang-nhap',           [AuthController::class, 'dangNhap'])->middleware('throttle:auth-login');
Route::get('/kiem-tra-dang-nhap',   [AuthController::class, 'kiemTraDangNhap'])->middleware('auth:sanctum');
Route::post('/dang-xuat',           [AuthController::class, 'dangXuat']);
Route::post('/admin-login',         [AuthController::class, 'dangNhap'])->middleware('throttle:auth-login');
Route::post('/auth/admin-login',    [AuthController::class, 'dangNhap'])->middleware('throttle:auth-login');

// THÊM ROUTES QUÊN MẬT KHẨU Ở ĐÂY:
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->middleware('throttle:auth-reset-send');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->middleware('throttle:auth-reset-otp');
Route::post('/forgot-password/reset-password', [ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:auth-reset-otp');

// Giao Dịch MB Bank & Thanh Toán Tự Động
Route::any('/giao-dich',                         [giaoDichController::class, 'getData'])->middleware(['auth:sanctum', 'admin']);
Route::post('/giao-dich/mo-phong',               [giaoDichController::class, 'moPhongThanhToan'])->middleware(['auth:sanctum', \App\Http\Middleware\RequireCustomer::class]);
Route::get('/kiem-tra-thanh-toan/{ma_hoa_don}',  [giaoDichController::class, 'kiemTraThanhToan'])->middleware(['auth:sanctum', \App\Http\Middleware\RequireCustomer::class]);

// File ảnh raster do Admin tải lên, không thực thi nội dung upload.
Route::get('/product-images/{product}/{filename}', [\App\Http\Controllers\Admin\ProductImageController::class, 'image'])->whereNumber('product');

// Public Client API
Route::prefix('client')->middleware(\App\Http\Middleware\RejectLegacySeller::class)->group(function () {
    Route::get('/don-hang', [\App\Http\Controllers\CustomerOrderController::class, 'index'])->middleware(['auth:sanctum', \App\Http\Middleware\RequireCustomer::class]);
    Route::get('/don-hang/{id}', [\App\Http\Controllers\CustomerOrderController::class, 'show'])->whereNumber('id')->middleware(['auth:sanctum', \App\Http\Middleware\RequireCustomer::class]);
    Route::get('/demo/san-pham/{slug}', [\App\Http\Controllers\ProductDetailController::class, 'show']);
    Route::get('/danh-mucs',              [ClientController::class, 'getDanhMucs']);
    Route::get('/tuy-chon-san-pham',      [ClientController::class, 'getTuyChonSanPham']);
    Route::get('/home-data',              [ClientController::class, 'getHomeData']);
    Route::get('/san-phams',              [ClientController::class, 'getSanPhams']);
    Route::get('/san-pham/{slug}',        [ClientController::class, 'getChiTietSanPham']);
    Route::post('/dat-hang',              [ClientController::class, 'datHang'])->middleware(['auth:sanctum', \App\Http\Middleware\RequireCustomer::class]);
    Route::post('/kiem-tra-voucher',      [ClientController::class, 'kiemTraVoucher']);
});

// ─── ADMIN ROUTES (Đúng chuẩn style be_k27_esport02) ───────────────────
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

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
        Route::post('/{product}/images', [\App\Http\Controllers\Admin\ProductImageController::class, 'upload'])->whereNumber('product');
        Route::post('/{product}/images/order', [\App\Http\Controllers\Admin\ProductImageController::class, 'arrange'])->whereNumber('product');
        Route::delete('/{product}/images/{image}', [\App\Http\Controllers\Admin\ProductImageController::class, 'remove'])->whereNumber('product')->whereNumber('image');
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

// Customer engagement: chỉ kích hoạt sau migration được phê duyệt trên đúng database.
Route::group([], function () {
    Route::post('/client/gop-y', [\App\Http\Controllers\FeedbackController::class, 'store'])->middleware([\App\Http\Middleware\EngagementReady::class, 'throttle:feedback']);
    Route::prefix('client')->middleware(['auth:sanctum', \App\Http\Middleware\RequireCustomer::class, \App\Http\Middleware\EngagementReady::class])->group(function () {
        Route::get('/thong-bao/count', [\App\Http\Controllers\CustomerInboxController::class, 'count']);
        Route::get('/thong-bao', [\App\Http\Controllers\CustomerInboxController::class, 'index']);
        Route::post('/thong-bao/doc-tat-ca', [\App\Http\Controllers\CustomerInboxController::class, 'readAll']);
        Route::post('/thong-bao/{id}/da-doc', [\App\Http\Controllers\CustomerInboxController::class, 'read'])->whereNumber('id');
        Route::match(['get','put'], '/tuy-chon-thong-bao', [\App\Http\Controllers\CustomerInboxController::class, 'preferences']);
        Route::get('/vi-uu-dai', [\App\Http\Controllers\CustomerInboxController::class, 'wallet']);
        Route::post('/vi-uu-dai/{id}/luu', [\App\Http\Controllers\CustomerInboxController::class, 'save'])->whereNumber('id');
    });
    Route::prefix('admin')->middleware(['auth:sanctum','admin', \App\Http\Middleware\EngagementReady::class])->group(function () {
        Route::get('/gop-y', [\App\Http\Controllers\FeedbackController::class, 'index']);
        Route::get('/gop-y/{id}', [\App\Http\Controllers\FeedbackController::class, 'show'])->whereNumber('id');
        Route::patch('/gop-y/{id}', [\App\Http\Controllers\FeedbackController::class, 'update'])->whereNumber('id');
        Route::get('/gop-y/{id}/anh/{image}', [\App\Http\Controllers\FeedbackController::class, 'image'])->whereNumber(['id','image']);
        Route::post('/gop-y/{id}/phan-hoi', [\App\Http\Controllers\FeedbackController::class, 'reply'])->whereNumber('id');
        Route::get('/chien-dich', [\App\Http\Controllers\Admin\CustomerCampaignController::class, 'index']);
        Route::post('/chien-dich/xem-truoc', [\App\Http\Controllers\Admin\CustomerCampaignController::class, 'preview']);
        Route::post('/chien-dich', [\App\Http\Controllers\Admin\CustomerCampaignController::class, 'store']);
        Route::post('/chien-dich/giao-lo', [\App\Http\Controllers\Admin\CustomerCampaignController::class, 'dispatch']);
    });
});
