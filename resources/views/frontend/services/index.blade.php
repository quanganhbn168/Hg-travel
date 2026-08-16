@extends('layouts.master')

@section('title', 'Dịch vụ du lịch | '.$siteSettings->site_name)
@section('meta_description', 'Dịch vụ thiết kế hành trình, visa, vé máy bay, khách sạn, vận chuyển, hướng dẫn viên và sự kiện từ '.$siteSettings->site_name.'.')
@section('canonical', route('services.index'))
@section('body_class', 'services-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/services.css') }}?v={{ filemtime(public_path('css/services.css')) }}">
@endpush

@section('breadcrumb')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active" aria-current="page">Dịch vụ</li></ol></nav>
@endsection

@section('content')
    <section class="services-hero page-banner" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');">
        <div class="container">
            <div class="row align-items-end g-4">
                <div class="col-lg-8">
                    <span class="section-eyebrow section-eyebrow-light">Dịch vụ đồng hành</span>
                    <h1>Mọi phần của hành trình, một đầu mối đồng hành.</h1>
                    <p>Từ ý tưởng ban đầu đến lúc trở về, HG kết nối những hạng mục cần thiết thành một kế hoạch rõ ràng, vừa vặn và dễ yên tâm.</p>
                </div>
                <div class="col-lg-4">
                    <div class="services-hero-note">
                        <span>Danh mục hiện có</span>
                        <strong>{{ str_pad((string) count($services), 2, '0', STR_PAD_LEFT) }} dịch vụ</strong>
                        <small>Chọn đúng nhóm bạn đang cần để xem phương án phù hợp.</small>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="breadcrumb-bar"><div class="container">@yield('breadcrumb')</div></div>

    <section class="section-space services-category-overview">
        <div class="container">
            <div class="section-heading services-section-heading">
                <div>
                    <span class="section-eyebrow">Chọn theo nhu cầu</span>
                    <h2 class="section-title">Bắt đầu từ phần bạn quan tâm nhất</h2>
                    <p class="section-description">Mỗi nhóm dịch vụ có một vai trò khác nhau trong hành trình. Anh/chị có thể xem tổng quan hoặc đi thẳng vào một hạng mục cụ thể.</p>
                </div>
                <span class="services-heading-index">HG / SERVICES</span>
            </div>

            <div class="row g-3 g-lg-4">
                @foreach ($categories as $category)
                    <div class="col-md-4">
                        <a class="service-category-card" href="{{ route('services.category', ['category' => $category['slug']]) }}">
                            <span class="service-category-icon"><i class="bi {{ $category['icon'] }}"></i></span>
                            <span class="service-category-kicker">{{ $category['kicker'] }}</span>
                            <strong>{{ $category['label'] }}</strong>
                            <small>{{ $category['description'] }}</small>
                            <span class="service-category-footer"><span>{{ $category['service_count'] }} dịch vụ</span><i class="bi bi-arrow-up-right"></i></span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-space services-catalog-section">
        <div class="container">
            <div class="section-heading services-section-heading">
                <div>
                    <span class="section-eyebrow">Danh mục dịch vụ</span>
                    <h2 class="section-title">Một hệ dịch vụ được thiết kế để đi cùng nhau</h2>
                </div>
                <span class="services-heading-note">Tư vấn theo từng nhu cầu riêng</span>
            </div>
            <div class="row g-3 g-lg-4">
                @foreach ($services as $service)
                    <div class="col-md-6 col-xl-4">
                        @include('frontend.services.partials.card', ['service' => $service, 'number' => $loop->iteration])
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="services-cta">
        <div class="container">
            <div>
                <span>Không cần chọn một mình</span>
                <h2>Chưa biết nên bắt đầu từ dịch vụ nào?</h2>
                <p>Chia sẻ điểm đến, thời gian và mục tiêu chuyến đi. HG sẽ giúp anh/chị ráp thành phương án phù hợp.</p>
            </div>
            <a class="btn btn-brand btn-brand-gold" href="{{ route('contact') }}">Trao đổi cùng HG <i class="bi bi-arrow-right"></i></a>
        </div>
    </section>
@endsection
