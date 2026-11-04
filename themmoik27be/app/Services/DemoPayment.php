<?php

namespace App\Services;

use App\Models\DonHang;
use App\Models\User;
use Illuminate\Http\Request;

class DemoPayment
{
    public static function token(string $code): string
    {
        return hash_hmac('sha256', 'sofep-demo-payment:'.$code, config('app.key'));
    }

    public static function authorize(Request $request, DonHang $order): void
    {
        $user = $request->user('sanctum');
        if ($user instanceof User) return;
        if ($user instanceof \App\Models\KhachHang && (($user->role ?: 'customer') !== 'customer' || $user->is_khoa || !$user->is_kich_hoat)) abort(403, 'Tài khoản không được thanh toán.');
        if ($user instanceof \App\Models\KhachHang && $user->role === 'customer' && !$user->is_khoa && $user->is_kich_hoat && $order->id_khach_hang === $user->id) return;
        abort_unless(hash_equals(self::token($order->ma_don_hang), (string) $request->input('payment_token', '')), 403, 'Không có quyền truy cập thanh toán.');
    }
}
