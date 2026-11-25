<?php

namespace App\Http\Middleware;

use App\Models\KhachHang;
use Closure;
use Illuminate\Http\Request;

class RequireCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless($user instanceof KhachHang && ($user->role ?: 'customer') === 'customer'
            && !$user->is_khoa && $user->is_kich_hoat, 403, 'Chỉ Customer đang hoạt động được thanh toán.');
        return $next($request);
    }
}
