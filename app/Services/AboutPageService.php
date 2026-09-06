<?php

namespace App\Services;

use App\Data\SiteSettingsData;
use App\Models\AboutPage;
use App\Models\Service;

class AboutPageService
{
    public function presentationData(AboutPage $page, SiteSettingsData $settings): array
    {
        $images = app(MediaReferenceService::class);
        $content = $this->profileContent($page);
        $content['clients']['items'] = array_map(fn (array $client) => $client + ['image_url' => $images->url($client['image'] ?? null, 'images/placeholder.svg')], $content['clients']['items']);

        return [
            'heroImage' => $images->url($page->hero_image, 'images/about/hg-trip/letter-travel.jpg'),
            'letterImage' => $images->url($page->background_image, 'images/about/hg-trip/hg-trip-traveller.png'),
            'storyImage' => $images->url($page->story_image, 'images/about/hg-trip/hg-trip-south-africa.jpg'),
            'companyName' => $settings->company_name ?: $settings->site_name ?: 'HG TRIP',
            'marketCards' => collect($page->markets ?: [])->map(fn ($market) => ['name' => trim((string) (is_array($market) ? ($market['name'] ?? '') : $market)), 'detail' => is_array($market) ? trim((string) ($market['detail'] ?? '')) : ''])->filter(fn ($market) => $market['name'] !== '')->values(),
            'content' => $content, 'intro' => $content['company_intro'], 'story' => $content['story'],
            'storySteps' => $content['story_steps'], 'values' => $content['values'], 'products' => $content['featured_products'],
            'support' => $content['support'], 'leaders' => $content['leaders'], 'clients' => $content['clients'], 'organisation' => $content['organisation'],
        ];
    }

    public function current(): AboutPage
    {
        return AboutPage::firstOrCreate(['key' => 'about'], $this->defaults());
    }

    public function update(array $data): AboutPage
    {
        $page = $this->current();
        $page->update($data);

        return $page;
    }

    /** @return array<string, mixed> */
    public function profileContent(AboutPage $page): array
    {
        return $page->profile_content ?: self::defaultProfileContent($this->defaultSupportServiceIds());
    }

    /** @param array<int, int> $supportServiceIds */
    public static function defaultProfileContent(array $supportServiceIds = []): array
    {
        return [
            'hero' => ['kicker' => 'HG TRIP · VỀ CHÚNG TÔI', 'cta_label' => 'Khám phá câu chuyện'],
            'letter' => ['signature_name' => 'HG TRIP', 'signature_tagline' => 'TRAVEL WITH PURPOSE'],
            'company_intro' => [
                'eyebrow' => 'VỀ CHÚNG TÔI',
                'title' => 'Giải pháp du lịch toàn diện, thiết kế cho từng nhu cầu.',
                'lead' => 'hoạt động trong lĩnh vực dịch vụ du lịch, tập trung phục vụ khách hàng cá nhân, gia đình, nhóm khách và doanh nghiệp.',
                'content' => 'Đằng sau một thương hiệu mới là đội ngũ đã trực tiếp xây dựng, tổ chức và vận hành nhiều chương trình cho khách hàng trong nước, quốc tế và doanh nghiệp.',
                'credentials' => ['Giấy chứng nhận đăng ký doanh nghiệp', 'Giấy phép kinh doanh lữ hành quốc tế'],
            ],
            'story' => [
                'eyebrow' => 'HÀNH TRÌNH CỦA CHÚNG TÔI',
                'fact' => 'Những chuyến đi được tạo nên từ sự lắng nghe, kinh nghiệm và kết nối chân thành.',
                'photo_alt' => 'Đoàn khách HG TRIP trong một hành trình quốc tế',
                'photo_caption' => 'Những hành trình thực tế cùng khách hàng HG TRIP',
            ],
            'story_steps' => [
                'eyebrow' => 'CÁCH HG TRIP TẠO NÊN MỘT HÀNH TRÌNH',
                'intro' => 'Một chuyến đi đáng nhớ không cần thật nhiều điểm đến; điều quan trọng là mỗi chi tiết đúng với người đi.',
                'items' => [
                    ['title' => 'HG TRIP ra đời từ niềm tin ấy', 'description' => 'Nếu mỗi khách hàng đều có một câu chuyện riêng, tại sao hành trình của họ lại phải giống nhau?'],
                    ['title' => 'Một hướng đi riêng', 'description' => 'Không chạy theo những hành trình đại trà; tập trung tạo nên chuyến đi phù hợp với từng khách hàng.'],
                    ['title' => 'Lắng nghe để thấu hiểu', 'description' => 'Tìm hiểu điều khách hàng muốn khám phá, cách họ muốn trải nghiệm và những giá trị họ muốn nhận lại.'],
                    ['title' => 'Thiết kế từng chi tiết', 'description' => 'Cân nhắc kỹ điểm đến, thời gian, trải nghiệm, dịch vụ và các chi tiết phù hợp với từng đối tượng khách.'],
                    ['title' => 'Luôn đồng hành', 'description' => 'Theo đuổi không chỉ một chuyến đi được tổ chức tốt mà là trải nghiệm dành riêng cho mỗi cá nhân.'],
                ],
            ],
            'values' => [
                'eyebrow' => 'GIÁ TRỊ CỐT LÕI',
                'title' => "Điều định hướng\nmọi hành trình",
                'intro' => 'Không chỉ tổ chức một chuyến đi, HG TRIP chăm chút cho cảm xúc và sự an tâm trong từng trải nghiệm.',
            ],
            'featured_products' => [
                'eyebrow' => 'SẢN PHẨM ĐẶC TRƯNG',
                'title' => "Đúng mục tiêu chuyến đi,\nđủ đầy từng dịch vụ.",
                'intro' => 'HG TRIP tổ chức chương trình trọn vẹn và linh hoạt, từ mục tiêu của doanh nghiệp đến những kỳ nghỉ dành riêng cho cá nhân, gia đình.',
                'items' => [
                    ['icon' => 'bi-buildings', 'title' => 'Du lịch doanh nghiệp và tổ chức sự kiện', 'description' => 'Xây dựng và triển khai chương trình kết hợp hội nghị, hội thảo, gặp gỡ đối tác, khảo sát thị trường và hoạt động nội bộ.'],
                    ['icon' => 'bi-people', 'title' => 'Du lịch khen thưởng và gắn kết tập thể', 'description' => 'Thiết kế chương trình ghi nhận thành tích, tạo động lực và tăng gắn kết, có thể kết hợp nghỉ dưỡng, gala, tiệc tối và hoạt động đặc biệt.'],
                    ['icon' => 'bi-briefcase', 'title' => 'Du lịch công tác', 'description' => 'Tối ưu vé máy bay, khách sạn, đưa đón, dịch vụ sân bay, hướng dẫn viên và hỗ trợ tại điểm đến.'],
                    ['icon' => 'bi-gem', 'title' => 'Du lịch nghỉ dưỡng cao cấp', 'description' => 'Tuyển chọn không gian lưu trú, chất lượng dịch vụ và trải nghiệm dành cho khách hàng có yêu cầu cao.'],
                ],
            ],
            'support' => [
                'eyebrow' => 'HỆ SINH THÁI DỊCH VỤ',
                'title' => "Một đầu mối cho\ntoàn bộ hành trình.",
                'intro' => 'Tất cả hạng mục được kết nối trong một kế hoạch thống nhất để khách hàng chủ động và an tâm hơn.',
                'service_ids' => $supportServiceIds,
            ],
            'leaders' => [
                'eyebrow' => 'ĐỘI NGŨ LÃNH ĐẠO',
                'title' => 'Kinh nghiệm tạo nên sự an tâm',
                'intro' => 'Đội ngũ điều hành đồng hành trực tiếp từ khâu thiết kế đến khi hành trình khép lại.',
                'ceo_role' => 'CEO HG TRIP',
                'deputy_role' => 'PHÓ GIÁM ĐỐC',
            ],
            'clients' => [
                'eyebrow' => 'KHÁCH HÀNG TIÊU BIỂU',
                'title' => "Được tin tưởng\ntrong những hành trình quan trọng.",
                'intro' => 'HG TRIP trân trọng sự đồng hành của các doanh nghiệp, tổ chức và đối tác đã lựa chọn dịch vụ của chúng tôi.',
                'items' => [
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
            ],
            'organisation' => [
                'eyebrow' => 'BỘ MÁY VÀ HIỆN DIỆN',
                'title' => 'Một đội ngũ vận hành sát sao, kết nối đa thị trường.',
                'intro' => 'Từ tư vấn, kinh doanh, điều hành đến hướng dẫn viên và đội xe, các bộ phận cùng phối hợp để mỗi kế hoạch diễn ra thông suốt.',
                'cta_label' => 'Liên hệ HG TRIP',
                'departments' => ['Phòng Hành chính - Kế toán', 'Phòng Kinh doanh: Nội địa - MICE - Sự kiện, Outbound, Vé máy bay, Visa', 'Phòng Điều hành: Điều hành, Hướng dẫn viên, Đội xe'],
                'offices' => [
                    ['icon' => 'bi-buildings-fill', 'label' => 'Trụ sở công ty', 'address' => 'Số 22/126 phố Hào Nam, phường Ô Chợ Dừa, TP. Hà Nội, Việt Nam'],
                    ['icon' => 'bi-globe-europe-africa', 'label' => 'VPĐD tại châu Âu', 'address' => '18 Rue Alfred Krieger, 57070 Saint Julien Les Metz, Pháp'],
                    ['icon' => 'bi-globe-americas', 'label' => 'VPĐD tại Mỹ', 'address' => '5524 Yorkshire Street, Springfield VA 22151, Hoa Kỳ'],
                ],
            ],
            'contact' => ['tagline' => 'See the world from different angles'],
        ];
    }

    /** @return array<int, int> */
    private function defaultSupportServiceIds(): array
    {
        return Service::query()
            ->whereIn('name', ['Visa các nước', 'Dịch vụ vé máy bay', 'Đặt phòng khách sạn', 'Vận chuyển', 'Dịch vụ sân bay', 'Hướng dẫn viên', 'Tổ chức sự kiện'])
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** @return array<string, mixed> */
    private function defaults(): array
    {
        return [
            'hero_title' => 'Mỗi hành trình bắt đầu từ một câu chuyện.',
            'hero_intro' => 'HG TRIP thiết kế những hành trình riêng, chỉn chu và đáng nhớ cho mỗi khách hàng.',
            'letter_title' => 'Thư ngỏ',
            'letter_content' => "Kính gửi Quý Khách hàng và Quý Đối tác,\n\nMỗi chuyến đi đều mang trong mình một câu chuyện. Tại HG TRIP, chúng tôi tin rằng giá trị của một hành trình không chỉ nằm ở điểm đến, mà còn ở cách hành trình ấy được thiết kế và trải nghiệm.\n\nDù là chuyến công tác, chương trình khen thưởng cho doanh nghiệp, kỳ nghỉ gia đình hay hành trình khám phá thế giới, chúng tôi luôn hướng tới việc tạo ra những trải nghiệm chỉn chu, cá nhân hóa và đáng nhớ.\n\nVới đội ngũ có nhiều năm kinh nghiệm trong lĩnh vực du lịch quốc tế và mạng lưới đối tác rộng khắp, HG TRIP cam kết mang đến dịch vụ chuyên nghiệp, minh bạch và đồng hành cùng khách hàng ở mọi chặng đường.",
            'story_title' => 'Câu chuyện HG TRIP',
            'story_content' => 'Chúng tôi bắt đầu với một câu hỏi rất đơn giản: nếu mỗi khách hàng đều có một câu chuyện riêng, tại sao hành trình của họ lại phải giống nhau? HG TRIP lựa chọn lắng nghe để thấu hiểu, từ đó tạo nên những chuyến đi phù hợp với từng nhu cầu và những khoảnh khắc đáng nhớ.',
            'vision' => 'Trở thành thương hiệu du lịch cao cấp được khách hàng và đối tác tin cậy tại Việt Nam và khu vực.',
            'mission' => 'Mang đến những hành trình được thiết kế riêng, tối ưu trải nghiệm và tạo ra giá trị bền vững cho khách hàng.',
            'markets_eyebrow' => 'CÁC THỊ TRƯỜNG HOẠT ĐỘNG',
            'markets_title' => "Kết nối những hành trình\nkhông giới hạn biên giới.",
            'markets_intro' => 'Từ một chuyến đi trong nước đến những thị trường quốc tế, HG TRIP chuẩn bị đồng bộ trải nghiệm và vận hành.',
            'profile_content' => self::defaultProfileContent($this->defaultSupportServiceIds()),
            'core_values' => [['title' => 'Chuyên nghiệp', 'description' => 'Quy trình rõ ràng, tác phong chuyên nghiệp và luôn nâng cao chất lượng trong từng dịch vụ.'], ['title' => 'Uy tín', 'description' => 'Cam kết dịch vụ minh bạch, đúng như thỏa thuận và bảo đảm chất lượng.'], ['title' => 'Tận tâm', 'description' => 'Lấy khách hàng làm trung tâm, lắng nghe và đáp ứng tối đa nhu cầu.'], ['title' => 'Sáng tạo', 'description' => 'Cải tiến sản phẩm cá nhân hóa để tạo trải nghiệm khác biệt.'], ['title' => 'Chia sẻ', 'description' => 'Luôn đồng hành, chia sẻ trách nhiệm và thành công cùng khách hàng, đối tác.']],
            'markets' => [['name' => 'Nội địa', 'detail' => 'Khám phá Việt Nam theo nhịp điệu riêng'], ['name' => 'Inbound', 'detail' => 'Đón khách quốc tế đến Việt Nam'], ['name' => 'Outbound', 'detail' => 'Hành trình quốc tế được thiết kế riêng'], ['name' => 'Dịch vụ khác', 'detail' => 'Visa, vé máy bay, khách sạn và các hỗ trợ cần thiết cho chuyến đi']],
            'commitments' => ['Không phát sinh chi phí bất hợp lý', 'Hỗ trợ khẩn cấp 24/7', 'Lịch trình tối ưu', 'Đối tác được lựa chọn kỹ', 'Trải nghiệm cá nhân hóa', 'Bảo hiểm đầy đủ'],
            'audiences' => ['Doanh nghiệp', 'Ngân hàng', 'Bảo hiểm', 'Gia đình', 'Cặp đôi', 'Nhóm bạn', 'Khách VIP'],
            'ceo_name' => 'Bà Nguyễn Thị Hương Giang',
            'ceo_bio' => 'Với trên 20 năm kinh nghiệm trong lĩnh vực du lịch, bà từng có gần 10 năm giữ vị trí Giám đốc Công ty Cổ phần Du lịch Hapro thuộc Tổng công ty Thương mại Hà Nội; sau đó là thành viên Hội đồng thành viên, Phó Giám đốc Công ty Cổ phần Lữ hành Nam Cường đến hết năm 2025.',
            'deputy_name' => 'Bà Vũ Thị Thùy Hương',
            'deputy_bio' => 'Có 10 năm kinh nghiệm chuyên sâu trong công tác điều hành tour, từng là Trưởng phòng Điều hành tại Công ty Cổ phần Lữ hành Nam Cường; trực tiếp tổ chức, phối hợp lịch trình, điều phối dịch vụ và quản lý vận hành hành trình.',
            'background_image' => 'images/about/hg-trip/hg-trip-traveller.png',
            'hero_image' => null,
            'story_image' => 'images/about/hg-trip/hg-trip-south-africa.jpg',
            'seo_title' => 'Về HG TRIP',
            'seo_description' => 'Tìm hiểu câu chuyện, đội ngũ và cam kết dịch vụ của HG TRIP.',
        ];
    }
}
