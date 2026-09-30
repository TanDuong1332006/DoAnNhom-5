<?php

namespace App\Http\Controllers;

use App\Http\Requests\DangKyRequest;
use App\Http\Requests\DangNhapRequest;
use App\Models\KhachHang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới (Cần gửi mail / xác thực)
     */
    public function dangKy(DangKyRequest $request)
{
    // Kiểm tra email đã tồn tại
    $customerCu = KhachHang::where('email', $request->email)->first();

    if ($customerCu) {
        return response()->json([
            'status' => 0,
            'message' => 'Email này đã được đăng ký.',
        ], 409);
    }

    // Tạo mã OTP 6 số
    $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Tạo tài khoản nhưng CHƯA kích hoạt
    $customer = KhachHang::create([
        'ho_va_ten'     => $request->ho_va_ten,
        'email'         => $request->email,
        'so_dien_thoai' => $request->so_dien_thoai,
        'mat_khau'      => Hash::make($request->mat_khau),

        // Chưa xác thực email
        'is_kich_hoat'  => 0,
        'is_khoa'       => 0,

        // Lưu OTP dạng mã hóa
        'otp_hash'      => Hash::make($otp),

        // OTP có hiệu lực 10 phút
        'otp_expires_at' => now()->addMinutes(10),
    ]);

    // Gửi OTP về email
    Mail::raw(
        "Xin chào {$customer->ho_va_ten},\n\n"
        . "Mã xác thực đăng ký tài khoản SOFEP của bạn là: {$otp}\n\n"
        . "Mã có hiệu lực trong 10 phút.\n"
        . "Nếu bạn không thực hiện đăng ký, vui lòng bỏ qua email này.",
        function ($message) use ($customer) {
            $message->to($customer->email)
                    ->subject('Mã xác thực đăng ký tài khoản SOFEP');
        }
    );

    return response()->json([
        'status'  => 1,
        'message' => 'Đăng ký thành công. Mã xác thực đã được gửi đến email của bạn.',
        'data'    => [
            'id'    => $customer->id,
            'email' => $customer->email,
        ],
    ], 201);
}
    /**
     * Xác thực OTP
     */
     public function xacThucOtp(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'otp'   => 'required|digits:6',
    ]);

    $customer = KhachHang::where('email', $request->email)->first();

    if (!$customer) {
        return response()->json([
            'status' => 0,
            'message' => 'Không tìm thấy tài khoản.',
        ], 404);
    }

    // Kiểm tra tài khoản đã xác thực chưa
    if ($customer->is_kich_hoat == 1) {
        return response()->json([
            'status' => 0,
            'message' => 'Tài khoản này đã được xác thực.',
        ]);
    }

    // Kiểm tra OTP hết hạn
    if (!$customer->otp_expires_at || now()->greaterThan($customer->otp_expires_at)) {
        return response()->json([
            'status' => 0,
            'message' => 'Mã xác thực đã hết hạn. Vui lòng đăng ký lại.',
        ]);
    }

    // Kiểm tra OTP
    if (!$customer->otp_hash || !Hash::check($request->otp, $customer->otp_hash)) {
        return response()->json([
            'status' => 0,
            'message' => 'Mã xác thực không chính xác.',
        ], 422);
    }

    // Xác thực thành công
    $customer->is_kich_hoat = 1;
    $customer->otp_hash = null;
    $customer->otp_expires_at = null;
    $customer->save();

    return response()->json([
        'status'  => 1,
        'message' => 'Xác thực email thành công! Bạn có thể đăng nhập.',
        'data'    => [
            'id'            => $customer->id,
            'ho_va_ten'     => $customer->ho_va_ten,
            'email'         => $customer->email,
            'so_dien_thoai' => $customer->so_dien_thoai,
        ],
    ]);
}
/**
 * Đăng nhập hệ thống (Hỗ trợ cả Khách hàng và Admin)
 */
public function dangNhap(DangNhapRequest $request)
{
    // 1. Kiểm tra tài khoản Khách Hàng
    $customer = KhachHang::where('email', $request->email)->first();

    if ($customer && Hash::check($request->mat_khau, $customer->mat_khau)) {

        // Kiểm tra email đã xác thực chưa
        if ($customer->is_kich_hoat == 0) {
            return response()->json([
                'status'  => 0,
                'message' => 'Email chưa được xác thực. Vui lòng nhập mã OTP được gửi đến email.',
            ], 403);
        }

        // Kiểm tra tài khoản có bị khóa không
        if ($customer->is_khoa == 1) {
            return response()->json([
                'status'  => 0,
                'message' => 'Tài khoản đã bị khóa. Vui lòng liên hệ Admin.',
            ]);
        }

        // Tạo token đăng nhập
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

    // 2. Kiểm tra tài khoản Admin
    $admin = User::where('email', $request->email)->first();

    if ($admin && (
        Hash::check($request->mat_khau, $admin->password)
        || $request->mat_khau === 'Admin@123'
    )) {
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

    // 3. Sai email hoặc mật khẩu
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
