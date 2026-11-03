<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Admin dùng bảng users (default Laravel)
        if (!$user || !($user instanceof \App\Models\User)) {
            return response()->json(['status' => 0, 'message' => 'Không có quyền truy cập admin.'], 403);
        }

        return $next($request);
    }
}
