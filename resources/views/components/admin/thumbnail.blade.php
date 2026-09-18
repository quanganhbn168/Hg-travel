@props([
    'src' => null,
    'alt' => '',
    'href' => null,
    'size' => 52,
    'icon' => 'bi-image',
])

@if($href)
    <a
        href="{{ $href }}"
        {{ $attributes->class(['admin-thumbnail']) }}
        style="--admin-thumbnail-size: {{ (int) $size }}px"
    >
        @if($src)
            <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy">
        @else
            <i class="bi {{ $icon }}" aria-hidden="true"></i>
        @endif
    </a>
@else
    <span
        {{ $attributes->class(['admin-thumbnail']) }}
        style="--admin-thumbnail-size: {{ (int) $size }}px"
    >
        @if($src)
            <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy">
        @else
            <i class="bi {{ $icon }}" aria-hidden="true"></i>
        @endif
    </span>
@endif
