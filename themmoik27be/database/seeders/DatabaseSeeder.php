<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\DanhMuc;
use App\Models\ThuongHieu;
use App\Models\MauSac;
use App\Models\KichThuoc;
use App\Models\SanPham;
use App\Models\ChiTietSanPham;
use App\Models\AnhSanPham;
use App\Models\KhachHang;
use App\Models\DiaChiNhanHang;
use App\Models\MaGiamGia;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\DanhGiaSanPham;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        User::updateOrCreate(
            ['email' => 'admin@shop.com'],
            [
                'name'     => 'Quản Trị Viên',
                'password' => Hash::make('Admin@123'),
            ]
        );

        // 2. Danh Mục (8 danh mục thời trang)
        $danh_mucs = [
            ['ten_danh_muc' => 'Áo Nam', 'slug_danh_muc' => 'ao-nam', 'hinh_anh' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=500', 'mo_ta' => 'Áo thun, sơ mi, polo nam cao cấp'],
            ['ten_danh_muc' => 'Quần Nam', 'slug_danh_muc' => 'quan-nam', 'hinh_anh' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=500', 'mo_ta' => 'Quần jean, kaki, tây âu nam'],
            ['ten_danh_muc' => 'Váy & Đầm Nữ', 'slug_danh_muc' => 'vay-dam-nu', 'hinh_anh' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=500', 'mo_ta' => 'Đầm dạ hội, váy xòe, váy công sở'],
            ['ten_danh_muc' => 'Áo Nữ', 'slug_danh_muc' => 'ao-nu', 'hinh_anh' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=500', 'mo_ta' => 'Áo kiểu, áo len, sơ mi nữ'],
            ['ten_danh_muc' => 'Áo Khoác & Blazer', 'slug_danh_muc' => 'ao-khoac-blazer', 'hinh_anh' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=500', 'mo_ta' => 'Áo khoác dù, bomber, blazer thanh lịch'],
            ['ten_danh_muc' => 'Giày & Sneaker', 'slug_danh_muc' => 'giay-sneaker', 'hinh_anh' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500', 'mo_ta' => 'Sneaker, giày da, giày lười'],
            ['ten_danh_muc' => 'Túi Xách & Ví', 'slug_danh_muc' => 'tui-xach-vi', 'hinh_anh' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500', 'mo_ta' => 'Túi xách nữ, balo da, ví nam nữ'],
            ['ten_danh_muc' => 'Phụ Kiện Thời Trang', 'slug_danh_muc' => 'phu-kien-thoi-trang', 'hinh_anh' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500', 'mo_ta' => 'Đồng hồ, thắt lưng, kính mát'],
        ];
        $dm_ids = [];
        foreach ($danh_mucs as $dm) {
            $created = DanhMuc::create(array_merge($dm, ['trang_thai' => 1]));
            $dm_ids[] = $created->id;
        }

        // 3. Thương Hiệu (6 thương hiệu)
        $thuong_hieus = [
            ['ten_thuong_hieu' => 'Uniqlo', 'slug_thuong_hieu' => 'uniqlo', 'hinh_anh' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/92/UNIQLO_logo.svg/200px-UNIQLO_logo.svg.png', 'mo_ta' => 'Thời trang phong cách tối giản Nhật Bản'],
            ['ten_thuong_hieu' => 'Zara', 'slug_thuong_hieu' => 'zara', 'hinh_anh' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fd/Zara_Logo.svg/200px-Zara_Logo.svg.png', 'mo_ta' => 'Thời trang xu hướng nhanh Tây Ban Nha'],
            ['ten_thuong_hieu' => 'H&M', 'slug_thuong_hieu' => 'hm', 'hinh_anh' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/H%26M-Logo.svg/200px-H%26M-Logo.svg.png', 'mo_ta' => 'Thời trang hiện đại Thụy Điển'],
            ['ten_thuong_hieu' => 'Routine', 'slug_thuong_hieu' => 'routine', 'hinh_anh' => 'https://routine.vn/media/logo/default/logo-routine-1.png', 'mo_ta' => 'Thương hiệu thời trang nam nữ Việt Nam'],
            ['ten_thuong_hieu' => 'Coolmate', 'slug_thuong_hieu' => 'coolmate', 'hinh_anh' => 'https://mcdn.coolmate.me/image/March2023/mceclip0_93.png', 'mo_ta' => 'Thời trang ứng dụng cho nam giới'],
            ['ten_thuong_hieu' => 'Nike', 'slug_thuong_hieu' => 'nike', 'hinh_anh' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Logo_NIKE.svg/200px-Logo_NIKE.svg.png', 'mo_ta' => 'Thể thao năng động cao cấp'],
        ];
        $th_ids = [];
        foreach ($thuong_hieus as $th) {
            $created = ThuongHieu::create(array_merge($th, ['trang_thai' => 1]));
            $th_ids[] = $created->id;
        }

        // 4. Màu Sắc
        $mau_sacs = [
            ['ten_mau' => 'Đen', 'ma_mau' => '#000000'],
            ['ten_mau' => 'Trắng', 'ma_mau' => '#FFFFFF'],
            ['ten_mau' => 'Xanh Navy', 'ma_mau' => '#000080'],
            ['ten_mau' => 'Be / Khaki', 'ma_mau' => '#F5F5DC'],
            ['ten_mau' => 'Xám Tiêu', 'ma_mau' => '#808080'],
            ['ten_mau' => 'Đỏ Burgundy', 'ma_mau' => '#800020'],
            ['ten_mau' => 'Xanh Rêu Pastel', 'ma_mau' => '#8A9A5B'],
        ];
        $ms_ids = [];
        foreach ($mau_sacs as $ms) {
            $created = MauSac::create(array_merge($ms, ['trang_thai' => 1]));
            $ms_ids[] = $created->id;
        }

        // 5. Kích Thước
        $kich_thuocs = [
            ['ten_kich_thuoc' => 'S', 'mo_ta' => 'Chiều cao 1m50 - 1m60, Cân nặng 45 - 53kg'],
            ['ten_kich_thuoc' => 'M', 'mo_ta' => 'Chiều cao 1m60 - 1m68, Cân nặng 54 - 62kg'],
            ['ten_kich_thuoc' => 'L', 'mo_ta' => 'Chiều cao 1m68 - 1m75, Cân nặng 63 - 72kg'],
            ['ten_kich_thuoc' => 'XL', 'mo_ta' => 'Chiều cao 1m75 - 1m82, Cân nặng 73 - 82kg'],
            ['ten_kich_thuoc' => 'XXL', 'mo_ta' => 'Chiều cao > 1m80, Cân nặng > 82kg'],
        ];
        $kt_ids = [];
        foreach ($kich_thuocs as $kt) {
            $created = KichThuoc::create(array_merge($kt, ['trang_thai' => 1]));
            $kt_ids[] = $created->id;
        }

        // 6. Sản Phẩm (12 sản phẩm đại diện đa dạng)
        $ds_san_pham = [
            [
                'id_danh_muc'    => $dm_ids[0],
                'id_thuong_hieu' => $th_ids[0],
                'ma_san_pham'    => 'SP-AONAM-01',
                'ten_san_pham'   => 'Áo Thun Nam Cổ Tròn Cotton Supima Cao Cấp',
                'slug_san_pham'  => 'ao-thun-nam-co-tron-cotton-supima',
                'gia_ban'        => 299000,
                'gia_khuyen_mai' => 249000,
                'hinh_anh'       => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500',
                'mo_ta_ngan'     => 'Chất liệu 100% cotton Supima mềm mịn, co giãn 4 chiều.',
                'mo_ta_chi_tiet' => 'Áo thun cotton Supima cao cấp thấm hút mồ hôi cực tốt, bền màu qua nhiều lần giặt.',
                'so_luong_ton'   => 120,
                'is_noi_bat'     => 1,
                'is_flash_sale'  => 1,
            ],
            [
                'id_danh_muc'    => $dm_ids[0],
                'id_thuong_hieu' => $th_ids[3],
                'ma_san_pham'    => 'SP-AONAM-02',
                'ten_san_pham'   => 'Áo Sơ Mi Nam Tay Dài Oxford Form Regular',
                'slug_san_pham'  => 'ao-so-mi-nam-tay-dai-oxford',
                'gia_ban'        => 450000,
                'gia_khuyen_mai' => 399000,
                'hinh_anh'       => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=500',
                'mo_ta_ngan'     => 'Vải Oxford dệt thoi dày dặn, đứng form và thoáng khí.',
                'mo_ta_chi_tiet' => 'Phong cách lịch lãm công sở hoặc dạo phố trẻ trung.',
                'so_luong_ton'   => 85,
                'is_noi_bat'     => 1,
                'is_flash_sale'  => 0,
            ],
            [
                'id_danh_muc'    => $dm_ids[1],
                'id_thuong_hieu' => $th_ids[1],
                'ma_san_pham'    => 'SP-QUANNAM-01',
                'ten_san_pham'   => 'Quần Jean Nam Slimfit Denim Co Giãn Nhẹ',
                'slug_san_pham'  => 'quan-jean-nam-slimfit-denim',
                'gia_ban'        => 590000,
                'gia_khuyen_mai' => 499000,
                'hinh_anh'       => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500',
                'mo_ta_ngan'     => 'Thiết kế tôn dáng, đường may chỉ kép chắc chắn.',
                'mo_ta_chi_tiet' => 'Chất denim cotton pha spandex tạo cảm giác thoải mái khi vận động.',
                'so_luong_ton'   => 95,
                'is_noi_bat'     => 1,
                'is_flash_sale'  => 0,
            ],
            [
                'id_danh_muc'    => $dm_ids[2],
                'id_thuong_hieu' => $th_ids[1],
                'ma_san_pham'    => 'SP-VAYDAM-01',
                'ten_san_pham'   => 'Đầm Xòe Nữ Cổ Vuông Thanh Lịch Dự Tiệc',
                'slug_san_pham'  => 'dam-xoe-nu-co-vuong-thanh-lich',
                'gia_ban'        => 650000,
                'gia_khuyen_mai' => 520000,
                'hinh_anh'       => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=500',
                'mo_ta_ngan'     => 'Chất liệu lụa satin bóng nhẹ, mềm rủ quyến rũ.',
                'mo_ta_chi_tiet' => 'Đầm xòe nhẹ nhàng giúp che khuyết điểm hoàn hảo, phù hợp dự tiệc và hẹn hò.',
                'so_luong_ton'   => 60,
                'is_noi_bat'     => 1,
                'is_flash_sale'  => 1,
            ],
            [
                'id_danh_muc'    => $dm_ids[3],
                'id_thuong_hieu' => $th_ids[2],
                'ma_san_pham'    => 'SP-AONU-01',
                'ten_san_pham'   => 'Áo Len Dệt Kim Cổ Tim Phong Cách Hàn Quốc',
                'slug_san_pham'  => 'ao-len-det-kim-co-tim-han-quoc',
                'gia_ban'        => 380000,
                'gia_khuyen_mai' => 299000,
                'hinh_anh'       => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=500',
                'mo_ta_ngan'     => 'Sợi len dệt êm dịu không gây dặm ngứa.',
                'mo_ta_chi_tiet' => 'Thiết kế cổ tim nhẹ nhàng nữ tính, phối cùng chân váy hay quần jean đều rất đẹp.',
                'so_luong_ton'   => 110,
                'is_noi_bat'     => 0,
                'is_flash_sale'  => 1,
            ],
            [
                'id_danh_muc'    => $dm_ids[4],
                'id_thuong_hieu' => $th_ids[0],
                'ma_san_pham'    => 'SP-AOKHOAC-01',
                'ten_san_pham'   => 'Áo Khoác Blazer Nam 2 Lớp Form Rộng Hiện Đại',
                'slug_san_pham'  => 'ao-khoac-blazer-nam-2-lop',
                'gia_ban'        => 890000,
                'gia_khuyen_mai' => 750000,
                'hinh_anh'       => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=500',
                'mo_ta_ngan'     => 'Chất vải wool pha đứng form, lớp lót lụa cao cấp.',
                'mo_ta_chi_tiet' => 'Áo blazer chuẩn phong cách smart-casual tinh tế cho chàng trai năng động.',
                'so_luong_ton'   => 45,
                'is_noi_bat'     => 1,
                'is_flash_sale'  => 0,
            ],
            [
                'id_danh_muc'    => $dm_ids[5],
                'id_thuong_hieu' => $th_ids[5],
                'ma_san_pham'    => 'SP-GIAY-01',
                'ten_san_pham'   => 'Giày Sneaker Nam Nữ Thể Thao Air Cushion',
                'slug_san_pham'  => 'giay-sneaker-the-thao-air-cushion',
                'gia_ban'        => 1250000,
                'gia_khuyen_mai' => 999000,
                'hinh_anh'       => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500',
                'mo_ta_ngan'     => 'Đế đệm khí êm ái, bảo vệ gót chân khi di chuyển nhiều.',
                'mo_ta_chi_tiet' => 'Thiết kế thể thao trẻ trung, độ bám tốt, êm chân suốt ngày dài.',
                'so_luong_ton'   => 70,
                'is_noi_bat'     => 1,
                'is_flash_sale'  => 1,
            ],
            [
                'id_danh_muc'    => $dm_ids[6],
                'id_thuong_hieu' => $th_ids[1],
                'ma_san_pham'    => 'SP-TUIXACH-01',
                'ten_san_pham'   => 'Túi Xách Da Nữ Đeo Chéo Khóa Mạ Vàng',
                'slug_san_pham'  => 'tui-xach-da-nu-deo-cheo',
                'gia_ban'        => 550000,
                'gia_khuyen_mai' => 450000,
                'hinh_anh'       => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500',
                'mo_ta_ngan'     => 'Chất da PU nhân tạo cao cấp chống trầy xước.',
                'mo_ta_chi_tiet' => 'Kích thước vừa vặn đựng được điện thoại, son phấn và ví tiền nhỏ.',
                'so_luong_ton'   => 50,
                'is_noi_bat'     => 0,
                'is_flash_sale'  => 0,
            ],
        ];

        $created_sps = [];
        foreach ($ds_san_pham as $item) {
            $sp = SanPham::create(array_merge($item, [
                'luot_xem'   => rand(120, 1500),
                'trang_thai' => 1
            ]));
            $created_sps[] = $sp;

            // Biến thể màu sắc & kích thước
            ChiTietSanPham::create([
                'id_san_pham'    => $sp->id,
                'id_mau_sac'     => $ms_ids[0],
                'id_kich_thuoc'  => $kt_ids[1], // M
                'so_luong_ton'   => 40,
                'gia_chenh_lech' => 0,
                'trang_thai'     => 1,
            ]);
            ChiTietSanPham::create([
                'id_san_pham'    => $sp->id,
                'id_mau_sac'     => $ms_ids[1],
                'id_kich_thuoc'  => $kt_ids[2], // L
                'so_luong_ton'   => 35,
                'gia_chenh_lech' => 0,
                'trang_thai'     => 1,
            ]);

            // Ảnh chi tiết
            AnhSanPham::create([
                'id_san_pham'   => $sp->id,
                'duong_dan_anh' => $sp->hinh_anh,
                'thu_tu'        => 1
            ]);
        }

        // 7. Khách Hàng (5 khách hàng mẫu)
        $khach_hangs = [
            ['ho_va_ten' => 'Nguyễn Văn An', 'email' => 'an.nguyen@gmail.com', 'so_dien_thoai' => '0912345678', 'dia_chi' => '120 Cầu Giấy, Hà Nội'],
            ['ho_va_ten' => 'Trần Thị Bích', 'email' => 'bich.tran@gmail.com', 'so_dien_thoai' => '0987654321', 'dia_chi' => '45 Lê Duẩn, Đà Nẵng'],
            ['ho_va_ten' => 'Lê Hoàng Long', 'email' => 'long.le@gmail.com', 'so_dien_thoai' => '0905111222', 'dia_chi' => '88 Nguyễn Huệ, Quận 1, TP.HCM'],
            ['ho_va_ten' => 'Phạm Quỳnh Anh', 'email' => 'quynhanh@gmail.com', 'so_dien_thoai' => '0933445566', 'dia_chi' => '15 Trần Phú, Nha Trang'],
            ['ho_va_ten' => 'Hoàng Minh Tuấn', 'email' => 'tuan.hoang@gmail.com', 'so_dien_thoai' => '0944556677', 'dia_chi' => '22 Hùng Vương, Cần Thơ'],
        ];
        $created_khs = [];
        foreach ($khach_hangs as $kh) {
            $created_kh = KhachHang::create([
                'ho_va_ten'     => $kh['ho_va_ten'],
                'email'         => $kh['email'],
                'so_dien_thoai' => $kh['so_dien_thoai'],
                'mat_khau'      => Hash::make('123456'),
                'dia_chi'       => $kh['dia_chi'],
                'is_kich_hoat'  => 1,
                'is_khoa'       => 0,
            ]);
            $created_khs[] = $created_kh;

            // Địa chỉ nhận hàng
            DiaChiNhanHang::create([
                'id_khach_hang'    => $created_kh->id,
                'ten_nguoi_nhan'   => $kh['ho_va_ten'],
                'so_dien_thoai'    => $kh['so_dien_thoai'],
                'dia_chi_chi_tiet' => $kh['dia_chi'],
                'is_mac_dinh'      => 1,
            ]);
        }

        // 8. Mã Giảm Giá
        $ma_giam_gias = [
            ['ma_code' => 'SALE10', 'ten_giam_gia' => 'Giảm 10% đơn từ 200k', 'loai_giam' => 'phan_tram', 'gia_tri' => 10, 'gia_tri_toi_thieu' => 200000, 'giam_toi_da' => 100000],
            ['ma_code' => 'FREESHIP', 'ten_giam_gia' => 'Miễn phí vận chuyển 30k', 'loai_giam' => 'co_dinh', 'gia_tri' => 30000, 'gia_tri_toi_thieu' => 300000, 'giam_toi_da' => 30000],
            ['ma_code' => 'FASHION50', 'ten_giam_gia' => 'Giảm 50k cho đơn từ 500k', 'loai_giam' => 'co_dinh', 'gia_tri' => 50000, 'gia_tri_toi_thieu' => 500000, 'giam_toi_da' => 50000],
            ['ma_code' => 'VIP20', 'ten_giam_gia' => 'Giảm 20% cho khách hàng thân thiết', 'loai_giam' => 'phan_tram', 'gia_tri' => 20, 'gia_tri_toi_thieu' => 1000000, 'giam_toi_da' => 300000],
        ];
        foreach ($ma_giam_gias as $mgg) {
            MaGiamGia::create(array_merge($mgg, [
                'so_luong'         => 200,
                'so_luong_da_dung' => 15,
                'ngay_bat_dau'     => '2026-01-01',
                'ngay_ket_thuc'    => '2026-12-31',
                'trang_thai'       => 1
            ]));
        }

        // 9. Đơn Hàng Mẫu (đủ các tháng trong năm 2026 để biểu đồ thống kê có dữ liệu thực tế)
        $don_hang_data = [
            ['ma' => 'DH2601001', 'kh_idx' => 0, 'sp_idx' => 0, 'sl' => 2, 'thang' => 1, 'tt_don' => 'da_giao', 'tt_tt' => 'da_thanh_toan'],
            ['ma' => 'DH2602002', 'kh_idx' => 1, 'sp_idx' => 2, 'sl' => 1, 'thang' => 2, 'tt_don' => 'da_giao', 'tt_tt' => 'da_thanh_toan'],
            ['ma' => 'DH2603003', 'kh_idx' => 2, 'sp_idx' => 3, 'sl' => 1, 'thang' => 3, 'tt_don' => 'da_giao', 'tt_tt' => 'da_thanh_toan'],
            ['ma' => 'DH2604004', 'kh_idx' => 3, 'sp_idx' => 6, 'sl' => 1, 'thang' => 4, 'tt_don' => 'da_giao', 'tt_tt' => 'da_thanh_toan'],
            ['ma' => 'DH2605005', 'kh_idx' => 4, 'sp_idx' => 1, 'sl' => 2, 'thang' => 5, 'tt_don' => 'da_giao', 'tt_tt' => 'da_thanh_toan'],
            ['ma' => 'DH2606006', 'kh_idx' => 0, 'sp_idx' => 5, 'sl' => 1, 'thang' => 6, 'tt_don' => 'da_giao', 'tt_tt' => 'da_thanh_toan'],
            ['ma' => 'DH2607007', 'kh_idx' => 1, 'sp_idx' => 4, 'sl' => 3, 'thang' => 7, 'tt_don' => 'da_giao', 'tt_tt' => 'da_thanh_toan'],
            ['ma' => 'DH2608008', 'kh_idx' => 2, 'sp_idx' => 0, 'sl' => 2, 'thang' => 8, 'tt_don' => 'da_giao', 'tt_tt' => 'da_thanh_toan'],
            ['ma' => 'DH2609009', 'kh_idx' => 3, 'sp_idx' => 2, 'sl' => 1, 'thang' => 9, 'tt_don' => 'dang_giao', 'tt_tt' => 'da_thanh_toan'],
            ['ma' => 'DH2609010', 'kh_idx' => 4, 'sp_idx' => 6, 'sl' => 1, 'thang' => 9, 'tt_don' => 'cho_xac_nhan', 'tt_tt' => 'chua_thanh_toan'],
        ];

        foreach ($don_hang_data as $dh_item) {
            $kh = $created_khs[$dh_item['kh_idx']];
            $sp = $created_sps[$dh_item['sp_idx']];
            $gia = $sp->gia_khuyen_mai ?? $sp->gia_ban;
            $tong_tien = $gia * $dh_item['sl'];

            $dh = DonHang::create([
                'id_khach_hang'          => $kh->id,
                'ma_don_hang'            => $dh_item['ma'],
                'ho_ten'                 => $kh->ho_va_ten,
                'so_dien_thoai'          => $kh->so_dien_thoai,
                'email'                  => $kh->email,
                'dia_chi_giao'           => $kh->dia_chi,
                'tong_tien_hang'         => $tong_tien,
                'phi_van_chuyen'         => 30000,
                'tien_giam_gia'          => 0,
                'tong_thanh_toan'        => $tong_tien + 30000,
                'phuong_thuc_thanh_toan' => 'COD',
                'trang_thai_thanh_toan'  => $dh_item['tt_tt'],
                'trang_thai_don_hang'    => $dh_item['tt_don'],
                'ghi_chu'                => 'Giao hàng giờ hành chính',
                'created_at'             => sprintf('2026-%02d-15 10:30:00', $dh_item['thang']),
                'updated_at'             => sprintf('2026-%02d-15 10:30:00', $dh_item['thang']),
            ]);

            ChiTietDonHang::create([
                'id_don_hang'    => $dh->id,
                'id_san_pham'    => $sp->id,
                'ten_san_pham'   => $sp->ten_san_pham,
                'hinh_anh'       => $sp->hinh_anh,
                'ten_mau'        => 'Đen',
                'ten_kich_thuoc' => 'L',
                'gia_ban'        => $gia,
                'so_luong'       => $dh_item['sl'],
                'thanh_tien'     => $tong_tien,
                'created_at'     => $dh->created_at,
                'updated_at'     => $dh->updated_at,
            ]);

            // Đánh giá sản phẩm mẫu
            DanhGiaSanPham::create([
                'id_san_pham'   => $sp->id,
                'id_khach_hang' => $kh->id,
                'so_sao'        => rand(4, 5),
                'tieu_de'       => 'Chất lượng rất ưng ý!',
                'noi_dung'      => 'Sản phẩm giao nhanh, chất vải dày dặn đúng như mô tả, shop phục vụ chu đáo 5 sao!',
                'trang_thai'    => 1,
            ]);
        }
    }
}
