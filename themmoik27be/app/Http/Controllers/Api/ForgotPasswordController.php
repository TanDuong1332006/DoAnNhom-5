<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KhachHang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use App\Services\PasswordResetOtp;

class ForgotPasswordController extends Controller
{
    // 1. Gửi OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:khach_hangs,email' // Đảm bảo email tồn tại trong DB
        ], [
            'email.exists' => 'Email này chưa được đăng ký trong hệ thống.'
        ]);

        $service = app(PasswordResetOtp::class);
        $service->locked($request->email, function () use ($request, $service) {
            $customer = KhachHang::where('email', $request->email)->firstOrFail();
            $otp = $service->issue($customer);
            try {
                Mail::to($customer->email)->send(new SendOtpMail($otp));
            } catch (\Throwable $error) {
                $service->forget($customer->email);
                throw $error;
            }
        });

        return response()->json(['message' => 'Mã OTP đã được gửi đến email của bạn!']);
    }

    // 2. Xác nhận OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6'
        ]);

        $service = app(PasswordResetOtp::class);
        $valid = $service->locked($request->email, fn () => $service->valid(
            KhachHang::where('email', $request->email)->first(), (string) $request->otp));
        if (!$valid) {
            return response()->json(['message' => 'Mã OTP không hợp lệ hoặc đã hết hạn.'], 400);
        }

        return response()->json(['message' => 'Xác nhận OTP thành công!']);
    }

    // 3. Đổi mật khẩu mới (Sử dụng Bcrypt)
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6',
            'password' => 'required|min:6|confirmed' // Yêu cầu có trường password_confirmation ở FE
        ]);

        $service = app(PasswordResetOtp::class);
        return $service->locked($request->email, function () use ($request, $service) {
            $response = DB::transaction(function () use ($request, $service) {
                $user = KhachHang::where('email', $request->email)->lockForUpdate()->first();
                if (!$service->valid($user, (string) $request->otp)) {
                    return response()->json(['message' => 'Mã xác nhận không hợp lệ.'], 400);
                }
                $user->mat_khau = Hash::make($request->password);
                $user->save();
                // Chỉ thu hồi token của đúng Customer (morph), không đụng token Admin cùng ID.
                $user->tokens()->delete();
                return response()->json(['message' => 'Đổi mật khẩu thành công!']);
            });
            if ($response->getStatusCode() === 200) $service->forget($request->email);
            return $response;
        });
    }
}
