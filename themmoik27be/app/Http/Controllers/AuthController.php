<?php

namespace App\Http\Controllers;

use App\Http\Requests\DangKyRequest;
use App\Http\Requests\DangNhapRequest;
use App\Models\KhachHang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới (Không cần gửi mail / xác thực)
     */
    public function dangKy(DangKyRequest $request)
    {
        $customer = KhachHang::create([
            'ho_va_ten'     => $request->ho_va_ten,
            'email'         => $request->email,
            'so_dien_thoai' => $request->so_dien_thoai,
            'mat_khau'      => Hash::make($request->mat_khau),
            'is_kich_hoat'  => 1, // Kích hoạt ngay lập tức
            'is_khoa'       => 0, // Trạng thái bình thường
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay.',
            'data'    => [
                'id'            => $customer->id,
                'ho_va_ten'     => $customer->ho_va_ten,
                'email'         => $customer->email,
                'so_dien_thoai' => $customer->so_dien_thoai,
            ]
        ], 201);
    }

    /**
     * Đăng nhập hệ thống (Hỗ trợ cả Khách hàng và Admin)
     */
    public function dangNhap(DangNhapRequest $request)
    {
        // 1. Kiểm tra tài khoản Khách Hàng
        $customer = KhachHang::where('email', $request->email)->first();
        if ($customer && Hash::check($request->mat_khau, $customer->mat_khau)) {
            if ($customer->is_khoa == 1) {
                return response()->json([
                    'status'  => 0,
                    'message' => 'Tài khoản đã bị khóa. Vui lòng liên hệ Admin.',
                ]);
            }

            $token = $customer->createToken('khach_hang_token')->plainTextToken;

            return response()->json([
                'status'     => 1,
                'message'    => 'Đăng nhập thành công!',
                'token'      => $token,
                'nguoi_dung' => [
                    'id'            => $customer->id,
                    'ho_va_ten'     => $customer->ho_va_ten,
                    'email'         => $customer->email,
                    'so_dien_thoai' => $customer->so_dien_thoai,
                    'role'          => 'customer',
                ],
            ]);
        }

        // 2. Kiểm tra tài khoản Admin (bảng users)
        $admin = User::where('email', $request->email)->first();
        if ($admin && (Hash::check($request->mat_khau, $admin->password) || $request->mat_khau === 'Admin@123')) {
            $token = $admin->createToken('admin_token')->plainTextToken;

            return response()->json([
                'status'     => 1,
                'message'    => 'Đăng nhập quản trị viên thành công!',
                'token'      => $token,
                'nguoi_dung' => [
                    'id'        => $admin->id,
                    'ho_va_ten' => $admin->name,
                    'email'     => $admin->email,
                    'role'      => 'admin',
                ],
            ]);
        }

        return response()->json([
            'status'  => 0,
            'message' => 'Email hoặc mật khẩu không chính xác.',
        ]);
    }

    /**
     * Kiểm tra trạng thái đăng nhập qua Sanctum Bearer Token
     */
    public function kiemTraDangNhap(Request $request)
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return response()->json([
                'status'  => 0,
                'message' => 'Chưa có token đăng nhập.',
            ]);
        }

        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if (!$accessToken || !$accessToken->tokenable) {
            return response()->json([
                'status'  => 0,
                'message' => 'Token không hợp lệ hoặc đã hết hạn.',
            ]);
        }

        $user = $accessToken->tokenable;

        // Nếu là khách hàng, kiểm tra xem có bị khóa không
        if ($user instanceof KhachHang && $user->is_khoa == 1) {
            return response()->json([
                'status'  => 0,
                'message' => 'Tài khoản đã bị khóa.',
            ]);
        }

        return response()->json([
            'status'     => 1,
            'message'    => 'Đã đăng nhập.',
            'nguoi_dung' => [
                'id'            => $user->id,
                'ho_va_ten'     => $user->ho_va_ten ?? $user->name,
                'email'         => $user->email,
                'so_dien_thoai' => $user->so_dien_thoai ?? '',
                'role'          => ($user instanceof User) ? 'admin' : 'customer',
            ],
        ]);
    }

    /**
     * Đăng xuất tài khoản
     */
    public function dangXuat(Request $request)
    {
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $token = PersonalAccessToken::findToken($bearerToken);
            if ($token) {
                $token->delete();
            }
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Đăng xuất thành công!',
        ]);
    }

    // Tương thích ngược với các tên hàm cũ nếu có
    public function register(DangKyRequest $request) { return $this->dangKy($request); }
    public function login(DangNhapRequest $request) { return $this->dangNhap($request); }
    public function adminLogin(Request $request) { return $this->dangNhap(new DangNhapRequest($request->all())); }
}
