<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cùng bucket cho các alias login; không dùng email/IP thô làm cache key.
        $key = function ($request, string $kind): string {
            $email = $request->input('email');
            $value = $kind === 'ip' ? ($request->ip() ?: 'unknown')
                : (is_string($email) ? mb_strtolower(trim($email)) : 'invalid');
            return hash_hmac('sha256', $value, config('app.key'));
        };
        $response = fn ($request, $headers) => response()->json([
            'status' => 0, 'message' => 'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau.',
        ], 429, $headers);
        $limit = fn ($max, $minutes, $bucket) => \Illuminate\Cache\RateLimiting\Limit::perMinutes($minutes, $max)
            ->by($bucket)->response($response);
        \Illuminate\Support\Facades\RateLimiter::for('auth-login', fn ($r) => [
            $limit(10, 1, 'email:'.$key($r, 'email')), $limit(30, 1, 'ip:'.$key($r, 'ip')),
        ]);
        \Illuminate\Support\Facades\RateLimiter::for('auth-register', fn ($r) => [
            $limit(3, 60, 'email:'.$key($r, 'email')), $limit(10, 60, 'ip:'.$key($r, 'ip')),
        ]);
        \Illuminate\Support\Facades\RateLimiter::for('auth-register-otp', fn ($r) => [
            $limit(5, 10, 'email:'.$key($r, 'email')), $limit(30, 10, 'ip:'.$key($r, 'ip')),
        ]);
        \Illuminate\Support\Facades\RateLimiter::for('auth-reset-send', fn ($r) => [
            $limit(1, 1, 'cooldown:'.$key($r, 'email')), $limit(3, 60, 'hour:'.$key($r, 'email')),
            $limit(10, 60, 'ip:'.$key($r, 'ip')),
        ]);
        // verify-otp và reset-password dùng chung, resend không xóa budget đoán mã.
        \Illuminate\Support\Facades\RateLimiter::for('auth-reset-otp', fn ($r) => [
            $limit(5, 5, 'email:'.$key($r, 'email')), $limit(30, 5, 'ip:'.$key($r, 'ip')),
        ]);
        \Illuminate\Support\Facades\RateLimiter::for('feedback', function ($request) {
            $key = hash_hmac('sha256', $request->ip() ?: 'unknown', config('app.key'));
            return [\Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by('minute:'.$key),
                \Illuminate\Cache\RateLimiting\Limit::perDay(20)->by('day:'.$key)];
        });
    }
}
