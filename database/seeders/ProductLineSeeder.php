<?php

namespace Database\Seeders;

use App\Models\ProductLine;
use App\Models\Service;
use App\Models\Tour;
use Illuminate\Database\Seeder;

class ProductLineSeeder extends Seeder
{
    public function run(): void
    {
        $lines = [
            [
                'slug' => 'du-lich-doanh-nghiep-su-kien',
                'name' => 'Du lịch doanh nghiệp & sự kiện',
                'kicker' => 'MICE · Hội nghị · Sự kiện',
                'icon' => 'bi-buildings',
                'summary' => 'Kết nối hành trình với hội nghị, hội thảo, khảo sát thị trường và những dấu mốc quan trọng của doanh nghiệp.',
                'description' => 'HG phối hợp mục tiêu tổ chức, hành trình, lưu trú, di chuyển và các hoạt động tại điểm đến thành một chương trình rõ đầu mối, phù hợp với quy mô và yêu cầu thực tế của doanh nghiệp.',
                'benefits' => ['Một kế hoạch thống nhất cho toàn bộ chương trình', 'Điều phối lịch trình, lưu trú, di chuyển và sự kiện', 'Tư vấn theo mục tiêu, quy mô và ngân sách của doanh nghiệp'],
                'tours' => ['da-nang-hoi-an-4-ngay', 'ha-long-du-thuyen-3-ngay', 'singapore-tron-trai-nghiem-4-ngay'],
                'services' => ['to-chuc-su-kien', 'van-chuyen', 'huong-dan-vien', 'dat-phong-khach-san'],
                'sort_order' => 1,
            ],
            [
                'slug' => 'du-lich-khen-thuong-gan-ket',
                'name' => 'Du lịch khen thưởng & gắn kết',
                'kicker' => 'Incentive · Team building · Gala',
                'icon' => 'bi-people',
                'summary' => 'Tạo động lực và tăng kết nối bằng chương trình nghỉ dưỡng, trải nghiệm địa phương và hoạt động tập thể.',
                'description' => 'Mỗi chương trình khen thưởng và gắn kết được xây dựng quanh mục tiêu của doanh nghiệp, từ lựa chọn điểm đến đến hoạt động tập thể, gala và trải nghiệm riêng cho đoàn.',
                'benefits' => ['Thiết kế chương trình theo văn hóa và mục tiêu đoàn', 'Kết hợp nghỉ dưỡng, trải nghiệm và hoạt động tập thể', 'Đồng hành điều phối xuyên suốt trước và trong chuyến đi'],
                'tours' => ['da-nang-hoi-an-4-ngay', 'phu-quoc-nghi-duong-4-ngay', 'ha-long-du-thuyen-3-ngay'],
                'services' => ['to-chuc-su-kien', 'van-chuyen', 'huong-dan-vien', 'dich-vu-san-bay'],
                'sort_order' => 2,
            ],
            [
                'slug' => 'du-lich-cong-tac',
                'name' => 'Du lịch công tác',
                'kicker' => 'Business travel · Airport service',
                'icon' => 'bi-briefcase',
                'summary' => 'Tối ưu chuyến bay, lưu trú, đưa đón và hỗ trợ tại điểm đến để khách hàng tập trung trọn vẹn cho công việc.',
                'description' => 'HG sắp xếp các hạng mục cần thiết cho chuyến công tác theo lịch làm việc, điểm đến và yêu cầu của từng cá nhân hoặc doanh nghiệp, với một đầu mối dễ kiểm soát.',
                'benefits' => ['Lịch trình bám sát mục tiêu công việc', 'Kết nối vé máy bay, khách sạn và đưa đón', 'Hỗ trợ tại điểm đến khi lịch trình thay đổi'],
                'tours' => ['tokyo-fuji-kyoto-6-ngay', 'singapore-tron-trai-nghiem-4-ngay', 'seoul-nami-everland-5-ngay'],
                'services' => ['ve-may-bay', 'dat-phong-khach-san', 'van-chuyen', 'dich-vu-san-bay'],
                'sort_order' => 3,
            ],
            [
                'slug' => 'nghi-duong-cao-cap',
                'name' => 'Nghỉ dưỡng cao cấp',
                'kicker' => 'Private journey · Premium stay',
                'icon' => 'bi-gem',
                'summary' => 'Những kỳ nghỉ được tuyển chọn kỹ về không gian lưu trú, chất lượng dịch vụ và chiều sâu trải nghiệm.',
                'description' => 'HG tuyển chọn và kết nối những lựa chọn nghỉ dưỡng phù hợp với nhịp điệu, ngân sách và tiêu chuẩn trải nghiệm riêng của từng khách hàng, gia đình hoặc nhóm nhỏ.',
                'benefits' => ['Lựa chọn lưu trú và trải nghiệm có chủ đích', 'Lịch trình linh hoạt theo nhịp nghỉ ngơi', 'Kết hợp xe riêng, sân bay và hỗ trợ tại điểm đến'],
                'tours' => ['phu-quoc-nghi-duong-4-ngay', 'bali-nghi-duong-5-ngay', 'ha-long-du-thuyen-3-ngay'],
                'services' => ['tour-thiet-ke-rieng', 'dat-phong-khach-san', 'van-chuyen', 'dich-vu-san-bay'],
                'sort_order' => 4,
            ],
        ];

        foreach ($lines as $line) {
            $productLine = ProductLine::updateOrCreate(['slug' => $line['slug']], [
                'name' => $line['name'],
                'kicker' => $line['kicker'],
                'summary' => $line['summary'],
                'description' => $line['description'],
                'icon' => $line['icon'],
                'benefits' => $line['benefits'],
                'sort_order' => $line['sort_order'],
                'is_active' => true,
                'is_home' => true,
            ]);

            $tourIds = collect($line['tours'])
                ->map(fn (string $slug) => Tour::where('slug', $slug)->value('id'))
                ->filter()
                ->values()
                ->mapWithKeys(fn ($id, int $index): array => [$id => ['sort_order' => $index + 1]])
                ->all();
            $productLine->tours()->sync($tourIds);

            $serviceIds = collect($line['services'])
                ->map(fn (string $slug) => Service::where('slug', $slug)->value('id'))
                ->filter()
                ->values()
                ->mapWithKeys(fn ($id, int $index): array => [$id => ['sort_order' => $index + 1]])
                ->all();
            $productLine->services()->sync($serviceIds);
        }
    }
}
