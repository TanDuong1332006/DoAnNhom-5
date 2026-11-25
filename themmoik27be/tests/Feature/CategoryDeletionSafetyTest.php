<?php

namespace Tests\Feature;

use App\Models\{KhachHang, User};
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CategoryDeletionSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!app()->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || config('database.connections.sqlite.url')) {
            throw new \RuntimeException('Isolated SQLite memory required.');
        }
        config(['logging.default' => 'null']);
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        DB::table('danh_mucs')->insert(['id' => 1, 'ten_danh_muc' => 'Test', 'slug_danh_muc' => 'test']);
    }

    private function admin(): string
    {
        return User::create(['name' => 'Test Admin', 'email' => 'category-admin@example.test', 'password' => 'unused'])
            ->createToken('test')->plainTextToken;
    }

    private function remove(?string $token, array $data = ['id' => 1])
    {
        $this->app['auth']->forgetGuards();
        return $this->postJson('/api/admin/danh-muc/destroy', $data, $token ? ['Authorization' => 'Bearer '.$token] : []);
    }

    private function product(int $status = 1): void
    {
        DB::table('san_phams')->insert(['id' => 1, 'id_danh_muc' => 1, 'ma_san_pham' => 'TEST-1',
            'ten_san_pham' => 'Test', 'slug_san_pham' => 'test-1', 'gia_ban' => 100, 'trang_thai' => $status]);
    }

    public function test_admin_can_delete_empty_category_and_invalid_ids_are_rejected(): void
    {
        $token = $this->admin();
        $this->remove($token, [])->assertUnprocessable();
        $this->remove($token, ['id' => 999])->assertUnprocessable();
        $this->remove($token, ['id' => 'invalid'])->assertUnprocessable();
        $this->remove($token)->assertOk()->assertJsonPath('status', 1);
        $this->assertDatabaseMissing('danh_mucs', ['id' => 1]);
    }

    public function test_category_with_hidden_product_is_also_preserved(): void
    {
        $this->product(0);
        $this->remove($this->admin())->assertStatus(409)->assertJsonPath('status', 0)
            ->assertJsonPath('message', 'Danh mục còn sản phẩm. Hãy tạm ẩn thay vì xóa.');
        $this->assertDatabaseHas('san_phams', ['id' => 1, 'id_danh_muc' => 1]);
        $this->assertDatabaseHas('danh_mucs', ['id' => 1]);
    }

    public function test_rejection_preserves_gallery_stock_variants_and_order_references(): void
    {
        $this->product();
        DB::table('anh_san_phams')->insert(['id' => 1, 'id_san_pham' => 1, 'duong_dan_anh' => '/test.jpg']);
        DB::table('chi_tiet_san_phams')->insert(['id' => 1, 'id_san_pham' => 1, 'so_luong_ton' => 5]);
        DB::table('don_hangs')->insert(['id' => 1, 'ma_don_hang' => 'TEST-HISTORY', 'ho_ten' => 'Test',
            'so_dien_thoai' => '0900000000', 'dia_chi_giao' => 'Test only', 'tong_tien_hang' => 100, 'tong_thanh_toan' => 100]);
        DB::table('chi_tiet_don_hangs')->insert(['id' => 1, 'id_don_hang' => 1, 'id_san_pham' => 1,
            'id_chi_tiet_san_pham' => 1, 'ten_san_pham' => 'Snapshot', 'hinh_anh' => '/history.jpg', 'gia_ban' => 100, 'thanh_tien' => 100]);
        $tables = ['danh_mucs', 'san_phams', 'chi_tiet_san_phams', 'anh_san_phams', 'don_hangs', 'chi_tiet_don_hangs'];
        $before = [];
        foreach ($tables as $table) $before[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        $this->remove($this->admin())->assertStatus(409);
        foreach ($tables as $table) $this->assertSame($before[$table], DB::table($table)->orderBy('id')->get()->toJson(), $table);
    }

    public function test_guest_customer_and_legacy_seller_cannot_delete_empty_category(): void
    {
        $this->remove(null)->assertUnauthorized();
        foreach (['customer', 'seller'] as $role) {
            $user = KhachHang::create(['ho_va_ten' => 'Test', 'email' => $role.'@example.test', 'mat_khau' => 'unused',
                'role' => $role, 'is_khoa' => 0, 'is_kich_hoat' => 1]);
            $this->remove($user->createToken('test')->plainTextToken)->assertForbidden();
        }
        $this->assertDatabaseHas('danh_mucs', ['id' => 1]);
    }
}
