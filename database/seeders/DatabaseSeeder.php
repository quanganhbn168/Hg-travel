<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            SiteAssetSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            DestinationSeeder::class,
            TravelMomentSeeder::class,
            TourCategorySeeder::class,
            ServiceCatalogSeeder::class,
            PromotionSeeder::class,
            PromotionTourSeeder::class,
            PostCategorySeeder::class,
            PostSeeder::class,
            TestimonialSeeder::class,
            SliderSeeder::class,
            SliderItemSeeder::class,
            MenuSeeder::class,
            MenuItemSeeder::class,
        ]);
    }
}
