@extends('layouts.admin')

@section('title', 'Cài đặt SEO')
@section('page-title', 'Cài đặt')

@section('content')
    <x-admin.settings-layout title="Cài đặt SEO" description="Thiết lập các giá trị SEO mặc định; nội dung SEO riêng của từng trang, tour hoặc bài viết sẽ được ưu tiên.">
    <x-slot:actions>
        @if ($canUpdateSettings)
            <button form="admin-settings-seo-form" class="btn btn-outline-primary"><i class="bi bi-check2 me-1"></i>Lưu SEO mặc định</button>
            <button form="admin-settings-robots-form" class="btn btn-primary"><i class="bi bi-file-earmark-text me-1"></i>Lưu robots.txt</button>
        @endif
    </x-slot:actions>
    <form id="admin-settings-seo-form" action="{{ route('admin.settings.seo.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="SEO mặc định">
                    <x-input name="seo_title" label="SEO title mặc định" :value="$settings->seo_title" />
                    <x-textarea name="seo_description" label="SEO description mặc định" :value="$settings->seo_description" rows="4" />
                    <x-textarea name="seo_keywords" label="SEO keywords mặc định" :value="$settings->seo_keywords" rows="3" />
                    <p class="text-muted small mb-0">Trang, tour và bài viết có SEO riêng sẽ được ưu tiên hơn các giá trị mặc định này.</p>
                </x-card>
            </div>
        </div>
    </form>
    <form id="admin-settings-robots-form" action="{{ route('admin.settings.robots.update') }}" method="POST">
        @csrf @method('PUT')
        <input type="hidden" name="robots_revision" value="{{ old('robots_revision', $robots['revision']) }}">
        <div class="row g-3" id="robots-settings">
            <div class="col-xl-8">
                <x-card type="primary" title="File robots.txt">
                    <p class="text-muted small">Chỉnh trực tiếp file <code>public/robots.txt</code>. Nhấn <strong>Lưu robots.txt</strong> để ghi file; nút Lưu SEO mặc định không thay đổi nội dung này.</p>
                    @unless ($robots['exists'])
                        <div class="alert alert-warning small" role="status">Chưa có file robots.txt. Nội dung bên dưới là mẫu cho phép public và chặn admin; nhấn Lưu robots.txt để tạo file.</div>
                    @endunless
                    <x-textarea name="robots_content" id="robots_content" label="Nội dung robots.txt" :value="$robots['content']" rows="10" maxlength="50000" spellcheck="false" aria-describedby="robots-help" :readonly="! $canUpdateSettings" required />
                    @error('robots_revision')<div class="text-danger small mb-2" role="alert">{{ $message }}</div>@enderror
                    <p id="robots-help" class="form-text mb-2">Tối đa 50.000 ký tự. Mẫu mặc định cho phép trang public, ảnh, CSS/JS và chỉ chặn admin. Sửa robots.txt không thay đổi meta index/follow của layout. Khi đổi domain, cập nhật cả dòng Sitemap.</p>
                    @if ($robots['exists'])
                        <a href="{{ route('robots') }}" target="_blank" rel="noopener" class="small"><i class="bi bi-box-arrow-up-right me-1"></i>Xem file robots.txt đang phục vụ</a>
                    @endif
                </x-card>
            </div>
        </div>
    </form>
    <div class="row g-3 mt-1" id="sitemap-settings">
        <div class="col-xl-8">
            <x-card type="primary" title="Sitemap">
                <p class="text-muted small">Sitemap được tạo tự động mỗi giờ. Nhấn nút bên dưới để tạo lại ngay (ví dụ sau khi đổi domain hoặc thêm nhiều nội dung). URL trong sitemap lấy theo <code>APP_URL</code>: <code>{{ rtrim(config('app.url'), '/') }}</code></p>
                @if (is_file(public_path('sitemap.xml')))
                    <p class="small mb-2">Cập nhật lần cuối: <strong>{{ \Illuminate\Support\Carbon::createFromTimestamp(filemtime(public_path('sitemap.xml')))->format('d/m/Y H:i') }}</strong>
                        · <a href="{{ route('sitemap') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i>Xem sitemap.xml</a></p>
                @else
                    <div class="alert alert-warning small" role="status">Chưa có file sitemap.xml.</div>
                @endif
                @if ($canUpdateSettings)
                    <form action="{{ route('admin.settings.sitemap.generate') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-primary"><i class="bi bi-diagram-3 me-1"></i>Tạo lại sitemap</button>
                    </form>
                @endif
            </x-card>
        </div>
    </div>
    </x-admin.settings-layout>
@endsection
