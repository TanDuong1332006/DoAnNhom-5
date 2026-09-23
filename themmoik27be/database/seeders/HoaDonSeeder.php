<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HoaDonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('hoa_dons')->delete();

        $rows = [
            [
                'ma_hoa_don'             => 'HD_2027',
                'tong_tien_hang'         => 3500,
                'giam_gia'               => 0,
                'phi_van_chuyen'         => 0,
                'tong_tien'              => 3500,
                'trang_thai'             => 1, // Đã Thanh Toán
                'phuong_thuc_thanh_toan' => 1,
                'ghi_chu'                => 'Thanh toán qua VietQR - MB Bank',
                'created_at'             => '2026-08-11 10:15:00',
                'updated_at'             => '2026-08-11 10:30:00',
            ],
            [
                'ma_hoa_don'             => 'HD_2028',
                'tong_tien_hang'         => 4800,
                'giam_gia'               => 500,
                'phi_van_chuyen'         => 0,
                'tong_tien'              => 4300,
                'trang_thai'             => 1, // Đã Thanh Toán
                'phuong_thuc_thanh_toan' => 1,
                'ghi_chu'                => 'Áp mã giảm giá 500 đ',
                'created_at'             => '2026-08-07 15:20:00',
                'updated_at'             => '2026-08-07 15:45:00',
            ],
            [
                'ma_hoa_don'             => 'HD_2029',
                'tong_tien_hang'         => 2000,
                'giam_gia'               => 0,
                'phi_van_chuyen'         => 0,
                'tong_tien'              => 2000,
                'trang_thai'             => 0, // Chưa Thanh Toán
                'phuong_thuc_thanh_toan' => 1,
                'ghi_chu'                => 'Khách hẹn chuyển khoản trong ngày',
                'created_at'             => '2026-08-05 09:00:00',
                'updated_at'             => '2026-08-05 09:00:00',
            ],
            [
                'ma_hoa_don'             => 'HD_2030',
                'tong_tien_hang'         => 3200,
                'giam_gia'               => 200,
                'phi_van_chuyen'         => 0,
                'tong_tien'              => 3000,
                'trang_thai'             => 0, // Chưa Thanh Toán
                'phuong_thuc_thanh_toan' => 1,
                'ghi_chu'                => 'Chờ xác nhận giao dịch qua VietQR',
                'created_at'             => '2026-08-02 14:10:00',
                'updated_at'             => '2026-08-02 14:10:00',
            ],
            [
                'ma_hoa_don'             => 'HD_2031',
                'tong_tien_hang'         => 5000,
                'giam_gia'               => 0,
                'phi_van_chuyen'         => 0,
                'tong_tien'              => 5000,
                'trang_thai'             => 0, // Chưa Thanh Toán
                'phuong_thuc_thanh_toan' => 1,
                'ghi_chu'                => 'Đơn hàng mới tạo',
                'created_at'             => '2026-07-01 11:00:00',
                'updated_at'             => '2026-07-01 11:30:00',
            ],
            [
                'ma_hoa_don'             => 'HD_2032',
                'tong_tien_hang'         => 4500,
                'giam_gia'               => 500,
                'phi_van_chuyen'         => 0,
                'tong_tien'              => 4000,
                'trang_thai'             => 0, // Chưa Thanh Toán
                'phuong_thuc_thanh_toan' => 1,
                'ghi_chu'                => 'Đơn hàng chờ xác nhận',
                'created_at'             => '2026-07-15 16:20:00',
                'updated_at'             => '2026-07-15 16:20:00',
            ],
            [
                'ma_hoa_don'             => 'HD_2033',
                'tong_tien_hang'         => 1500,
                'giam_gia'               => 0,
                'phi_van_chuyen'         => 0,
                'tong_tien'              => 1500,
                'trang_thai'             => 1, // Đã Thanh Toán
                'phuong_thuc_thanh_toan' => 1,
                'ghi_chu'                => 'Thanh toán hoàn tất',
                'created_at'             => '2026-08-10 17:00:00',
                'updated_at'             => '2026-08-10 17:15:00',
            ],
        ];

        DB::table('hoa_dons')->insert($rows);
    }
}
