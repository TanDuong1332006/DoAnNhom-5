<?php

namespace Tests\Feature;

use App\Models\KhachHang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\IsolatedApiTestCase;

class ProductDetailDemoTest extends IsolatedApiTestCase
{
    public function test_official_detail_resolves_slugs_and_keeps_hidden_products_unavailable(): void
    {
        DB::table('san_phams')->insert(['id' => 3, 'id_danh_muc' => 1, 'ten_san_pham' => 'Túi mẫu', 'slug_san_pham' => 'tui-mau', 'gia_ban' => 200, 'trang_thai' => 1]);
        DB::table('chi_tiet_san_phams')->insert(['id' => 51, 'id_san_pham' => 3, 'id_mau_sac' => null, 'id_kich_thuoc' => null, 'so_luong_ton' => 0, 'gia_chenh_lech' => 0]);
        $views = DB::table('san_phams')->where('id', 1)->value('luot_xem');
        $this->getJson('/api/client/san-pham/ao-mau?presentation=2')->assertOk()
            ->assertJsonPath('data.san_pham.id', 1)->assertJsonPath('data.san_pham.gia_ban', 100)
            ->assertJsonPath('data.san_pham.gia_khuyen_mai', 80)
            ->assertJsonPath('data.san_pham.chi_tiet_san_phams.0.gia_chenh_lech', 20)
            ->assertJsonPath('data.san_pham_lien_quan.0.id', 3);
        $this->getJson('/api/client/san-pham/tui-mau?presentation=2')->assertOk()
            ->assertJsonPath('data.san_pham.id', 3)
            ->assertJsonPath('data.san_pham.chi_tiet_san_phams.0.id_kich_thuoc', null)
            ->assertJsonPath('data.san_pham.chi_tiet_san_phams.0.so_luong_ton', 0);
        $this->getJson('/api/client/san-pham/an?presentation=2')->assertNotFound();
        $this->getJson('/api/client/san-pham/1garbage?presentation=2')->assertNotFound();
        $this->assertSame($views, DB::table('san_phams')->where('id', 1)->value('luot_xem'));
        $this->getJson('/api/client/san-pham/ao-mau')->assertOk()->assertJsonStructure(['data' => ['san_pham', 'san_pham_lien_quan']]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Chỉ mở rộng fixture SQLite :memory:, không chạy migration hoặc sửa database local.
        DB::statement('CREATE TABLE don_hangs (id INTEGER PRIMARY KEY, id_khach_hang INTEGER, ma_don_hang TEXT, ho_ten TEXT, so_dien_thoai TEXT, email TEXT, dia_chi_giao TEXT, tong_tien_hang NUMERIC, phi_van_chuyen NUMERIC, tien_giam_gia NUMERIC, ma_giam_gia_su_dung TEXT, tong_thanh_toan NUMERIC, phuong_thuc_thanh_toan TEXT, trang_thai_thanh_toan TEXT, trang_thai_don_hang TEXT, ghi_chu TEXT, created_at TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE hoa_dons (id INTEGER PRIMARY KEY, ma_hoa_don TEXT, tong_tien_hang NUMERIC, giam_gia NUMERIC, phi_van_chuyen NUMERIC, tong_tien NUMERIC, trang_thai INTEGER, phuong_thuc_thanh_toan TEXT, ghi_chu TEXT, created_at TEXT, updated_at TEXT)');
        foreach (['id_don_hang INTEGER', 'so_luong INTEGER', 'hinh_anh TEXT', 'ten_mau TEXT', 'ten_kich_thuoc TEXT', 'gia_ban NUMERIC', 'thanh_tien NUMERIC', 'created_at TEXT', 'updated_at TEXT'] as $column) {
            DB::statement('ALTER TABLE chi_tiet_don_hangs ADD COLUMN '.$column);
        }
        DB::statement('ALTER TABLE danh_gia_san_phams ADD COLUMN tieu_de TEXT');
        DB::statement('ALTER TABLE danh_gia_san_phams ADD COLUMN created_at TEXT');
        KhachHang::create(['ho_va_ten' => 'QA Customer', 'email' => 'demo@example.test', 'mat_khau' => Hash::make('Isolated-test-471!'), 'role' => 'customer', 'is_kich_hoat' => 1, 'is_khoa' => 0]);
        DB::table('chi_tiet_san_phams')->insert(['id' => 50, 'id_san_pham' => 1, 'id_mau_sac' => 1, 'id_kich_thuoc' => 1, 'so_luong_ton' => 4, 'gia_chenh_lech' => 20]);
    }

    public function test_demo_uses_approved_valid_reviews_and_completed_paid_order_quantities(): void
    {
        $customer = KhachHang::first();
        foreach ([[5, 1], [3, 1], [1, 0], [9, 1], [0, 1]] as $index => [$stars, $status]) {
            DB::table('danh_gia_san_phams')->insert(['id' => $index + 1, 'id_san_pham' => 1, 'id_khach_hang' => $customer->id, 'so_sao' => $stars, 'trang_thai' => $status, 'noi_dung' => 'Review fixture']);
        }
        foreach ([['da_giao', 'da_thanh_toan', 3], ['da_huy', 'da_thanh_toan', 20], ['da_giao', 'hoan_tien', 10], ['dang_giao', 'da_thanh_toan', 9], ['da_giao', 'chua_thanh_toan', 4]] as $index => [$order, $payment, $quantity]) {
            DB::table('don_hangs')->insert(['id' => $index + 1, 'trang_thai_don_hang' => $order, 'trang_thai_thanh_toan' => $payment]);
            DB::table('chi_tiet_don_hangs')->insert(['id_don_hang' => $index + 1, 'id_san_pham' => 1, 'so_luong' => $quantity]);
        }
        $views = DB::table('san_phams')->where('id', 1)->value('luot_xem');
        $this->getJson('/api/client/demo/san-pham/ao-mau')->assertOk()
            ->assertJsonPath('data.rating.count', 2)->assertJsonPath('data.rating.average', 4)
            ->assertJsonPath('data.sold_count', 3)->assertJsonCount(2, 'data.reviews')
            ->assertJsonPath('data.san_pham.chi_tiet_san_phams.0.id', 50)
            ->assertJsonPath('data.specifications', null)->assertJsonPath('data.size_guide', null);
        $this->assertSame($views, DB::table('san_phams')->where('id', 1)->value('luot_xem'));
        $this->getJson('/api/client/demo/san-pham/an')->assertNotFound();
        $this->getJson('/api/client/demo/san-pham/1garbage')->assertNotFound();
    }

    public function test_missing_optional_schema_hides_statistics_and_seller_stays_rejected(): void
    {
        DB::statement('DROP TABLE don_hangs');
        $this->getJson('/api/client/demo/san-pham/1')->assertOk()->assertJsonPath('data.sold_count', null)->assertJsonPath('data.rating.average', null);
        $seller = KhachHang::create(['ho_va_ten' => 'Legacy', 'email' => 'legacy@example.test', 'mat_khau' => Hash::make('Isolated-test-471!'), 'role' => 'seller']);
        $this->withToken($seller->createToken('seller')->plainTextToken)->getJson('/api/client/demo/san-pham/1')->assertForbidden();
    }

    private function orderPayload(?int $variant = 50): array
    {
        return ['ho_ten' => 'QA only', 'so_dien_thoai' => '0900000000', 'dia_chi_giao' => 'Isolated fixture address', 'tong_thanh_toan' => 100,
            'items' => [['id' => 1, 'id_chi_tiet_san_pham' => $variant, 'ten_san_pham' => 'Áo mẫu', 'quantity' => 1, 'gia_ban' => 100, 'color' => 'Đen', 'size' => 'M']]];
    }

    public function test_existing_checkout_persists_real_optional_variant_id(): void
    {
        $this->postJson('/api/client/dat-hang', $this->orderPayload())->assertCreated();
        $detail = DB::table('chi_tiet_don_hangs')->first();
        $this->assertSame(50, $detail->id_chi_tiet_san_pham);
        $this->assertSame(1, $detail->id_san_pham);
        $this->assertSame('M', $detail->ten_kich_thuoc);
    }

    public function test_checkout_rejects_cross_product_hidden_and_out_of_stock_variant_before_creating_order(): void
    {
        foreach ([999, 0, -1] as $invalid) $this->postJson('/api/client/dat-hang', $this->orderPayload($invalid))->assertUnprocessable();
        $payload = $this->orderPayload();
        $payload['items'][0]['id'] = 2;
        $this->postJson('/api/client/dat-hang', $payload)->assertUnprocessable();
        $payload = $this->orderPayload();
        $payload['items'][0]['quantity'] = 5;
        $this->postJson('/api/client/dat-hang', $payload)->assertUnprocessable();
        DB::table('chi_tiet_san_phams')->where('id', 50)->update(['trang_thai' => 0]);
        $this->postJson('/api/client/dat-hang', $this->orderPayload())->assertUnprocessable();
        $this->assertSame(0, DB::table('don_hangs')->count());
    }
}
