@extends('layouts.master')

@section('title', $page['title'].' | '.$siteSettings->site_name)
@section('meta_description', $page['description'])
@section('canonical', $canonical)
@section('og_title', $page['title'])
@section('og_description', $page['description'])
@section('og_image', $coverImageUrl ?: asset('images/logo-hgtrip.png'))
@section('twitter_title', $page['title'])
@section('twitter_description', $page['description'])
@section('twitter_image', $coverImageUrl ?: asset('images/logo-hgtrip.png'))
@section('body_class', 'destination-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/tour.css') }}?v={{ filemtime(public_path('css/tour.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/destination.css') }}?v={{ filemtime(public_path('css/destination.css')) }}">
@endpush

@section('structured_data')
    <script type="application/ld+json">
        {!! $structuredData !!}
    </script>
@endsection

@section('content')
    <section class="destination-hero page-banner" @if($coverImageUrl) style="--page-banner-image: url('{{ $coverImageUrl }}');" @endif>
        <div class="container">
            <nav class="destination-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                @foreach($breadcrumb as $item)
                    <span aria-hidden="true">/</span>
                    <a href="{{ $item['url'] }}" @if($loop->last) aria-current="page" @endif>{{ $item['name'] }}</a>
                @endforeach
            </nav>
            <div class="destination-hero-copy">
                <span class="section-eyebrow">Điểm đến</span>
                <h1 class="section-title">{{ $destination->name }}</h1>
                @if($destination->summary)<p class="section-description">{{ $destination->summary }}</p>@endif
            </div>
        </div>
    </section>

    @if($destination->description)
        <section class="section-space destination-introduction">
            <div class="container"><div class="destination-rich-content">{!! $destination->description !!}</div></div>
        </section>
    @endif

    @if($children)
        <section class="section-space destination-children">
            <div class="container">
                <div class="section-heading"><span class="section-eyebrow">Khám phá sâu hơn</span><h2 class="section-title">Các điểm đến trong {{ $destination->name }}</h2></div>
                <div class="row g-4">
                    @foreach($children as $child)
                        <div class="col-md-6 col-xl-3"><article class="destination-child-card"><a href="{{ $child['url'] }}" class="destination-child-image">@if($child['image_url'])<img src="{{ $child['image_url'] }}" alt="{{ $child['name'] }}" loading="lazy">@else<div class="home-placeholder-image"><i class="bi bi-geo-alt"></i></div>@endif</a><div class="destination-child-body"><h3><a href="{{ $child['url'] }}">{{ $child['name'] }}</a></h3><span>{{ $child['tour_count'] }} hành trình</span>@if($child['summary'])<p>{{ $child['summary'] }}</p>@endif</div></article></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section-space destination-tours">
        <div class="container">
            <div class="section-heading"><span class="section-eyebrow">Hành trình phù hợp</span><h2 class="section-title">Tour tại {{ $destination->name }}</h2><p class="section-description">{{ $tourCount }} hành trình đang được mở bán và sẵn sàng để bạn khám phá.</p></div>
            <div class="row g-4">
                @forelse($tours as $tour)
                    <div class="col-md-6 col-xl-4">@include('frontend.tours.partials.card', ['tour' => $tour])</div>
                @empty
                    <div class="col-12"><div class="home-empty-state"><i class="bi bi-map"></i><strong>Tour đang được cập nhật</strong><span>Hành trình tại điểm đến này sẽ sớm được bổ sung.</span></div></div>
                @endforelse
            </div>
            @if($tours->hasPages())<div class="mt-5">{{ $tours->links() }}</div>@endif
        </div>
    </section>
@endsection
