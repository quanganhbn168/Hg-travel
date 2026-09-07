@extends('layouts.master')

@section('title', ($service['seo_title'] ?: $service['title']).' | '.$siteSettings->site_name)
@section('meta_description', $service['seo_description'] ?: $service['intro'])
@section('meta_keywords', $service['title'].', dịch vụ du lịch, '.$siteSettings->site_name)
@if ($service['cover_image'])
@section('og_image', $service['cover_image'])
@endif
@section('body_class', 'services-page service-detail-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/services.css') }}?v={{ filemtime(public_path('css/services.css')) }}">
@endpush

@section('breadcrumb')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item"><a href="{{ route('services.index') }}">Dịch vụ</a></li><li class="breadcrumb-item"><a href="{{ route('services.category', ['category' => $category['slug']]) }}">{{ $category['label'] }}</a></li><li class="breadcrumb-item active" aria-current="page">{{ $service['title'] }}</li></ol></nav>
@endsection

@section('content')
    <section class="services-detail-hero page-banner" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');">
        <div class="container">
            <span class="section-eyebrow section-eyebrow-light">{{ $category['label'] }}</span>
            <h1>{{ $service['title'] }}</h1>
            <p>{{ $service['description'] }}</p>
        </div>
    </section>
    <div class="breadcrumb-bar"><div class="container">@yield('breadcrumb')</div></div>

    <section class="section-space service-detail-content">
        <div class="container">
            <div class="row g-4 g-xl-5 align-items-start">
                <div class="col-lg-8">
                    <div class="service-detail-overview">
                        <span class="section-eyebrow">Dịch vụ HG</span>
                        <h2>Điều gì làm nên một phương án phù hợp?</h2>
                        <p class="service-detail-lead">{{ $service['intro'] }}</p>
                        <div class="service-benefit-list">
                            @foreach ($service['benefits'] as $benefit)
                                <div class="service-benefit-item"><span><i class="bi bi-check2"></i></span><strong>{{ $benefit }}</strong></div>
                            @endforeach
                        </div>
                    </div>

                    <div class="service-process">
                        <div class="service-subheading"><span class="section-eyebrow">Cách HG đồng hành</span><h2>Từ nhu cầu đến lúc hoàn tất</h2></div>
                        <div class="row g-3">
                            <div class="col-md-4"><article class="service-process-step"><span>01</span><h3>Lắng nghe nhu cầu</h3><p>Trao đổi mục tiêu, thời gian, quy mô và những ưu tiên thật sự của chuyến đi.</p></article></div>
                            <div class="col-md-4"><article class="service-process-step"><span>02</span><h3>Đề xuất phương án</h3><p>HG tư vấn lựa chọn phù hợp, giải thích rõ các hạng mục và điều kiện đi kèm.</p></article></div>
                            <div class="col-md-4"><article class="service-process-step"><span>03</span><h3>Điều phối & đồng hành</h3><p>Đầu mối HG theo sát phần việc đã thống nhất và hỗ trợ khi anh/chị cần.</p></article></div>
                        </div>
                    </div>
                </div>

                <aside class="col-lg-4">
                    <div class="service-contact-card">
                        <span class="service-card-icon"><i class="bi {{ $service['icon'] }}"></i></span>
                        <span class="service-card-category">{{ $category['label'] }}</span>
                        <h2>{{ $service['title'] }}</h2>
                        <p>Chia sẻ nhu cầu của anh/chị để HG tư vấn đúng phần cần thiết, không thêm những lựa chọn không phù hợp.</p>
                        <a class="btn btn-brand w-100" href="{{ route('contact', ['subject' => $service['title']]) }}">Nhận tư vấn dịch vụ <i class="bi bi-arrow-right"></i></a>
                        <a class="service-contact-back" href="{{ route('services.category', ['category' => $category['slug']]) }}"><i class="bi bi-arrow-left"></i> Xem các dịch vụ cùng nhóm</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if ($relatedServices)
        <section class="section-space service-related-section">
            <div class="container">
                <div class="section-heading services-section-heading"><div><span class="section-eyebrow">Có thể kết hợp</span><h2 class="section-title">Dịch vụ liên quan</h2></div><a class="section-more" href="{{ route('services.category', ['category' => $category['slug']]) }}">Xem cả nhóm <i class="bi bi-arrow-up-right"></i></a></div>
                <div class="row g-3 g-lg-4">
                    @foreach ($relatedServices as $related)
                        <div class="col-md-6 col-xl-4">
                            @include('frontend.services.partials.card', ['service' => $related, 'number' => $loop->iteration, 'compact' => true])
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="services-cta">
        <div class="container">
            <div><span>Hành trình theo cách của bạn</span><h2>Một nhu cầu cụ thể, một phương án rõ ràng.</h2></div>
            <a class="btn btn-brand btn-brand-gold" href="{{ route('contact') }}">Bắt đầu trao đổi <i class="bi bi-arrow-right"></i></a>
        </div>
    </section>
@endsection
