<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\Tour;
use Illuminate\Database\Seeder;

class PromotionTourSeeder extends Seeder
{
    public function run(): void
    {
        $seasonal = Promotion::where('name', 'Ưu đãi mùa khởi hành')->firstOrFail();
        $seasonal->tours()->sync(Tour::where('is_featured', true)->pluck('id')->all());

        $earlyBird = Promotion::where('name', 'Đặt sớm nhận ưu đãi')->firstOrFail();
        $earlyBird->tours()->sync(Tour::whereIn('slug', [
            'jeju-xanh-mat-4-ngay',
            'singapore-tron-trai-nghiem-4-ngay',
        ])->pluck('id')->all());
    }
}
