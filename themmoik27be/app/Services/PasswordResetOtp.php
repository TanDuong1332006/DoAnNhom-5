<?php

namespace App\Services;

use App\Models\KhachHang;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\{Cache, Hash};

class PasswordResetOtp
{
    public function key(string $email): string
    {
        return 'sofep:reset:'.hash_hmac('sha256', mb_strtolower(trim($email)), config('app.key'));
    }

    public function locked(string $email, callable $callback)
    {
        try {
            return Cache::lock($this->key($email).':lock', 30)->block(3, $callback);
        } catch (LockTimeoutException $error) {
            abort(response()->json(['message' => 'Yêu cầu đang được xử lý. Vui lòng thử lại.'], 429, ['Retry-After' => 3]));
        }
    }

    public function issue(KhachHang $customer): string
    {
        $otp = (string) random_int(100000, 999999);
        Cache::put($this->key($customer->email), [
            'hash' => Hash::make($otp), 'expires_at' => now()->addMinutes(5)->timestamp,
            // Mã cũ mất hiệu lực sau đổi mật khẩu ngay cả khi cache cleanup thất bại.
            'credential' => hash_hmac('sha256', $customer->mat_khau, config('app.key')),
        ], now()->addMinutes(5));
        return $otp;
    }

    public function valid(?KhachHang $customer, string $otp): bool
    {
        if (!$customer) return false;
        $record = Cache::get($this->key($customer->email));
        return is_array($record) && isset($record['hash'], $record['expires_at'], $record['credential'])
            && now()->timestamp < $record['expires_at']
            && hash_equals($record['credential'], hash_hmac('sha256', $customer->mat_khau, config('app.key')))
            && Hash::check($otp, $record['hash']);
    }

    public function forget(string $email): void
    {
        Cache::forget($this->key($email));
        // Vô hiệu mã plaintext cũ, không đọc hoặc chấp nhận format cũ sau bản vá.
        Cache::forget('otp_reset_'.$email);
    }
}
