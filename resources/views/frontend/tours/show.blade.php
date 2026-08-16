@extends('layouts.master')

@section('title', $tour['seo_title'] ?: $tour['name'].' | '.$siteSettings->site_name)
@section('meta_description', $tour['seo_description'] ?: $tour['summary'] ?: 'Thông tin chi tiết '.$tour['name'])
@section('meta_keywords', 'tour '.$tour['name'].', '.$tour['category'].', tour du lịch')
@section('canonical', route('tours.show', ['tour' => $tour['slug']]))
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
    <section class="tour-detail-hero page-banner" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');">
        <div class="container">
            <div class="tour-detail-hero-inner text-center">
                <span class="section-eyebrow">{{ $tour['category'] ?: 'Hành trình chọn lọc' }}</span>
                <h1 class="tour-detail-title">{{ $tour['name'] }}</h1>
                @if ($tour['summary'])
                    <p class="tour-detail-summary">{{ $tour['summary'] }}</p>
                @endif
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
                        <span class="section-eyebrow">{{ $tour['category'] ?: 'Hành trình chọn lọc' }}</span>
                        <h2 class="tour-showcase-title">{{ $tour['name'] }}</h2>
                        <div class="tour-showcase-meta" aria-label="Thông tin nhanh về tour">
                            <div><i class="bi bi-geo-alt"></i><span>Khởi hành<strong>{{ $tour['next_departure'] ?: ($tour['booking_open'] ? 'Đang nhận booking' : 'Liên hệ tư vấn') }}</strong></span></div>
                            <div><i class="bi bi-calendar3"></i><span>Thời gian<strong>{{ $tour['duration'] }}</strong></span></div>
                            @if ($tour['destination'])<div><i class="bi bi-signpost-split"></i><span>Điểm đến<strong>{{ $tour['destination'] }}</strong></span></div>@endif
                            <div><i class="bi bi-upc-scan"></i><span>Mã tour<strong>{{ $tour['code'] }}</strong></span></div>
                        </div>

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
                                <a class="btn btn-brand btn-lg" href="{{ route('booking.create', ['tour' => $tour['slug']]) }}">Đặt tour ngay</a>
                            @elseif ($siteLinks['email'])
                                <a class="btn btn-brand btn-lg" href="mailto:{{ $siteLinks['email'] }}?subject={{ rawurlencode('Tư vấn tour '.$tour['name']) }}">Nhận tư vấn tour</a>
                            @else
                                <a class="btn btn-brand btn-lg" href="{{ route('contact') }}">Liên hệ tư vấn</a>
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
                @if ($tour['schedules'])<a href="#lich-khoi-hanh">Giá & lịch khởi hành</a>@endif
                @if ($tour['itineraries'])<a href="#lich-trinh">Lịch trình</a>@endif
                @if ($tour['inclusions'])<a href="#dich-vu">Dịch vụ</a>@endif
                @if ($tour['reviews'])<a href="#danh-gia">Đánh giá</a>@endif
                <a class="tour-detail-navigation-cta" href="#tour-booking">Đặt tour</a>
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
                                <span class="section-eyebrow">Tổng quan</span>
                                <h2>Thông tin hành trình</h2>
                            </div>
                            @if ($tour['description'])
                                <div class="tour-rich-text">{!! $tour['description'] !!}</div>
                            @elseif ($tour['summary'])
                                <p class="mb-0">{{ $tour['summary'] }}</p>
                            @endif
                        </section>

                        @if ($tour['schedules'])
                            <section id="lich-khoi-hanh" class="tour-detail-section">
                                <div class="tour-detail-section-heading">
                                    <span class="section-eyebrow">Giá tour</span>
                                    <h2>Lịch khởi hành</h2>
                                </div>
                                <div class="tour-departure-table-wrap">
                                    <table class="tour-departure-table">
                                        <thead><tr><th>Ngày khởi hành</th><th>Ngày về</th><th>Giá từ / khách</th><th>Chỗ còn</th></tr></thead>
                                        <tbody>
                                            @foreach ($tour['schedules'] as $schedule)
                                                <tr>
                                                    <td><strong>{{ $schedule['departure_date'] }}</strong></td>
                                                    <td>{{ $schedule['return_date'] ?: 'Đang cập nhật' }}</td>
                                                    <td><strong class="tour-departure-price">{{ $schedule['price_label'] }}</strong></td>
                                                    <td>{{ $schedule['seats_left'] }} chỗ</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <p class="tour-departure-note mb-0">Giá và lịch được cập nhật theo từng đợt khởi hành.</p>
                            </section>
                        @endif

                        @if ($tour['itineraries'])
                            <section id="lich-trinh" class="tour-detail-section">
                                <div class="tour-detail-section-heading">
                                    <span class="section-eyebrow">Theo ngày</span>
                                    <h2>Lịch trình chi tiết</h2>
                                </div>
                                <div class="tour-itinerary-list">
                                    @foreach ($tour['itineraries'] as $itinerary)
                                        <details class="tour-itinerary-item" @if ($loop->first) open @endif>
                                            <summary>
                                                <span class="tour-itinerary-day">Ngày {{ $itinerary['day_number'] }}</span>
                                                <span class="tour-itinerary-title">{{ $itinerary['title'] }}</span>
                                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                            </summary>
                                            <div class="tour-itinerary-content">
                                                @if ($itinerary['description'])
                                                    <p>{{ $itinerary['description'] }}</p>
                                                @endif
                                                @if ($itinerary['meals'] || $itinerary['accommodation'])
                                                    <div class="tour-itinerary-meta">
                                                        @if ($itinerary['meals'])<span><i class="bi bi-cup-hot"></i>{{ $itinerary['meals'] }}</span>@endif
                                                        @if ($itinerary['accommodation'])<span><i class="bi bi-building"></i>{{ $itinerary['accommodation'] }}</span>@endif
                                                    </div>
                                                @endif
                                            </div>
                                        </details>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if ($tour['inclusions'])
                            <section id="dich-vu" class="tour-detail-section">
                                <div class="tour-detail-section-heading">
                                    <span class="section-eyebrow">Dịch vụ</span>
                                    <h2>Dịch vụ bao gồm</h2>
                                </div>
                                <div class="tour-inclusion-grid">
                                    @foreach ($tour['inclusions'] as $inclusion)
                                        <div class="tour-inclusion-item {{ $inclusion['type'] === 'excluded' ? 'is-excluded' : '' }}">
                                            <i class="bi {{ $inclusion['type'] === 'excluded' ? 'bi-x-circle' : 'bi-check-circle' }}"></i>
                                            <span>{{ $inclusion['content'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if ($tour['reviews'])
                            <section id="danh-gia" class="tour-detail-section">
                                <div class="tour-detail-section-heading">
                                    <span class="section-eyebrow">Khách hàng</span>
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
                            <a class="btn btn-brand btn-lg w-100" href="{{ route('booking.create', ['tour' => $tour['slug']]) }}"><i class="bi bi-calendar2-check me-2"></i>Đặt tour ngay</a>
                        @elseif ($siteLinks['email'])
                            <a class="btn btn-brand btn-lg w-100" href="mailto:{{ $siteLinks['email'] }}?subject={{ rawurlencode('Tư vấn tour '.$tour['name']) }}"><i class="bi bi-envelope me-2"></i>Nhận tư vấn tour</a>
                        @else
                            <a class="btn btn-brand btn-lg w-100" href="{{ route('tours.index') }}">Xem các tour khác</a>
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
                        <span class="section-eyebrow">Có thể bạn sẽ thích</span>
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
