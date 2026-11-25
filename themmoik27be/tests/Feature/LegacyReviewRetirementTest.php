<?php

namespace Tests\Feature;

use App\Models\{KhachHang, User};
use Illuminate\Support\Facades\DB;
use Tests\Support\IsolatedApiTestCase;

class LegacyReviewRetirementTest extends IsolatedApiTestCase
{
    public function test_removed_admin_review_routes_return_404_and_never_mutate_history(): void
    {
        DB::table('danh_gia_san_phams')->insert(['id' => 1, 'id_san_pham' => 1, 'id_khach_hang' => 1,
            'trang_thai' => 1, 'so_sao' => 5, 'noi_dung' => 'Keep existing review']);
        $admin = User::create(['name' => 'Admin', 'email' => 'retire-admin@example.test', 'password' => 'unused']);
        $customer = KhachHang::create(['ho_va_ten' => 'Test', 'email' => 'retire-customer@example.test', 'mat_khau' => 'unused',
            'role' => 'customer', 'is_khoa' => 0, 'is_kich_hoat' => 1]);
        $before = DB::table('danh_gia_san_phams')->get()->toJson();
        foreach ([null, $admin->createToken('test')->plainTextToken, $customer->createToken('test')->plainTextToken] as $token) {
            foreach (['get-data' => 'GET', 'change-status' => 'POST', 'destroy' => 'POST'] as $action => $method) {
                $this->app['auth']->forgetGuards();
                $this->json($method, '/api/admin/danh-gia/'.$action, ['id' => 1], $token ? ['Authorization' => 'Bearer '.$token] : [])
                    ->assertNotFound();
            }
        }
        $this->assertSame($before, DB::table('danh_gia_san_phams')->get()->toJson());
    }
}
