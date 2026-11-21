<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

/** Demo coupons. firstOrCreate keeps Admin edits and usage counts on re-run. */
class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::firstOrCreate(['code' => 'OPENSHOP10'], [
            'description' => 'Giảm 10% cho mọi đơn, tối đa 500.000₫ (demo)',
            'type' => 'percent', 'value' => 10, 'max_discount' => 500000,
            'min_order_amount' => 0, 'is_active' => true,
        ]);

        Coupon::firstOrCreate(['code' => 'GIAM200K'], [
            'description' => 'Giảm 200.000₫ cho đơn từ 2.000.000₫, 100 lượt (demo)',
            'type' => 'fixed', 'value' => 200000, 'min_order_amount' => 2000000,
            'usage_limit' => 100, 'is_active' => true,
        ]);
    }
}
