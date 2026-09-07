@extends('layouts.master')

@section('title', $category['label'].' | Dịch vụ | '.$siteSettings->site_name)
@section('meta_description', $category['description'])
@section('body_class', 'services-page services-category-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/services.css') }}?v={{ filemtime(public_path('css/services.css')) }}">
@endpush

@section('breadcrumb')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item"><a href="{{ route('services.index') }}">Dịch vụ</a></li><li class="breadcrumb-item active" aria-current="page">{{ $category['label'] }}</li></ol></nav>
@endsection

@section('content')
    <section class="services-hero services-category-hero page-banner" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');">
        <div class="container">
            <span class="section-eyebrow section-eyebrow-light">{{ $category['kicker'] }}</span>
            <h1>{{ $category['label'] }}</h1>
            <p>{{ $category['description'] }}</p>
        </div>
    </section>
    <div class="breadcrumb-bar"><div class="container">@yield('breadcrumb')</div></div>

    <section class="section-space services-category-body">
        <div class="container">
            <nav class="services-filter-nav" aria-label="Các nhóm dịch vụ">
                <a href="{{ route('services.index') }}">Tất cả dịch vụ</a>
                @foreach ($categories as $item)
                    <a class="{{ $item['slug'] === $category['slug'] ? 'is-active' : '' }}" href="{{ route('services.category', ['category' => $item['slug']]) }}">{{ $item['label'] }} <small>{{ $item['service_count'] }}</small></a>
                @endforeach
            </nav>

            <div class="services-category-intro">
                <div>
                    <span class="section-eyebrow">{{ str_pad((string) count($services), 2, '0', STR_PAD_LEFT) }} hạng mục</span>
                    <h2>Những dịch vụ thuộc nhóm {{ $category['label'] }}</h2>
                </div>
                <p>{{ $category['description'] }} Đội ngũ HG sẽ tư vấn phần cần thiết trước, sau đó kết nối thêm các hạng mục liên quan nếu hành trình của anh/chị cần.</p>
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
                <span>Thiết kế theo hành trình thật</span>
                <h2>Cần kết hợp nhiều hạng mục trong cùng một chuyến đi?</h2>
            </div>
            <a class="btn btn-brand btn-brand-gold" href="{{ route('contact') }}">Nhận tư vấn <i class="bi bi-arrow-right"></i></a>
        </div>
    </section>
@endsection
