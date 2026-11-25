<?php

namespace App\Services;

use App\Models\DonHang;
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
        abort_unless($user, 401, 'Vui lòng đăng nhập để tiếp tục thanh toán.');
        abort_unless($user instanceof \App\Models\KhachHang
            && ($user->role ?: 'customer') === 'customer' && !$user->is_khoa && $user->is_kich_hoat
            && (int) $order->id_khach_hang === (int) $user->id, 403, 'Không có quyền truy cập thanh toán.');
    }
}
