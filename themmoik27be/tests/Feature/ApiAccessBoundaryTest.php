<?php

namespace Tests\Feature;

use App\Models\KhachHang;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\IsolatedApiTestCase;

class ApiAccessBoundaryTest extends IsolatedApiTestCase
{
    private function customer(string $role = 'seller'): KhachHang
    {
        return KhachHang::create([
            'ho_va_ten' => 'Test', 'email' => $role.'@example.test',
            'mat_khau' => Hash::make('Test-pass-471!'), 'role' => $role,
            'is_khoa' => 0, 'is_kich_hoat' => 1,
        ]);
    }

    private function callApi(string $method, string $uri, ?string $token = null, array $data = [])
    {
        // Guard không được giữ người dùng của request trước trong cùng test.
        $this->app['auth']->forgetGuards();

        return $this->json($method, $uri, $data, $token ? ['Authorization' => 'Bearer '.$token] : []);
    }

    public function test_every_admin_route_rejects_guests_and_non_admin_tokens(): void
    {
        $seller = $this->customer()->createToken('seller')->plainTextToken;
        $customer = $this->customer('customer')->createToken('customer')->plainTextToken;
        $before = DB::table('danh_mucs')->orderBy('id')->get()->toJson();
        $count = 0;

        foreach (app('router')->getRoutes() as $route) {
            if (!str_starts_with($route->uri(), 'api/admin/')) {
                continue;
            }
            $count++;
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth:sanctum', $middleware);
            $this->assertContains('admin', $middleware);
            $this->assertLessThan(array_search('admin', $middleware), array_search('auth:sanctum', $middleware));
            foreach ([null, 'invalid-token', $seller, $customer] as $token) {
                $this->callApi($route->methods()[0], '/'.preg_replace('/\{[^}]+\}/', '1', $route->uri()), $token, ['id' => 1])
                    ->assertStatus(in_array($token, [$seller, $customer], true) ? 403 : 401);
            }
        }

        $this->assertGreaterThan(0, $count);
        $this->assertSame($before, DB::table('danh_mucs')->orderBy('id')->get()->toJson());
    }

    public function test_admin_login_issues_a_user_token_accepted_by_admin_api(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => Hash::make('Test-pass-471!')]);
        $login = $this->callApi('POST', '/api/admin-login', null, [
            'email' => $admin->email, 'mat_khau' => 'Test-pass-471!',
        ])->assertOk()->assertJsonPath('nguoi_dung.role', 'admin');
        $token = $login->json('token');
        $this->assertInstanceOf(User::class, \Laravel\Sanctum\PersonalAccessToken::findToken($token)->tokenable);
        $this->callApi('GET', '/api/admin/danh-muc/get-data', $token)->assertOk()->assertJsonPath('status', 1);
        DB::table('san_phams')->where('id', 2)->update(['id_cua_hang' => 99]);
        $this->callApi('GET', '/api/admin/san-pham/get-data', $token)->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.id_cua_hang', 99);

        $admin->tokens()->update(['expires_at' => now()->subMinute()]);
        $this->callApi('GET', '/api/admin/danh-muc/get-data', $token)->assertUnauthorized();
        $admin->tokens()->delete();
        $this->callApi('GET', '/api/admin/danh-muc/get-data', $token)->assertUnauthorized();
    }

    public function test_admin_wrong_and_legacy_fixed_passwords_never_issue_tokens(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => Hash::make('Actual-admin-pass-471!')]);
        $originalHash = $admin->password;
        foreach (['/api/dang-nhap', '/api/admin-login', '/api/auth/admin-login'] as $endpoint) {
            foreach (['Wrong-admin-pass-471!', 'Admin@123'] as $password) {
                $response = $this->callApi('POST', $endpoint, null, ['email' => $admin->email, 'mat_khau' => $password])
                    ->assertUnauthorized()->assertJsonPath('status', 0);
                $this->assertArrayNotHasKey('token', $response->json());
                $this->assertSame(0, $admin->tokens()->count());
            }
        }
        $this->assertSame($originalHash, $admin->fresh()->password);
    }

    public function test_all_login_endpoints_accept_only_the_valid_admin_hash(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => Hash::make('Actual-admin-pass-471!')]);
        foreach (['/api/dang-nhap', '/api/admin-login', '/api/auth/admin-login'] as $endpoint) {
            $login = $this->callApi('POST', $endpoint, null, ['email' => $admin->email, 'mat_khau' => 'Actual-admin-pass-471!'])
                ->assertOk()->assertJsonPath('status', 1)->assertJsonPath('nguoi_dung.role', 'admin');
            $this->callApi('GET', '/api/kiem-tra-dang-nhap', $login->json('token'))->assertOk()->assertJsonPath('nguoi_dung.role', 'admin');
            $this->callApi('GET', '/api/admin/san-pham/get-data', $login->json('token'))->assertOk();
        }
    }

    public function test_admin_can_manage_products_without_changing_legacy_shop_ownership(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => Hash::make('Actual-admin-pass-471!')]);
        $token = $admin->createToken('admin')->plainTextToken;
        DB::table('san_phams')->where('id', 2)->update(['id_cua_hang' => 99]);
        $payload = ['id' => 2, 'ten_san_pham' => 'Ẩn', 'id_danh_muc' => 1, 'gia_ban' => 150, 'hinh_anh' => 'history.jpg'];
        $this->callApi('POST', '/api/admin/san-pham/update', $token, $payload)->assertOk()->assertJsonPath('status', 1);
        $product = DB::table('san_phams')->where('id', 2)->first();
        $this->assertSame(99, $product->id_cua_hang);
        $this->assertSame('an', $product->slug_san_pham);
        $this->assertSame(0, $product->trang_thai);
        $this->assertSame(150, $product->gia_ban);
        $this->callApi('GET', '/api/client/san-pham/an')->assertNotFound();
        $created = $this->callApi('POST', '/api/admin/san-pham/create', $token, [
            'ten_san_pham' => 'QA temporary product', 'id_danh_muc' => 1, 'gia_ban' => 200, 'trang_thai' => 0,
        ])->assertCreated();
        $id = $created->json('data.id');
        $this->callApi('POST', '/api/admin/san-pham/change-status', $token, ['id' => $id])->assertOk();
        $this->assertSame(1, DB::table('san_phams')->where('id', $id)->value('trang_thai'));
        $this->callApi('POST', '/api/admin/san-pham/destroy', $token, ['id' => $id])->assertOk();
        $this->assertFalse(DB::table('san_phams')->where('id', $id)->exists());
    }

    public function test_seller_routes_are_removed_and_legacy_token_is_rejected(): void
    {
        $seller = $this->customer();
        $token = $seller->createToken('seller')->plainTextToken;
        foreach (app('router')->getRoutes() as $route) {
            $this->assertFalse(str_starts_with($route->uri(), 'api/seller/'));
        }
        foreach (['danh-muc/get-data', 'san-pham/get-data', 'don-hang/get-data', 'cua-hang/get-data'] as $path) {
            $this->callApi('GET', '/api/seller/'.$path, $token)->assertNotFound();
        }
        foreach (['san-pham/create', 'san-pham/update', 'san-pham/destroy', 'cua-hang/update'] as $path) {
            $this->callApi('POST', '/api/seller/'.$path, $token)->assertNotFound();
        }
        $this->callApi('POST', '/api/dang-nhap', null, ['email' => $seller->email, 'mat_khau' => 'Test-pass-471!'])->assertForbidden();
        $this->callApi('GET', '/api/kiem-tra-dang-nhap', $token)->assertForbidden();
        $this->callApi('GET', '/api/client/san-phams', $token)->assertForbidden();
        $this->callApi('POST', '/api/client/dat-hang', $token)->assertForbidden();
        $this->assertSame(1, $seller->tokens()->count());
    }

    public function test_registration_rejects_seller_and_admin_without_creating_accounts(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        foreach (['seller', 'admin'] as $role) {
            $this->callApi('POST', '/api/dang-ky', null, $this->registrationData() + ['role' => $role])->assertUnprocessable()->assertJsonValidationErrors('role');
        }
        $this->assertSame(0, KhachHang::count());
        \Illuminate\Support\Facades\Mail::assertNothingSent();
    }

    private function registrationData(): array
    {
        return ['ho_va_ten' => 'Khach Hang', 'email' => 'new@example.test', 'so_dien_thoai' => '0901234567', 'mat_khau' => 'Test-pass-471!', 'xac_nhan_mk' => 'Test-pass-471!'];
    }

    public function test_customer_registration_otp_and_login_are_preserved(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $data = $this->registrationData();
        $this->callApi('POST', '/api/dang-ky', null, $data)->assertCreated()->assertJsonPath('data.role', 'customer');
        $customer = KhachHang::where('email', $data['email'])->firstOrFail();
        $this->assertSame(0, (int) $customer->is_kich_hoat);
        $this->assertNotNull($customer->otp_hash);
        $this->assertSame(0, DB::table('cua_hangs')->count());
        $this->callApi('POST', '/api/dang-nhap', null, $data)->assertForbidden();
        // Mã xác định chỉ tồn tại trong database bộ nhớ, không gửi email thật.
        $customer->update(['otp_hash' => Hash::make('123456'), 'otp_expires_at' => now()->addMinutes(10)]);
        $this->callApi('POST', '/api/xac-thuc-otp', null, ['email' => $data['email'], 'otp' => '000000'])->assertUnprocessable();
        $this->callApi('POST', '/api/xac-thuc-otp', null, ['email' => $data['email'], 'otp' => '123456'])->assertOk()->assertJsonPath('status', 1);
        $login = $this->callApi('POST', '/api/dang-nhap', null, $data)->assertOk()->assertJsonPath('nguoi_dung.role', 'customer');
        $this->callApi('GET', '/api/kiem-tra-dang-nhap', $login->json('token'))->assertOk();
        $this->callApi('GET', '/api/client/san-phams?all=1', $login->json('token'))->assertOk();
    }

    public function test_customer_password_reset_is_preserved(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $customer = $this->customer('customer');
        $this->callApi('POST', '/api/forgot-password/send-otp', null, ['email' => $customer->email])->assertOk();
        $otp = \Illuminate\Support\Facades\Cache::get('otp_reset_'.$customer->email);
        $this->assertNotNull($otp);
        $this->callApi('POST', '/api/forgot-password/verify-otp', null, ['email' => $customer->email, 'otp' => $otp])->assertOk();
        $this->callApi('POST', '/api/forgot-password/reset-password', null, [
            'email' => $customer->email, 'otp' => $otp, 'password' => 'Changed-pass-471!', 'password_confirmation' => 'Changed-pass-471!',
        ])->assertOk();
        $this->callApi('POST', '/api/dang-nhap', null, ['email' => $customer->email, 'mat_khau' => 'Changed-pass-471!'])->assertOk();
    }

    public function test_client_responses_are_public_and_preserve_frontend_fields(): void
    {
        $this->callApi('GET', '/api/client/home-data')->assertOk();
        foreach (['ao-mau', '1'] as $slug) {
            $this->callApi('GET', '/api/client/san-pham/'.$slug)->assertOk()->assertJsonPath('data.san_pham.id', 1);
        }
        foreach (['an', '2'] as $slug) {
            $this->callApi('GET', '/api/client/san-pham/'.$slug)->assertNotFound();
        }
        $categories = $this->callApi('GET', '/api/client/danh-mucs')->assertOk()->assertJsonCount(9, 'data');
        $byId = collect($categories->json('data'))->keyBy('id');
        $this->assertSame(1, $byId[1]['san_phams_count']);
        $this->assertSame(0, $byId[2]['san_phams_count']);
        $this->assertFalse($byId->has(10));
        $this->callApi('GET', '/api/client/tuy-chon-san-pham')->assertOk()
            ->assertJsonCount(1, 'data.mau_sacs')->assertJsonCount(2, 'data.kich_thuocs')
            ->assertJsonPath('data.mau_sacs.0.ten_mau', 'Đen')->assertJsonPath('data.kich_thuocs.1.ten_kich_thuoc', 'L');
        $this->callApi('GET', '/api/client/san-phams?all=1')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.danh_muc.slug_danh_muc', 'danh-muc-1')
            ->assertJsonStructure(['data' => [['id', 'ten_san_pham', 'slug_san_pham', 'gia_ban', 'gia_khuyen_mai', 'hinh_anh', 'mo_ta_ngan', 'is_noi_bat', 'is_flash_sale', 'luot_xem', 'id_danh_muc', 'danh_muc']]]);
        foreach (['danh-mucs', 'tuy-chon-san-pham', 'san-phams?all=1'] as $path) {
            $this->callApi('GET', '/api/client/'.$path, 'invalid-token')->assertOk();
        }
        $this->callApi('GET', '/api/client/san-phams?all=1&danh_muc=danh-muc-1&tu_khoa=Áo')->assertOk()->assertJsonCount(1, 'data');
        $this->callApi('GET', '/api/client/san-phams?all=1&id_danh_muc=2')->assertOk()->assertJsonCount(0, 'data');
    }
}
