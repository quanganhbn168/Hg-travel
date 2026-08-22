@php
    $items = [
        'admin.settings.website' => ['icon' => 'bi-globe2', 'label' => 'Website', 'description' => 'Thương hiệu và nội dung nền'],
        'admin.settings.business' => ['icon' => 'bi-building', 'label' => 'Doanh nghiệp', 'description' => 'Thông tin pháp lý'],
        'admin.settings.media' => ['icon' => 'bi-images', 'label' => 'Media', 'description' => 'Logo, ảnh và upload'],
        'admin.settings.seo' => ['icon' => 'bi-search', 'label' => 'SEO', 'description' => 'Metadata mặc định'],
        'admin.settings.contact' => ['icon' => 'bi-telephone', 'label' => 'Liên hệ', 'description' => 'Điện thoại, email, mạng xã hội'],
        'admin.settings.tour' => ['icon' => 'bi-calendar3', 'label' => 'Tour', 'description' => 'Lịch khởi hành và slot'],
    ];
@endphp

<aside class="card card-secondary card-outline mb-0 admin-settings-sidebar" aria-label="Điều hướng cài đặt">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="bi bi-sliders2 me-2"></i>Cài đặt hệ thống</h3>
    </div>
    <div class="card-body p-2">
        <p class="text-muted small px-2 mb-2">Quản lý các cấu hình website</p>
        <nav class="nav nav-pills flex-column admin-settings-nav">
    @foreach($items as $routeName => $item)
        @php $isActive = request()->routeIs($routeName); @endphp
        <a href="{{ route($routeName) }}"
           class="nav-link admin-settings-nav__item {{ $isActive ? 'active' : '' }}"
           @if($isActive) aria-current="page" @endif>
            <i class="bi {{ $item['icon'] }} admin-settings-nav__item-icon mt-1"></i>
            <span class="admin-settings-nav__item-copy"><strong class="d-block">{{ $item['label'] }}</strong><small class="d-block">{{ $item['description'] }}</small></span>
            <i class="bi bi-chevron-right admin-settings-nav__item-arrow ms-auto mt-1"></i>
        </a>
    @endforeach
        </nav>
    </div>
</aside>
