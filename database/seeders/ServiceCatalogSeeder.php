<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            [
                'slug' => 'thiet-ke-hanh-trinh',
                'name' => 'Thiết kế hành trình',
                'kicker' => '01 · Từ ý tưởng đến lịch trình',
                'icon' => 'bi-map',
                'description' => 'Những hạng mục nền tảng để bắt đầu một chuyến đi vừa vặn với thời gian, ngân sách và mục tiêu của bạn.',
                'sort_order' => 1,
            ],
            [
                'slug' => 'tai-diem-den',
                'name' => 'Tại điểm đến',
                'kicker' => '02 · Yên tâm trong từng chặng',
                'icon' => 'bi-signpost-2',
                'description' => 'Đội ngũ và đối tác đồng hành tại điểm đến, giúp việc di chuyển, thủ tục và trải nghiệm diễn ra trọn vẹn.',
                'sort_order' => 2,
            ],
            [
                'slug' => 'doanh-nghiep-su-kien',
                'name' => 'Doanh nghiệp & sự kiện',
                'kicker' => '03 · Đi cùng mục tiêu tổ chức',
                'icon' => 'bi-buildings',
                'description' => 'Kết nối du lịch, hội nghị và hoạt động gắn kết trong một kế hoạch thống nhất cho doanh nghiệp.',
                'sort_order' => 3,
            ],
        ]);

        $categoryModels = $categories->mapWithKeys(function (array $category): array {
            $model = ServiceCategory::updateOrCreate(['slug' => $category['slug']], $category + ['is_active' => true]);

            return [$model->slug => $model];
        });

        foreach ([
            ['slug' => 'tour-thiet-ke-rieng', 'category' => 'thiet-ke-hanh-trinh', 'icon' => 'bi-compass', 'name' => 'Tour du lịch thiết kế riêng', 'description' => 'Thiết kế hành trình theo mục đích, ngân sách và nhịp điệu riêng của từng khách hàng.', 'intro' => 'Từ một chuyến đi gia đình, nghỉ dưỡng đến hành trình khám phá nhiều quốc gia, HG cùng bạn xây dựng phương án riêng tại châu Á, châu Âu, châu Úc, châu Mỹ và châu Phi.', 'benefits' => ['Điều chỉnh theo số ngày và số người', 'Kết hợp tour, vé máy bay, khách sạn và visa', 'Lên phương án theo ngân sách dự kiến']],
            ['slug' => 'visa-cac-nuoc', 'category' => 'thiet-ke-hanh-trinh', 'icon' => 'bi-passport', 'name' => 'Visa các nước', 'description' => 'Tư vấn hồ sơ visa theo điểm đến và mục đích chuyến đi.', 'intro' => 'HG hỗ trợ tư vấn visa cho các hành trình tại châu Á, châu Âu, châu Úc, châu Mỹ và châu Phi; hồ sơ được rà soát theo yêu cầu thực tế của từng điểm đến.', 'benefits' => ['Rà soát giấy tờ trước khi nộp', 'Hướng dẫn biểu mẫu và các mốc chuẩn bị', 'Theo dõi tiến độ hồ sơ']],
            ['slug' => 've-may-bay', 'category' => 'thiet-ke-hanh-trinh', 'icon' => 'bi-airplane-engines', 'name' => 'Dịch vụ vé máy bay', 'description' => 'Tìm chuyến bay phù hợp với hành trình, giờ bay và ngân sách của bạn.', 'intro' => 'HG tư vấn các phương án vé máy bay nội địa và quốc tế để kết nối lịch trình tour trọn gói, tour riêng hoặc chuyến đi tự túc.', 'benefits' => ['So sánh giờ bay, hành lý và điều kiện vé', 'Gợi ý chặng bay phù hợp với lịch trình', 'Kết nối cùng dịch vụ tour và khách sạn']],
            ['slug' => 'dat-phong-khach-san', 'category' => 'thiet-ke-hanh-trinh', 'icon' => 'bi-buildings', 'name' => 'Đặt phòng khách sạn', 'description' => 'Gợi ý lưu trú phù hợp với lịch trình, ngân sách và phong cách nghỉ ngơi của bạn.', 'intro' => 'HG hỗ trợ lựa chọn khách sạn tại các điểm đến thuộc châu Á, châu Âu, châu Úc, châu Mỹ và châu Phi, đồng bộ cùng toàn bộ hành trình.', 'benefits' => ['Tư vấn vị trí phù hợp với lịch trình', 'Đối chiếu hạng phòng và tiện ích', 'Kết hợp cùng tour, vé máy bay hoặc visa']],
            ['slug' => 'van-chuyen', 'category' => 'tai-diem-den', 'icon' => 'bi-car-front', 'name' => 'Vận chuyển', 'description' => 'Sắp xếp phương tiện đồng bộ với lịch trình, quy mô đoàn và yêu cầu tại điểm đến.', 'intro' => 'HG kết nối xe đưa đón sân bay, xe phục vụ đoàn riêng và các phương án vận chuyển chuyên biệt để hành trình luôn thuận tiện, an toàn và đúng thời gian.', 'benefits' => ['Phương tiện phù hợp quy mô đoàn', 'Lịch đón tiễn bám sát hành trình', 'Điều phối đầu mối xuyên suốt chuyến đi']],
            ['slug' => 'dich-vu-san-bay', 'category' => 'tai-diem-den', 'icon' => 'bi-airplane', 'name' => 'Dịch vụ sân bay', 'description' => 'Đón tiễn, hỗ trợ thủ tục, phòng chờ và dịch vụ ưu tiên theo nhu cầu.', 'intro' => 'Các dịch vụ tại sân bay được lựa chọn theo từng hành trình, giúp khách hàng tiết kiệm thời gian và chủ động hơn tại điểm khởi hành cũng như điểm đến.', 'benefits' => ['Đón tiễn và hỗ trợ thủ tục', 'Tư vấn phòng chờ và dịch vụ ưu tiên', 'Kết nối cùng chuyến bay và phương tiện đưa đón']],
            ['slug' => 'huong-dan-vien', 'category' => 'tai-diem-den', 'icon' => 'bi-person-badge', 'name' => 'Hướng dẫn viên', 'description' => 'Kết nối hướng dẫn viên chuyên nghiệp, am hiểu điểm đến, văn hóa và lịch sử địa phương.', 'intro' => 'HG bố trí hướng dẫn viên theo thị trường, ngôn ngữ và đặc điểm đoàn để mỗi trải nghiệm tại điểm đến rõ ràng, gần gũi và giàu thông tin hơn.', 'benefits' => ['Phù hợp thị trường và ngôn ngữ', 'Am hiểu văn hóa, lịch sử địa phương', 'Phối hợp chặt chẽ cùng bộ phận điều hành']],
            ['slug' => 'to-chuc-su-kien', 'category' => 'doanh-nghiep-su-kien', 'icon' => 'bi-calendar-event', 'name' => 'Tổ chức sự kiện', 'description' => 'Triển khai hội nghị, hội thảo, tiệc tối và hoạt động gắn kết kết hợp cùng chuyến đi.', 'intro' => 'Từ ý tưởng đến vận hành tại điểm đến, HG phối hợp các hạng mục sự kiện và du lịch trong một kế hoạch thống nhất, phù hợp mục tiêu của doanh nghiệp.', 'benefits' => ['Xây dựng kịch bản theo mục tiêu chương trình', 'Điều phối địa điểm, lưu trú và di chuyển', 'Một đầu mối theo sát toàn bộ hoạt động']],
        ] as $sortOrder => $service) {
            Service::updateOrCreate(['slug' => $service['slug']], [
                'service_category_id' => $categoryModels[$service['category']]->id,
                'name' => $service['name'],
                'icon' => $service['icon'],
                'description' => $service['description'],
                'intro' => $service['intro'],
                'benefits' => $service['benefits'],
                'sort_order' => $sortOrder + 1,
                'is_active' => true,
            ]);
        }
    }
}
