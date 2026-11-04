<?php

namespace App\Http\Controllers;

use App\Http\Requests\DangKyRequest;
use App\Http\Requests\DangNhapRequest;
use App\Models\KhachHang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Đăng ký khách hàng.
     * Tài khoản cần xác thực OTP trước khi đăng nhập.
     */
public function dangKy(DangKyRequest $request)    {
        $role = $request->input('role', 'customer');

        // Chỉ cho phép đăng ký khách hàng.
        if (!in_array($role, ['customer'], true)) {
            return response()->json([
                'status' => 0,
                'message' => 'Loại tài khoản không hợp lệ.',
            ], 422);
        }

        if (KhachHang::where('email', $request->email)->exists()) {
            return response()->json([
                'status' => 0,
                'message' => 'Email này đã được đăng ký.',
            ], 409);
        }


        $otp = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        // Tạo tài khoản khách hàng trong cùng một giao dịch.
        $customer = DB::transaction(function () use ($request, $otp) {
            $customer = KhachHang::create([
                'ho_va_ten' => $request->ho_va_ten,
                'email' => $request->email,
                'so_dien_thoai' => $request->so_dien_thoai,
                'mat_khau' => Hash::make($request->mat_khau),
                'role' => 'customer',
                'is_kich_hoat' => 0,
                'is_khoa' => 0,
                'otp_hash' => Hash::make($otp),
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            return $customer;
        });

        // Giữ nguyên quy trình gửi OTP qua email.
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
            'status' => 1,
            'message' => 'Đăng ký thành công. Mã xác thực đã được gửi đến email của bạn.',
            'data' => [
                'id' => $customer->id,
                'email' => $customer->email,
                'role' => $customer->role,
                'cua_hang_id' => null,
                'trang_thai_cua_hang' => null,
            ],
        ], 201);
    }

    /**
     * Xác thực OTP.
     */
    public function xacThucOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $customer = KhachHang::where('email', $request->email)->first();

        if (!$customer) {
            return response()->json([
                'status' => 0,
                'message' => 'Không tìm thấy tài khoản.',
            ], 404);
        }

        if ($customer->is_kich_hoat == 1) {
            return response()->json([
                'status' => 0,
                'message' => 'Tài khoản này đã được xác thực.',
            ], 422);
        }

        if (
            !$customer->otp_expires_at ||
            now()->greaterThan($customer->otp_expires_at)
        ) {
            return response()->json([
                'status' => 0,
                'message' => 'Mã xác thực đã hết hạn. Vui lòng đăng ký lại.',
            ], 422);
        }

        if (
            !$customer->otp_hash ||
            !Hash::check($request->otp, $customer->otp_hash)
        ) {
            return response()->json([
                'status' => 0,
                'message' => 'Mã xác thực không chính xác.',
            ], 422);
        }

        $customer->is_kich_hoat = 1;
        $customer->otp_hash = null;
        $customer->otp_expires_at = null;
        $customer->save();

        return response()->json([
            'status' => 1,
            'message' => 'Xác thực email thành công! Bạn có thể đăng nhập.',
            'data' => [
                'id' => $customer->id,
                'ho_va_ten' => $customer->ho_va_ten,
                'email' => $customer->email,
                'so_dien_thoai' => $customer->so_dien_thoai,
                'role' => $customer->role ?? 'customer',
            ],
        ]);
    }

    /**
     * Đăng nhập khách hàng hoặc admin.
     */
    public function dangNhap(DangNhapRequest $request)
    {
        $customer = KhachHang::where('email', $request->email)->first();

        if ($customer && Hash::check($request->mat_khau, $customer->mat_khau)) {
            if ($customer->is_kich_hoat == 0) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Email chưa được xác thực. Vui lòng nhập mã OTP được gửi đến email.',
                ], 403);
            }

            if ($customer->is_khoa == 1) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Tài khoản đã bị khóa. Vui lòng liên hệ Admin.',
                ], 403);
            }

            $role = $customer->role ?: 'customer';

            // Không cho phép tài khoản trong bảng khách hàng tự nhận quyền admin.
            if (!in_array($role, ['customer'], true)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Vai trò tài khoản không hợp lệ.',
                ], 403);
            }

            $token = $customer->createToken('khach_hang_token')->plainTextToken;


            return response()->json([
                'status' => 1,
                'message' => 'Đăng nhập thành công!',
                'token' => $token,
                'nguoi_dung' => [
                    'id' => $customer->id,
                    'ho_va_ten' => $customer->ho_va_ten,
                    'email' => $customer->email,
                    'so_dien_thoai' => $customer->so_dien_thoai,
                    'role' => $role,
                    'cua_hang_id' => null,
                    'trang_thai_cua_hang' => null,
                ],
            ]);
        }

        // Kiểm tra tài khoản admin trong bảng users.
        $admin = User::where('email', $request->email)->first();

        if ($admin && Hash::check($request->mat_khau, $admin->password)) {
            $token = $admin->createToken('admin_token')->plainTextToken;

            return response()->json([
                'status' => 1,
                'message' => 'Đăng nhập quản trị viên thành công!',
                'token' => $token,
                'nguoi_dung' => [
                    'id' => $admin->id,
                    'ho_va_ten' => $admin->name,
                    'email' => $admin->email,
                    'role' => 'admin',
                ],
            ]);
        }

        return response()->json([
            'status' => 0,
            'message' => 'Email hoặc mật khẩu không chính xác.',
        ], 401);
    }

    /**
     * Kiểm tra trạng thái đăng nhập qua Sanctum Bearer Token.
     */
    public function kiemTraDangNhap(Request $request)
    {
        $user = $request->user();

        if ($user instanceof KhachHang) {
            if ($user->is_khoa == 1 || $user->is_kich_hoat != 1) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Tài khoản đã bị khóa.',
                ], 403);
            }

            $role = $user->role ?: 'customer';

            if (!in_array($role, ['customer'], true)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Vai trò tài khoản không hợp lệ.',
                ], 403);
            }


            return response()->json([
                'status' => 1,
                'message' => 'Đã đăng nhập.',
                'nguoi_dung' => [
                    'id' => $user->id,
                    'ho_va_ten' => $user->ho_va_ten,
                    'email' => $user->email,
                    'so_dien_thoai' => $user->so_dien_thoai ?? '',
                    'role' => $role,
                    'cua_hang_id' => null,
                    'trang_thai_cua_hang' => null,
                ],
            ]);
        }

        if ($user instanceof User) {
            return response()->json([
                'status' => 1,
                'message' => 'Đã đăng nhập.',
                'nguoi_dung' => [
                    'id' => $user->id,
                    'ho_va_ten' => $user->name,
                    'email' => $user->email,
                    'so_dien_thoai' => '',
                    'role' => 'admin',
                ],
            ]);
        }

        return response()->json([
            'status' => 0,
            'message' => 'Loại tài khoản không được hỗ trợ.',
        ], 403);
    }

    /**
     * Đăng xuất tài khoản.
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
            'status' => 1,
            'message' => 'Đăng xuất thành công!',
        ]);
    }

    // Tương thích với các tên hàm cũ.
    public function register(DangKyRequest $request)
    {
        return $this->dangKy($request);
    }

    public function login(DangNhapRequest $request)
    {
        return $this->dangNhap($request);
    }

    public function adminLogin(Request $request)
    {
        return $this->dangNhap(
            new DangNhapRequest($request->all())
        );
    }
}
