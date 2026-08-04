<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        Menu::updateOrCreate(['location' => 'header'], ['name' => 'Menu chính', 'is_active' => true]);
        Menu::updateOrCreate(['location' => 'footer'], ['name' => 'Menu chân trang', 'is_active' => true]);
    }
}
