<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Áo Nam', 'image' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=400'],
            ['name' => 'Áo Nữ', 'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=400'],
            ['name' => 'Quần Nam', 'image' => 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=400'],
            ['name' => 'Quần Nữ', 'image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=400'],
            ['name' => 'Giày Nam', 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400'],
            ['name' => 'Giày Nữ', 'image' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=400'],
            ['name' => 'Phụ Kiện', 'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?w=400'],
            ['name' => 'Túi Xách', 'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name'      => $cat['name'],
                'slug'      => Str::slug($cat['name']) . '-' . Str::random(4),
                'image'     => $cat['image'],
                'is_active' => 1,
            ]);
        }
    }
}
