<?php

namespace Tests\Feature;

use App\Models\{DonHang, KhachHang, User};
use Illuminate\Support\Facades\DB;

class CustomerOrderHistoryTest extends ProductDetailDemoTest
{
    public function test_history_is_paginated_owned_and_hides_internal_fields(): void
    {
        $owner = KhachHang::first();
        $other = KhachHang::create(['ho_va_ten' => 'Other', 'email' => 'history-other@example.test', 'mat_khau' => 'unused', 'role' => 'customer', 'is_kich_hoat' => 1, 'is_khoa' => 0]);
        foreach (range(1, 12) as $i) DonHang::create(['id_khach_hang' => $owner->id, 'ma_don_hang' => 'OWN'.$i, 'tong_thanh_toan' => $i, 'trang_thai_don_hang' => 'da_xac_nhan', 'trang_thai_thanh_toan' => 'chua_thanh_toan']);
        $foreign = DonHang::create(['id_khach_hang' => $other->id, 'ma_don_hang' => 'PRIVATE']);
        DB::table('chi_tiet_don_hangs')->insert(['id_don_hang' => 12, 'ten_san_pham' => 'Snapshot tên cũ', 'ten_mau' => 'Đen', 'ten_kich_thuoc' => 'M', 'so_luong' => 1, 'gia_ban' => 12, 'thanh_tien' => 12]);
        $token = $owner->createToken('history')->plainTextToken;
        $this->withToken($token)->getJson('/api/client/don-hang')->assertOk()->assertJsonPath('data.total', 12)->assertJsonPath('data.last_page', 2)->assertJsonPath('data.data.0.ma_don_hang', 'OWN12')->assertJsonMissingPath('data.data.0.id_khach_hang');
        $this->withToken($token)->getJson('/api/client/don-hang?page=2')->assertOk()->assertJsonCount(2, 'data.data');
        $this->withToken($token)->getJson('/api/client/don-hang/12')->assertOk()->assertJsonPath('data.chi_tiets.0.ten_san_pham', 'Snapshot tên cũ')->assertJsonPath('data.trang_thai_don_hang', 'da_xac_nhan')->assertJsonMissingPath('data.checkout_fingerprint');
        $this->withToken($token)->getJson('/api/client/don-hang/'.$foreign->id)->assertNotFound();
        $this->withToken($token)->getJson('/api/client/don-hang/99999')->assertNotFound();
        $this->withToken($token)->getJson('/api/client/don-hang?per_page=999')->assertUnprocessable();
        $this->withToken($token)->getJson('/api/client/don-hang?page=0')->assertUnprocessable();
    }

    public function test_guest_admin_seller_locked_and_inactive_cannot_read_history(): void
    {
        $this->getJson('/api/client/don-hang')->assertUnauthorized();
        $this->getJson('/api/client/don-hang/1')->assertUnauthorized();
        $actors = [User::create(['name' => 'Admin history', 'email' => 'history-admin@example.test', 'password' => 'unused'])];
        foreach ([['seller', 1, 0], ['customer', 0, 0], ['customer', 1, 1]] as $i => [$role, $active, $locked]) {
            $actors[] = KhachHang::create(['ho_va_ten' => 'Denied', 'email' => 'denied'.$i.'@example.test', 'mat_khau' => 'unused', 'role' => $role, 'is_kich_hoat' => $active, 'is_khoa' => $locked]);
        }
        foreach ($actors as $actor) {
            $this->app['auth']->forgetGuards();
            $token = $actor->createToken('history')->plainTextToken;
            $this->withToken($token)->getJson('/api/client/don-hang')->assertForbidden();
            $this->app['auth']->forgetGuards();
            $this->withToken($token)->getJson('/api/client/don-hang/1')->assertForbidden();
        }
    }
}
