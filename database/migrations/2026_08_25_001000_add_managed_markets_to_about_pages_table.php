<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_pages', function (Blueprint $table): void {
            $table->string('markets_eyebrow')->nullable()->after('mission');
            $table->text('markets_title')->nullable()->after('markets_eyebrow');
            $table->text('markets_intro')->nullable()->after('markets_title');
        });

        DB::table('about_pages')->whereNull('markets_eyebrow')->update(['markets_eyebrow' => 'CÁC THỊ TRƯỜNG HOẠT ĐỘNG']);
        DB::table('about_pages')->whereNull('markets_title')->update(['markets_title' => "Kết nối những hành trình\nkhông giới hạn biên giới."]);
        DB::table('about_pages')->whereNull('markets_intro')->update(['markets_intro' => 'Từ một chuyến đi trong nước đến những thị trường quốc tế, HG TRIP chuẩn bị đồng bộ trải nghiệm và vận hành.']);

        $legacyDefault = ['Nội địa', 'Inbound', 'Outbound', 'Middle East'];
        $currentCards = [
            ['name' => 'Nội địa', 'detail' => 'Khám phá Việt Nam theo nhịp điệu riêng'],
            ['name' => 'Inbound', 'detail' => 'Đón khách quốc tế đến Việt Nam'],
            ['name' => 'Outbound', 'detail' => 'Hành trình quốc tế được thiết kế riêng'],
            ['name' => 'Dịch vụ khác', 'detail' => 'Visa, vé máy bay, khách sạn và các hỗ trợ cần thiết cho chuyến đi'],
        ];
        $legacyDetails = collect($currentCards)->mapWithKeys(fn (array $market) => [$market['name'] => $market['detail']])->all();

        DB::table('about_pages')->select(['id', 'markets'])->orderBy('id')->each(function (object $page) use ($legacyDefault, $currentCards, $legacyDetails): void {
            $markets = json_decode((string) $page->markets, true);

            if (! is_array($markets) || $markets === []) {
                return;
            }

            $normalized = array_values($markets) === $legacyDefault
                ? $currentCards
                : collect($markets)->map(function ($market) use ($legacyDetails): array {
                    $name = trim((string) (is_array($market) ? ($market['name'] ?? '') : $market));
                    $detail = trim((string) (is_array($market) ? ($market['detail'] ?? '') : ($legacyDetails[$name] ?? '')));

                    return compact('name', 'detail');
                })->filter(fn (array $market) => $market['name'] !== '')->values()->all();

            DB::table('about_pages')->where('id', $page->id)->update([
                'markets' => json_encode($normalized, JSON_UNESCAPED_UNICODE),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('about_pages', function (Blueprint $table): void {
            $table->dropColumn(['markets_eyebrow', 'markets_title', 'markets_intro']);
        });
    }
};
