<?php

use App\Models\Service;
use App\Services\AboutPageService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_pages', function (Blueprint $table): void {
            $table->json('profile_content')->nullable()->after('markets_intro');
        });

        $supportServiceIds = Service::query()
            ->whereIn('name', ['Visa các nước', 'Dịch vụ vé máy bay', 'Đặt phòng khách sạn', 'Vận chuyển', 'Dịch vụ sân bay', 'Hướng dẫn viên', 'Tổ chức sự kiện'])
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::table('about_pages')
            ->whereNull('profile_content')
            ->update(['profile_content' => json_encode(AboutPageService::defaultProfileContent($supportServiceIds), JSON_UNESCAPED_UNICODE)]);
    }

    public function down(): void
    {
        Schema::table('about_pages', function (Blueprint $table): void {
            $table->dropColumn('profile_content');
        });
    }
};
