<?php

namespace App\Http\Middleware;

use App\Models\KhachHang;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class RejectLegacySeller
{
    public function handle(Request $request, Closure $next)
    {
        // API Client vẫn cho phép khách vãng lai; token Seller cũ bị từ chối.
        $token = $request->bearerToken();
        $user = $token ? PersonalAccessToken::findToken($token)?->tokenable : null;

        if ($user instanceof KhachHang && ($user->role ?: 'customer') !== 'customer') {
            return response()->json(['status' => 0, 'message' => 'Vai trò tài khoản không còn được hỗ trợ.'], 403);
        }

        return $next($request);
    }
}
