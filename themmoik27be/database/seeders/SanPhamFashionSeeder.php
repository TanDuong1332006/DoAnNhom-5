<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\DanhMuc;
use App\Models\ThuongHieu;
use App\Models\MauSac;
use App\Models\KichThuoc;
use App\Models\SanPham;
use App\Models\ChiTietSanPham;
use App\Models\AnhSanPham;

class SanPhamFashionSeeder extends Seeder
{
    public function run(): void
    {
        $danh_mucs = DanhMuc::all()->keyBy('slug_danh_muc');
        $thuong_hieus = ThuongHieu::pluck('id')->toArray();
        $mau_sacs = MauSac::pluck('id')->toArray();
        $kich_thuocs = KichThuoc::pluck('id')->toArray();

        if ($danh_mucs->isEmpty()) {
            $this->command->error("Chưa có danh mục nào trong database!");
            return;
        }

        $allCategoriesData = [
            // ─── 1. ÁO NAM (20 sản phẩm) ──────────────────────────────────
            'ao-nam' => [
                'prefix' => 'AONAM',
                'items' => [
                    [
                        'ten' => 'Áo Thun Nam Boxy Heavyweight Cotton Supima SOFEP',
                        'gia' => 350000, 'km' => 290000,
                        'img' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=700',
                        'mo_ta' => 'Chất vải 100% Cotton Supima 320GSM dày dặn, phom Boxy chuẩn mực đường phố đương đại.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nam Oxford Cổ Button-Down Relaxed Fit',
                        'gia' => 490000, 'km' => 420000,
                        'img' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=700',
                        'mo_ta' => 'Vải dệt thoi Oxford chống nhăn, cổ áo đính cúc thanh lịch cho chàng trai năng động.'
                    ],
                    [
                        'ten' => 'Áo Polo Nam Dệt Kim Mercerized Cotton Ánh Kim',
                        'gia' => 520000, 'km' => 450000,
                        'img' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=700',
                        'mo_ta' => 'Sợi bông chải kỹ bóng mượt tự nhiên, viền cổ dệt sọc Jacquard sang trọng.'
                    ],
                    [
                        'ten' => 'Áo Thun Nam Graphic Oversized SOFEP Studio 2026',
                        'gia' => 380000, 'km' => 310000,
                        'img' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=700',
                        'mo_ta' => 'In họa tiết hình học trừu tượng công nghệ in lụa thủ công cao cấp không bong tróc.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nam Cuban Collar Hoạ Tiết Minimalist',
                        'gia' => 460000, 'km' => 390000,
                        'img' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=700',
                        'mo_ta' => 'Cổ bẻ Cuba phóng khoáng, chất vải rayon lụa mát lịm cho mùa hè và du lịch.'
                    ],
                    [
                        'ten' => 'Áo Thun Nam Tay Dài Waffle Knit Tổ Ong Thu Đông',
                        'gia' => 420000, 'km' => 360000,
                        'img' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=700',
                        'mo_ta' => 'Cấu trúc dệt tổ ong thoáng khí, giữ ấm nhẹ nhàng, bo tay áo tinh tế.'
                    ],
                    [
                        'ten' => 'Áo Polo Nam Zip Collar Khóa Kéo Kim Loại Sang Trọng',
                        'gia' => 550000, 'km' => 470000,
                        'img' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=700',
                        'mo_ta' => 'Khóa kéo mạ crom bạc cao cấp thay thế cúc bấm truyền thống, phong cách Milan hiện đại.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nam Vải Đũi Linen Thoáng Mát Tự Nhiên',
                        'gia' => 480000, 'km' => 410000,
                        'img' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=700',
                        'mo_ta' => 'Sợi lanh tự nhiên mộc mạc, thấm hút mồ hôi tối đa, tôn vẻ nam tính lãng tử.'
                    ],
                    [
                        'ten' => 'Áo Thun Nam Cổ Tròn Clean Fit Trơn Màu Đen Obsidian',
                        'gia' => 290000, 'km' => 240000,
                        'img' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=700',
                        'mo_ta' => 'Món đồ basic không thể thiếu trong tủ đồ, may viền móc xích đôi chống bai dão.'
                    ],
                    [
                        'ten' => 'Áo Ba Lỗ Nam Tanktop Ribbed Co Giãn Tôn Thể Hình',
                        'gia' => 250000, 'km' => 199000,
                        'img' => 'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?w=700',
                        'mo_ta' => 'Vải dệt gân tăm ôm nhẹ cơ thể, thông thoáng cho các hoạt động thể thao dạo phố.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nam Denim Chambray Cổ Bẻ Nam Tính',
                        'gia' => 580000, 'km' => 499000,
                        'img' => 'https://images.unsplash.com/photo-1516257984-b1b4d707412e?w=700',
                        'mo_ta' => 'Chất vải denim mỏng dệt sợi xanh chàm indigo, wash nhẹ theo phong cách Workwear.'
                    ],
                    [
                        'ten' => 'Áo Thun Nam Raglan Tay Phối Màu Phong Cách Retro',
                        'gia' => 340000, 'km' => 280000,
                        'img' => 'https://images.unsplash.com/photo-1562157873-818bc0726f68?w=700',
                        'mo_ta' => 'Tay áo cắt xéo Raglan kinh điển bóng chày Mỹ, tạo cảm giác bờ vai rộng vạm vỡ.'
                    ],
                    [
                        'ten' => 'Áo Polo Nam Bo Cổ Dệt Họa Tiết Houndstooth',
                        'gia' => 530000, 'km' => 460000,
                        'img' => 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=700',
                        'mo_ta' => 'Điểm nhấn nẹp cổ họa tiết nanh sói tinh tế, logo SOFEP thêu tỉ mỉ ngực trái.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nam Flannel Kẻ Caro Phong Cách Grunge',
                        'gia' => 510000, 'km' => 430000,
                        'img' => 'https://images.unsplash.com/photo-1529374255404-311a2a4f1fd9?w=700',
                        'mo_ta' => 'Bề mặt vải dạ nỉ cào lông ấm áp, đường may caro khớp nối tỉ mỉ từng chi tiết.'
                    ],
                    [
                        'ten' => 'Áo Thun Nam Drop-Shoulder Acid Wash Vintage Streetwear',
                        'gia' => 390000, 'km' => 330000,
                        'img' => 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=700',
                        'mo_ta' => 'Kỹ thuật giặt xả acid wash tạo hiệu ứng màu loang cổ điển độc bản không trùng lặp.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nam Lụa Satin Trơn Dự Tiệc Sang Trọng',
                        'gia' => 620000, 'km' => 540000,
                        'img' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=700',
                        'mo_ta' => 'Chất lụa satin bắt sáng nhẹ dưới ánh đèn tiệc tối, rũ tự nhiên, chuẩn mực quý ông.'
                    ],
                    [
                        'ten' => 'Áo Thun Nam Form Rộng Thêu Chữ Nổi SOFEP Haute Atelier',
                        'gia' => 370000, 'km' => 320000,
                        'img' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=700',
                        'mo_ta' => 'Chữ thêu vi tính dày 3D sắc nét, đường cắt vai rủ tự do chuẩn aesthetic 2026.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nam Cổ Trụ Mandarin Tối Giản Đương Đại',
                        'gia' => 470000, 'km' => 399000,
                        'img' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=700',
                        'mo_ta' => 'Thiết kế cổ tàu hiện đại lược bỏ lá cổ rườm rà, tạo vẻ ngoài tri thức và gọn gàng.'
                    ],
                    [
                        'ten' => 'Áo Thun Nam Cổ Tim V-Neck Co Giãn Sợi Modal Thoáng Khí',
                        'gia' => 320000, 'km' => 270000,
                        'img' => 'https://images.unsplash.com/photo-1523381294911-8d3cead13475?w=700',
                        'mo_ta' => 'Sợi gỗ sồi Modal siêu mềm mượt, kháng khuẩn tự nhiên, nhẹ như không mặc.'
                    ],
                    [
                        'ten' => 'Áo Polo Nam Pique Cotton Kháng Khuẩn Khử Mùi Active',
                        'gia' => 490000, 'km' => 410000,
                        'img' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=700',
                        'mo_ta' => 'Cấu trúc dệt mắt chim tổ ong pique thoát nhiệt nhanh, phù hợp cho ngày hè năng động.'
                    ]
                ]
            ],

            // ─── 2. QUẦN NAM (20 sản phẩm) ────────────────────────────────
            'quan-nam' => [
                'prefix' => 'QUANNAM',
                'items' => [
                    [
                        'ten' => 'Quần Jean Nam Ống Suông Wide-Leg Vintage Wash SOFEP',
                        'gia' => 690000, 'km' => 590000,
                        'img' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=700',
                        'mo_ta' => 'Denim 14oz dày dặn đứng phom, ống suông rủ tự nhiên che khuyết điểm chân hoàn hảo.'
                    ],
                    [
                        'ten' => 'Quần Kaki Nam Chino Dáng Tapered Fit Co Giãn Nhẹ',
                        'gia' => 540000, 'km' => 460000,
                        'img' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=700',
                        'mo_ta' => 'Chất vải Kaki Twill dệt chéo bề mặt mịn, cạp quần lót viền chỉ đôi cao cấp.'
                    ],
                    [
                        'ten' => 'Quần Tây Nam Xếp Ly Phong Cách Sartorial Hàn Quốc',
                        'gia' => 650000, 'km' => 550000,
                        'img' => 'https://images.unsplash.com/photo-1506630448388-4e683c67ddb0?w=700',
                        'mo_ta' => 'Đường ly xếp đôi tinh xảo, cạp cao tôn chiều dài chân chuẩn tỷ lệ người mẫu.'
                    ],
                    [
                        'ten' => 'Quần Parachute Nam Utility Cargo Túi Hộp Dây Rút',
                        'gia' => 590000, 'km' => 499000,
                        'img' => 'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?w=700',
                        'mo_ta' => 'Chất liệu dù parachute siêu nhẹ trượt nước, gấu quần có nút siết rút linh hoạt.'
                    ],
                    [
                        'ten' => 'Quần Short Nam Kaki Túi Chéo Năng Động Dạo Phố',
                        'gia' => 380000, 'km' => 320000,
                        'img' => 'https://images.unsplash.com/photo-1560243563-062bfc001d68?w=700',
                        'mo_ta' => 'Độ dài trên gối vừa vặn, lưng thun co giãn thoải mái cho ngày cuối tuần.'
                    ],
                    [
                        'ten' => 'Quần Jean Nam Rách Gối Xước Nhẹ Phong Cách Streetwear',
                        'gia' => 640000, 'km' => 550000,
                        'img' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=700',
                        'mo_ta' => 'Vết mài xước thủ công nghệ thuật, phối cùng sneaker tạo vẻ ngoài bụi bặm cá tính.'
                    ],
                    [
                        'ten' => 'Quần Jogger Nam Vải Dù 2 Lớp Kháng Gió Đô Thị',
                        'gia' => 490000, 'km' => 420000,
                        'img' => 'https://images.unsplash.com/photo-1555689502-c4b22d76c56f?w=700',
                        'mo_ta' => 'Bo gấu thun co giãn êm ái, túi hông có khóa kéo an toàn bảo vệ điện thoại.'
                    ],
                    [
                        'ten' => 'Quần Tây Nam Cạp Gurkha Khóa Kim Loại Sang Trọng',
                        'gia' => 720000, 'km' => 620000,
                        'img' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=700',
                        'mo_ta' => 'Lấy cảm hứng từ quân phục Gurkha truyền thống, khóa bên hông tùy chỉnh không cần thắt lưng.'
                    ],
                    [
                        'ten' => 'Quần Short Nam Thể Thao 2 Lớp Chạy Bộ Kháng Nước',
                        'gia' => 350000, 'km' => 290000,
                        'img' => 'https://images.unsplash.com/photo-1560243563-062bfc001d68?w=700',
                        'mo_ta' => 'Lớp lót trong nén cơ bắp chống ma sát, lớp ngoài xẻ tà tăng biên độ vận động.'
                    ],
                    [
                        'ten' => 'Quần Jean Nam Slimfit Màu Xanh Chàm Indigo Tối Giản',
                        'gia' => 620000, 'km' => 520000,
                        'img' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=700',
                        'mo_ta' => 'Màu nhuộm chàm thô sâu màu, phom ôm vừa phải tôn vóc dáng thon gọn.'
                    ],
                    [
                        'ten' => 'Quần Kaki Nam Ống Đứng Regular Fit Chuẩn Công Sở',
                        'gia' => 520000, 'km' => 450000,
                        'img' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=700',
                        'mo_ta' => 'Xử lý chống bám bụi và chống phai màu khi giặt máy, thích hợp mặc cả tuần.'
                    ],
                    [
                        'ten' => 'Quần Túi Hộp Nam Cargo Pants Vải Ripstop Quân Đội',
                        'gia' => 680000, 'km' => 580000,
                        'img' => 'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?w=700',
                        'mo_ta' => 'Cấu trúc dệt ô vuông Ripstop chống rách toạc, 6 túi đa dụng tiện lợi.'
                    ],
                    [
                        'ten' => 'Quần Jean Nam Màu Đen Obsidian Wash Nhẹ Chống Bụi',
                        'gia' => 610000, 'km' => 530000,
                        'img' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=700',
                        'mo_ta' => 'Sắc đen huyền bí Obsidian nhuộm lưu huỳnh giữ màu cực tốt qua nhiều mùa.'
                    ],
                    [
                        'ten' => 'Quần Tây Nam Co Giãn 4 Chiều Công Sở Không Nhăn',
                        'gia' => 670000, 'km' => 580000,
                        'img' => 'https://images.unsplash.com/photo-1506630448388-4e683c67ddb0?w=700',
                        'mo_ta' => 'Công nghệ dệt sợi Spandex 4 chiều giúp đứng lên ngồi xuống vô cùng dễ chịu.'
                    ],
                    [
                        'ten' => 'Quần Short Nam Vải Đũi Linen Đi Biển & Du Lịch',
                        'gia' => 360000, 'km' => 299000,
                        'img' => 'https://images.unsplash.com/photo-1560243563-062bfc001d68?w=700',
                        'mo_ta' => 'Sợi lanh tự nhiên mộc mạc thoáng mát, dây rút thắt nơ kiểu Ý năng động.'
                    ],
                    [
                        'ten' => 'Quần Nỉ Nam Sweatpants Bo Gấu Dày Dặn 380GSM',
                        'gia' => 450000, 'km' => 390000,
                        'img' => 'https://images.unsplash.com/photo-1555689502-c4b22d76c56f?w=700',
                        'mo_ta' => 'Lớp nỉ chân cua bông dày dặn giữ nhiệt tốt trong những ngày se lạnh.'
                    ],
                    [
                        'ten' => 'Quần Jean Nam Ống Loe Bootcut Phong Cách Retro Y2K',
                        'gia' => 710000, 'km' => 610000,
                        'img' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=700',
                        'mo_ta' => 'Ống loe nhẹ từ đầu gối xuống cổ chân, phối hoàn hảo cùng chelsea boot hay sneaker.'
                    ],
                    [
                        'ten' => 'Quần Kaki Nam Xếp Ly Baggy Dáng Rộng Tự Do',
                        'gia' => 560000, 'km' => 480000,
                        'img' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=700',
                        'mo_ta' => 'Thiết kế baggy thoải mái tối đa cho hông và đùi, phóng khoáng đậm chất nghệ sĩ.'
                    ],
                    [
                        'ten' => 'Quần Short Nam Jean Denim Cắt Gấu Tua Rua Bụi Bặm',
                        'gia' => 410000, 'km' => 350000,
                        'img' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=700',
                        'mo_ta' => 'Vết cắt gấu phóng khoáng tự nhiên, chất denim xanh mài mộc mạc.'
                    ],
                    [
                        'ten' => 'Quần Tây Nam Họa Tiết Kẻ Sọc Pinstripe Cổ Điển',
                        'gia' => 690000, 'km' => 590000,
                        'img' => 'https://images.unsplash.com/photo-1506630448388-4e683c67ddb0?w=700',
                        'mo_ta' => 'Sọc kẻ chỉ trắng mỏng dọc thân quần tạo hiệu ứng kéo dài đôi chân thanh thoát.'
                    ]
                ]
            ],

            // ─── 3. VÁY & ĐẦM NỮ (20 sản phẩm) ────────────────────────────
            'vay-dam-nu' => [
                'prefix' => 'VAYDAM',
                'items' => [
                    [
                        'ten' => 'Đầm Lụa Satin Hai Dây Dáng Dài Hở Lưng Quyến Rũ SOFEP',
                        'gia' => 790000, 'km' => 680000,
                        'img' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=700',
                        'mo_ta' => 'Chất lụa satin óng ả mềm rủ theo từng bước di chuyển, dây đan chéo sau lưng gợi cảm.'
                    ],
                    [
                        'ten' => 'Váy Xòe Nữ Cổ Vuông Tay Bồng Tiểu Thư Dự Tiệc',
                        'gia' => 650000, 'km' => 550000,
                        'img' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=700',
                        'mo_ta' => 'Cổ vuông khoe xương quai xanh tinh tế, tùng váy xếp ly bồng bềnh công chúa.'
                    ],
                    [
                        'ten' => 'Đầm Bodycon Dệt Kim Gân Tăm Ôm Sát Tôn Đường Cong',
                        'gia' => 520000, 'km' => 430000,
                        'img' => 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=700',
                        'mo_ta' => 'Sợi dệt co giãn ôm sát 3 vòng hoàn hảo, cổ tròn thanh lịch dễ phối cùng blazer.'
                    ],
                    [
                        'ten' => 'Chân Váy Chữ A Xếp Ly Phong Cách Tennis Năng Động',
                        'gia' => 380000, 'km' => 310000,
                        'img' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?w=700',
                        'mo_ta' => 'Đường ly sắc nét có lót quần trong an toàn, mang lại nét trẻ trung ngọt ngào.'
                    ],
                    [
                        'ten' => 'Đầm Suông Nữ Vải Đũi Linen Thắt Nơ Eo Tinh Tế',
                        'gia' => 580000, 'km' => 490000,
                        'img' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=700',
                        'mo_ta' => 'Vải lanh đũi mềm mát, dây thắt eo điều chỉnh tạo điểm nhấn vòng hai thon thả.'
                    ],
                    [
                        'ten' => 'Chân Váy Midi Xẻ Tà Nữ Tính Dáng Dài Thanh Lịch',
                        'gia' => 460000, 'km' => 390000,
                        'img' => 'https://images.unsplash.com/photo-1550639525-c97d455acf70?w=700',
                        'mo_ta' => 'Đường xẻ tà bên hông khoe khéo đôi chân dài, chất liệu crepe rũ không nhăn.'
                    ],
                    [
                        'ten' => 'Đầm Maxi Đi Biển Hoạ Tiết Hoa Nhí Bohemian Mùa Hè',
                        'gia' => 690000, 'km' => 590000,
                        'img' => 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?w=700',
                        'mo_ta' => 'Họa tiết hoa cúc nhí cổ điển, tà váy bay bổng tuyệt đẹp trong những chuyến nghỉ dưỡng.'
                    ],
                    [
                        'ten' => 'Váy Yếm Nữ Vải Kaki Vintage Dễ Phối Đồ Dạo Phố',
                        'gia' => 450000, 'km' => 380000,
                        'img' => 'https://images.unsplash.com/photo-1502716119720-b23a93e5fe1b?w=700',
                        'mo_ta' => 'Thiết kế yếm hai dây bản to, có hai túi vuông tiện dụng phía trước.'
                    ],
                    [
                        'ten' => 'Đầm Dạ Hội Trễ Vai Đính Đá Pha Lê Cao Cấp SOFEP',
                        'gia' => 1250000, 'km' => 990000,
                        'img' => 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?w=700',
                        'mo_ta' => 'Thiết kế đầm dạ hội sang trọng đính kết thủ công, tôn trọn vẹn bờ vai trần kiêu kỳ.'
                    ],
                    [
                        'ten' => 'Chân Váy Bút Chì Công Sở Co Giãn Ôm Tôn Dáng',
                        'gia' => 420000, 'km' => 350000,
                        'img' => 'https://images.unsplash.com/photo-1550639525-c97d455acf70?w=700',
                        'mo_ta' => 'Chân váy chuẩn mực cho quý cô văn phòng, xẻ sau nhẹ nhàng bước đi linh hoạt.'
                    ],
                    [
                        'ten' => 'Đầm Sơ Mi Nữ Kèm Thắt Lưng Bản Nhỏ Thời Thượng',
                        'gia' => 590000, 'km' => 499000,
                        'img' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=700',
                        'mo_ta' => 'Cổ sơ mi thanh lịch kết hợp cùng tùng đầm suông, diện đi làm hay cà phê đều đẹp.'
                    ],
                    [
                        'ten' => 'Chân Váy Chữ A Nữ Da PU Cao Cấp Khóa Bạc Cá Tính',
                        'gia' => 480000, 'km' => 410000,
                        'img' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?w=700',
                        'mo_ta' => 'Chất da PU bóng mịn mềm mại, phối cùng áo len hoặc boot cổ cao cực sành điệu.'
                    ],
                    [
                        'ten' => 'Đầm Babydoll Nữ Tay Lỡ Xinh Xắn Ngọt Ngào',
                        'gia' => 430000, 'km' => 360000,
                        'img' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=700',
                        'mo_ta' => 'Phom dáng babydoll rộng rãi giấu eo tốt, mang lại vẻ ngoài trẻ trung như búp bê.'
                    ],
                    [
                        'ten' => 'Chân Váy Jean Dáng Dài Xẻ Trước Phong Cách Ulzzang',
                        'gia' => 520000, 'km' => 440000,
                        'img' => 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=700',
                        'mo_ta' => 'Chất denim cotton 100% không giãn, đường xẻ giữa ấn tượng tôn chiều cao.'
                    ],
                    [
                        'ten' => 'Đầm Suông Đuôi Cá Nữ Dự Tiệc Màu Trắng Ngọc Trai',
                        'gia' => 740000, 'km' => 630000,
                        'img' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=700',
                        'mo_ta' => 'Phần đuôi cá nhún bèo mềm mại tạo nhịp điệu uyển chuyển theo từng bước chân.'
                    ],
                    [
                        'ten' => 'Váy Nhún Bèo Tầng Vải Chiffon Bay Bổng Lãng Mạn',
                        'gia' => 560000, 'km' => 470000,
                        'img' => 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?w=700',
                        'mo_ta' => 'Lớp voan chiffon mềm xếp tầng bồng bềnh, họa tiết hoa phấn pastel ngọt ngào.'
                    ],
                    [
                        'ten' => 'Đầm Ôm Body Cổ Yếm Khoét Lưng Dự Tiệc Quyến Rũ',
                        'gia' => 680000, 'km' => 570000,
                        'img' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=700',
                        'mo_ta' => 'Cổ yếm ôm trọn vòng một, khoét lưng chữ U gợi cảm không thể rời mắt.'
                    ],
                    [
                        'ten' => 'Chân Váy Xếp Tầng Tutu Phối Lưới Thời Thượng Paris',
                        'gia' => 510000, 'km' => 430000,
                        'img' => 'https://images.unsplash.com/photo-1502716119720-b23a93e5fe1b?w=700',
                        'mo_ta' => 'Lớp lưới xếp phồng nhẹ nhàng, phong cách tiểu thư đài các dễ phối trang phục.'
                    ],
                    [
                        'ten' => 'Đầm Ren Trắng Nữ Tinh Khôi Dáng Xòe Lót Lụa',
                        'gia' => 820000, 'km' => 699000,
                        'img' => 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?w=700',
                        'mo_ta' => 'Họa tiết ren hoa thêu nổi thủ công, lớp lót lụa habutai êm dịu nâng niu làn da.'
                    ],
                    [
                        'ten' => 'Váy Suông Nữ Thể Thao Phối Sọc Năng Động Trẻ Trung',
                        'gia' => 390000, 'km' => 320000,
                        'img' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?w=700',
                        'mo_ta' => 'Vải thun cotton co giãn thấm hút mồ hôi, phối sọc thể thao hai bên sườn áo.'
                    ]
                ]
            ],

            // ─── 4. ÁO NỮ (20 sản phẩm) ───────────────────────────────────
            'ao-nu' => [
                'prefix' => 'AONU',
                'items' => [
                    [
                        'ten' => 'Áo Croptop Nữ Tay Ngắn Cổ Tròn Ôm Body Cotton SOFEP',
                        'gia' => 260000, 'km' => 210000,
                        'img' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=700',
                        'mo_ta' => 'Chất vải thun cotton spandex co giãn 4 chiều mềm mịn, tôn trọn vẹn eo thon.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nữ Lụa Cổ Nơ Phong Cách Công Sở Paris',
                        'gia' => 480000, 'km' => 410000,
                        'img' => 'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?w=700',
                        'mo_ta' => 'Dải nơ cổ thắt mềm rủ thanh lịch, chất lụa cao cấp không nhăn khi làm việc.'
                    ],
                    [
                        'ten' => 'Áo Kiểu Nữ Trễ Vai Tay Bồng Bo Chun Nữ Tính',
                        'gia' => 390000, 'km' => 320000,
                        'img' => 'https://images.unsplash.com/photo-1554568218-0f1715e72254?w=700',
                        'mo_ta' => 'Bo chun trễ vai êm ái không hằn da, khoe trọn vẹn bờ vai trần quyến rũ.'
                    ],
                    [
                        'ten' => 'Áo Len Nữ Cổ Lọ Dệt Kim Giữ Ấm Mùa Đông',
                        'gia' => 460000, 'km' => 380000,
                        'img' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=700',
                        'mo_ta' => 'Len dệt mịn sợi nhỏ không dặm ngứa, phom ôm nhẹ ấm áp mùa gió lạnh.'
                    ],
                    [
                        'ten' => 'Áo Thun Nữ Oversized In Hình Chữ Nổi Minimalist',
                        'gia' => 320000, 'km' => 260000,
                        'img' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=700',
                        'mo_ta' => 'Form rộng giấu quần sành điệu, chất vải cotton 100% thoáng mát tự nhiên.'
                    ],
                    [
                        'ten' => 'Áo Hai Dây Nữ Lụa Satin Viền Ren Cổ V Gợi Cảm',
                        'gia' => 290000, 'km' => 230000,
                        'img' => 'https://images.unsplash.com/photo-1551803091-e20673f15770?w=700',
                        'mo_ta' => 'Viền ren tinh tế trước ngực, dây áo tùy chỉnh độ dài linh hoạt.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nữ Voan Kẻ Sọc Dáng Rộng Dạo Phố',
                        'gia' => 450000, 'km' => 380000,
                        'img' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=700',
                        'mo_ta' => 'Sọc xanh trắng tươi trẻ, mặc đơn hoặc khoác ngoài áo hai dây đều tuyệt đẹp.'
                    ],
                    [
                        'ten' => 'Áo Kiểu Nữ Cổ Vuông Tay Nhún Điệu Đà Tiểu Thư',
                        'gia' => 410000, 'km' => 340000,
                        'img' => 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?w=700',
                        'mo_ta' => 'Đường may nhún ngực tinh xảo tôn vòng một, tay bồng nhẹ nhàng nữ tính.'
                    ],
                    [
                        'ten' => 'Áo Ba Lỗ Nữ Dệt Rib Co Giãn Tôn Dáng Thể Thao',
                        'gia' => 230000, 'km' => 180000,
                        'img' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=700',
                        'mo_ta' => 'Chất dệt kim gân co giãn ôm dáng, dễ dàng phối cùng quần jean hay áo khoác ngoài.'
                    ],
                    [
                        'ten' => 'Áo Nỉ Nữ Hoodie Form Rộng Phong Cách Ulzzang Hàn Quốc',
                        'gia' => 490000, 'km' => 410000,
                        'img' => 'https://images.unsplash.com/photo-1485230895905-ec40ba36b9bc?w=700',
                        'mo_ta' => 'Nón trùm 2 lớp dày dặn, túi kangaroo phía trước rộng rãi tiện dụng.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nữ Trắng Cổ Đức Basic Vải Kate Lụa',
                        'gia' => 390000, 'km' => 330000,
                        'img' => 'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?w=700',
                        'mo_ta' => 'Chiếc áo sơ mi trắng tiêu chuẩn cho phái đẹp, form dáng đứng đắn chỉn chu.'
                    ],
                    [
                        'ten' => 'Áo Len Nữ Cardigan Nút Cài Ngọc Trai Vintage',
                        'gia' => 520000, 'km' => 430000,
                        'img' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=700',
                        'mo_ta' => 'Hàng nút ngọc trai nhân tạo sang trọng, chất len mềm rủ nhẹ nhàng mùa thu.'
                    ],
                    [
                        'ten' => 'Áo Croptop Nữ Dài Tay Khóa Kéo Hai Đầu Sành Điệu',
                        'gia' => 350000, 'km' => 290000,
                        'img' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=700',
                        'mo_ta' => 'Khóa kéo hai đầu kim loại cho phép tùy biến kiểu mặc kéo hở trên dưới cá tính.'
                    ],
                    [
                        'ten' => 'Áo Kiểu Nữ Thắt Nơ Lưng Hở Vai Gợi Cảm Đi Tiệc',
                        'gia' => 430000, 'km' => 360000,
                        'img' => 'https://images.unsplash.com/photo-1554568218-0f1715e72254?w=700',
                        'mo_ta' => 'Khoét lưng thắt nơ vải tinh xảo, diện cùng quần ống rộng tạo phong thái kiêu kỳ.'
                    ],
                    [
                        'ten' => 'Áo Thun Nữ Cổ Tim Vải Tencel Mát Mịn Thấm Hút',
                        'gia' => 280000, 'km' => 220000,
                        'img' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=700',
                        'mo_ta' => 'Sợi sinh học Tencel từ bột gỗ thiên nhiên, êm dịu tuyệt đối với làn da nhạy cảm.'
                    ],
                    [
                        'ten' => 'Áo Sơ Mi Nữ Tay Lỡ Vải Đũi Mộc Mạc Tối Giản',
                        'gia' => 410000, 'km' => 340000,
                        'img' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=700',
                        'mo_ta' => 'Tay lỡ duyên dáng, cúc áo bằng gỗ dừa thân thiện môi trường.'
                    ],
                    [
                        'ten' => 'Áo Corset Nữ Định Hình Eo Gợi Cảm Phối Ren',
                        'gia' => 490000, 'km' => 410000,
                        'img' => 'https://images.unsplash.com/photo-1551803091-e20673f15770?w=700',
                        'mo_ta' => 'Gọng định hình mềm mại ôm khít đường cong eo mà không gây khó thở.'
                    ],
                    [
                        'ten' => 'Áo Dệt Kim Nữ Cộc Tay Cổ Polo Trẻ Trung Năng Động',
                        'gia' => 370000, 'km' => 310000,
                        'img' => 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?w=700',
                        'mo_ta' => 'Dệt jacquard họa tiết xương cá thanh mảnh, cổ bẻ phối màu nhã nhặn.'
                    ],
                    [
                        'ten' => 'Áo Peplum Nữ Che Khuyết Điểm Bụng Tôn Vóc Dáng',
                        'gia' => 470000, 'km' => 399000,
                        'img' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?w=700',
                        'mo_ta' => 'Chiết eo cao và xòe nhẹ phần đuôi áo giúp tạo ảo giác vòng eo con kiến.'
                    ],
                    [
                        'ten' => 'Áo Thun Nữ Body Cổ Khóa Half-Zip Thể Thao Cao Cấp',
                        'gia' => 360000, 'km' => 299000,
                        'img' => 'https://images.unsplash.com/photo-1485230895905-ec40ba36b9bc?w=700',
                        'mo_ta' => 'Chất liệu thun thể thao co giãn 4 chiều, khóa zip nửa ngực tiện lợi tùy chỉnh.'
                    ]
                ]
            ],

            // ─── 5. ÁO KHOÁC & BLAZER (20 sản phẩm) ───────────────────────
            'ao-khoac-blazer' => [
                'prefix' => 'AOKHOAC',
                'items' => [
                    [
                        'ten' => 'Áo Khoác Blazer Nam 2 Hàng Khuy Double-Breasted SOFEP',
                        'gia' => 1150000, 'km' => 950000,
                        'img' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=700',
                        'mo_ta' => 'Thiết kế 6 khuy cổ điển phong cách quý tộc Ý, vải wool pha đứng form hoàn hảo.'
                    ],
                    [
                        'ten' => 'Áo Khoác Bomber Dù 2 Lớp Chống Gió Chống Nước',
                        'gia' => 690000, 'km' => 580000,
                        'img' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=700',
                        'mo_ta' => 'Bo cổ và tay áo dệt thun dày dặn, túi tay áo có khóa zip tiện dụng.'
                    ],
                    [
                        'ten' => 'Áo Khoác Blazer Nữ Cắt Lửng Cropped Thời Thượng',
                        'gia' => 780000, 'km' => 650000,
                        'img' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?w=700',
                        'mo_ta' => 'Dáng croptop ngang eo tôn chiều dài chân, đệm vai nhẹ tạo phom quyền lực.'
                    ],
                    [
                        'ten' => 'Áo Khoác Denim Bò Unisex Lót Lông Cừu Ấm Áp Mùa Đông',
                        'gia' => 890000, 'km' => 760000,
                        'img' => 'https://images.unsplash.com/photo-1544441893-675973e31985?w=700',
                        'mo_ta' => 'Cổ áo và thân trong lót lông cừu tổng hợp giữ nhiệt cực tốt, cúc đồng đúc chắc chắn.'
                    ],
                    [
                        'ten' => 'Áo Khoác Măng Tô Dạ Dáng Dài Thu Đông Đẳng Cấp',
                        'gia' => 1450000, 'km' => 1190000,
                        'img' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=700',
                        'mo_ta' => 'Chất dạ lông cừu ép nhiệt mềm mượt, ve áo chữ K bản rộng sang trọng vượt thời gian.'
                    ],
                    [
                        'ten' => 'Áo Khoác Gió Thể Thao Chống Nước Có Mũ Trùm Đa Năng',
                        'gia' => 520000, 'km' => 430000,
                        'img' => 'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?w=700',
                        'mo_ta' => 'Chỉ số chống nước 5000mm, đường ép seam chống thấm nước tuyệt đối qua đường may.'
                    ],
                    [
                        'ten' => 'Áo Khoác Blazer Nam Form Rộng Relaxed Fit Smart Casual',
                        'gia' => 980000, 'km' => 820000,
                        'img' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=700',
                        'mo_ta' => 'Không đệm vai cứng nhắc, tạo cảm giác thoải mái tự nhiên khi đi làm hay hẹn hò.'
                    ],
                    [
                        'ten' => 'Áo Khoác Da Biker Da Cừu Cao Cấp Khóa Xéo Đinh Tán',
                        'gia' => 1850000, 'km' => 1490000,
                        'img' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=700',
                        'mo_ta' => 'Chất da cừu tự nhiên mềm mại như nhung, khóa kéo kim loại YKK bền bỉ trọn đời.'
                    ],
                    [
                        'ten' => 'Áo Khoác Cardigan Len Vặn Thừng Dáng Rộng Vintage',
                        'gia' => 620000, 'km' => 520000,
                        'img' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=700',
                        'mo_ta' => 'Họa tiết vặn thừng dày nổi 3D, nút sừng bò tự nhiên mang đậm hơi thở đồng quê Anh quốc.'
                    ],
                    [
                        'ten' => 'Áo Khoác Kaki Safari 4 Túi Hộp Quân Đội Đa Dụng',
                        'gia' => 750000, 'km' => 630000,
                        'img' => 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?w=700',
                        'mo_ta' => 'Lấy cảm hứng từ áo dã ngoại Safari, dây rút eo bên trong giúp tôn eo linh hoạt.'
                    ],
                    [
                        'ten' => 'Áo Blazer Nữ Kẻ Caro Houndstooth Nanh Sói Hoàng Gia',
                        'gia' => 850000, 'km' => 720000,
                        'img' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?w=700',
                        'mo_ta' => 'Họa tiết nanh sói đen trắng cổ điển, lớp lót lụa satin mượt mà ôm sát cơ thể.'
                    ],
                    [
                        'ten' => 'Áo Khoác Phao Lông Vũ Dáng Ngắn Siêu Nhẹ 800FP',
                        'gia' => 1290000, 'km' => 1050000,
                        'img' => 'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?w=700',
                        'mo_ta' => 'Lõi lông vũ 90/10 siêu nhẹ chỉ 280g, có túi gấp gọn tiện lợi mang đi du lịch.'
                    ],
                    [
                        'ten' => 'Áo Khoác Varsity Jacket Phối Tay Da Học Đường Trẻ Trung',
                        'gia' => 820000, 'km' => 690000,
                        'img' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=700',
                        'mo_ta' => 'Thân dạ phối tay da tổng hợp cao cấp, logo chữ thêu khăn xù nổi bật ngực áo.'
                    ],
                    [
                        'ten' => 'Áo Khoác Trench Coat Dáng Dài Phong Cách London Cổ Điển',
                        'gia' => 1550000, 'km' => 1290000,
                        'img' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=700',
                        'mo_ta' => 'Vải gabardine dệt mật độ cao chống gió và nước mưa nhẹ, kèm đai thắt eo khóa kim loại.'
                    ],
                    [
                        'ten' => 'Áo Khoác Gió Nam Nữ Trượt Nước Đi Mưa Có Phản Quang',
                        'gia' => 450000, 'km' => 380000,
                        'img' => 'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?w=700',
                        'mo_ta' => 'Đường viền phản quang ban đêm an toàn, gấp gọn thành chiếc túi nhỏ đeo hông.'
                    ],
                    [
                        'ten' => 'Áo Blazer Nam Linen Không Đệm Vai Thoáng Mát Mùa Hè',
                        'gia' => 890000, 'km' => 750000,
                        'img' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=700',
                        'mo_ta' => 'Chất sợi lanh đũi mộc mạc, phù hợp khí hậu nhiệt đới mà vẫn giữ được sự lịch thiệp.'
                    ],
                    [
                        'ten' => 'Áo Khoác Nhựa Dù Trong Suốt Thời Trang Độc Lạ Y2K',
                        'gia' => 490000, 'km' => 399000,
                        'img' => 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?w=700',
                        'mo_ta' => 'Chất liệu TPU mờ chống nước hoàn toàn, khoe trọn outfit mặc bên trong.'
                    ],
                    [
                        'ten' => 'Áo Khoác Nỉ Hoodie Zip 2 Chiều Form Rộng Streetwear',
                        'gia' => 580000, 'km' => 480000,
                        'img' => 'https://images.unsplash.com/photo-1544441893-675973e31985?w=700',
                        'mo_ta' => 'Khóa kéo hai chiều tiện lợi, vải nỉ bông dày 400GSM siêu đầm tay.'
                    ],
                    [
                        'ten' => 'Áo Khoác Kimono Cách Điệu Vải Gấm Sang Trọng Dạo Phố',
                        'gia' => 720000, 'km' => 599000,
                        'img' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=700',
                        'mo_ta' => 'Tay áo kimono rộng rãi, hoa văn dệt chìm sang trọng mang cảm hứng Á Đông.'
                    ],
                    [
                        'ten' => 'Áo Blazer Nữ Không Tay Dáng Dài Vest Gilet Thanh Lịch',
                        'gia' => 640000, 'km' => 540000,
                        'img' => 'https://images.unsplash.com/photo-1548883354-7622d03aca27?w=700',
                        'mo_ta' => 'Thiết kế gilet dáng dài che khuyết điểm hông, phối cùng sơ mi trắng hay đầm suông.'
                    ]
                ]
            ],

            // ─── 6. GIÀY & SNEAKER (20 sản phẩm) ──────────────────────────
            'giay-sneaker' => [
                'prefix' => 'GIAY',
                'items' => [
                    [
                        'ten' => 'Giày Sneaker Thấp Cổ Low-Top Trắng Chunky Sole SOFEP',
                        'gia' => 950000, 'km' => 790000,
                        'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=700',
                        'mo_ta' => 'Da bò hạt cao cấp dễ lau chùi, đế đúc cao su nguyên khối êm ái tăng 4cm chiều cao.'
                    ],
                    [
                        'ten' => 'Giày Lười Da Bò Nam Loafer Khóa Mạ Vàng Sang Trọng',
                        'gia' => 1250000, 'km' => 990000,
                        'img' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=700',
                        'mo_ta' => 'Da bò Nappa nhập khẩu mềm như găng tay, đai khóa mạ vàng 18K tinh tế.'
                    ],
                    [
                        'ten' => 'Giày Cao Gót Nữ Mũi Nhọn 7cm Đính Đá Dự Tiệc Quý Phái',
                        'gia' => 850000, 'km' => 690000,
                        'img' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=700',
                        'mo_ta' => 'Gót nhọn thanh mảnh bọc kim loại, lót đế đệm mút cao su non không đau mỏi chân.'
                    ],
                    [
                        'ten' => 'Giày Sneaker Chạy Bộ Siêu Nhẹ Thoáng Khí Breathable Pro',
                        'gia' => 750000, 'km' => 599000,
                        'img' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=700',
                        'mo_ta' => 'Thân giày dệt sợi Flyknit thoáng khí như một chiếc tất, đế đàn hồi trợ lực bước chạy.'
                    ],
                    [
                        'ten' => 'Giày Sandal Nữ Dây Mảnh Quai Đan Đế Vuông 5cm',
                        'gia' => 490000, 'km' => 399000,
                        'img' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=700',
                        'mo_ta' => 'Quai mảnh đan chéo thanh thoát tôn bàn chân thon thả, đế vuông vững chãi.'
                    ],
                    [
                        'ten' => 'Giày Da Nam Oxford Buộc Dây Công Sở Da Bóng Cao Cấp',
                        'gia' => 1450000, 'km' => 1190000,
                        'img' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=700',
                        'mo_ta' => 'Cấu trúc may đế Goodyear bền bỉ trọn đời, da bóng phủ sáp thủ công truyền thống.'
                    ],
                    [
                        'ten' => 'Giày Sneaker Cổ Cao High-Top Canvas Bụi Bặm Vintage',
                        'gia' => 620000, 'km' => 499000,
                        'img' => 'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?w=700',
                        'mo_ta' => 'Vải bố canvas 16oz dày dặn, mũi cao su bảo vệ ngón chân chống va đập.'
                    ],
                    [
                        'ten' => 'Giày Mule Sục Da Nữ Mũi Vuông Thanh Lịch Tiện Dụng',
                        'gia' => 550000, 'km' => 450000,
                        'img' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=700',
                        'mo_ta' => 'Thiết kế hở gót xỏ chân nhanh chóng, mũi vuông hiện đại chuẩn aesthetic Hàn Quốc.'
                    ],
                    [
                        'ten' => 'Giày Thể Thao Tập Gym Nam Nữ Đế Bám Chống Trượt Chuyên Nghiệp',
                        'gia' => 780000, 'km' => 640000,
                        'img' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=700',
                        'mo_ta' => 'Đế bằng hỗ trợ các bài squat và deadlift vững chắc, hỗ trợ cổ chân tối đa.'
                    ],
                    [
                        'ten' => 'Giày Búp Bê Nữ Nơ Nhỏ Đế Bệt Êm Chân Da Cừu',
                        'gia' => 460000, 'km' => 370000,
                        'img' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=700',
                        'mo_ta' => 'Da cừu dẻo dai có thể cuộn tròn lại, lót đệm siêu êm cho ngày dài đi bộ.'
                    ],
                    [
                        'ten' => 'Giày Chelsea Boots Nam Da Lộn Cổ Thun Phong Cách Rocker',
                        'gia' => 1350000, 'km' => 1090000,
                        'img' => 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=700',
                        'mo_ta' => 'Da lộn chải mịn sẫm màu, cổ thun đàn hồi dễ mang tháo, phom mũi thon gọn nam tính.'
                    ],
                    [
                        'ten' => 'Giày Boots Nữ Cổ Cao Qua Gối Da Thuộc Tôn Chân',
                        'gia' => 1290000, 'km' => 1050000,
                        'img' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=700',
                        'mo_ta' => 'Dáng ôm sát đùi tôn vóc dáng đồng hồ cát, khóa kéo dọc bắp chân tiện lợi.'
                    ],
                    [
                        'ten' => 'Giày Sneaker Retro Vintage 1980s Đế Bánh Mì Dày Dặn',
                        'gia' => 890000, 'km' => 720000,
                        'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=700',
                        'mo_ta' => 'Phối màu vàng kem ngà cổ điển, chất liệu da lộn phối lưới mang cảm hứng marathon 80s.'
                    ],
                    [
                        'ten' => 'Giày Sandal Nam Quai Dù Đế Cao Su Êm Ái Đi Mưa',
                        'gia' => 390000, 'km' => 310000,
                        'img' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=700',
                        'mo_ta' => 'Quai dệt nylon chịu tải cao mau khô ráo, khóa dán velcro điều chỉnh linh hoạt.'
                    ],
                    [
                        'ten' => 'Giày Cao Gót Nữ Quai Trong Suốt Mũi Vuông Độc Đáo',
                        'gia' => 620000, 'km' => 499000,
                        'img' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=700',
                        'mo_ta' => 'Quai silicon trong suốt tạo cảm giác đôi chân dài miên man không giới hạn.'
                    ],
                    [
                        'ten' => 'Giày Lười Nam Moccasin Da Lộn Mềm Mại Thắt Dây Da',
                        'gia' => 880000, 'km' => 720000,
                        'img' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=700',
                        'mo_ta' => 'Khâu tay viền mũi tỉ mỉ, đế cao su hạt dăm chống trượt tuyệt đối khi lái xe.'
                    ],
                    [
                        'ten' => 'Giày Chunky Sneaker Đế Khủng Tăng Chiều Cao 5cm',
                        'gia' => 920000, 'km' => 760000,
                        'img' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=700',
                        'mo_ta' => 'Phom dáng hầm hố hiện đại, đế bọt EVA siêu nhẹ không tạo cảm giác nặng nề.'
                    ],
                    [
                        'ten' => 'Giày Da Nữ Derby Cột Dây Đế Răng Cưa Phong Cách Cổ Điển',
                        'gia' => 780000, 'km' => 640000,
                        'img' => 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=700',
                        'mo_ta' => 'Da bóng viền chỉ nổi màu kem, phối cùng tất trắng và chân váy ngắn cực xinh.'
                    ],
                    [
                        'ten' => 'Giày Slip-On Nữ Vải Canvas Thêu Họa Tiết Hoa Nhỏ',
                        'gia' => 420000, 'km' => 340000,
                        'img' => 'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?w=700',
                        'mo_ta' => 'Giày lười tiện lợi không cần buộc dây, lót đệm hoạt tính thấm hút mồ hôi chân.'
                    ],
                    [
                        'ten' => 'Giày Thể Thao Cầu Lông Tennis Chuyên Dụng Bọc Mũi',
                        'gia' => 850000, 'km' => 699000,
                        'img' => 'https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=700',
                        'mo_ta' => 'Đế cao su kếp màu vàng tự nhiên không để lại vệt sàn, đệm bảo vệ mắt cá chân.'
                    ]
                ]
            ],

            // ─── 7. TÚI XÁCH & VÍ (20 sản phẩm) ───────────────────────────
            'tui-xach-vi' => [
                'prefix' => 'TUIXACH',
                'items' => [
                    [
                        'ten' => 'Túi Xách Da Nữ Đeo Chéo Khóa Mạ Vàng Cao Cấp SOFEP Atelier',
                        'gia' => 650000, 'km' => 520000,
                        'img' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=700',
                        'mo_ta' => 'Chất da thuộc cao cấp nhập khẩu, khóa mạ vàng 18K tinh xảo không phai màu.'
                    ],
                    [
                        'ten' => 'Balo Da Nam Đựng Laptop 15.6 Inch Chống Nước Doanh Nhân',
                        'gia' => 890000, 'km' => 750000,
                        'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=700',
                        'mo_ta' => 'Ngăn chống sốc bảo vệ laptop tối đa, có cổng sạc USB tích hợp bên hông balo.'
                    ],
                    [
                        'ten' => 'Túi Tote Vải Canvas Dày Dặn Đựng Vừa Hồ Sơ Laptop A4',
                        'gia' => 290000, 'km' => 220000,
                        'img' => 'https://images.unsplash.com/photo-1598532163257-ae3c6b2524b6?w=700',
                        'mo_ta' => 'Vải canvas dệt mật độ cao chịu lực đến 15kg, có ngăn con có khóa kéo an toàn.'
                    ],
                    [
                        'ten' => 'Ví Da Nam Dáng Ngang Da Bò Thật Khắc Tên Thủ Công',
                        'gia' => 450000, 'km' => 360000,
                        'img' => 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=700',
                        'mo_ta' => 'Da sáp ngựa điên bộc lộ vân tự nhiên càng dùng càng bóng đẹp, 8 ngăn thẻ tiện lợi.'
                    ],
                    [
                        'ten' => 'Túi Đeo Chéo Nam Nữ Bao Tử Thể Thao Chống Trộm Siêu Nhẹ',
                        'gia' => 350000, 'km' => 280000,
                        'img' => 'https://images.unsplash.com/photo-1577733966973-d680bffd2e80?w=700',
                        'mo_ta' => 'Khóa kéo ẩn áp sát người chống móc trộm đồ khi đi du lịch hoặc chạy xe máy.'
                    ],
                    [
                        'ten' => 'Túi Xách Nữ Kẹp Nách Da Vân Cá Sấu Thời Thượng Paris',
                        'gia' => 580000, 'km' => 470000,
                        'img' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=700',
                        'mo_ta' => 'Dập vân cá sấu bóng mờ sang chảnh, quai đeo vai vừa vặn dưới cánh tay.'
                    ],
                    [
                        'ten' => 'Balo Du Lịch Đa Năng Cỡ Lớn Chống Thấm 40L Mở Vali',
                        'gia' => 950000, 'km' => 799000,
                        'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=700',
                        'mo_ta' => 'Mở ngang 180 độ như một chiếc vali, có ngăn riêng để giày dép thông gió.'
                    ],
                    [
                        'ten' => 'Ví Cầm Tay Nữ Clutch Dạ Hội Đính Hạt Lấp Lánh Khóa Bạc',
                        'gia' => 720000, 'km' => 590000,
                        'img' => 'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?w=700',
                        'mo_ta' => 'Đính kết pha lê bắt sáng rực rỡ, kèm dây xích đeo chéo tháo rời linh hoạt.'
                    ],
                    [
                        'ten' => 'Túi Đeo Chéo Nữ Dáng Tròn Mini Đựng Son Phấn Xinh Xắn',
                        'gia' => 380000, 'km' => 299000,
                        'img' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=700',
                        'mo_ta' => 'Hình tròn độc đáo viền chỉ may tay sắc sảo, phụ kiện chụp ảnh check-in tuyệt vời.'
                    ],
                    [
                        'ten' => 'Túi Đựng Laptop Chống Sốc Phối Quai Da Doanh Nhân',
                        'gia' => 420000, 'km' => 340000,
                        'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=700',
                        'mo_ta' => 'Đệm chống sốc 360 độ góc cạnh máy tính, lớp nhung chống trầy xước vỏ máy.'
                    ],
                    [
                        'ten' => 'Ví Da Nam Dáng Đứng Nhiều Ngăn Thẻ Tích Hợp Chặn RFID',
                        'gia' => 490000, 'km' => 390000,
                        'img' => 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=700',
                        'mo_ta' => 'Lớp lót chống quét trộm sóng RFID bảo vệ thẻ tín dụng thanh toán không chạm.'
                    ],
                    [
                        'ten' => 'Túi Bucket Da Dây Rút Miệng Thời Trang Phong Cách Pháp',
                        'gia' => 560000, 'km' => 460000,
                        'img' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=700',
                        'mo_ta' => 'Dáng túi xô lòng sâu chứa được nhiều đồ, dây rút tua rua điệu đà.'
                    ],
                    [
                        'ten' => 'Balo Mini Nữ Da PU Nơ Nhỏ Dễ Thương Đi Học & Dạo Phố',
                        'gia' => 450000, 'km' => 360000,
                        'img' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=700',
                        'mo_ta' => 'Nhỏ gọn nhưng chứa trọn ipad mini, son phấn, sạc dự phòng gọn gàng.'
                    ],
                    [
                        'ten' => 'Túi Đeo Hông Chạy Bộ Chống Thấm Mồ Hôi Co Giãn',
                        'gia' => 220000, 'km' => 170000,
                        'img' => 'https://images.unsplash.com/photo-1577733966973-d680bffd2e80?w=700',
                        'mo_ta' => 'Đai thun co giãn ôm khít vòng eo không nảy bật khi chạy, có lỗ luồn tai nghe.'
                    ],
                    [
                        'ten' => 'Túi Xách Nữ Công Sở Đựng Tài Liệu A4 Da Thật Khóa Số',
                        'gia' => 1100000, 'km' => 890000,
                        'img' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=700',
                        'mo_ta' => 'Phom hộp vuông vắn uy quyền cho nữ lãnh đạo, nhiều ngăn phân loại tài liệu khoa học.'
                    ],
                    [
                        'ten' => 'Ví Đựng Thẻ Card Holder Mini Da Bò Thật Mạ Logo SOFEP',
                        'gia' => 280000, 'km' => 210000,
                        'img' => 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=700',
                        'mo_ta' => 'Siêu mỏng chỉ 0.4cm, bỏ túi quần áo không bị cộm, chứa được 6 thẻ và tiền gập.'
                    ],
                    [
                        'ten' => 'Túi Du Lịch Xách Tay Da Trống Tập Gym Thể Thao Cao Cấp',
                        'gia' => 780000, 'km' => 640000,
                        'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=700',
                        'mo_ta' => 'Dáng trống thể thao dung tích 35L, quai xách bọc đệm êm kèm quai đeo chéo vai.'
                    ],
                    [
                        'ten' => 'Túi Xách Nữ Hobo Da Mềm Đeo Vai Phong Cách Tối Giản',
                        'gia' => 610000, 'km' => 499000,
                        'img' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=700',
                        'mo_ta' => 'Chất da mềm rủ tự nhiên hình vầng trăng khuyết, phong cách Parisian Chic thanh thoát.'
                    ],
                    [
                        'ten' => 'Ví Nữ Dáng Dài Hai Khóa Kéo Kèm Dây Đeo Cổ Tay',
                        'gia' => 490000, 'km' => 399000,
                        'img' => 'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?w=700',
                        'mo_ta' => 'Đựng vừa tất cả các dòng điện thoại thông minh cỡ lớn, ngăn đựng tiền phẳng phiu.'
                    ],
                    [
                        'ten' => 'Túi Đeo Chéo Mini Trong Suốt Nhựa PVC Đi Lễ Hội Âm Nhạc',
                        'gia' => 260000, 'km' => 199000,
                        'img' => 'https://images.unsplash.com/photo-1598532163257-ae3c6b2524b6?w=700',
                        'mo_ta' => 'Chất liệu PVC chống nước đạt tiêu chuẩn qua cửa an ninh sự kiện, thời trang Y2K.'
                    ]
                ]
            ],

            // ─── 8. PHỤ KIỆN THỜI TRANG (20 sản phẩm) ─────────────────────
            'phu-kien-thoi-trang' => [
                'prefix' => 'PHUKIEN',
                'items' => [
                    [
                        'ten' => 'Đồng Hồ Nam Dây Da Kính Sapphire Máy Cơ Tự Động SOFEP',
                        'gia' => 1890000, 'km' => 1490000,
                        'img' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=700',
                        'mo_ta' => 'Mặt kính Sapphire chống xước hoàn hảo, lộ cơ mặt lưng quyến rũ, chống nước 5ATM.'
                    ],
                    [
                        'ten' => 'Kính Mát Unisex Phi Công Aviator Tròng Phân Cực Polarized',
                        'gia' => 590000, 'km' => 470000,
                        'img' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=700',
                        'mo_ta' => 'Khung viền hợp kim titan siêu nhẹ không gỉ sét, tròng chống tia UV400 bảo vệ mắt.'
                    ],
                    [
                        'ten' => 'Thắt Lưng Nam Da Bò Thật Khóa Tự Động Mạ Vàng 18K',
                        'gia' => 480000, 'km' => 390000,
                        'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=700',
                        'mo_ta' => 'Khóa bấm ray trượt tự động mượt mà không cần đục lỗ dây, da bò dập vân sang trọng.'
                    ],
                    [
                        'ten' => 'Mũ Nồi Beret Nữ Vải Dạ Thời Trang Phong Cách Cổ Điển',
                        'gia' => 290000, 'km' => 230000,
                        'img' => 'https://images.unsplash.com/photo-1576053139778-7e32f2ae3cfd?w=700',
                        'mo_ta' => 'Chất dạ len mềm giữ phom tròn đầy đặn, tạo điểm nhấn nghệ thuật cho gương mặt.'
                    ],
                    [
                        'ten' => 'Khăn Choàng Cổ Lụa Tơ Tằm Họa Tiết Hội Họa Cổ Điển',
                        'gia' => 550000, 'km' => 440000,
                        'img' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?w=700',
                        'mo_ta' => '100% tơ tằm dệt tay mượt mà mát lạnh mùa hè ấm áp mùa đông, viền cuộn mép thủ công.'
                    ],
                    [
                        'ten' => 'Vòng Tay Bạc Ý 925 Đính Đá Zirconia Sang Trọng Cho Nữ',
                        'gia' => 620000, 'km' => 499000,
                        'img' => 'https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=700',
                        'mo_ta' => 'Bạc Ý 925 sáng bóng phủ rhodium chống xỉn màu, đính đá lấp lánh như kim cương.'
                    ],
                    [
                        'ten' => 'Mũ Lưỡi Trai Unisex Thêu Chữ SOFEP Haute Atelier Vải Cotton',
                        'gia' => 250000, 'km' => 199000,
                        'img' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=700',
                        'mo_ta' => 'Form mũ chuẩn cứng cáp, khóa cài kim loại dập nổi logo phía sau tùy chỉnh vòng đầu.'
                    ],
                    [
                        'ten' => 'Dây Chuyền Nam Titan Mặt Khắc Hình Học Geometric Cá Tính',
                        'gia' => 340000, 'km' => 270000,
                        'img' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=700',
                        'mo_ta' => 'Thép titan không gỉ không đen khi tiếp xúc nước hay mồ hôi, mặt dây sắc nét mạnh mẽ.'
                    ],
                    [
                        'ten' => 'Bông Tai Nữ Ngọc Trai Nước Ngọt Chuôi Bạc Ý Tinh Khôi',
                        'gia' => 420000, 'km' => 340000,
                        'img' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=700',
                        'mo_ta' => 'Ngọc trai nước ngọt ánh ngũ sắc tự nhiên, chuôi xỏ bằng bạc thật an toàn cho tai.'
                    ],
                    [
                        'ten' => 'Thắt Lưng Nữ Bản Nhỏ Khóa Tròn Vàng Phối Váy & Quần Tây',
                        'gia' => 270000, 'km' => 210000,
                        'img' => 'https://images.unsplash.com/photo-1620656798579-1984d9e87dfa?w=700',
                        'mo_ta' => 'Bản dây nhỏ 1.8cm tạo điểm nhấn thon gọn cho vòng eo khi diện cùng đầm suông.'
                    ],
                    [
                        'ten' => 'Kính Râm Gọng Mắt Mèo Nữ Cá Tính Phong Cách Milan',
                        'gia' => 520000, 'km' => 410000,
                        'img' => 'https://images.unsplash.com/photo-1508296695146-257a814070b4?w=700',
                        'mo_ta' => 'Gọng mắt mèo nâng đường nét gương mặt thanh tú, tròng kính gradient chuyển màu êm dịu.'
                    ],
                    [
                        'ten' => 'Mũ Len Beanie Unisex Giữ Ấm Mùa Đông Dệt Rib',
                        'gia' => 220000, 'km' => 170000,
                        'img' => 'https://images.unsplash.com/photo-1576871337632-b9aef4c17ab9?w=700',
                        'mo_ta' => 'Sợi len acrylic mềm xốp giữ nhiệt tốt, viền gập bản rộng tạo vẻ trẻ trung năng động.'
                    ],
                    [
                        'ten' => 'Cà Vạt Nam Lụa Dệt Thoi Kèm Kẹp Cà Vạt Mạ Vàng Cao Cấp',
                        'gia' => 460000, 'km' => 370000,
                        'img' => 'https://images.unsplash.com/photo-1589756823695-278bc923f962?w=700',
                        'mo_ta' => 'Bản 7cm vừa vặn với mọi vóc dáng, chất lụa dệt vân chìm sang trọng kèm hộp quà VIP.'
                    ],
                    [
                        'ten' => 'Vòng Cổ Nữ Choker Ren Quyến Rũ Dự Tiệc Tối',
                        'gia' => 210000, 'km' => 160000,
                        'img' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=700',
                        'mo_ta' => 'Ren dệt hoa văn mềm mại ôm sát cổ, mặt đá giọt nước lấp lánh kiêu kỳ.'
                    ],
                    [
                        'ten' => 'Găng Tay Da Cừu Nam Lót Lông Cảm Ứng Điện Thoại Mùa Đông',
                        'gia' => 480000, 'km' => 380000,
                        'img' => 'https://images.unsplash.com/photo-1520903920243-00d872a2d1c9?w=700',
                        'mo_ta' => 'Da cừu thật mềm mại, đầu ngón tay tích hợp sợi nano cảm ứng mượt mà trên màn hình.'
                    ],
                    [
                        'ten' => 'Khăn Bandana Lụa Vuông Cột Cổ & Quai Túi Thời Trang',
                        'gia' => 190000, 'km' => 140000,
                        'img' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?w=700',
                        'mo_ta' => 'Kích thước 70x70cm, đa năng làm khăn quàng cổ, băng đô tóc hoặc thắt nơ quai túi xách.'
                    ],
                    [
                        'ten' => 'Hộp 5 Đôi Vớ Tất Cổ Cao Nam Nữ Dệt Kim Kháng Khuẩn Bạc',
                        'gia' => 199000, 'km' => 159000,
                        'img' => 'https://images.unsplash.com/photo-1582966772680-860e372bb558?w=700',
                        'mo_ta' => 'Công nghệ phân tử ion bạc khử mùi hôi chân 24/7, cổ chun dệt rib không thít chặt.'
                    ],
                    [
                        'ten' => 'Kẹp Tóc Nữ Đính Đá Càng Cua Phong Cách Hàn Quốc Cao Cấp',
                        'gia' => 160000, 'km' => 120000,
                        'img' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=700',
                        'mo_ta' => 'Lò xo thép lực giữ tóc chắc chắn không gãy, đính ngọc trai nhân tạo sáng lấp lánh.'
                    ],
                    [
                        'ten' => 'Ghim Cài Áo Vest Nam Nữ Kim Loại Mạ Vàng Khảm Xà Cừ',
                        'gia' => 310000, 'km' => 240000,
                        'img' => 'https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=700',
                        'mo_ta' => 'Phụ kiện điểm nhấn hoàn hảo trên ve áo vest blazer hoặc áo khoác dạ tiệc tối.'
                    ],
                    [
                        'ten' => 'Ô Dù Cầm Tay Mini Gấp Gọn Chống Tia Cực Tím UV99% Lớp Đen',
                        'gia' => 280000, 'km' => 220000,
                        'img' => 'https://images.unsplash.com/photo-1534353436294-0dbd4bdac845?w=700',
                        'mo_ta' => 'Khung nan sợi thủy tinh chống lật khi gió to, phủ lớp keo vinyl đen cản 99% tia UV.'
                    ]
                ]
            ],
        ];

        $totalCreated = 0;

        foreach ($allCategoriesData as $slugCategory => $catGroup) {
            if (!$danh_mucs->has($slugCategory)) {
                $this->command->warn("Không tìm thấy danh mục: {$slugCategory}");
                continue;
            }

            $dm = $danh_mucs->get($slugCategory);
            $prefix = $catGroup['prefix'];
            $items = $catGroup['items'];

            $this->command->info("Đang tạo 20 sản phẩm cho danh mục: {$dm->ten_danh_muc} (slug: {$slugCategory})...");

            foreach ($items as $idx => $item) {
                $sku = 'SFP-' . $prefix . '-' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                $slug = Str::slug($item['ten']) . '-' . Str::lower(Str::random(4));
                $brandId = !empty($thuong_hieus) ? $thuong_hieus[array_rand($thuong_hieus)] : 1;

                $sp = SanPham::create([
                    'id_danh_muc'    => $dm->id,
                    'id_thuong_hieu' => $brandId,
                    'ma_san_pham'    => $sku,
                    'ten_san_pham'   => $item['ten'],
                    'slug_san_pham'  => $slug,
                    'gia_ban'        => $item['gia'],
                    'gia_khuyen_mai' => $item['km'],
                    'hinh_anh'       => $item['img'],
                    'mo_ta_ngan'     => $item['mo_ta'],
                    'mo_ta_chi_tiet' => $item['mo_ta'] . ' Thiết kế độc quyền thuộc bộ sưu tập SOFEP Haute Atelier 2026. Được may đo và gia công tỉ mỉ với độ bền vượt trội, dễ dàng bảo quản và giặt ủi.',
                    'so_luong_ton'   => rand(45, 180),
                    'is_noi_bat'     => ($idx % 3 === 0) ? 1 : 0,
                    'is_flash_sale'  => ($idx % 4 === 0) ? 1 : 0,
                    'luot_xem'       => rand(180, 2900),
                    'trang_thai'     => 1,
                ]);

                // 2 biến thể màu sắc & kích thước
                if (!empty($mau_sacs) && !empty($kich_thuocs)) {
                    $selectedColors = array_slice($mau_sacs, 0, min(3, count($mau_sacs)));
                    foreach ($selectedColors as $cId) {
                        ChiTietSanPham::create([
                            'id_san_pham'    => $sp->id,
                            'id_mau_sac'     => $cId,
                            'id_kich_thuoc'  => $kich_thuocs[array_rand($kich_thuocs)],
                            'so_luong_ton'   => rand(20, 50),
                            'gia_chenh_lech' => 0,
                            'trang_thai'     => 1,
                        ]);
                    }
                }

                // Gallery ảnh sản phẩm (2 ảnh)
                AnhSanPham::create([
                    'id_san_pham'   => $sp->id,
                    'duong_dan_anh' => $sp->hinh_anh,
                    'thu_tu'        => 1
                ]);

                AnhSanPham::create([
                    'id_san_pham'   => $sp->id,
                    'duong_dan_anh' => $sp->hinh_anh,
                    'thu_tu'        => 2
                ]);

                $totalCreated++;
            }
        }

        $this->command->info("🎉 HOÀN TẤT: Đã tạo thành công {$totalCreated} sản phẩm (20 sản phẩm cho mỗi danh mục)!");
    }
}
