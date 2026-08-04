@extends('layouts.admin')

@section('title', 'Cài đặt')
@section('page-title', 'Cài đặt hệ thống')

@section('content')
    <form action="{{ route('admin.settings.general.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="Thông tin website">
                    <x-input name="site_name" label="Tên website" :value="$settings['site_name']" required />
                    <x-input name="company_name" label="Tên pháp lý công ty" :value="$settings['company_name']" />
                    <div class="row"><div class="col-md-6"><x-input name="contact_phone" label="Số điện thoại (Ms Giang)" :value="$settings['contact_phone']" /></div><div class="col-md-6"><x-input name="contact_phone_secondary" label="Số điện thoại (Ms Hương)" :value="$settings['contact_phone_secondary']" /></div></div>
                    <div class="row"><div class="col-md-4"><x-input type="email" name="contact_email" label="Email liên hệ 1" :value="$settings['contact_email']" /></div><div class="col-md-4"><x-input type="email" name="contact_email_secondary" label="Email liên hệ 2" :value="$settings['contact_email_secondary']" /></div><div class="col-md-4"><x-input type="email" name="contact_email_tertiary" label="Email liên hệ 3" :value="$settings['contact_email_tertiary']" /></div></div>
                    <x-textarea name="office_address" label="Địa chỉ" :value="$settings['office_address']" rows="3" />
                    <x-input name="seo_title" label="SEO title mặc định" :value="$settings['seo_title']" />
                    <x-textarea name="seo_description" label="SEO description mặc định" :value="$settings['seo_description']" rows="3" />
                    <x-textarea name="seo_keywords" label="SEO keywords mặc định" :value="$settings['seo_keywords']" rows="2" />
                </x-card>
            </div>
            <div class="col-xl-4">
                <x-card type="info" title="Nhận diện & media" class="mb-3">
                    <x-image-upload name="logo_url" label="Logo" :value="$settings['logo_url']" />
                    <x-image-upload name="favicon_url" label="Favicon" :value="$settings['favicon_url']" />
                    <x-image-upload name="image_share_url" label="Ảnh chia sẻ mặc định (OG/Twitter)" :value="$settings['image_share_url']" />
                    <x-image-upload name="page_banner_url" label="Banner trang trong" :value="$settings['page_banner_url']" />
                    <x-input type="url" name="facebook_url" label="Facebook URL" :value="$settings['facebook_url']" />
                    <x-input type="url" name="instagram_url" label="Instagram URL" :value="$settings['instagram_url']" />
                    <x-input type="url" name="youtube_url" label="Youtube URL" :value="$settings['youtube_url']" />
                    <x-input type="url" name="zalo_url" label="Zalo URL" :value="$settings['zalo_url']" />
                    <x-input type="url" name="messenger_url" label="Messenger URL" :value="$settings['messenger_url']" />
                    <x-input type="url" name="whatsapp_url" label="WhatsApp URL" :value="$settings['whatsapp_url']" />
                    <x-input name="media_allowed_extensions" label="Định dạng cho phép" :value="$settings['media_allowed_extensions']" required />
                    <x-input type="number" name="media_max_size" label="Dung lượng tối đa (MB)" :value="$settings['media_max_size']" required />
                </x-card>
                <div class="card"><div class="card-body text-end"><button class="btn btn-primary">Lưu cài đặt</button></div></div>
            </div>
        </div>
    </form>
@endsection
