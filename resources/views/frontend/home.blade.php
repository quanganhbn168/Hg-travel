@extends('layouts.master')

@section('title', $siteSettings->seo_title ?: 'Khám phá những hành trình đáng nhớ')
@section('meta_description', $siteSettings->seo_description ?: 'Khám phá các điểm đến, tour du lịch và cảm hứng hành trình cùng ' . $siteSettings->site_name . '.')
@section('meta_keywords', $siteSettings->seo_keywords ?: 'du lịch, tour du lịch, điểm đến, khám phá thế giới')
@section('body_class', 'home-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('vendor/aos/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3.3.1/dist/css/glightbox.min.css">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tour.css') }}?v={{ filemtime(public_path('css/tour.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('js/home-swiper.js') }}"></script>
    <script src="{{ asset('js/home.js') }}?v={{ filemtime(public_path('js/home.js')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/glightbox@3.3.1/dist/js/glightbox.min.js"></script>
    <script src="{{ asset('js/home-gallery.js') }}"></script>
@endpush

@section('structured_data')
    <script type="application/ld+json">{!! $structuredData !!}</script>
@endsection

@section('content')
    <section class="home-swiper swiper" data-home-swiper aria-label="Các hành trình nổi bật">
        @if ($heroSlides)
            <h1 class="visually-hidden">{{ $siteSettings->site_name }} - {{ $siteSettings->seo_title ?: 'Khám phá những hành trình đáng nhớ' }}</h1>
            <div class="swiper-wrapper">
                @foreach ($heroSlides as $slide)
                    <article class="swiper-slide home-hero" aria-roledescription="slide" aria-label="{{ $loop->iteration }} / {{ count($heroSlides) }}">
                        @if ($slide['image_url'])
                            <img class="home-hero-image" src="{{ $slide['image_url'] }}" alt="{{ $slide['title'] }}" width="1920" height="760" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                        @endif
                        <div class="home-hero-shade"></div>
                        <div class="container"><div class="home-hero-content">
                            <span class="home-hero-eyebrow">{{ $slide['eyebrow'] }}</span>
                            <h2 class="home-hero-title">{{ $slide['title'] }}</h2>
                            <p class="home-hero-description">{{ $slide['description'] }}</p>
                            <div class="home-hero-actions"><a class="btn btn-brand btn-lg" href="{{ $slide['button_url'] }}">{{ $slide['button_label'] }} <i class="bi bi-arrow-right"></i></a><a class="btn btn-outline-light btn-lg rounded-pill" href="{{ route('about') }}">Về chúng tôi</a></div>
                        </div></div>
                    </article>
                @endforeach
            </div>
            @if (count($heroSlides) > 1)
                <div class="home-swiper-controls container" aria-label="Điều khiển slider">
                    <div class="home-swiper-pagination" data-swiper-pagination>@foreach ($heroSlides as $slide)<button class="home-swiper-dot {{ $loop->first ? 'is-active' : '' }}" type="button" data-swiper-dot="{{ $loop->index }}" aria-label="Đến slide {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>@endforeach</div>
                    <div class="home-swiper-navigation"><button type="button" data-swiper-prev aria-label="Slide trước"><i class="bi bi-arrow-left"></i></button><button type="button" data-swiper-next aria-label="Slide tiếp theo"><i class="bi bi-arrow-right"></i></button></div>
                </div>
            @endif
        @else
            <article class="home-hero home-hero-fallback">
                @if ($heroFallback['image_url'])<img class="home-hero-image" src="{{ $heroFallback['image_url'] }}" alt="{{ $siteSettings->site_name }}" width="1920" height="760" fetchpriority="high">@endif
                <div class="home-hero-shade"></div>
                <div class="container"><div class="home-hero-content">
                    <span class="home-hero-eyebrow">{{ $heroFallback['eyebrow'] }}</span><h1 class="home-hero-title">{{ $heroFallback['title'] }}</h1><p class="home-hero-description">{{ $heroFallback['description'] }}</p>
                    <div class="home-hero-actions"><a class="btn btn-brand btn-lg" href="{{ $heroFallback['button_url'] }}">{{ $heroFallback['button_label'] }} <i class="bi bi-arrow-right"></i></a><a class="btn btn-outline-light btn-lg rounded-pill" href="{{ route('about') }}">Về chúng tôi</a></div>
                </div></div>
            </article>
        @endif
    </section>

    <section class="home-search-section" aria-label="Tìm kiếm hành trình" data-aos="fade-up">
        <div class="container">
            <div class="home-search-panel">
                <div class="home-search-heading"><span class="section-eyebrow">Tìm hành trình</span><h2>Chuyến đi tiếp theo của bạn bắt đầu từ đây</h2></div>
                <form action="{{ route('tours.index') }}" method="GET">
                    <div class="row align-items-end g-3">
                        <div class="col-lg-4 col-md-6"><label class="form-label" for="home-search">Bạn muốn đi đâu?</label><input class="form-control" id="home-search" type="search" name="q" value="{{ request('q') }}" placeholder="Tên điểm đến, tên tour..."></div>
                        <div class="col-lg-3 col-md-6"><label class="form-label" for="home-destination">Điểm đến</label><select class="form-select" id="home-destination" name="destination"><option value="">Tất cả điểm đến</option>@foreach ($destinationOptions as $destination)<option value="{{ $destination->slug }}" @selected(request('destination') === $destination->slug)>{{ $destination->name }}</option>@endforeach</select></div>
                        <div class="col-lg-2 col-md-6"><label class="form-label" for="home-duration">Thời lượng</label><select class="form-select" id="home-duration" name="duration"><option value="">Mọi thời lượng</option><option value="1-3">1 - 3 ngày</option><option value="4-7">4 - 7 ngày</option><option value="8+">Trên 7 ngày</option></select></div>
                        <div class="col-lg-2 col-md-6"><label class="form-label" for="home-budget">Ngân sách</label><select class="form-select" id="home-budget" name="budget"><option value="">Mọi mức giá</option><option value="under-5">Dưới 5 triệu</option><option value="5-10">5 - 10 triệu</option><option value="over-10">Trên 10 triệu</option></select></div>
                        <div class="col-lg-1 col-md-6"><button class="btn btn-brand w-100 home-search-submit" type="submit" aria-label="Tìm tour"><i class="bi bi-arrow-right"></i><span class="d-lg-none ms-2">Tìm tour</span></button></div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="section-space home-focus-section" aria-labelledby="focus-tour-types-title" data-aos="fade-up">
        <div class="container">
            <div class="section-heading home-focus-heading"><div><span class="section-eyebrow">Loại hình tour</span><h2 id="focus-tour-types-title" class="section-title">Chọn cách bạn muốn đi</h2></div><p class="section-description">Khám phá các loại hình tour được HG tuyển chọn theo nhịp điệu và trải nghiệm bạn mong muốn.</p></div>
            <div class="home-focus-grid">
                @foreach ($tourTypes as $type)
                    <a class="home-focus-card" href="{{ $type['url'] }}">
                        @if ($type['cover_image_url'])
                            <img class="home-focus-card-image" src="{{ $type['cover_image_url'] }}" alt="{{ $type['title'] }}" loading="lazy">
                        @endif
                        <span class="home-focus-card-shade" aria-hidden="true"></span>
                        <div class="home-focus-card-copy"><strong>{{ $type['title'] }}</strong>@if ($type['description'])<div class="home-focus-card-description">{!! $type['description'] !!}</div>@endif</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-space home-surface-section" aria-labelledby="featured-tours-title" data-aos="fade-up">
        <div class="container"><div class="section-heading d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3"><div><span class="section-eyebrow">Tour nổi bật</span><h2 id="featured-tours-title" class="section-title">Những hành trình tiêu biểu</h2><p class="section-description">Những hành trình tiêu biểu đang mở bán, cân bằng giữa trải nghiệm, thời gian và chất lượng dịch vụ.</p></div><a class="section-more" href="{{ route('tours.index') }}">Xem tất cả hành trình <i class="bi bi-arrow-up-right"></i></a></div>
            <div class="home-featured-tours-grid">@forelse ($featuredTours as $tour)<div class="home-featured-tour-item">@include('frontend.tours.partials.card', ['tour' => $tour])</div>@empty<div class="home-empty-state"><i class="bi bi-map"></i><strong>Tour nổi bật đang được cập nhật</strong><span>Hành trình sẽ xuất hiện tại đây khi được đánh dấu nổi bật trong hệ thống quản trị.</span></div>@endforelse</div>
        </div>
    </section>

    <section class="section-space home-promo-section {{ $promotionBackdropUrl ? 'has-promo-backdrop' : '' }}" aria-labelledby="promotion-title" data-aos="fade-up" data-home-parallax>
        @if ($promotionBackdropUrl)
            <div class="home-parallax-backdrop home-promo-backdrop" aria-hidden="true" style="--promo-image: url('{{ $promotionBackdropUrl }}');"></div>
        @endif
        <div class="home-promo-overlay" aria-hidden="true"></div>
        <div class="container home-promo-content">
            @if ($promotionalTours)
                <div class="promo-slider-heading"><div><h2 id="promotion-title">Ưu đãi đang diễn ra</h2></div><a class="section-more" href="{{ route('tours.index') }}">Xem tất cả tour <i class="bi bi-arrow-up-right"></i></a></div>
                <div class="home-promo-slider-wrap"><div class="home-promo-slider swiper" data-promo-swiper aria-label="Các tour đang ưu đãi"><div class="swiper-wrapper">@foreach ($promotionalTours as $tour)<div class="swiper-slide">@include('frontend.tours.partials.card', ['tour' => $tour, 'showPromotionMeta' => true])</div>@endforeach</div></div><div class="home-promo-slider-navigation" aria-label="Điều khiển tour ưu đãi"><button type="button" data-promo-prev aria-label="Ưu đãi trước"><i class="bi bi-arrow-left"></i></button><button type="button" data-promo-next aria-label="Ưu đãi tiếp theo"><i class="bi bi-arrow-right"></i></button></div></div>
            @else
                <h2 id="promotion-title" class="visually-hidden">Ưu đãi đang diễn ra</h2>
                <div class="home-empty-state home-empty-state-dark"><i class="bi bi-ticket-perforated"></i><strong>Ưu đãi mới đang được chuẩn bị</strong><span>Các tour có chương trình đang diễn ra sẽ được hiển thị tại đây.</span></div>
            @endif
        </div>
    </section>

    <section class="section-space home-about-section" aria-labelledby="about-title" data-aos="fade-up">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="home-about-media">
                        @if ($brandImageUrl)
                            <img src="{{ $brandImageUrl }}" alt="Trải nghiệm hành trình cùng HG TRIP" loading="lazy">
                        @else
                            <div class="home-placeholder-image home-placeholder-image-tall"><i class="bi bi-image"></i></div>
                        @endif
                        <div class="home-about-signature"><span>HG TRIP</span><strong>See the world<br>from different angles</strong></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="home-about-copy">
                        <span class="section-eyebrow">Về HG TRIP</span>
                        <h2 id="about-title">{{ $brandIntroduction['title'] }}</h2>
                        @foreach ($brandIntroduction['paragraphs'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach
                        <a class="section-more home-about-link" href="{{ route('about') }}">Khám phá câu chuyện HG TRIP <i class="bi bi-arrow-up-right"></i></a>
                    </div>
                    <div class="home-about-principles">
                        @foreach ($brandIntroduction['highlights'] as $highlight)
                            <article class="home-about-principle"><span>{{ $highlight['number'] }}</span><div><h3>{{ $highlight['title'] }}</h3><p>{{ $highlight['description'] }}</p></div></article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-impact-section {{ $impactBackdropUrl ? 'has-impact-backdrop' : '' }}" aria-labelledby="impact-title" data-aos="fade-up" data-home-parallax>
        @if ($impactBackdropUrl)
            <div class="home-parallax-backdrop home-impact-backdrop" aria-hidden="true" style="--impact-image: url('{{ $impactBackdropUrl }}');"></div>
        @endif
        <div class="home-impact-overlay" aria-hidden="true"></div>
        <div class="container home-impact-content"><div class="section-heading text-center mx-auto"><h2 id="impact-title" class="section-title">{{ $impactTitle }}</h2></div><div class="row g-0">@foreach ($impactStats as $stat)<div class="col-6 col-lg-3"><div class="impact-stat"><strong>{{ $stat['number'] }}</strong><span>{{ $stat['label'] }}</span></div></div>@endforeach</div></div>
    </section>

    <section class="section-space home-services-section" aria-labelledby="services-title" data-aos="fade-up">
        <div class="container">
            <div class="section-heading home-services-heading"><div><span class="section-eyebrow">Dịch vụ cung cấp</span><h2 id="services-title" class="section-title">Mọi dịch vụ cần thiết cho một hành trình trọn vẹn</h2></div><div><p class="section-description">Từng hạng mục được kết nối trong một kế hoạch thống nhất, rõ đầu mối và dễ kiểm soát.</p><a class="section-more" href="{{ route('services.index') }}">Xem toàn bộ dịch vụ <i class="bi bi-arrow-up-right"></i></a></div></div>
            <div class="home-service-list">
                @foreach ($serviceCategories as $service)
                    <a class="home-service-item" href="{{ url($service['url']) }}"><span class="home-service-icon"><i class="bi {{ $service['icon'] }}"></i></span><span class="home-service-copy"><strong>{{ $service['title'] }}</strong><small>{{ $service['description'] }}</small></span><i class="bi bi-arrow-up-right home-service-arrow"></i></a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section-space home-discovery-section" aria-labelledby="discovery-title" data-aos="fade-up">
        <div class="container"><div class="section-heading home-discovery-heading"><div><span class="section-eyebrow">Chọn theo điểm đến</span><h2 id="discovery-title" class="section-title">Điểm đến nổi bật</h2><p class="section-description">Khám phá những điểm đến được yêu thích và chọn hành trình phù hợp với bạn.</p></div><a class="section-more" href="{{ route('tours.index') }}">Xem tất cả tour <i class="bi bi-arrow-up-right"></i></a></div>
            @php($destinationTabs = $destinationTabs ?: [['key' => 'all', 'label' => 'Tất cả']])
            <div class="home-tabs" role="tablist" aria-label="Điểm đến nổi bật">@foreach ($destinationTabs as $tab)<button class="home-tab {{ $loop->first ? 'is-active' : '' }}" type="button" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-destination-tab="{{ $tab['key'] }}">{{ $tab['label'] }}</button>@endforeach</div>
            <div class="home-destination-grid">@forelse ($featuredDestinations as $destination)<div class="home-destination-item destination-bento-item {{ $loop->first ? 'is-bento-featured' : '' }}" data-destination-item="{{ $destination['tab_key'] }}"><article class="destination-card"><a class="destination-card-image d-block" href="{{ route('tours.index', ['destination' => $destination['slug']]) }}">@if ($destination['image_url'])<img src="{{ $destination['image_url'] }}" alt="{{ $destination['name'] }}" loading="lazy">@else<div class="home-placeholder-image"><i class="bi bi-image"></i></div>@endif</a><div class="destination-card-body"><span class="destination-card-kicker">{{ $destination['tab_label'] }}</span><h3 class="destination-card-title"><a href="{{ route('tours.index', ['destination' => $destination['slug']]) }}">{{ $destination['name'] }}</a></h3><p class="destination-card-meta">{{ $destination['tour_count'] }} hành trình đang chờ bạn</p>@if ($destination['summary'])<p class="destination-card-summary">{{ $destination['summary'] }}</p>@endif</div></article></div>@empty<div class="home-empty-state"><i class="bi bi-geo-alt"></i><strong>Điểm đến đang được cập nhật</strong><span>Những địa danh đầu tiên sẽ xuất hiện tại đây ngay khi được thêm từ hệ thống quản trị.</span></div>@endforelse</div>
        </div>
    </section>

    <section class="home-custom-tour-section {{ $customTourBackdropUrl ? 'has-custom-tour-backdrop' : '' }}" aria-labelledby="custom-tour-title" data-aos="fade-up" data-home-parallax data-parallax-distance="160">
        @if ($customTourBackdropUrl)
            <div class="home-parallax-backdrop home-custom-tour-backdrop" aria-hidden="true" style="--custom-tour-image: url('{{ $customTourBackdropUrl }}');"></div>
        @endif
        <div class="home-custom-tour-overlay" aria-hidden="true"></div>
        <div class="container home-custom-tour-content"><div class="home-custom-tour-copy"><span class="section-eyebrow section-eyebrow-light">Hành trình của riêng bạn</span><h2 id="custom-tour-title">{{ $customTourContent['title'] }}</h2><p>{{ $customTourContent['description'] }}</p><a class="btn btn-brand btn-brand-gold" href="{{ route('contact') }}">Nhận tư vấn miễn phí <i class="bi bi-arrow-right"></i></a></div></div>
    </section>

    <section class="section-space" aria-labelledby="testimonial-title"><div class="container"><div class="section-heading text-center mx-auto"><h2 id="testimonial-title" class="section-title">Khách hàng nói gì về chúng tôi</h2></div>@if ($testimonials)<div class="row g-4">@foreach ($testimonials as $testimonial)<div class="col-md-4"><article class="testimonial-card"><div class="testimonial-card-head">@if ($testimonial['avatar_url'])<img src="{{ $testimonial['avatar_url'] }}" alt="{{ $testimonial['name'] }}" loading="lazy">@else<span class="testimonial-avatar"><i class="bi bi-person"></i></span>@endif<div><strong>{{ $testimonial['name'] }}</strong>@if ($testimonial['title'])<span>{{ $testimonial['title'] }}</span>@endif</div></div><div class="testimonial-stars" aria-label="{{ $testimonial['rating'] }} trên 5 sao">@for ($rating = 1; $rating <= 5; $rating++)<i class="bi {{ $rating <= $testimonial['rating'] ? 'bi-star-fill' : 'bi-star' }}"></i>@endfor</div><p class="testimonial-content">“{{ $testimonial['content'] }}”</p></article></div>@endforeach</div>@else<div class="home-empty-state"><i class="bi bi-chat-heart"></i><strong>Cảm nhận khách hàng đang được cập nhật</strong><span>Những câu chuyện sau hành trình sẽ xuất hiện tại đây.</span></div>@endif</div></section>

    <section class="section-space home-gallery-section" aria-labelledby="gallery-title"><div class="container"><div class="section-heading d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3"><div><h2 id="gallery-title" class="section-title">Khoảnh khắc cùng HG</h2><p class="section-description">Đi để nhìn thấy thế giới, và để những khoảnh khắc đẹp ở lại thật lâu.</p></div><a class="section-more" href="{{ route('contact') }}">Chia sẻ ảnh của bạn <i class="bi bi-arrow-up-right"></i></a></div><div class="home-gallery-grid">@foreach ($customerGallery as $photo)<a class="home-gallery-item glightbox" href="{{ $photo['url'] }}" data-gallery="moments-{{ $photo['group_slug'] }}" data-title="{{ $photo['title'] }}" @if($photo['caption']) data-description="{{ $photo['caption'] }}" @endif><img src="{{ $photo['url'] }}" alt="{{ $photo['alt'] }}" loading="lazy"></a>@endforeach</div></div></section>

    <section class="home-partners-section" aria-labelledby="partners-title"><div class="container"><div class="section-heading text-center mx-auto"><h2 id="partners-title" class="section-title">Đồng hành cùng những thương hiệu uy tín</h2></div><div class="partner-marquee" aria-label="Các đối tác">@foreach (array_merge($partners, $partners) as $partner)<span class="partner-pill"><i class="bi bi-shield-check"></i>{{ $partner }}</span>@endforeach</div></div></section>

    <section class="section-space" aria-labelledby="news-title"><div class="container"><div class="section-heading d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3"><div><h2 id="news-title" class="section-title">Tin tức & Cẩm nang du lịch</h2></div><a class="section-more" href="{{ route('posts.index') }}">Xem tất cả <i class="bi bi-arrow-up-right"></i></a></div>@if ($latestPosts)<div class="row g-4">@foreach ($latestPosts as $post)<div class="col-md-4"><article class="news-card"><a class="news-card-image" href="{{ route('posts.show', ['post' => $post['slug']]) }}">@if ($post['image_url'])<img src="{{ $post['image_url'] }}" alt="{{ $post['name'] }}" loading="lazy">@else<div class="home-placeholder-image"><i class="bi bi-journal-richtext"></i></div>@endif</a><div class="news-card-body"><span>{{ $post['category'] ?: 'Cẩm nang' }} @if ($post['published_at']) · {{ $post['published_at'] }} @endif</span><h3><a href="{{ route('posts.show', ['post' => $post['slug']]) }}">{{ $post['name'] }}</a></h3>@if ($post['summary'])<p>{{ \Illuminate\Support\Str::limit($post['summary'], 110) }}</p>@endif</div></article></div>@endforeach</div>@else<div class="home-empty-state"><i class="bi bi-journal-richtext"></i><strong>Tin tức & cẩm nang đang được cập nhật</strong><span>Bài viết mới sẽ xuất hiện tại đây ngay khi được xuất bản.</span></div>@endif</div></section>
@endsection
