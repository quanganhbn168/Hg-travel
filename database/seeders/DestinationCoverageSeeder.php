<?php

namespace Database\Seeders;

use App\Services\DestinationCoverageService;
use Illuminate\Database\Seeder;

class DestinationCoverageSeeder extends Seeder
{
    public function run(): void
    {
        app(DestinationCoverageService::class)->syncAll();
    }
}
