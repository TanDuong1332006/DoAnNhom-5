<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

abstract class IsolatedApiTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Không chạy migration hoặc kết nối database ngoài bộ nhớ của test.
        if (!app()->environment('testing')
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || config('database.connections.sqlite.url')) {
            throw new \RuntimeException('Tests require isolated SQLite :memory:.');
        }

        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('PDO SQLite is required.');
        }

        config(['logging.default' => 'null']);
        foreach ([
            'CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT, password TEXT, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE khach_hangs (id INTEGER PRIMARY KEY, ho_va_ten TEXT, email TEXT, mat_khau TEXT, so_dien_thoai TEXT, otp_hash TEXT, otp_expires_at TEXT, role TEXT, is_khoa INTEGER, is_kich_hoat INTEGER, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE personal_access_tokens (id INTEGER PRIMARY KEY, tokenable_type TEXT, tokenable_id INTEGER, name TEXT, token TEXT, abilities TEXT, last_used_at TEXT, expires_at TEXT, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE chi_tiet_don_hangs (id INTEGER PRIMARY KEY, id_san_pham INTEGER REFERENCES san_phams(id) ON DELETE SET NULL, id_chi_tiet_san_pham INTEGER REFERENCES chi_tiet_san_phams(id) ON DELETE SET NULL, ten_san_pham TEXT)',
            'CREATE TABLE cua_hangs (id INTEGER PRIMARY KEY, id_chu_shop INTEGER)',
            'CREATE TABLE danh_mucs (id INTEGER PRIMARY KEY, ten_danh_muc TEXT, slug_danh_muc TEXT, hinh_anh TEXT, mo_ta TEXT, trang_thai INTEGER)',
            'CREATE TABLE thuong_hieus (id INTEGER PRIMARY KEY, ten_thuong_hieu TEXT, trang_thai INTEGER)',
            'CREATE TABLE san_phams (id INTEGER PRIMARY KEY, id_danh_muc INTEGER, id_thuong_hieu INTEGER, ten_san_pham TEXT, slug_san_pham TEXT, gia_ban INTEGER, gia_khuyen_mai INTEGER, hinh_anh TEXT, mo_ta_ngan TEXT, is_noi_bat INTEGER, is_flash_sale INTEGER, luot_xem INTEGER, trang_thai INTEGER, id_cua_hang INTEGER, ma_san_pham TEXT, mo_ta_chi_tiet TEXT, so_luong_ton INTEGER, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE chi_tiet_san_phams (id INTEGER PRIMARY KEY, id_san_pham INTEGER REFERENCES san_phams(id) ON DELETE CASCADE, id_mau_sac INTEGER, id_kich_thuoc INTEGER, so_luong_ton INTEGER DEFAULT 0, gia_chenh_lech NUMERIC DEFAULT 0, trang_thai INTEGER DEFAULT 1, created_at TEXT, updated_at TEXT)',
            'CREATE TABLE anh_san_phams (id INTEGER PRIMARY KEY, id_san_pham INTEGER REFERENCES san_phams(id) ON DELETE CASCADE, duong_dan_anh TEXT, thu_tu INTEGER DEFAULT 0)',
            'CREATE TABLE danh_gia_san_phams (id INTEGER PRIMARY KEY, id_san_pham INTEGER REFERENCES san_phams(id) ON DELETE CASCADE, id_khach_hang INTEGER, trang_thai INTEGER DEFAULT 1, so_sao INTEGER, noi_dung TEXT)',
            'CREATE TABLE mau_sacs (id INTEGER PRIMARY KEY, ten_mau TEXT, ma_mau TEXT, trang_thai INTEGER)',
            'CREATE TABLE kich_thuocs (id INTEGER PRIMARY KEY, ten_kich_thuoc TEXT, mo_ta TEXT, trang_thai INTEGER)',
        ] as $sql) {
            DB::statement($sql);
        }

        for ($id = 1; $id <= 10; $id++) {
            DB::table('danh_mucs')->insert([
                'id' => $id, 'ten_danh_muc' => "Danh mục $id",
                'slug_danh_muc' => "danh-muc-$id", 'trang_thai' => $id === 10 ? 0 : 1,
            ]);
        }
        DB::table('san_phams')->insert([
            ['id' => 1, 'id_danh_muc' => 1, 'ten_san_pham' => 'Áo mẫu', 'slug_san_pham' => 'ao-mau', 'gia_ban' => 100, 'gia_khuyen_mai' => 80, 'hinh_anh' => 'ao.jpg', 'mo_ta_ngan' => 'Áo', 'is_noi_bat' => 1, 'is_flash_sale' => 1, 'luot_xem' => 5, 'trang_thai' => 1],
            ['id' => 2, 'id_danh_muc' => 1, 'ten_san_pham' => 'Ẩn', 'slug_san_pham' => 'an', 'gia_ban' => 100, 'gia_khuyen_mai' => null, 'hinh_anh' => null, 'mo_ta_ngan' => null, 'is_noi_bat' => 0, 'is_flash_sale' => 0, 'luot_xem' => 0, 'trang_thai' => 0],
        ]);
        DB::table('mau_sacs')->insert([
            ['id' => 1, 'ten_mau' => 'Đen', 'ma_mau' => '#000000', 'trang_thai' => 1],
            ['id' => 2, 'ten_mau' => 'Ẩn', 'ma_mau' => '#ffffff', 'trang_thai' => 0],
        ]);
        DB::table('kich_thuocs')->insert([
            ['id' => 1, 'ten_kich_thuoc' => 'M', 'trang_thai' => 1],
            ['id' => 2, 'ten_kich_thuoc' => 'L', 'trang_thai' => 1],
            ['id' => 3, 'ten_kich_thuoc' => 'Ẩn', 'trang_thai' => 0],
        ]);
    }

}
