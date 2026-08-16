@php
    $items = [
        'admin.settings.website' => ['icon' => 'bi-globe2', 'label' => 'Website'],
        'admin.settings.business' => ['icon' => 'bi-building', 'label' => 'Doanh nghiệp'],
        'admin.settings.media' => ['icon' => 'bi-images', 'label' => 'Media'],
        'admin.settings.seo' => ['icon' => 'bi-search', 'label' => 'SEO'],
        'admin.settings.contact' => ['icon' => 'bi-telephone', 'label' => 'Liên lạc'],
    ];
@endphp

<nav class="admin-settings-nav mb-3" aria-label="Điều hướng cài đặt">
    @foreach($items as $routeName => $item)
        @php $isActive = request()->routeIs($routeName); @endphp
        <a href="{{ route($routeName) }}"
           class="admin-settings-nav__item {{ $isActive ? 'is-active' : '' }}"
           @if($isActive) aria-current="page" @endif>
            <i class="bi {{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
