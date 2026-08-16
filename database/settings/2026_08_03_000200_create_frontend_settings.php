<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach ([
            'website.site_name' => 'Du lịch',
            'website.about_title' => 'Hành trình đáng nhớ bắt đầu từ cách nó được thiết kế.',
            'website.about_paragraph_one' => 'HG TRIP xây dựng những chuyến đi phù hợp với mục đích, nhịp độ và kỳ vọng riêng của từng khách hàng — từ gia đình, nhóm bạn đến doanh nghiệp.',
            'website.about_paragraph_two' => 'Nền tảng kinh nghiệm thực tiễn của đội ngũ giúp mỗi hành trình được chuẩn bị chỉn chu, vận hành minh bạch và luôn có người đồng hành ở mọi chặng đường.',
            'website.custom_tour_title' => 'Thiết kế tour du lịch theo yêu cầu',
            'website.custom_tour_description' => 'HG lắng nghe sở thích, ngân sách và nhịp điệu của riêng bạn để cùng tạo nên một hành trình thật vừa vặn.',
            'website.impact_title' => 'HG trong những con số',
            'website.impact_stat_one_number' => '20+', 'website.impact_stat_one_label' => 'Năm kinh nghiệm lãnh đạo',
            'website.impact_stat_two_number' => '03', 'website.impact_stat_two_label' => 'Thị trường hoạt động',
            'website.impact_stat_three_number' => '24/7', 'website.impact_stat_three_label' => 'Hỗ trợ khẩn cấp',
            'website.impact_stat_four_number' => '02', 'website.impact_stat_four_label' => 'Văn phòng đại diện quốc tế',
            'website.partner_names' => "Vietnam Airlines\nVietjet Air\nBamboo Airways\nTurkish Airlines\nKTO Korea\nVisa Vietnam",
            'business.company_name' => null, 'business.legal_representative' => null, 'business.tax_code' => null,
            'media.media_allowed_extensions' => 'jpg,jpeg,png,webp,gif,pdf,doc,docx', 'media.media_max_size' => 10,
            'media.logo_url' => null, 'media.favicon_url' => null, 'media.image_share_url' => null, 'media.page_banner_url' => null,
            'media.homepage_hero_url' => null, 'media.about_image_url' => null,
            'seo.seo_title' => 'Du lịch', 'seo.seo_description' => null, 'seo.seo_keywords' => null,
            'contact.contact_email' => null, 'contact.contact_email_secondary' => null, 'contact.contact_email_tertiary' => null,
            'contact.contact_phone' => null, 'contact.contact_phone_secondary' => null, 'contact.office_address' => null,
            'contact.facebook_url' => null, 'contact.instagram_url' => null, 'contact.youtube_url' => null, 'contact.zalo_url' => null,
            'contact.messenger_url' => null, 'contact.whatsapp_url' => null,
        ] as $property => $value) {
            if (! $this->migrator->exists($property)) {
                $this->migrator->add($property, $value);
            }
        }
    }

    public function down(): void
    {
        foreach (['website', 'business', 'media', 'seo', 'contact'] as $group) {
            foreach (\Spatie\LaravelSettings\Models\SettingsProperty::query()->where('group', $group)->pluck('name') as $name) {
                $this->migrator->deleteIfExists("{$group}.{$name}");
            }
        }
    }
};
