<?php

namespace App\Services;

use App\Data\SiteSettingsData;

class AboutProfileCatalog
{
    /** @return array<string, mixed> */
    public function profile(?SiteSettingsData $settings = null): array
    {
        return [
            'company' => [
                'legal_name' => $settings?->company_name ?: 'Công ty TNHH Dịch vụ Du lịch và Thương mại HG',
                'tagline' => 'See the world from different angles',
                'address' => $settings?->office_address,
                'phones' => collect([$settings?->contact_phone, $settings?->contact_phone_secondary])->filter()->values()->all(),
                'email' => $settings?->contact_email,
                'website' => parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.name', 'HG TRIP'),
            ],
            'credentials' => [
                'Giấy chứng nhận đăng ký doanh nghiệp',
                'Giấy phép kinh doanh lữ hành quốc tế',
            ],
            'story_steps' => [
                ['number' => '01', 'title' => 'HG TRIP ra đời từ niềm tin ấy', 'description' => 'Nếu mỗi khách hàng đều có một câu chuyện riêng, tại sao hành trình của họ lại phải giống nhau?'],
                ['number' => '02', 'title' => 'Một hướng đi riêng', 'description' => 'Không chạy theo những hành trình đại trà; tập trung tạo nên chuyến đi phù hợp với từng khách hàng.'],
                ['number' => '03', 'title' => 'Lắng nghe để thấu hiểu', 'description' => 'Tìm hiểu điều khách hàng muốn khám phá, cách họ muốn trải nghiệm và những giá trị họ muốn nhận lại.'],
                ['number' => '04', 'title' => 'Thiết kế từng chi tiết', 'description' => 'Cân nhắc kỹ điểm đến, thời gian, trải nghiệm, dịch vụ và các chi tiết phù hợp với từng đối tượng khách.'],
                ['number' => '05', 'title' => 'Luôn đồng hành', 'description' => 'Theo đuổi không chỉ một chuyến đi được tổ chức tốt mà là trải nghiệm dành riêng cho mỗi cá nhân.'],
            ],
            'featured_products' => [
                ['icon' => 'bi-buildings', 'title' => 'Du lịch doanh nghiệp và tổ chức sự kiện', 'description' => 'Xây dựng và triển khai chương trình kết hợp hội nghị, hội thảo, gặp gỡ đối tác, khảo sát thị trường và hoạt động nội bộ.'],
                ['icon' => 'bi-people', 'title' => 'Du lịch khen thưởng và gắn kết tập thể', 'description' => 'Thiết kế chương trình ghi nhận thành tích, tạo động lực và tăng gắn kết, có thể kết hợp nghỉ dưỡng, gala, tiệc tối và hoạt động đặc biệt.'],
                ['icon' => 'bi-briefcase', 'title' => 'Du lịch công tác', 'description' => 'Tối ưu vé máy bay, khách sạn, đưa đón, dịch vụ sân bay, hướng dẫn viên và hỗ trợ tại điểm đến.'],
                ['icon' => 'bi-gem', 'title' => 'Du lịch nghỉ dưỡng cao cấp', 'description' => 'Tuyển chọn không gian lưu trú, chất lượng dịch vụ và trải nghiệm dành cho khách hàng có yêu cầu cao.'],
            ],
            'support_services' => [
                ['icon' => 'bi-airplane-engines', 'title' => 'Vé máy bay', 'description' => 'Tư vấn hành trình, hãng bay, hạng vé và hỗ trợ điều chỉnh khi cần thiết.'],
                ['icon' => 'bi-buildings', 'title' => 'Khách sạn và nghỉ dưỡng', 'description' => 'Lựa chọn lưu trú phù hợp vị trí, tiêu chuẩn dịch vụ, nhu cầu và ngân sách.'],
                ['icon' => 'bi-car-front', 'title' => 'Vận chuyển', 'description' => 'Xe đưa đón sân bay, xe đoàn riêng và phương án chuyên biệt, đồng bộ với lịch trình.'],
                ['icon' => 'bi-airplane', 'title' => 'Dịch vụ sân bay', 'description' => 'Đón tiễn, hỗ trợ thủ tục, phòng chờ và các dịch vụ ưu tiên.'],
                ['icon' => 'bi-calendar-event', 'title' => 'Tổ chức sự kiện', 'description' => 'Hội nghị, hội thảo, tiệc tối, lễ kỷ niệm, ra mắt sản phẩm và hoạt động gắn kết.'],
                ['icon' => 'bi-person-badge', 'title' => 'Hướng dẫn viên', 'description' => 'Đội ngũ chuyên nghiệp, am hiểu điểm đến, văn hóa và lịch sử địa phương.'],
                ['icon' => 'bi-passport', 'title' => 'Visa', 'description' => 'Tư vấn chiến lược hồ sơ cho Anh, Mỹ, Canada, Úc, Schengen, Nhật Bản, Hàn Quốc và nhiều điểm đến khác.'],
            ],
            'markets' => [
                ['name' => 'Nội địa', 'detail' => 'Khám phá Việt Nam theo nhịp điệu riêng'],
                ['name' => 'Inbound', 'detail' => 'Đón khách quốc tế đến Việt Nam'],
                ['name' => 'Outbound', 'detail' => 'Hành trình quốc tế được thiết kế riêng'],
                ['name' => 'Dịch vụ khác', 'detail' => 'Visa, vé máy bay, khách sạn và các hỗ trợ cần thiết cho chuyến đi'],
            ],
            'clients' => [
                ['name' => 'MB', 'image' => 'images/about/hg-trip/clients/mb.png'],
                ['name' => 'The University of Sydney', 'image' => 'images/about/hg-trip/clients/university-of-sydney.png'],
                ['name' => 'Vifon', 'image' => 'images/about/hg-trip/clients/vifon.png'],
                ['name' => 'Hợp Lực', 'image' => 'images/about/hg-trip/clients/hop-luc.png'],
                ['name' => 'Seojin Group', 'image' => 'images/about/hg-trip/clients/seojin.png'],
                ['name' => 'MIC', 'image' => 'images/about/hg-trip/clients/mic.png'],
                ['name' => 'MB Life', 'image' => 'images/about/hg-trip/clients/mb-life.png'],
                ['name' => 'TalentPool', 'image' => 'images/about/hg-trip/clients/talent-pool.png'],
                ['name' => 'VNVC', 'image' => 'images/about/hg-trip/clients/vnvc.png'],
                ['name' => 'Vabiotech', 'image' => 'images/about/hg-trip/clients/vabiotech.png'],
            ],
            'offices' => [
                ['label' => 'Trụ sở công ty', 'icon' => 'bi-buildings-fill', 'address' => 'Số 22/126 phố Hào Nam, phường Ô Chợ Dừa, TP. Hà Nội, Việt Nam'],
                ['label' => 'VPĐD tại châu Âu', 'icon' => 'bi-globe-europe-africa', 'address' => '18 Rue Alfred Krieger, 57070 Saint Julien Les Metz, Pháp'],
                ['label' => 'VPĐD tại Mỹ', 'icon' => 'bi-globe-americas', 'address' => '5524 Yorkshire Street, Springfield VA 22151, Hoa Kỳ'],
            ],
            'organisation' => [
                'Phòng Hành chính - Kế toán',
                'Phòng Kinh doanh: Nội địa - MICE - Sự kiện, Outbound, Vé máy bay, Visa',
                'Phòng Điều hành: Điều hành, Hướng dẫn viên, Đội xe',
            ],
        ];
    }
}
