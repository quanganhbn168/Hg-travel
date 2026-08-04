<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        Promotion::updateOrCreate(['name' => 'Ưu đãi mùa khởi hành'], [
            'discount_type' => 'percentage',
            'discount_value' => 8,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonths(2),
            'usage_limit' => 100,
            'is_active' => true,
        ]);

        Promotion::updateOrCreate(['name' => 'Đặt sớm nhận ưu đãi'], [
            'discount_type' => 'fixed',
            'discount_value' => 1000000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonths(3),
            'usage_limit' => 80,
            'is_active' => true,
        ]);
    }
}
