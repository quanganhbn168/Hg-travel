@extends('layouts.admin')

@section('title', 'Trang giới thiệu')
@section('page-title', 'Quản lý trang Giới thiệu')

@php
    $profile = $profileContent;
    $lineList = static fn (array $items): string => collect($items)->filter()->implode(PHP_EOL);
    $entryLines = static fn (array $items, array $keys): string => collect($items)->map(fn ($item) => collect($keys)->map(fn ($key) => trim((string) data_get($item, $key, '')))->implode(' | '))->filter()->implode(PHP_EOL);
    $marketLines = $entryLines($about->markets ?: [], ['name', 'detail']);
    $valueLines = $entryLines($about->core_values ?: [], ['title', 'description']);
    $supportServiceIds = collect(data_get($profile, 'support.service_ids', []))->map(fn ($id) => (int) $id)->all();
@endphp

@section('content')
    <form method="POST" action="{{ route('admin.about.update') }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-xl-8">
                <x-card title="1. Hero & thư ngỏ">
                    <div class="row g-3">
                        <div class="col-md-6"><x-input name="hero_kicker" label="Nhãn Hero" :value="data_get($profile, 'hero.kicker')"/><x-input name="hero_title" label="Tiêu đề Hero" :value="$about->hero_title" required/><x-textarea name="hero_intro" label="Dẫn nhập Hero" :value="$about->hero_intro" rows="4"/><x-input name="hero_cta_label" label="Nhãn nút Hero" :value="data_get($profile, 'hero.cta_label')"/></div>
                        <div class="col-md-6"><x-input name="letter_title" label="Tiêu đề thư ngỏ" :value="$about->letter_title"/><x-textarea name="letter_content" label="Nội dung thư ngỏ" :value="$about->letter_content" rows="8"/><x-input name="letter_signature_name" label="Tên ký thư" :value="data_get($profile, 'letter.signature_name')"/><x-input name="letter_signature_tagline" label="Dòng dưới chữ ký" :value="data_get($profile, 'letter.signature_tagline')"/></div>
                    </div>
                </x-card>

                <x-card title="2. Giới thiệu doanh nghiệp" class="mt-3" :collapsible="true">
                    <x-input name="intro_eyebrow" label="Nhãn phần" :value="data_get($profile, 'company_intro.eyebrow')"/>
                    <x-textarea name="intro_title" label="Tiêu đề" :value="data_get($profile, 'company_intro.title')" rows="3"/>
                    <x-textarea name="intro_lead" label="Đoạn 1 (tên công ty được lấy tự động từ Cài đặt)" :value="data_get($profile, 'company_intro.lead')" rows="3"/>
                    <x-textarea name="intro_content" label="Đoạn 2" :value="data_get($profile, 'company_intro.content')" rows="4"/>
                    <x-textarea name="credentials_text" label="Chứng nhận / giấy phép" :value="$lineList(data_get($profile, 'company_intro.credentials', []))" rows="3"/>
                    <p class="form-text mb-0">Mỗi dòng là một chứng nhận hiển thị kèm dấu xác thực.</p>
                </x-card>

                <x-card title="3. Câu chuyện & quy trình hành trình" class="mt-3" :collapsible="true">
                    <x-input name="story_eyebrow" label="Nhãn Câu chuyện HG TRIP" :value="data_get($profile, 'story.eyebrow')"/>
                    <x-input name="story_title" label="Tiêu đề câu chuyện" :value="$about->story_title"/>
                    <x-textarea name="story_content" label="Nội dung câu chuyện" :value="$about->story_content" rows="6"/>
                    <x-textarea name="story_fact" label="Dòng nhấn" :value="data_get($profile, 'story.fact')" rows="2"/>
                    <div class="row g-3"><div class="col-md-6"><x-input name="story_photo_alt" label="Mô tả ảnh" :value="data_get($profile, 'story.photo_alt')"/></div><div class="col-md-6"><x-input name="story_photo_caption" label="Chú thích ảnh" :value="data_get($profile, 'story.photo_caption')"/></div></div>
                    <hr>
                    <x-input name="story_steps_eyebrow" label="Nhãn quy trình" :value="data_get($profile, 'story_steps.eyebrow')"/>
                    <x-textarea name="story_steps_intro" label="Dẫn nhập quy trình" :value="data_get($profile, 'story_steps.intro')" rows="3"/>
                    <x-textarea name="story_steps_text" label="Các bước hành trình" :value="$entryLines(data_get($profile, 'story_steps.items', []), ['title', 'description'])" rows="10"/>
                    <p class="form-text mb-0">Mỗi dòng: <code>Tiêu đề bước | Diễn giải</code>. Thứ tự dòng là thứ tự hiển thị.</p>
                </x-card>

                <x-card title="4. Thị trường & giá trị cốt lõi" class="mt-3" :collapsible="true">
                    <div class="row g-3"><div class="col-md-6"><x-input name="markets_eyebrow" label="Nhãn thị trường" :value="$about->markets_eyebrow"/><x-textarea name="markets_title" label="Tiêu đề thị trường" :value="$about->markets_title" rows="3"/><x-textarea name="markets_intro" label="Dẫn nhập thị trường" :value="$about->markets_intro" rows="3"/><x-textarea name="markets_text" label="Các ô thị trường" :value="$marketLines" rows="8"/><p class="form-text mb-0">Mỗi dòng: <code>Tên thị trường | Mô tả</code>.</p></div><div class="col-md-6"><x-input name="values_eyebrow" label="Nhãn giá trị cốt lõi" :value="data_get($profile, 'values.eyebrow')"/><x-textarea name="values_title" label="Tiêu đề giá trị cốt lõi" :value="data_get($profile, 'values.title')" rows="3"/><x-textarea name="values_intro" label="Dẫn nhập giá trị cốt lõi" :value="data_get($profile, 'values.intro')" rows="3"/><x-textarea name="core_values_text" label="Các giá trị cốt lõi" :value="$valueLines" rows="8"/><p class="form-text mb-0">Mỗi dòng: <code>Tiêu đề | Diễn giải</code>.</p></div></div>
                </x-card>

                <x-card title="5. Sản phẩm đặc trưng & hệ sinh thái dịch vụ" class="mt-3" :collapsible="true">
                    <x-input name="products_eyebrow" label="Nhãn sản phẩm" :value="data_get($profile, 'featured_products.eyebrow')"/>
                    <x-textarea name="products_title" label="Tiêu đề sản phẩm" :value="data_get($profile, 'featured_products.title')" rows="3"/>
                    <x-textarea name="products_intro" label="Dẫn nhập sản phẩm" :value="data_get($profile, 'featured_products.intro')" rows="3"/>
                    <x-textarea name="products_text" label="Các sản phẩm đặc trưng" :value="$entryLines(data_get($profile, 'featured_products.items', []), ['icon', 'title', 'description'])" rows="9"/>
                    <p class="form-text">Mỗi dòng: <code>icon Bootstrap | Tiêu đề | Mô tả</code>.</p>
                    <hr>
                    <x-input name="support_eyebrow" label="Nhãn hệ sinh thái dịch vụ" :value="data_get($profile, 'support.eyebrow')"/>
                    <x-textarea name="support_title" label="Tiêu đề hệ sinh thái dịch vụ" :value="data_get($profile, 'support.title')" rows="3"/>
                    <x-textarea name="support_intro" label="Dẫn nhập hệ sinh thái dịch vụ" :value="data_get($profile, 'support.intro')" rows="3"/>
                    <label class="form-label fw-semibold">Dịch vụ hiển thị</label>
                    <div class="row g-2">@foreach($services as $service)<div class="col-md-6"><label class="d-flex gap-2 align-items-start border rounded p-2 h-100"><input class="form-check-input mt-1" type="checkbox" name="support_service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, $supportServiceIds, true))><span><strong class="d-block">{{ $service->name }}</strong><small class="text-muted">{{ $service->description }}</small></span></label></div>@endforeach</div>
                    <p class="form-text mb-0">Nội dung từng dịch vụ lấy trực tiếp từ mục <strong>Dịch vụ</strong> trong quản trị; thứ tự theo trường thứ tự của dịch vụ.</p>
                </x-card>
            </div>

            <div class="col-xl-4">
                <x-card title="Hình ảnh trang Giới thiệu">
                    <x-image-upload name="hero_image" label="Ảnh Hero đầu trang" :value="$about->hero_image"/>
                    <p class="form-text mb-4">Banner toàn chiều ngang ở đầu trang.</p>
                    <x-image-upload name="background_image" label="Ảnh section Thư ngỏ" :value="$about->background_image"/>
                    <p class="form-text mb-4">Ảnh dọc nằm cạnh nội dung Thư ngỏ.</p>
                    <x-image-upload name="story_image" label="Ảnh Câu chuyện HG TRIP" :value="$about->story_image"/>
                    <p class="form-text mb-0">Ưu tiên ảnh ngang hoặc gần vuông để giữ chủ thể khi cắt ảnh.</p>
                </x-card>

                <x-card title="6. Lãnh đạo" class="mt-3" :collapsible="true">
                    <x-input name="leaders_eyebrow" label="Nhãn phần" :value="data_get($profile, 'leaders.eyebrow')"/>
                    <x-textarea name="leaders_title" label="Tiêu đề" :value="data_get($profile, 'leaders.title')" rows="2"/>
                    <x-textarea name="leaders_intro" label="Đoạn dẫn" :value="data_get($profile, 'leaders.intro')" rows="3"/>
                    <x-input name="ceo_role" label="Chức danh CEO" :value="data_get($profile, 'leaders.ceo_role')"/><x-input name="ceo_name" label="Tên CEO" :value="$about->ceo_name"/><x-textarea name="ceo_bio" label="Giới thiệu CEO" :value="$about->ceo_bio" rows="5"/>
                    <x-input name="deputy_role" label="Chức danh Phó giám đốc" :value="data_get($profile, 'leaders.deputy_role')"/><x-input name="deputy_name" label="Tên Phó giám đốc" :value="$about->deputy_name"/><x-textarea name="deputy_bio" label="Giới thiệu Phó giám đốc" :value="$about->deputy_bio" rows="5"/>
                </x-card>

                <x-card title="7. Khách hàng tiêu biểu" class="mt-3" :collapsible="true">
                    <x-input name="clients_eyebrow" label="Nhãn phần" :value="data_get($profile, 'clients.eyebrow')"/>
                    <x-textarea name="clients_title" label="Tiêu đề" :value="data_get($profile, 'clients.title')" rows="3"/>
                    <x-textarea name="clients_intro" label="Đoạn dẫn" :value="data_get($profile, 'clients.intro')" rows="3"/>
                    <x-textarea name="clients_text" label="Logo khách hàng" :value="$entryLines(data_get($profile, 'clients.items', []), ['name', 'image'])" rows="12"/>
                    <p class="form-text mb-0">Mỗi dòng: <code>Tên khách hàng | đường dẫn logo</code>. Tải logo vào Thư viện media rồi dán đường dẫn ảnh tại đây.</p>
                </x-card>

                <x-card title="8. Bộ máy & văn phòng" class="mt-3" :collapsible="true">
                    <x-input name="organisation_eyebrow" label="Nhãn phần" :value="data_get($profile, 'organisation.eyebrow')"/>
                    <x-textarea name="organisation_title" label="Tiêu đề" :value="data_get($profile, 'organisation.title')" rows="3"/>
                    <x-textarea name="organisation_intro" label="Đoạn dẫn" :value="data_get($profile, 'organisation.intro')" rows="4"/>
                    <x-input name="organisation_cta_label" label="Nhãn nút liên hệ" :value="data_get($profile, 'organisation.cta_label')"/>
                    <x-textarea name="departments_text" label="Các phòng ban" :value="$lineList(data_get($profile, 'organisation.departments', []))" rows="5"/>
                    <x-textarea name="offices_text" label="Văn phòng / đại diện" :value="$entryLines(data_get($profile, 'organisation.offices', []), ['icon', 'label', 'address'])" rows="8"/>
                    <p class="form-text mb-0">Văn phòng: <code>icon Bootstrap | Nhãn | Địa chỉ</code>.</p>
                </x-card>

                <x-card title="9. Dải liên hệ, danh sách & SEO" class="mt-3" :collapsible="true">
                    <x-input name="contact_tagline" label="Tagline cạnh tên công ty" :value="data_get($profile, 'contact.tagline')"/>
                    <p class="form-text">Tên công ty, số điện thoại và email lấy từ phần Cài đặt chung/Liên hệ.</p>
                    <x-textarea name="commitments_text" label="Cam kết" :value="$lineList($about->commitments ?: [])" rows="5"/>
                    <x-textarea name="audiences_text" label="Khách hàng hướng tới" :value="$lineList($about->audiences ?: [])" rows="5"/>
                    <x-input name="seo_title" label="SEO title" :value="$about->seo_title"/>
                    <x-textarea name="seo_description" label="SEO description" :value="$about->seo_description" rows="4"/>
                </x-card>
            </div>

            <div class="col-12 text-end">
                <button class="btn btn-primary">Lưu toàn bộ trang Giới thiệu</button>
            </div>
        </div>
    </form>
@endsection
