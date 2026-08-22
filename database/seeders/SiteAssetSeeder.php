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

        $settings = app(SiteSettingsService::class);
        $media = $settings->media();
        $logoUrl = $media->logo_url ?: 'images/logo-hg.png';

        $website = $settings->website();
        $website->site_name = 'HG Trip';
        $website->save();

        $business = $settings->business();
        $business->company_name = 'CÔNG TY TNHH DỊCH VỤ DU LỊCH VÀ THƯƠNG MẠI HG';
        $business->save();

        $contact = $settings->contact();
        $contact->office_address = 'Số 22, ngõ 126 phố Hào Nam, phường Ô Chợ Dừa, Hà Nội';
        $contact->contact_phone = '0916 16 9983';
        $contact->contact_phone_secondary = '0906 066 036';
        $contact->contact_email = 'giangnh@hgtrip.biz';
        $contact->contact_email_secondary = 'huongvu@hgtrip.biz';
        $contact->contact_email_tertiary = 'hgtrip.ltdcompany@gmail.com';
        $contact->save();

        $seo = $settings->seo();
        $seo->seo_title = 'HG Trip | Dịch vụ du lịch và thương mại';
        $seo->seo_description = 'HG Trip cung cấp tour trọn gói, tour thiết kế riêng, visa, vé máy bay và đặt phòng khách sạn cho các hành trình trong nước, châu Á, châu Âu, châu Úc, châu Mỹ và châu Phi.';
        $seo->seo_keywords = 'HG Trip, tour du lịch, visa, vé máy bay, khách sạn, tour thiết kế riêng';
        $seo->save();

        $media->logo_url = $logoUrl;
        $media->favicon_url = $media->favicon_url ?: $logoUrl;
        $media->image_share_url = in_array($media->image_share_url, [null, '', 'images/logo-hg.png'], true)
            ? 'images/image_share.png'
            : $media->image_share_url;
        $media->save();
    }
}
