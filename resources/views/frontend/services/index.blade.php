@extends('layouts.master')

@section('title', 'Dịch vụ du lịch | '.$siteSettings->site_name)
@section('meta_description', 'Dịch vụ visa, vé máy bay và khách sạn được tư vấn rõ ràng bởi '.$siteSettings->site_name.'.')
@section('canonical', route('services.index'))
@section('body_class', 'services-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/services.css') }}">
@endpush

@section('breadcrumb')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active" aria-current="page">Dịch vụ</li></ol></nav>
@endsection

@section('content')
    <section class="services-hero page-banner" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');"><div class="container"><div class="services-hero-copy"><span class="section-eyebrow section-eyebrow-light">Dịch vụ đồng hành</span><h1>Chuẩn bị hành trình nhẹ nhàng hơn cùng HG</h1><p>Từ hồ sơ visa, chuyến bay đến nơi lưu trú, HG giúp bạn kết nối từng phần của chuyến đi một cách rõ ràng và thuận tiện.</p></div></div></section>
    <div class="breadcrumb-bar"><div class="container">@yield('breadcrumb')</div></div>

    <section class="section-space pt-0"><div class="container"><nav class="services-jump-nav" aria-label="Đi đến dịch vụ">@foreach ($services as $service)<a href="#{{ $service['slug'] }}"><i class="bi {{ $service['icon'] }}"></i>{{ $service['title'] }}</a>@endforeach</nav>
        <div class="services-list">@foreach ($services as $service)<article class="service-detail" id="{{ $service['slug'] }}"><div class="row g-4 align-items-center"><div class="col-lg-5"><div class="service-detail-intro"><span class="service-detail-icon"><i class="bi {{ $service['icon'] }}"></i></span><span class="section-eyebrow">Dịch vụ HG</span><h2>{{ $service['title'] }}</h2><p>{{ $service['intro'] }}</p></div></div><div class="col-lg-7"><div class="service-detail-panel"><h3>HG hỗ trợ bạn</h3><ul>@foreach ($service['benefits'] as $benefit)<li><i class="bi bi-check2"></i>{{ $benefit }}</li>@endforeach</ul><div class="service-detail-actions"><a class="btn btn-brand" href="{{ route('contact', ['subject' => $service['title']]) }}">Nhận tư vấn {{ $service['title'] }} <i class="bi bi-arrow-right"></i></a><a class="btn btn-outline-brand" href="{{ route('tours.index') }}">Xem tour phù hợp</a></div></div></div></div></article>@endforeach</div>
    </div></section>

    <section class="services-cta"><div class="container"><div><span>Hành trình theo cách của bạn</span><h2>Cần kết hợp nhiều dịch vụ cho một chuyến đi?</h2></div><a class="btn btn-brand btn-brand-gold" href="{{ route('contact') }}">Trao đổi cùng HG <i class="bi bi-arrow-right"></i></a></div></section>
@endsection
