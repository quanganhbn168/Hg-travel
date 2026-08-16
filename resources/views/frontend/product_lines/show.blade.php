@extends('layouts.master')

@section('title', $productLine['seo_title'].' | '.$siteSettings->site_name)
@section('meta_description', $productLine['seo_description'])
@section('meta_keywords', $productLine['name'].', giải pháp du lịch, '.$siteSettings->site_name)
@section('canonical', route('product-lines.show', ['productLine' => $productLine['slug']]))
@section('body_class', 'product-line-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/tour.css') }}?v={{ filemtime(public_path('css/tour.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/product-lines.css') }}?v={{ filemtime(public_path('css/product-lines.css')) }}">
@endpush

@section('structured_data')
    <script type="application/ld+json">
        {!! $structuredData !!}
    </script>
@endsection

@section('content')
    <section class="product-line-hero page-banner" style="--page-banner-image: url('{{ $productLine['hero_image'] ?: $siteAssets['page_banner'] }}');">
        <div class="container">
            <nav class="product-line-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a><span>/</span><span>Giải pháp du lịch</span><span>/</span><strong>{{ $productLine['name'] }}</strong>
            </nav>
            <div class="product-line-hero-copy">
                <span class="section-eyebrow section-eyebrow-light">{{ $productLine['kicker'] ?: 'Giải pháp du lịch của HG' }}</span>
                <h1>{{ $productLine['name'] }}</h1>
                <p>{{ $productLine['summary'] }}</p>
            </div>
        </div>
    </section>

    <section class="section-space product-line-overview">
        <div class="container">
            <div class="row g-4 g-xl-5 align-items-start">
                <div class="col-lg-7">
                    <span class="section-eyebrow">Một giải pháp rõ ràng</span>
                    <h2 class="section-title">Bắt đầu từ mục tiêu thật của chuyến đi.</h2>
                    <p class="product-line-lead">{{ $productLine['description'] }}</p>
                    @if ($productLine['benefits'])
                        <div class="product-line-benefits">
                            @foreach ($productLine['benefits'] as $benefit)
                                <div class="product-line-benefit"><i class="bi bi-check2-circle"></i><span>{{ $benefit }}</span></div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="col-lg-5">
                    <aside class="product-line-contact-card">
                        <span class="product-line-contact-icon"><i class="bi {{ $productLine['icon'] }}"></i></span>
                        <span class="section-eyebrow">Trao đổi cùng HG</span>
                        <h2>{{ $productLine['name'] }}</h2>
                        <p>Chia sẻ quy mô, thời gian và mục tiêu của anh/chị để HG đề xuất phương án phù hợp.</p>
                        <a class="btn btn-brand w-100" href="{{ route('contact', ['subject' => $productLine['name']]) }}">Nhận tư vấn giải pháp <i class="bi bi-arrow-right"></i></a>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    @if ($productLine['tours'])
        <section class="section-space product-line-tours">
            <div class="container">
                <div class="section-heading product-line-section-heading">
                    <div><span class="section-eyebrow">Hành trình phù hợp</span><h2 class="section-title">Những lựa chọn có thể bắt đầu ngay</h2><p class="section-description">Các hành trình được kết nối với giải pháp này. Lịch trình vẫn có thể điều chỉnh theo nhu cầu thực tế.</p></div>
                    <a class="section-more" href="{{ route('tours.index') }}">Xem tất cả hành trình <i class="bi bi-arrow-up-right"></i></a>
                </div>
                <div class="row g-4">
                    @foreach ($productLine['tours'] as $tour)
                        <div class="col-md-6 col-xl-4">@include('frontend.tours.partials.card', ['tour' => $tour])</div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($productLine['services'])
        <section class="section-space product-line-services">
            <div class="container">
                <div class="section-heading product-line-section-heading"><div><span class="section-eyebrow">Dịch vụ đồng hành</span><h2 class="section-title">Các hạng mục có thể kết hợp</h2></div><a class="section-more" href="{{ route('services.index') }}">Xem toàn bộ dịch vụ <i class="bi bi-arrow-up-right"></i></a></div>
                <div class="row g-3 g-lg-4">
                    @foreach ($productLine['services'] as $service)
                        <div class="col-md-6 col-xl-3"><a class="product-line-service-card" href="{{ route('services.show', ['service' => $service['slug']]) }}"><span class="product-line-service-icon"><i class="bi {{ $service['icon'] }}"></i></span><small>{{ $service['category'] }}</small><h3>{{ $service['title'] }}</h3><p>{{ $service['description'] }}</p><span class="product-line-service-link">Xem chi tiết <i class="bi bi-arrow-up-right"></i></span></a></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="product-line-cta">
        <div class="container"><div><span>Thiết kế theo mục tiêu của bạn</span><h2>Chưa có lịch trình phù hợp? HG sẽ cùng anh/chị xây dựng từ đầu.</h2></div><a class="btn btn-brand btn-brand-gold" href="{{ route('contact', ['subject' => $productLine['name']]) }}">Bắt đầu trao đổi <i class="bi bi-arrow-right"></i></a></div>
    </section>
@endsection
