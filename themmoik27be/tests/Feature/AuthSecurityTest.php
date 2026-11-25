<?php

namespace Tests\Feature;

use App\Mail\SendOtpMail;
use App\Models\{KhachHang, User};
use App\Services\PasswordResetOtp;
use Illuminate\Support\Facades\{Cache, Hash, Mail};
use Tests\Support\IsolatedApiTestCase;

class AuthSecurityTest extends IsolatedApiTestCase
{
    private function customer(): KhachHang
    {
        return KhachHang::create(['ho_va_ten' => 'Security UAT', 'email' => 'security@example.test',
            'mat_khau' => Hash::make('Test-security-147!'), 'role' => 'customer', 'is_khoa' => 0, 'is_kich_hoat' => 1]);
    }

    private function api(string $path, array $data = [], ?string $token = null, string $ip = '127.0.0.1', string $method = 'POST')
    {
        $this->app['auth']->forgetGuards();
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])->json($method, '/api/'.$path, $data,
            $token ? ['Authorization' => 'Bearer '.$token] : []);
    }

    private function send(KhachHang $customer): string
    {
        Mail::fake();
        $this->api('forgot-password/send-otp', ['email' => $customer->email])->assertOk();
        return (string) Mail::sent(SendOtpMail::class)->last()->otp;
    }

    private function resetData(KhachHang $customer, string $otp): array
    {
        return ['email' => $customer->email, 'otp' => $otp, 'password' => 'Changed-security-147!',
            'password_confirmation' => 'Changed-security-147!'];
    }

    public function test_login_aliases_share_account_budget_and_retry_after_expires(): void
    {
        $customer = $this->customer();
        $aliases = ['dang-nhap', 'admin-login', 'auth/admin-login'];
        for ($i = 0; $i < 10; $i++) {
            $this->api($aliases[$i % 3], ['email' => $customer->email, 'mat_khau' => 'Wrong-security-147!'])->assertUnauthorized();
        }
        $this->api('admin-login', ['email' => strtoupper($customer->email), 'mat_khau' => 'Test-security-147!'], null, '127.0.0.2')
            ->assertStatus(429)->assertHeader('Retry-After');
        $this->assertSame(0, $customer->tokens()->count());
        $this->travel(61)->seconds();
        $this->api('dang-nhap', ['email' => $customer->email, 'mat_khau' => 'Test-security-147!'])->assertOk();
    }

    public function test_ip_limit_stops_rotating_email_addresses(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->api('dang-nhap', ['email' => "missing$i@example.test", 'mat_khau' => 'Wrong-security-147!'])->assertUnauthorized();
        }
        $this->api('dang-nhap', ['email' => 'next@example.test', 'mat_khau' => 'Wrong-security-147!'])->assertStatus(429);
    }

    public function test_admin_login_success_blocked_customer_and_role_boundaries(): void
    {
        $customer = $this->customer();
        $admin = User::create(['name' => 'Admin QA', 'email' => 'admin-security@example.test', 'password' => Hash::make('Test-security-147!')]);
        $adminLogin = $this->api('auth/admin-login', ['email' => $admin->email, 'mat_khau' => 'Test-security-147!'])->assertOk();
        $this->api('admin/danh-muc/get-data', [], $adminLogin->json('token'), method: 'GET')->assertOk();
        $login = $this->api('dang-nhap', ['email' => $customer->email, 'mat_khau' => 'Test-security-147!'])->assertOk();
        $this->api('admin/danh-muc/get-data', [], $login->json('token'), method: 'GET')->assertForbidden();
        $this->api('client/dat-hang')->assertUnauthorized();
        $customer->update(['is_khoa' => 1]);
        $this->api('dang-nhap', ['email' => $customer->email, 'mat_khau' => 'Test-security-147!'])->assertForbidden();
    }

    public function test_registration_otp_budget_exact_expiry_and_success_are_enforced(): void
    {
        $customer = $this->customer();
        $customer->update(['is_kich_hoat' => 0, 'otp_hash' => Hash::make('123456'), 'otp_expires_at' => now()->addMinutes(10)]);
        for ($i = 0; $i < 5; $i++) $this->api('xac-thuc-otp', ['email' => $customer->email, 'otp' => '000000'])->assertUnprocessable();
        $this->api('xac-thuc-otp', ['email' => $customer->email, 'otp' => '123456'])->assertStatus(429);
        $this->assertSame(0, (int) $customer->fresh()->is_kich_hoat);
        $this->travel(601)->seconds();
        $this->api('xac-thuc-otp', ['email' => $customer->email, 'otp' => '123456'])->assertUnprocessable();
        $customer->update(['otp_expires_at' => now()]);
        $this->api('xac-thuc-otp', ['email' => $customer->email, 'otp' => '123456'])->assertUnprocessable();
        $customer->update(['otp_expires_at' => now()->addMinute()]);
        $this->api('xac-thuc-otp', ['email' => $customer->email, 'otp' => '123456'])->assertOk();
        $this->api('xac-thuc-otp', ['email' => $customer->email, 'otp' => '123456'])->assertUnprocessable();
        $this->assertNull($customer->fresh()->otp_hash);
    }

    public function test_registration_send_is_limited_without_creating_extra_accounts(): void
    {
        Mail::fake();
        $data = ['ho_va_ten' => 'Khach UAT', 'email' => 'register-security@example.test', 'so_dien_thoai' => '0900000000',
            'mat_khau' => 'Test-security-147!', 'xac_nhan_mk' => 'Test-security-147!'];
        $this->api('dang-ky', $data)->assertCreated();
        $this->api('dang-ky', $data)->assertUnprocessable();
        $this->api('dang-ky', $data)->assertUnprocessable();
        $this->api('dang-ky', $data)->assertStatus(429);
        $this->assertSame(1, KhachHang::count());
        // raw registration email không phải Mailable; send bị giới hạn trước controller.
    }

    public function test_reset_resend_cooldown_and_hourly_quota_do_not_clear_guess_budget(): void
    {
        $customer = $this->customer();
        $this->send($customer);
        $this->api('forgot-password/send-otp', ['email' => $customer->email])->assertStatus(429);
        for ($i = 0; $i < 4; $i++) $this->api('forgot-password/verify-otp', ['email' => $customer->email, 'otp' => '000000'])->assertStatus(400);
        $this->travel(61)->seconds();
        $this->api('forgot-password/send-otp', ['email' => $customer->email])->assertOk();
        $this->api('forgot-password/reset-password', $this->resetData($customer, '000000'))->assertStatus(400);
        $this->api('forgot-password/verify-otp', ['email' => $customer->email, 'otp' => '000000'])->assertStatus(429);
        $this->travel(61)->seconds();
        $this->api('forgot-password/send-otp', ['email' => $customer->email])->assertOk();
        $this->travel(61)->seconds();
        $this->api('forgot-password/send-otp', ['email' => $customer->email])->assertStatus(429);
    }

    public function test_reset_otp_is_hashed_expires_at_boundary_and_old_format_is_rejected(): void
    {
        $customer = $this->customer();
        $otp = $this->send($customer);
        $record = Cache::get(app(PasswordResetOtp::class)->key($customer->email));
        $this->assertNotSame($otp, $record['hash']);
        $this->assertTrue(Hash::check($otp, $record['hash']));
        $this->travelTo(\Carbon\Carbon::createFromTimestamp($record['expires_at']));
        $this->api('forgot-password/verify-otp', ['email' => $customer->email, 'otp' => $otp])->assertStatus(400);
        $this->api('forgot-password/reset-password', $this->resetData($customer, $otp))->assertStatus(400);
        Cache::put('otp_reset_'.$customer->email, '123456', 300);
        $this->api('forgot-password/reset-password', $this->resetData($customer, '123456'))->assertStatus(400);
        $this->assertTrue(Hash::check('Test-security-147!', $customer->fresh()->mat_khau));
    }

    public function test_reset_consumes_code_revokes_all_customer_tokens_and_preserves_other_accounts(): void
    {
        $customer = $this->customer();
        $old = $customer->createToken('first')->plainTextToken;
        $customer->createToken('second');
        $other = KhachHang::create(['ho_va_ten' => 'Other', 'email' => 'other-security@example.test', 'mat_khau' => 'unused',
            'role' => 'customer', 'is_khoa' => 0, 'is_kich_hoat' => 1]);
        $otherToken = $other->createToken('other')->plainTextToken;
        $admin = User::create(['id' => $customer->id, 'name' => 'Admin', 'email' => 'separate-admin@example.test', 'password' => 'unused']);
        $adminToken = $admin->createToken('admin')->plainTextToken;
        $otp = $this->send($customer);
        $this->api('forgot-password/verify-otp', ['email' => $customer->email, 'otp' => $otp])->assertOk();
        $this->api('forgot-password/reset-password', $this->resetData($customer, $otp))->assertOk();
        $this->assertSame(0, $customer->tokens()->count());
        $this->api('kiem-tra-dang-nhap', [], $old, method: 'GET')->assertUnauthorized();
        $this->api('kiem-tra-dang-nhap', [], $otherToken, method: 'GET')->assertOk();
        $this->api('admin/danh-muc/get-data', [], $adminToken, method: 'GET')->assertOk();
        $this->api('forgot-password/reset-password', $this->resetData($customer, $otp))->assertStatus(400);
        $this->api('dang-nhap', ['email' => $customer->email, 'mat_khau' => 'Test-security-147!'])->assertUnauthorized();
        $login = $this->api('dang-nhap', ['email' => $customer->email, 'mat_khau' => 'Changed-security-147!'])->assertOk();
        $this->api('kiem-tra-dang-nhap', [], $login->json('token'), method: 'GET')->assertOk();
        $customer->tokens()->update(['expires_at' => now()->subSecond()]);
        $this->api('kiem-tra-dang-nhap', [], $login->json('token'), method: 'GET')->assertUnauthorized();
    }

    public function test_cache_lock_prevents_reset_while_another_request_is_consuming_code(): void
    {
        $customer = $this->customer();
        $otp = $this->send($customer);
        $lock = Cache::lock(app(PasswordResetOtp::class)->key($customer->email).':lock', 30);
        $this->assertTrue($lock->get());
        try { $this->api('forgot-password/reset-password', $this->resetData($customer, $otp))->assertStatus(429); }
        finally { $lock->release(); }
        $this->assertTrue(Hash::check('Test-security-147!', $customer->fresh()->mat_khau));
        $this->api('forgot-password/reset-password', $this->resetData($customer, $otp))->assertOk();
    }

    public function test_resend_replaces_old_code_and_failed_reset_preserves_password_and_token(): void
    {
        $customer = $this->customer();
        $token = $customer->createToken('existing-device')->plainTextToken;
        $this->send($customer);
        $key = app(PasswordResetOtp::class)->key($customer->email);
        // Mã fixture xác định, khác mọi mã sinh mới (random_int bắt đầu ở 100000).
        $record = Cache::get($key); $record['hash'] = Hash::make('000000');
        Cache::put($key, $record, 300);
        $this->api('forgot-password/verify-otp', ['email' => $customer->email, 'otp' => '000000'])->assertOk();
        $this->travel(61)->seconds();
        $this->api('forgot-password/send-otp', ['email' => $customer->email])->assertOk();
        $this->api('forgot-password/reset-password', $this->resetData($customer, '000000'))->assertStatus(400);
        $this->assertTrue(Hash::check('Test-security-147!', $customer->fresh()->mat_khau));
        $this->api('kiem-tra-dang-nhap', [], $token, method: 'GET')->assertOk();
    }
}
