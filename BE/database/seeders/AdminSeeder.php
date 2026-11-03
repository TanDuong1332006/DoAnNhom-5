<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@shop.com'],
            [
                'name'     => 'Admin Shop',
                'password' => bcrypt('Admin@123'),
            ]
        );
    }
}
