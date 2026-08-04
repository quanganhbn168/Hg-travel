<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        Slider::updateOrCreate(['key' => 'home'], [
            'name' => 'Slider trang chủ',
            'is_active' => true,
        ]);
    }
}
