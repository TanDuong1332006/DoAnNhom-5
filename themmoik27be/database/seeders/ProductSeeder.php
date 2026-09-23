<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('name');

        $products = [
            // Áo Nam
            [
                'category' => 'Áo Nam',
                'name' => 'Áo Polo Nam Classic',
                'description' => 'Áo polo nam chất liệu cotton cao cấp, thoáng mát, phù hợp đi làm và dạo phố.',
                'price' => 350000, 'sale_price' => 280000, 'quantity' => 50,
                'image' => 'https://images.unsplash.com/photo-1593030761757-71fae45fa0e7?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Áo Nam',
                'name' => 'Áo Thun Nam Basic',
                'description' => 'Áo thun nam cổ tròn basic, nhiều màu sắc, chất liệu cotton 100%.',
                'price' => 199000, 'sale_price' => null, 'quantity' => 100,
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Áo Nam',
                'name' => 'Áo Sơ Mi Nam Sọc',
                'description' => 'Áo sơ mi nam kẻ sọc form slim fit, thanh lịch và năng động.',
                'price' => 450000, 'sale_price' => 380000, 'quantity' => 30,
                'image' => 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=500',
                'is_featured' => 0,
            ],
            // Áo Nữ
            [
                'category' => 'Áo Nữ',
                'name' => 'Áo Blouse Nữ Hoa',
                'description' => 'Áo blouse nữ họa tiết hoa, chất liệu voan nhẹ, phong cách nhẹ nhàng.',
                'price' => 320000, 'sale_price' => 259000, 'quantity' => 45,
                'image' => 'https://images.unsplash.com/photo-1485230895905-ec40ba36b9bc?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Áo Nữ',
                'name' => 'Áo Thun Nữ Crop Top',
                'description' => 'Áo thun nữ crop top form ngắn, năng động và cá tính.',
                'price' => 150000, 'sale_price' => null, 'quantity' => 80,
                'image' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Áo Nữ',
                'name' => 'Áo Dài Cách Tân',
                'description' => 'Áo dài cách tân hiện đại, phù hợp các buổi tiệc và sự kiện đặc biệt.',
                'price' => 650000, 'sale_price' => 520000, 'quantity' => 20,
                'image' => 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=500',
                'is_featured' => 0,
            ],
            // Quần Nam
            [
                'category' => 'Quần Nam',
                'name' => 'Quần Jeans Nam Slim Fit',
                'description' => 'Quần jeans nam dáng slim fit, co giãn tốt, thoải mái khi vận động.',
                'price' => 550000, 'sale_price' => 449000, 'quantity' => 40,
                'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Quần Nam',
                'name' => 'Quần Kaki Nam',
                'description' => 'Quần kaki nam form straight, lịch sự phù hợp đi làm và đi chơi.',
                'price' => 420000, 'sale_price' => null, 'quantity' => 55,
                'image' => 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=500',
                'is_featured' => 0,
            ],
            // Quần Nữ
            [
                'category' => 'Quần Nữ',
                'name' => 'Quần Legging Nữ',
                'description' => 'Quần legging nữ co giãn 4 chiều, thoải mái cho mọi hoạt động.',
                'price' => 180000, 'sale_price' => 149000, 'quantity' => 90,
                'image' => 'https://images.unsplash.com/photo-1617196034183-421b4040ed20?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Quần Nữ',
                'name' => 'Chân Váy Denim',
                'description' => 'Chân váy denim midi phong cách retro, kết hợp đa dạng với nhiều loại áo.',
                'price' => 380000, 'sale_price' => 299000, 'quantity' => 35,
                'image' => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=500',
                'is_featured' => 0,
            ],
            // Giày Nam
            [
                'category' => 'Giày Nam',
                'name' => 'Giày Sneaker Nam',
                'description' => 'Giày sneaker nam trắng basic, phù hợp nhiều outfit khác nhau.',
                'price' => 850000, 'sale_price' => 699000, 'quantity' => 25,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Giày Nam',
                'name' => 'Giày Da Nam Oxford',
                'description' => 'Giày da nam kiểu Oxford sang trọng, phù hợp công sở và sự kiện.',
                'price' => 1250000, 'sale_price' => null, 'quantity' => 15,
                'image' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=500',
                'is_featured' => 0,
            ],
            // Giày Nữ
            [
                'category' => 'Giày Nữ',
                'name' => 'Giày Cao Gót Nữ',
                'description' => 'Giày cao gót nữ 7cm thanh lịch, phù hợp tiệc tùng và đi làm.',
                'price' => 750000, 'sale_price' => 599000, 'quantity' => 20,
                'image' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Giày Nữ',
                'name' => 'Sandal Nữ Đế Bằng',
                'description' => 'Sandal nữ đế bằng thoải mái, thiết kế đơn giản dễ phối đồ.',
                'price' => 290000, 'sale_price' => 240000, 'quantity' => 40,
                'image' => 'https://images.unsplash.com/photo-1603487742131-4160ec999306?w=500',
                'is_featured' => 0,
            ],
            // Phụ Kiện
            [
                'category' => 'Phụ Kiện',
                'name' => 'Đồng Hồ Nam Dây Da',
                'description' => 'Đồng hồ nam dây da sang trọng, mặt tròn cổ điển, chống nước 30m.',
                'price' => 1500000, 'sale_price' => 1199000, 'quantity' => 12,
                'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Phụ Kiện',
                'name' => 'Kính Mắt Unisex',
                'description' => 'Kính mắt unisex gọng kim loại, chống tia UV, thời trang.',
                'price' => 450000, 'sale_price' => 380000, 'quantity' => 30,
                'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=500',
                'is_featured' => 0,
            ],
            // Túi Xách
            [
                'category' => 'Túi Xách',
                'name' => 'Túi Tote Nữ Vải Canvas',
                'description' => 'Túi tote nữ vải canvas chắc chắn, đựng được nhiều đồ, thiết kế tối giản.',
                'price' => 280000, 'sale_price' => 220000, 'quantity' => 50,
                'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500',
                'is_featured' => 1,
            ],
            [
                'category' => 'Túi Xách',
                'name' => 'Balo Du Lịch Unisex',
                'description' => 'Balo du lịch 30L chống thấm nước, nhiều ngăn tiện lợi, thích hợp đi phượt.',
                'price' => 890000, 'sale_price' => 720000, 'quantity' => 18,
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500',
                'is_featured' => 1,
            ],
        ];

        foreach ($products as $p) {
            $category = $categories->get($p['category']);
            if (!$category) continue;

            Product::create([
                'category_id'  => $category->id,
                'name'         => $p['name'],
                'slug'         => Str::slug($p['name']) . '-' . Str::random(6),
                'description'  => $p['description'],
                'content'      => '<p>' . $p['description'] . '</p><p>Sản phẩm chính hãng, được kiểm tra chất lượng kỹ lưỡng trước khi giao đến tay khách hàng.</p>',
                'price'        => $p['price'],
                'sale_price'   => $p['sale_price'],
                'quantity'     => $p['quantity'],
                'image'        => $p['image'],
                'is_featured'  => $p['is_featured'],
                'is_active'    => 1,
                'views'        => rand(10, 500),
            ]);
        }
    }
}
