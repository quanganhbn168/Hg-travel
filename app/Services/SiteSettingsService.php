<?php

namespace App\Services;

use App\Data\SiteSettingsData;
use App\Settings\BusinessSettings;
use App\Settings\ContactSettings;
use App\Settings\MediaSettings;
use App\Settings\SeoSettings;
use App\Settings\TourSettings;
use App\Settings\WebsiteSettings;
use App\Support\MediaFields;
use Spatie\LaravelSettings\Exceptions\MissingSettings;
use Spatie\LaravelSettings\Settings;

class SiteSettingsService
{
    public function website(): WebsiteSettings
    {
        return $this->load(WebsiteSettings::class, [
            'site_name' => config('app.name', 'Du lịch'),
            'about_title' => 'Hành trình đáng nhớ bắt đầu từ cách nó được thiết kế.',
            'about_paragraph_one' => 'HG TRIP xây dựng những chuyến đi phù hợp với mục đích, nhịp độ và kỳ vọng riêng của từng khách hàng — từ gia đình, nhóm bạn đến doanh nghiệp.',
            'about_paragraph_two' => 'Nền tảng kinh nghiệm thực tiễn của đội ngũ giúp mỗi hành trình được chuẩn bị chỉn chu, vận hành minh bạch và luôn có người đồng hành ở mọi chặng đường.',
            'custom_tour_title' => 'Thiết kế tour du lịch theo yêu cầu',
            'custom_tour_description' => 'HG lắng nghe sở thích, ngân sách và nhịp điệu của riêng bạn để cùng tạo nên một hành trình thật vừa vặn.',
            'impact_title' => 'HG trong những con số',
            'impact_stat_one_number' => '20+',
            'impact_stat_one_label' => 'Năm kinh nghiệm lãnh đạo',
            'impact_stat_two_number' => '03',
            'impact_stat_two_label' => 'Thị trường hoạt động',
            'impact_stat_three_number' => '24/7',
            'impact_stat_three_label' => 'Hỗ trợ khẩn cấp',
            'impact_stat_four_number' => '02',
            'impact_stat_four_label' => 'Văn phòng đại diện quốc tế',
            'partner_names' => 'Vietnam Airlines'.PHP_EOL.'Vietjet Air'.PHP_EOL.'Bamboo Airways'.PHP_EOL.'Turkish Airlines'.PHP_EOL.'KTO Korea'.PHP_EOL.'Visa Vietnam',
        ]);
    }

    public function business(): BusinessSettings
    {
        return $this->load(BusinessSettings::class, [
            'company_name' => null,
            'legal_representative' => null,
            'tax_code' => null,
            'travel_license_number' => null,
            'brand_statement' => 'Chúng tôi mong muốn mỗi lần khách hàng lựa chọn HG TRIP không chỉ là một lần đặt dịch vụ, mà là một lần bắt đầu một hành trình mà ở đó họ có thể hoàn toàn an tâm tận hưởng, khám phá và tạo nên những kỷ niệm của riêng mình.',
        ]);
    }

    public function media(): MediaSettings
    {
        $settings = $this->load(MediaSettings::class, [
            'media_allowed_extensions' => 'jpg,jpeg,png,webp,gif,pdf,doc,docx',
            'media_max_size' => 10,
            'logo_url' => null,
            'favicon_url' => null,
            'image_share_url' => null,
            'page_banner_url' => null,
            'homepage_hero_url' => null,
            'about_image_url' => null,
            'media_ids' => [],
        ]);
        $references = app(MediaReferenceService::class);
        foreach (MediaFields::SETTINGS as $field) {
            if ($id = ($settings->media_ids[$field] ?? null)) {
                if ($media = $references->find('media:'.$id)) {
                    $settings->$field = $references->originalPath($media);
                }
            }
        }

        return $settings;
    }

    public function seo(): SeoSettings
    {
        return $this->load(SeoSettings::class, [
            'seo_title' => config('app.name', 'Du lịch'),
            'seo_description' => null,
            'seo_keywords' => null,
        ]);
    }

    public function contact(): ContactSettings
    {
        return $this->load(ContactSettings::class, [
            'contact_email' => null,
            'contact_email_secondary' => null,
            'contact_email_tertiary' => null,
            'contact_phone' => null,
            'contact_phone_secondary' => null,
            'office_address' => null,
            'facebook_url' => null,
            'instagram_url' => null,
            'youtube_url' => null,
            'zalo_url' => null,
            'messenger_url' => null,
            'whatsapp_url' => null,
        ]);
    }

    public function tour(): TourSettings
    {
        return $this->load(TourSettings::class, [
            'show_schedules' => true,
            'show_seat_availability' => true,
            'schedule_note' => 'Giá, lịch và số chỗ được cập nhật theo từng đợt khởi hành.',
        ]);
    }

    public function general(): SiteSettingsData
    {
        $website = $this->website();
        $business = $this->business();
        $media = $this->media();
        $seo = $this->seo();
        $contact = $this->contact();

        return new SiteSettingsData(
            site_name: $website->site_name,
            company_name: $business->company_name,
            legal_representative: $business->legal_representative,
            tax_code: $business->tax_code,
            travel_license_number: $business->travel_license_number,
            brand_statement: $business->brand_statement,
            contact_email: $contact->contact_email,
            contact_email_secondary: $contact->contact_email_secondary,
            contact_email_tertiary: $contact->contact_email_tertiary,
            contact_phone: $contact->contact_phone,
            contact_phone_secondary: $contact->contact_phone_secondary,
            office_address: $contact->office_address,
            seo_title: $seo->seo_title,
            seo_description: $seo->seo_description,
            seo_keywords: $seo->seo_keywords,
            logo_url: $media->logo_url,
            image_share_url: $media->image_share_url,
            page_banner_url: $media->page_banner_url,
            facebook_url: $contact->facebook_url,
            instagram_url: $contact->instagram_url,
            youtube_url: $contact->youtube_url,
            zalo_url: $contact->zalo_url,
            messenger_url: $contact->messenger_url,
            whatsapp_url: $contact->whatsapp_url,
        );
    }

    /** @return list<array{number: string, label: string}> */
    public function impactStats(): array
    {
        $settings = $this->website();

        return [
            ['number' => $settings->impact_stat_one_number, 'label' => $settings->impact_stat_one_label],
            ['number' => $settings->impact_stat_three_number, 'label' => $settings->impact_stat_three_label],
            ['number' => $settings->impact_stat_four_number, 'label' => $settings->impact_stat_four_label],
        ];
    }

    /**
     * @template T of Settings
     *
     * @param  class-string<T>  $settingsClass
     * @param  array<string, mixed>  $fallback
     * @return T
     */
    private function load(string $settingsClass, array $fallback): Settings
    {
        try {
            $settings = app($settingsClass);
            $settings->toArray();

            return $settings;
        } catch (MissingSettings $exception) {
            report($exception);
            if (request()->is('admin/*')) {
                throw $exception;
            }

            return $settingsClass::fake($fallback, false);
        }
    }
}
