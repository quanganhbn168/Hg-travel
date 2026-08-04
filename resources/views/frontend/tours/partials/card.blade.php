@php($discountPercent = (int) ($tour['discount_percent'] ?? 0))
@php($hasDiscount = $discountPercent > 0 && filled($tour['sale_price_label'] ?? null))

<article class="tour-card">
    <a class="tour-card-image d-block" href="{{ route('tours.show', ['tour' => $tour['slug']]) }}" aria-label="Xem {{ $tour['name'] }}">
        @if ($tour['image_url'])
            <img src="{{ $tour['image_url'] }}" alt="{{ $tour['name'] }}" loading="lazy" width="720" height="450">
        @else
            <div class="home-placeholder-image"><i class="bi bi-map"></i></div>
        @endif
        @if ($hasDiscount)
            <span class="tour-card-sale-burst" aria-label="Giảm {{ $discountPercent }} phần trăm">Giảm <strong>{{ $discountPercent }}%</strong></span>
        @endif
    </a>
    <div class="tour-card-body">
        <div class="tour-card-topline">
            <span class="tour-card-category">{{ $tour['category'] ?: 'Hành trình' }}</span>
            @if ($tour['destination'])
                <span class="tour-card-destination"><i class="bi bi-geo-alt"></i> {{ $tour['destination'] }}</span>
            @endif
        </div>
        <h2 class="tour-card-title"><a href="{{ route('tours.show', ['tour' => $tour['slug']]) }}">{{ $tour['name'] }}</a></h2>
        <div class="tour-card-meta">
            <span><i class="bi bi-clock"></i> {{ $tour['duration'] }}</span>
            @if ($tour['next_departure'] ?? null)
                <span><i class="bi bi-calendar3"></i> Khởi hành: {{ $tour['next_departure'] }}</span>
            @endif
        </div>
        <div class="tour-card-footer">
            <div class="tour-card-price">
                <small>Giá từ</small>
                @if ($hasDiscount)
                    <del>{{ $tour['price_label'] }}</del>
                    <strong class="tour-card-sale-price">{{ $tour['sale_price_label'] }}</strong>
                @else
                    <strong>{{ $tour['price_label'] }}</strong>
                @endif
            </div>
            <div class="tour-card-actions">
                <a class="btn btn-sm btn-outline-brand" href="{{ route('tours.show', ['tour' => $tour['slug']]) }}">Xem chi tiết</a>
                <a class="btn btn-sm btn-brand" href="{{ route('tours.show', ['tour' => $tour['slug']]) }}#tour-booking">Đặt ngay</a>
            </div>
        </div>
    </div>
</article>
