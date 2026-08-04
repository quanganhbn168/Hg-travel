<?php

namespace Database\Seeders;

use App\Models\SiteAsset;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;

class SiteAssetSeeder extends Seeder
{
    public function run(): void
    {
        SiteAsset::firstOrCreate(['key' => 'site']);

        $settings = app(SiteSettingsService::class)->general();
        $logoUrl = $settings->logo_url ?: 'images/logo-hg.png';

        $settings->fill([
            'site_name' => 'HG Trip',
            'company_name' => 'CÔNG TY TNHH DỊCH VỤ DU LỊCH VÀ THƯƠNG MẠI HG',
            'office_address' => 'Số 22, ngõ 126 phố Hào Nam, phường Ô Chợ Dừa, Hà Nội',
            'contact_phone' => '0916 16 9983',
            'contact_phone_secondary' => '0906 066 036',
            'contact_email' => 'giangnh@hgtrip.biz',
            'contact_email_secondary' => 'huongvu@hgtrip.biz',
            'contact_email_tertiary' => 'hgtrip.ltdcompany@gmail.com',
            'seo_title' => 'HG Trip | Dịch vụ du lịch và thương mại',
            'seo_description' => 'HG Trip cung cấp tour trọn gói, tour thiết kế riêng, visa, vé máy bay và đặt phòng khách sạn cho các hành trình trong nước, châu Á, châu Âu, châu Úc, châu Mỹ và châu Phi.',
            'seo_keywords' => 'HG Trip, tour du lịch, visa, vé máy bay, khách sạn, tour thiết kế riêng',
            'logo_url' => $logoUrl,
            'favicon_url' => $settings->favicon_url ?: $logoUrl,
            'image_share_url' => in_array($settings->image_share_url, [null, '', 'images/logo-hg.png'], true)
                ? 'images/image_share.png'
                : $settings->image_share_url,
        ])->save();
    }
}
