@extends('layouts.master')

@section('title', $tour['seo_title'] ?: $tour['name'].' | '.$siteSettings->site_name)
@section('meta_description', $tour['seo_description'] ?: $tour['summary'] ?: 'Thông tin chi tiết '.$tour['name'])
@section('meta_keywords', 'tour '.$tour['name'].', '.$tour['category'].', tour du lịch')
@if ($tour['image_url'] ?: $tour['banner_image_url'])
@section('og_image', $tour['image_url'] ?: $tour['banner_image_url'])
@endif
@section('body_class', 'tour-detail-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/detail-tour.css') }}?v={{ filemtime(public_path('css/detail-tour.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/tour.css') }}?v={{ filemtime(public_path('css/tour.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('js/detail-tour.js') }}"></script>
@endpush

@section('structured_data')
    <script type="application/ld+json">
        {!! $structuredData !!}
    </script>
@endsection

@section('breadcrumb')
                    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item"><a href="{{ route('tours.index') }}">Tour du lịch</a></li>@if ($tour['category'])<li class="breadcrumb-item"><a href="{{ route('tours.category', ['category' => $tour['category_slug']]) }}">{{ $tour['category'] }}</a></li>@endif<li class="breadcrumb-item active" aria-current="page">{{ $tour['name'] }}</li></ol></nav>
@endsection

@section('content')
    <section class="tour-detail-hero page-banner" style="--page-banner-image: url('{{ $tour['banner_image_url'] ?: $siteAssets['page_banner'] }}');">
        <div class="container">
            <div class="tour-detail-hero-inner text-center">
                <h1 class="tour-detail-title">{{ $tour['name'] }}</h1>
                <div class="tour-detail-meta justify-content-center">
                    <span><i class="bi bi-clock"></i>{{ $tour['duration'] }}</span>
                    @if ($tour['destination'])
                        <span><i class="bi bi-geo-alt"></i>{{ $tour['destination'] }}</span>
                    @endif
                    @if ($tour['max_guests'])
                        <span><i class="bi bi-people"></i>Tối đa {{ $tour['max_guests'] }} khách</span>
                    @endif
                    <span><i class="bi bi-calendar3"></i>{{ $tour['next_departure'] ? 'Khởi hành: '.$tour['next_departure'] : ($tour['booking_open'] ? 'Đang nhận booking' : 'Liên hệ tư vấn') }}</span>
                </div>
                @if ($tour['average_rating'])
                    <div class="tour-detail-rating justify-content-center" aria-label="{{ $tour['average_rating'] }} trên 5 sao">
                        <span class="tour-rating-stars">@for ($rating = 1; $rating <= 5; $rating++)<i class="bi {{ $rating <= round($tour['average_rating']) ? 'bi-star-fill' : 'bi-star' }}"></i>@endfor</span>
                        <strong>{{ number_format((float) $tour['average_rating'], 1) }}</strong>
                        <span>{{ $tour['review_count'] }} đánh giá</span>
                    </div>
                @endif
                <span class="tour-detail-code">Mã tour: {{ $tour['code'] }}</span>
            </div>
        </div>
    </section>
    <div class="breadcrumb-bar"><div class="container">@yield('breadcrumb')</div></div>

    <section class="tour-detail-showcase" id="tour-booking">
        <div class="container">
            <div class="row g-4 g-xl-5 align-items-start">
                <div class="col-lg-6">
                    @if ($tour['gallery'])
                        <div class="tour-media-gallery" data-tour-gallery>
                            <div class="swiper tour-media-gallery-main">
                                <div class="swiper-wrapper">
                                    @foreach ($tour['gallery'] as $image)
                                        <div class="swiper-slide">
                                            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" width="1200" height="760" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                        </div>
                                    @endforeach
                                </div>
                                @if (count($tour['gallery']) > 1)
                                    <button class="tour-gallery-control tour-gallery-control-prev" type="button" aria-label="Ảnh trước"><i class="bi bi-chevron-left"></i></button>
                                    <button class="tour-gallery-control tour-gallery-control-next" type="button" aria-label="Ảnh tiếp theo"><i class="bi bi-chevron-right"></i></button>
                                @endif
                            </div>
                            @if (count($tour['gallery']) > 1)
                                <div class="swiper tour-media-gallery-thumbs" aria-label="Danh sách ảnh tour">
                                    <div class="swiper-wrapper">
                                        @foreach ($tour['gallery'] as $image)
                                            <button class="swiper-slide" type="button" aria-label="Xem ảnh {{ $loop->iteration }}">
                                                <img src="{{ $image['url'] }}" alt="" width="240" height="160" loading="lazy">
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="tour-media-gallery-placeholder"><i class="bi bi-map"></i><span>Hình ảnh tour đang được cập nhật</span></div>
                    @endif
                </div>

                <div class="col-lg-6">
                    <div class="tour-showcase-panel">
                        <h2 class="tour-showcase-title">{{ $tour['name'] }}</h2>
                        <div class="tour-showcase-meta" aria-label="Thông tin nhanh về tour">
                            <div><i class="bi bi-geo-alt"></i><span>Khởi hành<strong>{{ $tour['next_departure'] ?: ($tour['booking_open'] ? 'Đang nhận booking' : 'Liên hệ tư vấn') }}</strong></span></div>
                            <div><i class="bi bi-calendar3"></i><span>Thời gian<strong>{{ $tour['duration'] }}</strong></span></div>
                            @if ($tour['destination'])<div><i class="bi bi-signpost-split"></i><span>Điểm đến<strong>{{ $tour['destination'] }}</strong></span></div>@endif
                            <div><i class="bi bi-upc-scan"></i><span>Mã tour<strong>{{ $tour['code'] }}</strong></span></div>
                        </div>

                        @if ($tour['summary'])
                            <p class="tour-showcase-summary">{{ $tour['summary'] }}</p>
                        @endif

                        <div class="tour-showcase-price">
                            <span>Từ</span>
                            @if ($tour['discount_percent'] && $tour['sale_price_label'])
                                <del>{{ $tour['price_label'] }}</del><strong>{{ $tour['sale_price_label'] }}</strong>
                            @else
                                <strong>{{ $tour['price_label'] }}</strong>
                            @endif
                            <span>/ khách</span>
                        </div>

                        <div class="tour-showcase-benefits">
                            <span><i class="bi bi-check2"></i>Thông tin giá tour rõ ràng</span>
                            <span><i class="bi bi-check2"></i>Lịch khởi hành được cập nhật</span>
                            <span><i class="bi bi-check2"></i>HG hỗ trợ tư vấn trước chuyến đi</span>
                        </div>

                        <div class="tour-showcase-actions">
                            @if ($tour['booking_open'])
                                <a class="btn btn-brand btn-lg" href="#tour-booking-modal" data-bs-toggle="modal" data-bs-target="#tour-booking-modal">Đăng ký & giữ chỗ</a>
                            @else
                                <span class="btn btn-outline-secondary btn-lg disabled" aria-disabled="true">Tạm ngừng nhận booking</span>
                            @endif
                            @if ($tour['schedules'])
                                <a class="tour-showcase-calendar" href="#lich-khoi-hanh" aria-label="Xem lịch khởi hành"><i class="bi bi-calendar3"></i></a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <nav class="tour-detail-navigation" aria-label="Điều hướng nội dung tour">
        <div class="container">
            <div class="tour-detail-navigation-inner">
                <a href="#tong-quan">Tổng quan</a>
                @if ($tour['highlights'])<a href="#diem-noi-bat">Điểm nổi bật</a>@endif
                @if ($tour['schedules'])<a href="#lich-khoi-hanh">Giá & lịch khởi hành</a>@endif
                @if ($tour['itineraries'])<a href="#lich-trinh">Lịch trình</a>@endif
                @if ($tour['sections'] || $tour['inclusions'])<a href="#dich-vu">Dịch vụ & lưu ý</a>@endif
                @if ($tour['reviews'])<a href="#danh-gia">Đánh giá</a>@endif
                @if ($tour['booking_open'])<a class="tour-detail-navigation-cta" href="#tour-booking-modal" data-bs-toggle="modal" data-bs-target="#tour-booking-modal">Đặt tour</a>@endif
            </div>
        </div>
    </nav>

    <section class="section-space tour-detail-body">
        <div class="container">
            <div class="row g-5 tour-detail-layout">
                <div class="col-lg-8">
                    <article class="tour-detail-content">
                        <section id="tong-quan" class="tour-detail-section">
                            <div class="tour-detail-section-heading">
                                <h2>Thông tin hành trình</h2>
                            </div>
                            @if ($tour['description'])
                                <div class="tour-rich-text">{!! $tour['description'] !!}</div>
                            @elseif ($tour['summary'])
                                <p class="mb-0">{{ $tour['summary'] }}</p>
                            @endif
                        </section>

                        @if ($tour['highlights'])
                            <section id="diem-noi-bat" class="tour-detail-section tour-highlights">
                                @foreach ($tour['highlights'] as $section)
                                    <div class="tour-detail-section-heading"><h2>{{ $section['title'] }}</h2></div>
                                    @if ($section['content'])<div class="tour-rich-text">{!! $section['content'] !!}</div>@endif
                                @endforeach
                            </section>
                        @endif

                        @if ($tour['schedules'])
                            <section id="lich-khoi-hanh" class="tour-detail-section">
                                <div class="tour-detail-section-heading">
                                    <h2>Lịch khởi hành</h2>
                                </div>
                                <x-frontend.tour-schedule-picker
                                    :schedules="$tour['schedules']"
                                    :booking-open="$tour['booking_open']"
                                    :show-seat-availability="$tour['show_seat_availability']"
                                    :note="$tour['schedule_note']"
                                />
                            </section>
                        @endif

                        @if ($tour['booking_open'])
                            <x-frontend.tour-booking-form :tour="$tour" />
                        @else
                            <section class="tour-detail-section tour-booking-form-section" id="tour-booking-form">
                                <div class="tour-detail-section-heading mb-0"><h2>Tour đang tạm ngừng nhận booking</h2><p>HG sẽ mở lại biểu mẫu khi có lịch khởi hành phù hợp.</p></div>
                            </section>
                        @endif

                        @if ($tour['itineraries'])
                            <section id="lich-trinh" class="tour-detail-section">
                                <div class="tour-detail-section-heading tour-itinerary-heading">
                                    <h2>Lịch trình chi tiết</h2>
                                    <button type="button" class="tour-content-toggle" data-itinerary-toggle aria-controls="tour-itinerary-list" hidden>Mở tất cả các ngày</button>
                                </div>
                                <div class="tour-itinerary-list" id="tour-itinerary-list">
                                    @foreach ($tour['itineraries'] as $itinerary)
                                        <details class="tour-itinerary-item" @if ($loop->first) open @endif>
                                            <summary>
                                                <span class="tour-itinerary-day">Ngày {{ $itinerary['day_number'] }}</span>
                                                <span class="tour-itinerary-title">{{ $itinerary['title'] }}</span>
                                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                            </summary>
                                            <div class="tour-itinerary-content">
                                                @if ($itinerary['description'])
                                                    <div class="tour-rich-text">{!! $itinerary['description'] !!}</div>
                                                @endif
                                                @if ($itinerary['meals'] || $itinerary['accommodation'])
                                                    <dl class="tour-itinerary-meta">
                                                        @if ($itinerary['meal_lines'])
                                                            <div><dt><i class="bi bi-cup-hot" aria-hidden="true"></i>Bữa ăn</dt><dd>@foreach ($itinerary['meal_lines'] as $line)<span>{{ $line }}</span>@endforeach</dd></div>
                                                        @endif
                                                        @if ($itinerary['accommodation_lines'])
                                                            <div><dt><i class="bi bi-building" aria-hidden="true"></i>Nghỉ đêm & khách sạn</dt><dd>@foreach ($itinerary['accommodation_lines'] as $line)<span>{{ $line }}</span>@endforeach</dd></div>
                                                        @endif
                                                    </dl>
                                                @endif
                                            </div>
                                        </details>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if ($tour['sections'] || $tour['inclusions'])
                            <section id="dich-vu" class="tour-detail-section">
                                <div class="tour-detail-section-heading">
                                    <h2>Dịch vụ & thông tin cần biết</h2>
                                </div>
                                <div class="tour-policy-list">
                                    @foreach ($tour['inclusion_groups'] as $group)
                                        <details class="tour-policy-item">
                                            <summary><h3>{{ $group['title'] }}</h3><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                            <div class="tour-policy-content"><ul class="tour-service-list @if ($group['type'] === 'excluded') is-excluded @endif">@foreach ($group['items'] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
                                        </details>
                                    @endforeach
                                    @foreach ($tour['sections'] as $section)
                                        <details class="tour-policy-item">
                                            <summary><h3>{{ $section['title'] }}</h3><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                            <div class="tour-policy-content">@if ($section['content'])<div class="tour-rich-text">{!! $section['content'] !!}</div>@endif</div>
                                        </details>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if ($tour['reviews'])
                            <section id="danh-gia" class="tour-detail-section">
                                <div class="tour-detail-section-heading">
                                    <h2>Đánh giá từ khách hàng</h2>
                                </div>
                                <div class="tour-review-list">
                                    @foreach ($tour['reviews'] as $review)
                                        <article class="tour-review-card">
                                            <div class="tour-review-stars" aria-label="{{ $review['rating'] }} trên 5 sao">
                                                @for ($rating = 1; $rating <= 5; $rating++)
                                                    <i class="bi {{ $rating <= $review['rating'] ? 'bi-star-fill' : 'bi-star' }}"></i>
                                                @endfor
                                            </div>
                                            @if ($review['title'])<strong>{{ $review['title'] }}</strong>@endif
                                            <p>“{{ $review['content'] }}”</p>
                                            <small>{{ $review['name'] }}</small>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </article>
                </div>

                <aside class="col-lg-4">
                    <div class="tour-booking-card">
                        <p class="tour-booking-label">Giá tour trọn gói</p>
                        <div class="tour-booking-price">
                            <small>Giá khởi điểm / khách</small>
                            @if ($tour['discount_percent'] && $tour['sale_price_label'])
                                <div class="tour-booking-discount">Giảm {{ $tour['discount_percent'] }}%</div>
                                <del>{{ $tour['price_label'] }}</del>
                                <strong class="tour-booking-sale-price">{{ $tour['sale_price_label'] }}</strong>
                            @else
                                <strong>{{ $tour['price_label'] }}</strong>
                            @endif
                        </div>
                        <div class="tour-booking-highlights">
                            <span><i class="bi bi-clock"></i>{{ $tour['duration'] }}</span>
                            <span><i class="bi bi-calendar3"></i>{{ $tour['next_departure'] ? 'Khởi hành '.$tour['next_departure'] : ($tour['booking_open'] ? 'Đang nhận booking' : 'Liên hệ tư vấn') }}</span>
                            <span><i class="bi bi-upc-scan"></i>Mã tour: {{ $tour['code'] }}</span>
                        </div>

                        @if ($tour['booking_open'])
                            <a class="btn btn-brand btn-lg w-100" href="#tour-booking-modal" data-bs-toggle="modal" data-bs-target="#tour-booking-modal"><i class="bi bi-calendar2-check me-2"></i>Đăng ký & giữ chỗ</a>
                        @else
                            <span class="btn btn-outline-secondary btn-lg w-100 disabled" aria-disabled="true">Tạm ngừng nhận booking</span>
                        @endif
                        <a class="btn btn-link text-muted w-100 mt-2" href="{{ route('tours.index') }}"><i class="bi bi-arrow-left me-1"></i>Quay lại danh sách tour</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if ($relatedTours)
        <section class="section-space bg-light-subtle">
            <div class="container">
                <div class="section-heading d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3">
                    <div>
                        <h2 class="section-title">Những hành trình tương tự</h2>
                    </div>
                    <a class="btn btn-link text-brand fw-semibold p-0" href="{{ route('tours.index') }}">Xem tất cả <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-4">
                    @foreach ($relatedTours as $relatedTour)
                        <div class="col-md-6 col-xl-4">
                            @include('frontend.tours.partials.card', ['tour' => $relatedTour])
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
