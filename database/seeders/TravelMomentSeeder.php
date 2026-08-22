<?php

namespace Database\Seeders;

use App\Models\TravelMoment;
use App\Models\TravelMomentGroup;
use Illuminate\Database\Seeder;

class TravelMomentSeeder extends Seeder
{
    public function run(): void
    {
        $groups = collect([
            ['name' => 'Biển & nghỉ dưỡng', 'slug' => 'bien-va-nghi-duong', 'description' => 'Những khoảnh khắc thư thái bên biển và các kỳ nghỉ đáng nhớ.', 'sort_order' => 1],
            ['name' => 'Văn hóa & khám phá', 'slug' => 'van-hoa-va-kham-pha', 'description' => 'Đi sâu vào bản sắc địa phương, thiên nhiên và những miền đất mới.', 'sort_order' => 2],
            ['name' => 'Team Building & MICE', 'slug' => 'team-building-va-mice', 'description' => 'Khoảnh khắc kết nối dành cho tập thể, doanh nghiệp và sự kiện.', 'sort_order' => 3],
        ])->mapWithKeys(function (array $group): array {
            $model = TravelMomentGroup::updateOrCreate(['slug' => $group['slug']], $group + ['is_active' => true]);

            return [$group['slug'] => $model];
        });

        foreach ([
            ['group' => 'bien-va-nghi-duong', 'title' => 'Khách hàng tận hưởng biển xanh', 'slug' => 'khach-hang-tan-huong-bien-xanh', 'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85', 'sort_order' => 1],
            ['group' => 'bien-va-nghi-duong', 'title' => 'Khoảnh khắc đáng nhớ của khách hàng', 'slug' => 'khoanh-khac-dang-nho-cua-khach-hang', 'image_url' => 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=900&q=85', 'sort_order' => 2],
            ['group' => 'van-hoa-va-kham-pha', 'title' => 'Khách hàng khám phá núi rừng', 'slug' => 'khach-hang-kham-pha-nui-rung', 'image_url' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=900&q=85', 'sort_order' => 1],
            ['group' => 'van-hoa-va-kham-pha', 'title' => 'Khách hàng trải nghiệm phố cổ', 'slug' => 'khach-hang-trai-nghiem-pho-co', 'image_url' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=900&q=85', 'sort_order' => 2],
            ['group' => 'team-building-va-mice', 'title' => 'Khách hàng trên hành trình khám phá', 'slug' => 'khach-hang-tren-hanh-trinh-kham-pha', 'image_url' => 'https://images.unsplash.com/photo-1526772662000-3f88f10405ff?auto=format&fit=crop&w=900&q=85', 'sort_order' => 1],
            ['group' => 'team-building-va-mice', 'title' => 'Khách hàng tại điểm đến châu Âu', 'slug' => 'khach-hang-tai-diem-den-chau-au', 'image_url' => 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=900&q=85', 'sort_order' => 2],
        ] as $moment) {
            TravelMoment::updateOrCreate(['slug' => $moment['slug']], [
                'group_id' => $groups[$moment['group']]->id,
                'title' => $moment['title'],
                'image_url' => $moment['image_url'],
                'alt_text' => $moment['title'],
                'sort_order' => $moment['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
