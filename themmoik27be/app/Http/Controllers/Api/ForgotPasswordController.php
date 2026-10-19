<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KhachHang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

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

        $email = $request->email;
        $otp = rand(100000, 999999); // Random 6 số

        // Lưu OTP vào Cache 5 phút, key là email
        Cache::put('otp_reset_' . $email, $otp, now()->addMinutes(5));

        // Gửi mail
        Mail::to($email)->send(new SendOtpMail($otp));

        return response()->json(['message' => 'Mã OTP đã được gửi đến email của bạn!']);
    }

    // 2. Xác nhận OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6'
        ]);

        $cachedOtp = Cache::get('otp_reset_' . $request->email);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
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

        // Kiểm tra lại OTP lần cuối để bảo mật
        $cachedOtp = Cache::get('otp_reset_' . $request->email);
        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'Mã xác nhận không hợp lệ.'], 400);
        }

        // Tìm khach-hangs và đổi mật khẩu
        $user = KhachHang::where('email', $request->email)->first();
        // Hash::make chính là dùng Bcrypt để mã hóa
        $user->mat_khau = Hash::make($request->password); 
        $user->save();

        // Đổi mật khẩu xong thì xóa OTP đi
        Cache::forget('otp_reset_' . $request->email);

        return response()->json(['message' => 'Đổi mật khẩu thành công!']);
    }
}