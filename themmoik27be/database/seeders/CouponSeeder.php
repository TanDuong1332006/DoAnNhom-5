<?php
namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code'      => 'SALE10',
                'type'      => 'percent',
                'value'     => 10,
                'min_order' => 200000,
                'max_uses'  => 100,
                'expires_at'=> '2027-12-31',
            ],
            [
                'code'      => 'SALE20',
                'type'      => 'percent',
                'value'     => 20,
                'min_order' => 500000,
                'max_uses'  => 50,
                'expires_at'=> '2027-12-31',
            ],
            [
                'code'      => 'FREESHIP',
                'type'      => 'fixed',
                'value'     => 30000,
                'min_order' => 100000,
                'max_uses'  => 200,
                'expires_at'=> '2027-12-31',
            ],
            [
                'code'      => 'GIAM50K',
                'type'      => 'fixed',
                'value'     => 50000,
                'min_order' => 300000,
                'max_uses'  => null,
                'expires_at'=> '2027-06-30',
            ],
        ];

        foreach ($coupons as $c) {
            Coupon::create(array_merge($c, ['is_active' => 1]));
        }
    }
}
