<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProductCatalogPhaseTwoTest extends \Tests\Support\IsolatedApiTestCase
{
    private function adminToken(): string
    {
        return User::create(['name' => 'QA Admin', 'email' => \Illuminate\Support\Str::random(8).'@example.test', 'password' => Hash::make('Isolated-pass-471!')])
            ->createToken('phase-two')->plainTextToken;
    }

    private function api(string $method, string $path, string $token, array $data = [])
    {
        $this->app['auth']->forgetGuards();
        return $this->json($method, '/api/'.$path, $data, ['Authorization' => 'Bearer '.$token]);
    }

    public function test_product_content_variants_and_visibility_round_trip(): void
    {
        $token = $this->adminToken();
        $payload = ['ten_san_pham' => 'Áo SOFEP QA', 'id_danh_muc' => 1, 'gia_ban' => 200000, 'gia_khuyen_mai' => 180000,
            'hinh_anh' => '/images/qa-shirt.jpg', 'mo_ta_ngan' => 'Mô tả riêng áo QA', 'mo_ta_chi_tiet' => 'Chi tiết đúng áo QA'];
        $created = $this->api('POST', 'admin/san-pham/create', $token, $payload)->assertCreated();
        $id = $created->json('data.id');
        $slug = $created->json('data.slug_san_pham');
        $this->assertSame(0, $created->json('data.trang_thai'));
        $this->getJson('/api/client/san-pham/'.$slug)->assertNotFound();
        $this->api('POST', 'admin/chi-tiet-san-pham/create', $token, [
            'id_san_pham' => $id, 'id_mau_sac' => 1, 'id_kich_thuoc' => 1, 'so_luong_ton' => 7, 'gia_chenh_lech' => 25000,
        ])->assertCreated();
        $this->api('POST', 'admin/chi-tiet-san-pham/create', $token, [
            'id_san_pham' => $id, 'id_mau_sac' => 1, 'id_kich_thuoc' => 2, 'so_luong_ton' => 3, 'gia_chenh_lech' => -10000,
        ])->assertCreated();
        $this->api('POST', 'admin/chi-tiet-san-pham/create', $token, [
            'id_san_pham' => $id, 'id_mau_sac' => null, 'id_kich_thuoc' => null, 'so_luong_ton' => 99, 'trang_thai' => 0,
        ])->assertCreated();
        DB::table('anh_san_phams')->insert(['id_san_pham' => $id, 'duong_dan_anh' => '/images/qa-back.jpg', 'thu_tu' => 1]);
        $this->api('POST', 'admin/san-pham/change-status', $token, ['id' => $id])->assertOk();
        $detail = $this->getJson('/api/client/san-pham/'.$slug)->assertOk()
            ->assertJsonPath('data.san_pham.hinh_anh', '/images/qa-shirt.jpg')
            ->assertJsonPath('data.san_pham.mo_ta_ngan', $payload['mo_ta_ngan'])
            ->assertJsonPath('data.san_pham.mo_ta_chi_tiet', $payload['mo_ta_chi_tiet'])
            ->assertJsonPath('data.san_pham.anh_san_phams.0.duong_dan_anh', '/images/qa-back.jpg')
            ->assertJsonCount(2, 'data.san_pham.chi_tiet_san_phams');
        $variants = collect($detail->json('data.san_pham.chi_tiet_san_phams'))->keyBy('id_kich_thuoc');
        $this->assertSame('Đen', $variants[1]['mau_sac']['ten_mau']);
        $this->assertSame('M', $variants[1]['kich_thuoc']['ten_kich_thuoc']);
        $this->assertSame(7, $variants[1]['so_luong_ton']);
        $this->assertSame(205000, (int) $detail->json('data.san_pham.gia_khuyen_mai') + $variants[1]['gia_chenh_lech']);
        $this->assertSame(10, (int) DB::table('san_phams')->where('id', $id)->value('so_luong_ton'));
        $this->getJson('/api/client/san-phams?all=1&tu_khoa=SOFEP QA')->assertOk()->assertJsonCount(1, 'data');
        $this->api('POST', 'admin/san-pham/update', $token, array_replace($payload, ['id' => $id, 'ten_san_pham' => 'Tên mới', 'trang_thai' => 2]))->assertOk();
        $this->assertSame($slug, DB::table('san_phams')->where('id', $id)->value('slug_san_pham'));
        $this->getJson('/api/client/san-pham/'.$id)->assertNotFound();
        $this->getJson('/api/client/san-phams?all=1&tu_khoa=Tên mới')->assertJsonCount(0, 'data');
    }

    public function test_invalid_product_and_variant_data_are_rejected(): void
    {
        $token = $this->adminToken();
        $valid = ['ten_san_pham' => 'QA', 'id_danh_muc' => 1, 'gia_ban' => 100];
        foreach ([['gia_ban' => -1], ['gia_ban' => 'abc'], ['gia_ban' => 1.001], ['so_luong_ton' => 1.5],
            ['id_danh_muc' => 999], ['gia_khuyen_mai' => 101], ['trang_thai' => 8], ['hinh_anh' => 'javascript:alert(1)'],
            ['slug_san_pham' => 'slug invalid'], ['ma_san_pham' => str_repeat('a', 256)]] as $invalid) {
            $this->api('POST', 'admin/san-pham/create', $token, array_replace($valid, $invalid))->assertUnprocessable();
        }
        $variant = ['id_san_pham' => 1, 'id_mau_sac' => 1, 'id_kich_thuoc' => 1, 'so_luong_ton' => 4];
        foreach ([['id_mau_sac' => 999], ['id_kich_thuoc' => 999], ['so_luong_ton' => 0.1], ['gia_chenh_lech' => -1000], ['trang_thai' => 2]] as $invalid) {
            $this->api('POST', 'admin/chi-tiet-san-pham/create', $token, array_replace($variant, $invalid))->assertUnprocessable();
        }
        $this->api('POST', 'admin/chi-tiet-san-pham/create', $token, $variant)->assertCreated();
        $this->api('POST', 'admin/chi-tiet-san-pham/create', $token, $variant)->assertUnprocessable();
        $this->api('POST', 'admin/chi-tiet-san-pham/create', $token, array_replace($variant, ['id_kich_thuoc' => 2, 'gia_chenh_lech' => -20]))->assertCreated();
        $this->api('POST', 'admin/san-pham/update', $token, [
            'id' => 1, 'ten_san_pham' => 'QA', 'id_danh_muc' => 1, 'gia_ban' => 10, 'gia_khuyen_mai' => null,
        ])->assertUnprocessable();
        $id = DB::table('chi_tiet_san_phams')->value('id');
        $this->api('POST', 'admin/chi-tiet-san-pham/update', $token, ['id' => $id, 'id_san_pham' => 2, 'so_luong_ton' => 1])->assertUnprocessable();
        $this->api('POST', 'admin/chi-tiet-san-pham/update', $token, ['id' => $id, 'so_luong_ton' => 2, 'gia_chenh_lech' => 10, 'trang_thai' => 0])->assertOk();
        $this->getJson('/api/client/san-pham/ao-mau')->assertJsonCount(1, 'data.san_pham.chi_tiet_san_phams');
    }

    public function test_deleting_products_or_variants_with_orders_preserves_history(): void
    {
        $token = $this->adminToken();
        DB::table('chi_tiet_san_phams')->insert(['id' => 10, 'id_san_pham' => 1, 'id_mau_sac' => 1, 'id_kich_thuoc' => 1]);
        DB::table('chi_tiet_don_hangs')->insert(['id' => 1, 'id_san_pham' => 1, 'id_chi_tiet_san_pham' => 10, 'ten_san_pham' => 'Tên tại lúc đặt hàng']);
        $before = DB::table('chi_tiet_don_hangs')->get()->toJson();
        $this->api('POST', 'admin/san-pham/destroy', $token, ['id' => 1])->assertStatus(409);
        $this->api('POST', 'admin/chi-tiet-san-pham/destroy', $token, ['id' => 10])->assertStatus(409);
        $this->api('POST', 'admin/chi-tiet-san-pham/update', $token, ['id' => 10, 'id_kich_thuoc' => 2, 'so_luong_ton' => 0])->assertStatus(409);
        $this->assertSame($before, DB::table('chi_tiet_don_hangs')->get()->toJson());
        $this->assertTrue(DB::table('san_phams')->where('id', 1)->exists());
        $this->assertTrue(DB::table('chi_tiet_san_phams')->where('id', 10)->exists());
    }

    public function test_session_check_and_admin_api_reject_invalid_expired_and_revoked_tokens(): void
    {
        $this->getJson('/api/kiem-tra-dang-nhap')->assertUnauthorized();
        foreach (['not-a-token', $this->adminToken()] as $token) {
            if ($token !== 'not-a-token') DB::table('personal_access_tokens')->update(['expires_at' => now()->subMinute()]);
            foreach (['kiem-tra-dang-nhap', 'admin/san-pham/get-data'] as $endpoint) {
                $this->api('GET', $endpoint, $token)->assertUnauthorized();
            }
        }
        $token = $this->adminToken();
        DB::table('personal_access_tokens')->delete();
        $this->api('GET', 'kiem-tra-dang-nhap', $token)->assertUnauthorized();
    }

    public function test_deleting_product_without_orders_removes_only_its_dependents(): void
    {
        $token = $this->adminToken();
        DB::table('chi_tiet_san_phams')->insert(['id' => 10, 'id_san_pham' => 1]);
        DB::table('anh_san_phams')->insert(['id' => 10, 'id_san_pham' => 1, 'duong_dan_anh' => '/images/front.jpg']);
        $this->api('POST', 'admin/san-pham/destroy', $token, ['id' => 1])->assertOk();
        $this->assertFalse(DB::table('san_phams')->where('id', 1)->exists());
        $this->assertFalse(DB::table('chi_tiet_san_phams')->where('id', 10)->exists());
        $this->assertFalse(DB::table('anh_san_phams')->where('id', 10)->exists());
        $this->assertTrue(DB::table('san_phams')->where('id', 2)->exists());
    }
}
