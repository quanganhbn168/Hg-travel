@php($discountPercent = (int) ($tour['discount_percent'] ?? 0))
@php($hasDiscount = $discountPercent > 0 && filled($tour['sale_price_label'] ?? null))
@php($showPromotionMeta = $showPromotionMeta ?? false)
@php($promotionSeatsLabel = array_key_exists('seats_left', $tour) && $tour['seats_left'] !== null ? 'Còn '.(int) $tour['seats_left'].' chỗ' : ($tour['seats_label'] ?? null))
@php($departureDates = $tour['departure_dates'] ?? [])
@php($canBook = (bool) ($tour['booking_open'] ?? true))
@php($bookingModalId = 'tour-card-booking-modal')

<article class="tour-card {{ $showPromotionMeta ? 'tour-card--promotion' : '' }}">
    <a class="tour-card-image d-block" href="{{ route('tours.show', ['tour' => $tour['slug']]) }}" aria-label="Xem {{ $tour['name'] }}">
        @if ($tour['image_url'])
            <img src="{{ $tour['image_url'] }}" alt="{{ $tour['name'] }}" loading="lazy" width="720" height="450">
        @else
            <div class="home-placeholder-image"><i class="bi bi-map"></i></div>
        @endif
        @if (! $showPromotionMeta && $hasDiscount)
            <span class="tour-card-sale-burst" aria-label="Giảm {{ $discountPercent }} phần trăm">Giảm <strong>{{ $discountPercent }}%</strong></span>
        @endif
    </a>
    @if ($showPromotionMeta && (($tour['next_departure_at'] ?? null) || $promotionSeatsLabel))
        <div class="tour-card-countdown-strip" @if ($tour['next_departure_at'] ?? null) data-home-countdown="{{ $tour['next_departure_at'] }}" @endif aria-live="polite">
            @if ($tour['next_departure_at'] ?? null)
                <strong data-home-countdown-value>{{ $tour['countdown_label'] ?? 'Đang cập nhật' }}</strong>
            @endif
            @if ($promotionSeatsLabel)
                <span>{{ $promotionSeatsLabel }}</span>
            @endif
        </div>
    @endif
    <div class="tour-card-body">
        <h2 class="tour-card-title"><a href="{{ route('tours.show', ['tour' => $tour['slug']]) }}">{{ $tour['name'] }}</a></h2>
        @if ($showPromotionMeta)
            <div class="tour-card-promotion-meta">
                @if ($tour['next_departure'] ?? null)
                    <span><i class="bi bi-calendar3"></i><span>{{ $tour['next_departure'] }} · {{ $tour['duration_compact'] ?? $tour['duration'] }}</span></span>
                @endif
                @if ($tour['transport'] ?? null)
                    <span><i class="bi bi-airplane"></i><span>{{ $tour['transport'] }}</span></span>
                @endif
                @if ($departureDates)
                    <span class="tour-card-departure-label"><i class="bi bi-calendar3"></i><span>Ngày khởi hành:</span></span>
                    <div class="tour-card-departure-picker" aria-label="Các ngày khởi hành">
                        <span class="tour-card-departure-arrow" aria-hidden="true">‹</span>
                        <div class="tour-card-departure-dates">
                            @foreach (array_slice($departureDates, 0, 4) as $departureDate)
                                <span class="tour-card-departure-date">{{ $departureDate }}</span>
                            @endforeach
                        </div>
                        <span class="tour-card-departure-arrow" aria-hidden="true">›</span>
                    </div>
                @endif
            </div>
        @else
            <div class="tour-card-meta">
                <span><i class="bi bi-clock"></i> {{ $tour['duration'] }}</span>
                <span><i class="bi bi-airplane"></i> {{ $tour['transport'] ?? 'Theo chương trình' }}</span>
                @if ($tour['next_departure'] ?? null)
                    <span><i class="bi bi-calendar3"></i> Khởi hành: {{ $tour['next_departure'] }}</span>
                @endif
            </div>
        @endif
        <div class="tour-card-footer">
            <div class="tour-card-price">
                <small>{{ $showPromotionMeta ? 'Từ' : 'Giá từ' }}</small>
                @if ($hasDiscount)
                    <del>{{ $tour['price_label'] }}</del>
                    <strong class="tour-card-sale-price">{{ $tour['sale_price_label'] }}</strong>
                @else
                    <strong>{{ $tour['price_label'] }}</strong>
                @endif
            </div>
            @if ($showPromotionMeta)
                @if ($canBook)
                    <a class="btn btn-brand tour-card-booking-action" href="#{{ $bookingModalId }}" data-bs-toggle="modal" data-bs-target="#{{ $bookingModalId }}" data-tour-booking-trigger data-tour-id="{{ $tour['id'] }}" data-tour-name="{{ $tour['name'] }}">ĐẶT NGAY</a>
                @else
                    <span class="btn btn-brand tour-card-booking-action disabled" aria-disabled="true">ĐẶT NGAY</span>
                @endif
            @else
                <div class="tour-card-actions">
                    <a class="btn btn-sm btn-outline-brand" href="{{ route('tours.show', ['tour' => $tour['slug']]) }}">Xem chi tiết</a>
                    @if ($canBook)
                        <a class="btn btn-sm btn-brand" href="#{{ $bookingModalId }}" data-bs-toggle="modal" data-bs-target="#{{ $bookingModalId }}" data-tour-booking-trigger data-tour-id="{{ $tour['id'] }}" data-tour-name="{{ $tour['name'] }}">Đặt ngay</a>
                    @else
                        <span class="btn btn-sm btn-brand disabled" aria-disabled="true">Đặt ngay</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</article>
