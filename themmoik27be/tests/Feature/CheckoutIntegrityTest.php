<?php

namespace Tests\Feature;

use App\Models\MaGiamGia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutIntegrityTest extends ProductDetailDemoTest
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('CREATE TABLE ma_giam_gias (id INTEGER PRIMARY KEY, ma_code TEXT UNIQUE, ten_giam_gia TEXT, loai_giam TEXT, gia_tri NUMERIC, gia_tri_toi_thieu NUMERIC DEFAULT 0, giam_toi_da NUMERIC, so_luong INTEGER DEFAULT 100, so_luong_da_dung INTEGER DEFAULT 0, ngay_bat_dau TEXT, ngay_ket_thuc TEXT, trang_thai INTEGER DEFAULT 1, created_at TEXT, updated_at TEXT)');
        foreach (['ton_kho_da_giu INTEGER DEFAULT 0', 'voucher_da_giu INTEGER DEFAULT 0', 'checkout_fingerprint TEXT'] as $column) DB::statement('ALTER TABLE don_hangs ADD COLUMN '.$column);
        foreach (['doi_tuong TEXT DEFAULT \'all\'', 'gioi_han_moi_khach INTEGER', 'ids_khach_hang TEXT'] as $column) DB::statement('ALTER TABLE ma_giam_gias ADD COLUMN '.$column);

    }

    private function payload(): array
    {
        return ['ho_ten' => 'QA demo', 'so_dien_thoai' => '0900000000', 'dia_chi_giao' => 'Fixture only',
            'tong_thanh_toan' => 1, 'tong_tien_hang' => 1, 'phi_van_chuyen' => 0, 'tien_giam_gia' => 999999,
            'id_khach_hang' => 999, 'phuong_thuc_thanh_toan' => 'MB_BANK',
            'items' => [['id' => 1, 'id_chi_tiet_san_pham' => 50, 'quantity' => 2, 'gia_ban' => 1, 'ten_san_pham' => 'Giả mạo']]];
    }

    public function test_logout_revokes_admin_token_on_server(): void
    {
        $token = \App\Models\User::create(['name' => 'QA', 'email' => 'logout-admin@example.test', 'password' => 'unused'])->createToken('admin')->plainTextToken;
        $this->withToken($token)->postJson('/api/dang-xuat')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/admin/san-pham/get-data')->assertUnauthorized();
    }

    public function test_admin_revenue_and_bestsellers_only_count_completed_paid_orders(): void
    {
        foreach ([['da_giao', 'da_thanh_toan', 400], ['da_huy', 'chua_thanh_toan', 900], ['cho_xac_nhan', 'chua_thanh_toan', 800], ['da_giao', 'hoan_tien', 700]] as $index => [$status, $payment, $total]) {
            DB::table('don_hangs')->insert(['id' => $index + 1, 'trang_thai_don_hang' => $status, 'trang_thai_thanh_toan' => $payment, 'tong_thanh_toan' => $total, 'created_at' => '2026-11-03 10:00:00']);
            DB::table('chi_tiet_don_hangs')->insert(['id_don_hang' => $index + 1, 'id_san_pham' => 1, 'ten_san_pham' => 'Áo mẫu', 'so_luong' => $index + 1, 'thanh_tien' => $total]);
        }
        $admin = \App\Models\User::create(['name' => 'QA', 'email' => 'stats-admin@example.test', 'password' => 'unused'])->createToken('admin')->plainTextToken;
        $this->withToken($admin)->getJson('/api/admin/thong-ke/tong-quan')->assertOk()->assertJsonPath('data.tong_doanh_thu', 400)->assertJsonPath('data.top_san_pham.0.tong_so_luong', 1);
        DB::enableQueryLog(); DB::flushQueryLog();
        $this->withToken($admin)->getJson('/api/admin/thong-ke/doanh-thu-theo-thang?nam=2026')->assertOk()->assertJsonPath('data.10.tong_doanh_thu', 400)->assertJsonPath('data.10.so_don_hang', 1)->assertJsonCount(12, 'data');
        $queries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], 'SUM(tong_thanh_toan)'));
        $this->assertCount(1, $queries); DB::disableQueryLog();
        $this->withToken($admin)->getJson('/api/admin/thong-ke/doanh-thu-theo-nam?tu_nam=2025&den_nam=2026')->assertOk()->assertJsonPath('data.0.tong_doanh_thu', 0)->assertJsonPath('data.1.tong_doanh_thu', 400);
    }

    public function test_customer_management_keeps_legacy_seller_out_of_active_business(): void
    {
        $seller = \App\Models\KhachHang::create(['ho_va_ten' => 'Legacy seller', 'email' => 'legacy-seller@example.test', 'mat_khau' => 'unused', 'role' => 'seller', 'is_khoa' => 0, 'is_kich_hoat' => 1]);
        $admin = \App\Models\User::create(['name' => 'QA', 'email' => 'customer-admin@example.test', 'password' => 'unused'])->createToken('admin')->plainTextToken;
        $this->withToken($admin)->getJson('/api/admin/khach-hang/get-data')->assertOk()->assertJsonCount(1, 'data');
        $this->withToken($admin)->postJson('/api/admin/khach-hang/toggle-block', ['id' => $seller->id])->assertNotFound();
        $this->assertEquals(0, $seller->fresh()->is_khoa);
        $this->assertSame('seller', $seller->fresh()->role);
    }

    public function test_quote_checks_price_and_voucher_without_reserving_stock_or_usage(): void
    {
        MaGiamGia::create(['ma_code' => 'QUOTE', 'ten_giam_gia' => 'QA', 'loai_giam' => 'phan_tram', 'gia_tri' => 10, 'so_luong' => 1, 'so_luong_da_dung' => 0, 'trang_thai' => 1]);
        $payload = $this->payload(); $payload['ma_giam_gia_su_dung'] = 'QUOTE';
        unset($payload['ho_ten'], $payload['so_dien_thoai'], $payload['dia_chi_giao']);
        $this->postJson('/api/client/kiem-tra-voucher', $payload)->assertOk()->assertJsonPath('data.tong_thanh_toan', 30180);
        $this->assertEquals(4, DB::table('chi_tiet_san_phams')->where('id', 50)->value('so_luong_ton'));
        $this->assertEquals(0, MaGiamGia::first()->so_luong_da_dung);
        $this->assertSame(0, DB::table('don_hangs')->count());
        $payload['items'][] = $payload['items'][0]; $payload['items'][0]['quantity'] = 3;
        $this->postJson('/api/client/kiem-tra-voucher', $payload)->assertUnprocessable();
    }

    public function test_idempotency_key_is_not_revealed_by_order_number_and_different_payload_is_rejected(): void
    {
        $key = (string) Str::uuid();
        $response = $this->postJson('/api/client/dat-hang', $this->payload(), ['Idempotency-Key' => $key])->assertCreated();
        $this->assertNotSame('HD_'.strtoupper(str_replace('-', '', $key)), $response->json('don_hang.ma_don_hang'));
        $payload = $this->payload(); $payload['items'][0]['quantity'] = 1;
        $this->postJson('/api/client/dat-hang', $payload, ['Idempotency-Key' => $key])->assertStatus(409);
        $this->assertSame(1, DB::table('don_hangs')->count());
    }

    public function test_customer_audiences_and_per_customer_limits_are_checked_on_server(): void
    {
        $customer = \App\Models\KhachHang::first();
        $token = $customer->createToken('qa')->plainTextToken;
        $coupon = MaGiamGia::create(['ma_code' => 'NEW', 'ten_giam_gia' => 'QA', 'loai_giam' => 'co_dinh', 'gia_tri' => 10, 'so_luong' => 10, 'so_luong_da_dung' => 0, 'trang_thai' => 1, 'doi_tuong' => 'new', 'gioi_han_moi_khach' => 1]);
        $payload = $this->payload(); $payload['items'][0]['quantity'] = 1; $payload['ma_giam_gia_su_dung'] = 'NEW';
        $this->postJson('/api/client/dat-hang', $payload)->assertUnprocessable();
        $this->withToken($token)->postJson('/api/client/dat-hang', $payload)->assertCreated();
        $this->withToken($token)->postJson('/api/client/dat-hang', $payload)->assertUnprocessable();
        $coupon->update(['doi_tuong' => 'selected', 'ids_khach_hang' => [999]]);
        $this->withToken($token)->postJson('/api/client/dat-hang', $payload)->assertUnprocessable();
        $coupon->update(['ids_khach_hang' => [$customer->id]]);
        $this->withToken($token)->postJson('/api/client/dat-hang', $payload)->assertUnprocessable();
        $coupon->update(['gioi_han_moi_khach' => 2]);
        $this->withToken($token)->postJson('/api/client/dat-hang', $payload)->assertCreated();
    }

    public function test_admin_cancellation_restores_reservations_once_and_preserves_history(): void
    {
        MaGiamGia::create(['ma_code' => 'CANCEL', 'ten_giam_gia' => 'QA', 'loai_giam' => 'co_dinh', 'gia_tri' => 10, 'so_luong' => 1, 'so_luong_da_dung' => 0, 'trang_thai' => 1]);
        $payload = $this->payload(); $payload['ma_giam_gia_su_dung'] = 'CANCEL';
        $result = $this->postJson('/api/client/dat-hang', $payload)->assertCreated();
        $id = $result->json('don_hang.id');
        $customer = \App\Models\KhachHang::first()->createToken('customer')->plainTextToken;
        $this->withToken($customer)->postJson('/api/admin/don-hang/update-status', ['id' => $id, 'trang_thai_don_hang' => 'da_huy'])->assertForbidden();
        $this->app['auth']->forgetGuards();
        $admin = \App\Models\User::create(['name' => 'QA Admin', 'email' => 'qa-admin@example.test', 'password' => 'unused'])->createToken('admin')->plainTextToken;
        foreach ([1, 2] as $_) $this->withToken($admin)->postJson('/api/admin/don-hang/update-status', ['id' => $id, 'trang_thai_don_hang' => 'da_huy'])->assertOk();
        $this->assertEquals(4, DB::table('chi_tiet_san_phams')->where('id', 50)->value('so_luong_ton'));
        $this->assertEquals(0, MaGiamGia::first()->so_luong_da_dung);
        $this->withToken($admin)->postJson('/api/admin/don-hang/update-status', ['id' => $id, 'trang_thai_don_hang' => 'da_xac_nhan'])->assertStatus(409);
        $this->withToken($admin)->postJson('/api/admin/don-hang/destroy', ['id' => $id])->assertStatus(409);
        $this->assertSame(1, DB::table('don_hangs')->count());
        $this->assertSame(1, DB::table('chi_tiet_don_hangs')->count());
    }

    public function test_admin_voucher_validation_and_normalized_unique_code(): void
    {
        $admin = \App\Models\User::create(['name' => 'QA Admin', 'email' => 'voucher-admin@example.test', 'password' => 'unused'])->createToken('admin')->plainTextToken;
        $payload = ['ma_code' => ' qa10 ', 'ten_giam_gia' => 'QA', 'loai_giam' => 'phan_tram', 'gia_tri' => 101, 'so_luong' => 10, 'trang_thai' => 1];
        $this->withToken($admin)->postJson('/api/admin/ma-giam-gia/create', $payload)->assertUnprocessable();
        $payload['gia_tri'] = 10;
        $this->withToken($admin)->postJson('/api/admin/ma-giam-gia/create', $payload)->assertCreated();
        $this->withToken($admin)->postJson('/api/admin/ma-giam-gia/create', $payload)->assertUnprocessable();
        $this->assertSame('QA10', MaGiamGia::first()->ma_code);
    }

    public function test_server_reprices_and_idempotency_does_not_consume_stock_twice(): void
    {
        $key = (string) Str::uuid();
        $result = $this->postJson('/api/client/dat-hang', $this->payload(), ['Idempotency-Key' => $key])->assertCreated()
            ->assertJsonPath('don_hang.tong_tien_hang', 200)->assertJsonPath('don_hang.tong_thanh_toan', 30200)
            ->assertJsonPath('don_hang.id_khach_hang', null)->assertJsonPath('vietqr_url', null);
        $this->postJson('/api/client/dat-hang', $this->payload(), ['Idempotency-Key' => $key])->assertCreated()
            ->assertJsonPath('don_hang.ma_don_hang', $result->json('don_hang.ma_don_hang'));
        $this->assertSame(1, DB::table('don_hangs')->count());
        $this->assertEquals(2, DB::table('chi_tiet_san_phams')->where('id', 50)->value('so_luong_ton'));
        $this->assertSame('Áo mẫu', DB::table('chi_tiet_don_hangs')->value('ten_san_pham'));
    }

    public function test_duplicate_lines_and_missing_variant_roll_back_whole_order(): void
    {
        $payload = $this->payload();
        $payload['items'][0]['quantity'] = 3;
        $payload['items'][] = $payload['items'][0];
        $this->postJson('/api/client/dat-hang', $payload)->assertUnprocessable();
        $payload = $this->payload(); unset($payload['items'][0]['id_chi_tiet_san_pham']);
        $this->postJson('/api/client/dat-hang', $payload)->assertUnprocessable();
        $payload['items'] = [];
        $this->postJson('/api/client/dat-hang', $payload)->assertUnprocessable();
        $this->assertSame(0, DB::table('don_hangs')->count());
        $this->assertEquals(4, DB::table('chi_tiet_san_phams')->where('id', 50)->value('so_luong_ton'));
    }

    public function test_voucher_limit_is_consumed_once_and_invalid_coupon_rolls_back_stock(): void
    {
        MaGiamGia::create(['ma_code' => 'QA50', 'ten_giam_gia' => 'QA', 'loai_giam' => 'phan_tram', 'gia_tri' => 50, 'giam_toi_da' => 80, 'so_luong' => 1, 'so_luong_da_dung' => 0, 'trang_thai' => 1]);
        $payload = $this->payload(); $payload['ma_giam_gia_su_dung'] = 'qa50';
        $this->postJson('/api/client/dat-hang', $payload)->assertCreated()->assertJsonPath('don_hang.tien_giam_gia', 80)->assertJsonPath('don_hang.tong_thanh_toan', 30120);
        $this->postJson('/api/client/dat-hang', $payload)->assertUnprocessable()->assertJsonValidationErrors('ma_giam_gia_su_dung');
        $this->assertEquals(1, MaGiamGia::first()->so_luong_da_dung);
        $this->assertEquals(2, DB::table('chi_tiet_san_phams')->where('id', 50)->value('so_luong_ton'));
    }

    public function test_demo_payment_needs_order_capability_and_is_repeatable_without_real_bank(): void
    {
        $result = $this->postJson('/api/client/dat-hang', $this->payload())->assertCreated();
        $code = $result->json('don_hang.ma_don_hang');
        $this->getJson('/api/kiem-tra-thanh-toan/'.$code)->assertForbidden();
        $this->postJson('/api/giao-dich/mo-phong', ['ma_hoa_don' => $code])->assertForbidden();
        $payment = ['ma_hoa_don' => $code, 'payment_token' => $result->json('payment_token')];
        $this->postJson('/api/giao-dich/mo-phong', $payment)->assertOk();
        $this->postJson('/api/giao-dich/mo-phong', $payment)->assertOk();
        $this->getJson('/api/kiem-tra-thanh-toan/'.$code.'?payment_token='.$payment['payment_token'])->assertOk()->assertJsonPath('da_thanh_toan', true);
        $this->getJson('/api/giao-dich')->assertForbidden();
    }

    public function test_voucher_dates_minimum_and_fixed_amount_are_enforced(): void
    {
        $coupon = new MaGiamGia(['loai_giam' => 'co_dinh', 'gia_tri' => 500, 'so_luong' => 2, 'so_luong_da_dung' => 0, 'trang_thai' => 1, 'gia_tri_toi_thieu' => 0]);
        $this->assertEquals(200, $coupon->discountFor(200));
        foreach ([['ngay_bat_dau' => now()->addDay()->toDateString()], ['ngay_ket_thuc' => now()->subDay()->toDateString()], ['gia_tri_toi_thieu' => 300], ['trang_thai' => 0]] as $attributes) {
            try { (clone $coupon)->fill($attributes)->discountFor(200); $this->fail('Voucher phải bị từ chối.'); }
            catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('ma_giam_gia_su_dung', $e->errors()); }
        }
    }
}
