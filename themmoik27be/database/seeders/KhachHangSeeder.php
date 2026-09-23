<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KhachHangSeeder extends Seeder
{
    /**
     * is_active: 0=Chua active, 1=Da active
     * is_block:  0=Binh thuong, 1=Tam Khoa
     */
    public function run(): void
    {
        DB::table('khach_hangs')->delete();

        $rows = [
            ['ho_va_ten'=>'Nguyễn Văn An','email'=>'nguyenvanan@gmail.com','so_dien_thoai'=>'0901234567','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1995-03-15','gioi_tinh'=>1,'dia_chi'=>'123 Nguyễn Huệ, Q1, TP.HCM','is_active'=>1,'is_block'=>0],
            ['ho_va_ten'=>'Trần Thị Mai','email'=>'tranthimai@gmail.com','so_dien_thoai'=>'0912345678','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1998-07-22','gioi_tinh'=>0,'dia_chi'=>'45 Lê Duẩn, Hải Châu, Đà Nẵng','is_active'=>0,'is_block'=>0],
            ['ho_va_ten'=>'Lê Hoàng Long','email'=>'lehoanglong@gmail.com','so_dien_thoai'=>'0923456789','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1992-11-05','gioi_tinh'=>1,'dia_chi'=>'78 Cầu Giấy, Hà Nội','is_active'=>1,'is_block'=>1],
            ['ho_va_ten'=>'Phạm Thu Trang','email'=>'phamthutrang@gmail.com','so_dien_thoai'=>'0934567890','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'2000-01-18','gioi_tinh'=>0,'dia_chi'=>'12 Trần Phú, Nha Trang','is_active'=>0,'is_block'=>0],
            ['ho_va_ten'=>'Hoàng Minh Đức','email'=>'hoangminhduc@gmail.com','so_dien_thoai'=>'0945678901','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1996-09-30','gioi_tinh'=>1,'dia_chi'=>'89 Hùng Vương, Huế','is_active'=>0,'is_block'=>0],
            ['ho_va_ten'=>'Võ Thị Bích Ngọc','email'=>'vobichngoc@gmail.com','so_dien_thoai'=>'0956789012','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1997-04-12','gioi_tinh'=>0,'dia_chi'=>'56 Quang Trung, Gò Vấp, TP.HCM','is_active'=>1,'is_block'=>1],
            ['ho_va_ten'=>'Đặng Quốc Bảo','email'=>'dangquocbao@gmail.com','so_dien_thoai'=>'0967890123','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1994-12-25','gioi_tinh'=>1,'dia_chi'=>'34 Lạch Tray, Hải Phòng','is_active'=>1,'is_block'=>0],
            ['ho_va_ten'=>'Bùi Phương Linh','email'=>'buiphuonglinh@gmail.com','so_dien_thoai'=>'0978901234','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'2001-06-08','gioi_tinh'=>0,'dia_chi'=>'102 Nguyễn Văn Cừ, Cần Thơ','is_active'=>0,'is_block'=>0],
            ['ho_va_ten'=>'Đỗ Tuấn Anh','email'=>'dotuananh@gmail.com','so_dien_thoai'=>'0989012345','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1993-08-19','gioi_tinh'=>1,'dia_chi'=>'67 Bà Triệu, Hà Nội','is_active'=>1,'is_block'=>1],
            ['ho_va_ten'=>'Ngô Thanh Hương','email'=>'ngothanhuong@gmail.com','so_dien_thoai'=>'0990123456','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1999-10-03','gioi_tinh'=>0,'dia_chi'=>'23 Nguyễn Đình Chiểu, Q3, TP.HCM','is_active'=>1,'is_block'=>0],
            ['ho_va_ten'=>'Dương Trọng Hiếu','email'=>'duongtronghieu@gmail.com','so_dien_thoai'=>'0812345678','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1991-05-14','gioi_tinh'=>1,'dia_chi'=>'15 Điện Biên Phủ, Đà Nẵng','is_active'=>0,'is_block'=>0],
            ['ho_va_ten'=>'Lý Cẩm Tú','email'=>'lycamtu@gmail.com','so_dien_thoai'=>'0823456789','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'2002-02-28','gioi_tinh'=>0,'dia_chi'=>'88 Nguyễn Trãi, Q5, TP.HCM','is_active'=>1,'is_block'=>1],
            ['ho_va_ten'=>'Phan Thanh Hải','email'=>'phanthanhhai@gmail.com','so_dien_thoai'=>'0834567890','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1990-09-09','gioi_tinh'=>1,'dia_chi'=>'40 Trần Hưng Đạo, Quy Nhơn','is_active'=>1,'is_block'=>0],
            ['ho_va_ten'=>'Vũ Thùy Dung','email'=>'vuthuydung@gmail.com','so_dien_thoai'=>'0845678901','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1998-12-16','gioi_tinh'=>0,'dia_chi'=>'59 Phố Huế, Hà Nội','is_active'=>0,'is_block'=>0],
            ['ho_va_ten'=>'Trịnh Hữu Phúc','email'=>'trinhhuuphuc@gmail.com','so_dien_thoai'=>'0856789012','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1995-07-07','gioi_tinh'=>1,'dia_chi'=>'11 Lý Thường Kiệt, Vũng Tàu','is_active'=>1,'is_block'=>0],
            ['ho_va_ten'=>'Lâm Kim Ngân','email'=>'lamkimngan@gmail.com','so_dien_thoai'=>'0867890123','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'2000-11-20','gioi_tinh'=>0,'dia_chi'=>'72 CMT8, Q10, TP.HCM','is_active'=>1,'is_block'=>1],
            ['ho_va_ten'=>'Mai Văn Cường','email'=>'maivancuong@gmail.com','so_dien_thoai'=>'0878901234','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1993-04-05','gioi_tinh'=>1,'dia_chi'=>'29 Hoàng Hoa Thám, Hà Nội','is_active'=>0,'is_block'=>0],
            ['ho_va_ten'=>'Nguyễn Thảo Nhi','email'=>'nguyenthaonhi@gmail.com','so_dien_thoai'=>'0889012345','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'2001-08-14','gioi_tinh'=>0,'dia_chi'=>'18 Pasteur, Đà Nẵng','is_active'=>1,'is_block'=>0],
            ['ho_va_ten'=>'Hà Gia Huy','email'=>'hagiahuy@gmail.com','so_dien_thoai'=>'0890123456','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1997-03-26','gioi_tinh'=>1,'dia_chi'=>'95 Lê Lợi, Vinh, Nghệ An','is_active'=>1,'is_block'=>1],
            ['ho_va_ten'=>'Tạ Khánh Linh','email'=>'takhanhlinh@gmail.com','so_dien_thoai'=>'0701234567','mat_khau'=>Hash::make('123456'),'ngay_sinh'=>'1999-05-19','gioi_tinh'=>0,'dia_chi'=>'63 Võ Văn Tần, Q3, TP.HCM','is_active'=>0,'is_block'=>0],
        ];

        foreach ($rows as &$item) {
            $item['created_at'] = now();
            $item['updated_at'] = now();
        }

        DB::table('khach_hangs')->insert($rows);
    }
}