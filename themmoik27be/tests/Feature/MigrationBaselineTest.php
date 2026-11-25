<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\{DB, Schema, Mail};
use Tests\TestCase;

class MigrationBaselineTest extends TestCase
{
    public function test_actual_migrations_build_an_empty_database_and_customer_can_register(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        config(['logging.default' => 'null']);
        Mail::fake();
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->assertTrue(Schema::hasColumn('khach_hangs', 'role'));
        $this->assertTrue(Schema::hasTable('saved_vouchers'));
        $this->assertTrue(Schema::hasColumn('chi_tiet_san_phams', 'id_anh_san_pham'));
        $this->postJson('/api/dang-ky', ['ho_va_ten' => 'Schema UAT', 'email' => 'schema@example.test',
            'so_dien_thoai' => '0900000000', 'mat_khau' => 'Schema-security-147!', 'xac_nhan_mk' => 'Schema-security-147!'])
            ->assertCreated();
        $this->assertSame('customer', DB::table('khach_hangs')->value('role'));
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        // Chạy up trên schema legacy đã có role không đổi giá trị/kiểu và không drop khi down.
        DB::table('khach_hangs')->update(['role' => 'seller']);
        $migration = require database_path('migrations/2026_11_18_000001_add_customer_role_baseline.php');
        $migration->up(); $migration->down();
        $this->assertSame('seller', DB::table('khach_hangs')->value('role'));
    }
}
