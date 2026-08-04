<?php

namespace App\Services;

class TravelServiceCatalog
{
    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return [
            [
                'slug' => 'tour-tron-goi',
                'icon' => 'bi-map',
                'title' => 'Tour du lịch trọn gói',
                'description' => 'Những hành trình được chuẩn bị sẵn, rõ lịch trình và dịch vụ cho chuyến đi thuận tiện hơn.',
                'intro' => 'HG tư vấn tour trọn gói tại các thị trường châu Á, châu Âu, châu Úc, châu Mỹ và châu Phi, phù hợp với thời gian và nhu cầu của từng nhóm khách.',
                'benefits' => ['Lịch trình và dịch vụ được thể hiện rõ ràng', 'Tư vấn ngày khởi hành phù hợp', 'Đồng hành trước và trong hành trình'],
            ],
            [
                'slug' => 'tour-thiet-ke-rieng',
                'icon' => 'bi-compass',
                'title' => 'Tour du lịch thiết kế riêng',
                'description' => 'Thiết kế hành trình theo điểm đến, ngân sách, số người và nhịp điệu riêng của bạn.',
                'intro' => 'Từ một chuyến đi gia đình, nghỉ dưỡng đến hành trình khám phá nhiều quốc gia, HG cùng bạn xây dựng phương án riêng tại châu Á, châu Âu, châu Úc, châu Mỹ và châu Phi.',
                'benefits' => ['Điều chỉnh theo số ngày và số người', 'Kết hợp tour, vé máy bay, khách sạn và visa', 'Lên phương án theo ngân sách dự kiến'],
            ],
            [
                'slug' => 'visa-cac-nuoc',
                'icon' => 'bi-passport',
                'title' => 'Visa các nước',
                'description' => 'Tư vấn hồ sơ visa theo điểm đến và mục đích chuyến đi.',
                'intro' => 'HG hỗ trợ tư vấn visa cho các hành trình tại châu Á, châu Âu, châu Úc, châu Mỹ và châu Phi; hồ sơ được rà soát theo yêu cầu thực tế của từng điểm đến.',
                'benefits' => ['Rà soát giấy tờ trước khi nộp', 'Hướng dẫn biểu mẫu và các mốc chuẩn bị', 'Theo dõi tiến độ hồ sơ'],
            ],
            [
                'slug' => 've-may-bay',
                'icon' => 'bi-airplane-engines',
                'title' => 'Dịch vụ vé máy bay',
                'description' => 'Tìm chuyến bay phù hợp với hành trình, giờ bay và ngân sách của bạn.',
                'intro' => 'HG tư vấn các phương án vé máy bay nội địa và quốc tế để kết nối lịch trình tour trọn gói, tour riêng hoặc chuyến đi tự túc.',
                'benefits' => ['So sánh giờ bay, hành lý và điều kiện vé', 'Gợi ý chặng bay phù hợp với lịch trình', 'Kết nối cùng dịch vụ tour và khách sạn'],
            ],
            [
                'slug' => 'dat-phong-khach-san',
                'icon' => 'bi-buildings',
                'title' => 'Đặt phòng khách sạn',
                'description' => 'Gợi ý lưu trú phù hợp với lịch trình, ngân sách và phong cách nghỉ ngơi của bạn.',
                'intro' => 'HG hỗ trợ lựa chọn khách sạn tại các điểm đến thuộc châu Á, châu Âu, châu Úc, châu Mỹ và châu Phi, đồng bộ cùng toàn bộ hành trình.',
                'benefits' => ['Tư vấn vị trí phù hợp với lịch trình', 'Đối chiếu hạng phòng và tiện ích', 'Kết hợp cùng tour, vé máy bay hoặc visa'],
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function homeCards(): array
    {
        return array_map(fn (array $service): array => [
            'icon' => $service['icon'],
            'title' => $service['title'],
            'description' => $service['description'],
            'url' => '/dich-vu#'.$service['slug'],
        ], $this->all());
    }
}
