@php($compact = $compact ?? false)

<article class="service-card {{ $compact ? 'service-card-compact' : '' }}">
    <div class="service-card-topline">
        <span class="service-card-number">{{ str_pad((string) ($number ?? $loop->iteration ?? 1), 2, '0', STR_PAD_LEFT) }}</span>
        <span class="service-card-icon"><i class="bi {{ $service['icon'] }}"></i></span>
    </div>
    <div class="service-card-body">
        <span class="service-card-category">{{ $service['category'] }}</span>
        <h2 class="service-card-title"><a href="{{ route('services.show', ['service' => $service['slug']]) }}">{{ $service['title'] }}</a></h2>
        <p>{{ $service['description'] }}</p>
    </div>
    <a class="service-card-link" href="{{ route('services.show', ['service' => $service['slug']]) }}">
        Xem chi tiết <i class="bi bi-arrow-up-right"></i>
    </a>
</article>
