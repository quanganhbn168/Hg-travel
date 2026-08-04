<!doctype html>
@php($brandLogo = $siteAssets['logo'] ?: asset('images/logo-hg.png'))
@php($shareImage = $siteAssets['share'] ?: $brandLogo)

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ config('app.theme_color', '#b4935f') }}">

    <title>@yield('title', $siteSettings->seo_title ?: $siteSettings->site_name)</title>
    <meta name="description" content="@yield('meta_description', $siteSettings->seo_description ?: '')">
    <meta name="keywords" content="@yield('meta_keywords', $siteSettings->seo_keywords ?: '')">
    <meta name="author" content="{{ $siteSettings->site_name }}">
    <meta name="robots" content="@yield('meta_robots', 'index,follow')">
    <meta name="format-detection" content="telephone=no">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $siteSettings->site_name }}">
    <meta property="og:title" content="@yield('og_title', $siteSettings->seo_title ?: $siteSettings->site_name)">
    <meta property="og:description" content="@yield('og_description', $siteSettings->seo_description ?: '')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:locale" content="vi_VN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('twitter_title', $siteSettings->seo_title ?: $siteSettings->site_name)">
    <meta name="twitter:description" content="@yield('twitter_description', $siteSettings->seo_description ?: '')">
    <meta property="og:image" content="@yield('og_image', $shareImage)">
    <meta name="twitter:image" content="@yield('twitter_image', $shareImage)">
    @stack('meta')
    @yield('structured_data')

    <link rel="icon" href="{{ $siteAssets['favicon'] ?: $brandLogo }}">

    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    @yield('page_styles')
    @stack('styles')
    @stack('page_styles')
    @stack('head')
</head>

<body class="@yield('body_class')">
    <a class="skip-link" href="#main-content">Bỏ qua đến nội dung</a>

    <div class="site-topbar">
        <div class="container">
            <div class="topbar-inner d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    @foreach ($siteLinks['phones'] as $phone)
                        <a class="topbar-link {{ $loop->first ? '' : 'd-none d-xl-inline-flex' }}" href="{{ $phone['href'] }}">{{ $phone['label'] }}: {{ $phone['value'] }}</a>
                    @endforeach
                    @if ($siteLinks['email'])
                        <a class="topbar-link d-none d-sm-inline-flex" href="mailto:{{ $siteLinks['email'] }}">{{ $siteLinks['email'] }}</a>
                    @endif
                </div>
                <div class="topbar-utility d-none d-md-flex">
                    <a class="topbar-link" href="{{ url('/gioi-thieu') }}">Giới thiệu</a>
                    <a class="topbar-link" href="{{ route('contact') }}">Liên hệ</a>
                </div>
            </div>
        </div>
    </div>

    <header class="site-header">
        <nav class="navbar navbar-expand-lg" aria-label="Điều hướng chính">
            <div class="container">
                <a class="site-brand" href="{{ route('home') }}" aria-label="{{ $siteSettings->site_name }} - Trang chủ">
                    <img class="site-brand-logo" src="{{ $brandLogo }}" alt="{{ $siteSettings->site_name ?: 'HG' }}">
                </a>

                <div class="site-header-actions order-lg-3">
                    <button class="header-icon" type="button" data-search-toggle aria-controls="site-search-panel" aria-expanded="false" aria-label="Mở tìm kiếm">
                        <i class="bi bi-search"></i>
                    </button>
                    <a class="btn btn-brand d-none d-sm-inline-flex" href="{{ url('/dat-tour') }}">
                        <i class="bi bi-calendar2-check"></i>Đặt tour
                    </a>
                </div>

                <button class="navbar-toggler order-lg-2 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#site-mobile-navigation" aria-controls="site-mobile-navigation" aria-expanded="false" aria-label="Mở menu">
                    <i class="bi bi-list"></i>
                </button>

                <div class="navbar-collapse order-lg-2 d-none d-lg-flex flex-grow-1 justify-content-center" id="site-desktop-navigation">
                    <ul class="navbar-nav site-navigation">
                        @foreach ($headerMenuItems as $item)
                            <li class="nav-item {{ $item['children'] ? 'dropdown' : '' }}">
                                <a class="nav-link {{ $item['children'] ? 'dropdown-toggle' : '' }} {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}" target="{{ $item['target'] }}" @if ($item['target'] === '_blank') rel="noopener noreferrer" @endif @if ($item['children']) data-bs-toggle="dropdown" aria-expanded="false" @endif>
                                    {{ $item['title'] }}
                                </a>
                                @if ($item['children'])
                                    <ul class="dropdown-menu">
                                        @foreach ($item['children'] as $child)
                                            <li class="{{ $child['children'] ? 'dropdown-submenu' : '' }}">
                                                <a class="dropdown-item {{ $child['active'] ? 'active' : '' }}" href="{{ $child['url'] }}" target="{{ $child['target'] }}" @if ($child['target'] === '_blank') rel="noopener noreferrer" @endif>
                                                    {{ $child['title'] }}
                                                </a>
                                                @if ($child['children'])
                                                    <ul class="dropdown-menu dropdown-menu-nested">
                                                        @foreach ($child['children'] as $grandchild)
                                                            <li><a class="dropdown-item {{ $grandchild['active'] ? 'active' : '' }}" href="{{ $grandchild['url'] }}" target="{{ $grandchild['target'] }}" @if ($grandchild['target'] === '_blank') rel="noopener noreferrer" @endif>{{ $grandchild['title'] }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </nav>

        <div class="offcanvas offcanvas-end site-mobile-menu d-lg-none" tabindex="-1" id="site-mobile-navigation" aria-labelledby="site-mobile-navigation-title">
            <div class="offcanvas-header">
                <a class="site-brand" href="{{ route('home') }}" aria-label="{{ $brandName }} - Trang chủ">
                    <img class="site-brand-logo" src="{{ $brandLogo }}" alt="{{ $siteSettings->site_name ?: 'HG' }}">
                </a>
                <h2 class="visually-hidden" id="site-mobile-navigation-title">Menu chính</h2>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng menu"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav site-navigation">
                    @foreach ($headerMenuItems as $item)
                        <li class="nav-item {{ $item['children'] ? 'dropdown' : '' }}">
                            <a class="nav-link {{ $item['children'] ? 'dropdown-toggle' : '' }} {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}" target="{{ $item['target'] }}" @if ($item['target'] === '_blank') rel="noopener noreferrer" @endif @if ($item['children']) data-bs-toggle="dropdown" aria-expanded="false" @endif>
                                {{ $item['title'] }}
                            </a>
                            @if ($item['children'])
                                <ul class="dropdown-menu">
                                    @foreach ($item['children'] as $child)
                                        <li class="{{ $child['children'] ? 'dropdown-submenu' : '' }}"><a class="dropdown-item {{ $child['active'] ? 'active' : '' }}" href="{{ $child['url'] }}" target="{{ $child['target'] }}" @if ($child['target'] === '_blank') rel="noopener noreferrer" @endif>{{ $child['title'] }}</a>@if ($child['children'])<ul class="mobile-submenu">@foreach ($child['children'] as $grandchild)<li><a class="dropdown-item {{ $grandchild['active'] ? 'active' : '' }}" href="{{ $grandchild['url'] }}" target="{{ $grandchild['target'] }}" @if ($grandchild['target'] === '_blank') rel="noopener noreferrer" @endif>{{ $grandchild['title'] }}</a></li>@endforeach</ul>@endif</li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <div class="mobile-menu-actions">
                    <button class="btn btn-brand w-100" type="button" data-search-toggle aria-controls="site-search-panel" aria-expanded="false"><i class="bi bi-search"></i>Tìm kiếm</button>
                    <a class="btn btn-outline-brand w-100" href="{{ url('/dat-tour') }}"><i class="bi bi-calendar2-check"></i>Đặt tour</a>
                </div>
            </div>
        </div>

        <div class="site-search-panel" id="site-search-panel" hidden>
            <div class="container">
                <form class="site-search-form" method="GET" action="{{ route('tours.index') }}" role="search">
                    <label class="visually-hidden" for="site-search-input">Tìm kiếm tour</label>
                    <div class="site-search-input-wrap">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input id="site-search-input" name="q" type="search" value="{{ request('q') }}" placeholder="Bạn muốn tìm tour, điểm đến nào?" autocomplete="off">
                    </div>
                    <button class="btn btn-brand" type="submit">Tìm kiếm</button>
                    <button class="site-search-close" type="button" data-search-close aria-label="Đóng tìm kiếm"><i class="bi bi-x-lg"></i></button>
                </form>
            </div>
        </div>
    </header>

    @yield('before_content')

    <main id="main-content">
        @yield('content')
    </main>

    @yield('after_content')

    <footer class="site-footer">
        <div class="container">
            <div class="footer-main">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6">
                        <a class="site-brand site-brand-footer" href="{{ route('home') }}" aria-label="{{ $brandName }} - Trang chủ"><img class="site-brand-logo" src="{{ $brandLogo }}" alt="{{ $siteSettings->site_name ?: 'HG' }}"></a>
                        @if ($siteSettings->company_name)<p class="footer-company-name">{{ $siteSettings->company_name }}</p>@endif
                        <p class="footer-description">{{ $brandName }} đồng hành cùng bạn từ cảm hứng lên đường đến từng dịch vụ cần thiết cho chuyến đi.</p>
                        <div class="social-links" aria-label="Mạng xã hội">
                            @if ($siteLinks['facebook'])
                                <a class="social-link" href="{{ $siteLinks['facebook'] }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                            @endif
                            @if ($siteLinks['instagram'])
                                <a class="social-link" href="{{ $siteLinks['instagram'] }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                            @endif
                            @if ($siteLinks['youtube'])
                                <a class="social-link" href="{{ $siteLinks['youtube'] }}" target="_blank" rel="noopener noreferrer" aria-label="Youtube"><i class="bi bi-youtube"></i></a>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-4"><h2 class="footer-title">Tour</h2><ul class="footer-links"><li><a href="{{ route('tours.index', ['scope' => 'international']) }}">Tour nước ngoài</a></li><li><a href="{{ route('tours.index', ['scope' => 'domestic']) }}">Tour trong nước</a></li><li><a href="{{ route('tours.index') }}">Tất cả tour</a></li></ul></div>
                    <div class="col-lg-2 col-sm-4"><h2 class="footer-title">Dịch vụ</h2><ul class="footer-links"><li><a href="{{ route('services.index') }}#tour-tron-goi">Tour trọn gói</a></li><li><a href="{{ route('services.index') }}#visa-cac-nuoc">Visa các nước</a></li><li><a href="{{ route('services.index') }}#dat-phong-khach-san">Đặt phòng khách sạn</a></li></ul></div>
                    <div class="col-lg-2 col-sm-4"><h2 class="footer-title">Thông tin</h2><ul class="footer-links"><li><a href="{{ route('posts.index') }}">Cẩm nang du lịch</a></li><li><a href="{{ route('contact') }}">Liên hệ</a></li><li><a href="{{ url('/gioi-thieu') }}">Giới thiệu</a></li></ul></div>
                    <div class="col-lg-2 col-md-6">
                        <h2 class="footer-title">Kết nối</h2>
                        <ul class="footer-contact">
                            <li><i class="bi bi-telephone"></i><span>
                                @if ($siteLinks['phones'])
                                    @foreach ($siteLinks['phones'] as $phone)
                                        <a href="{{ $phone['href'] }}">{{ $phone['label'] }}: {{ $phone['value'] }}</a>@if (! $loop->last)<br>@endif
                                    @endforeach
                                @else
                                    Hotline đang cập nhật
                                @endif
                            </span></li>
                            <li><i class="bi bi-envelope"></i><span>
                                @if ($siteLinks['emails'])
                                    @foreach ($siteLinks['emails'] as $email)
                                        <a href="mailto:{{ $email }}">{{ $email }}</a>@if (! $loop->last)<br>@endif
                                    @endforeach
                                @else
                                    Email đang cập nhật
                                @endif
                            </span></li>
                            <li><i class="bi bi-geo-alt"></i><span>{{ $siteSettings->office_address ?: 'Địa chỉ đang cập nhật' }}</span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-bottom d-flex flex-column flex-sm-row justify-content-between gap-2"><span>&copy; {{ now()->year }} {{ $siteSettings->company_name ?: $brandName }}.</span><div class="footer-bottom-links"><a href="{{ url('/chinh-sach-bao-mat') }}">Chính sách bảo mật</a><a href="{{ url('/dieu-khoan') }}">Điều khoản</a></div></div>
        </div>
    </footer>

    <div class="floating-actions" aria-label="Liên hệ nhanh">
        @if ($siteLinks['phone_href'])
            <a class="floating-action floating-phone" href="{{ $siteLinks['phone_href'] }}" aria-label="Gọi {{ $siteLinks['phone'] }}" data-bs-toggle="tooltip" data-bs-title="Gọi ngay">
                <i class="bi bi-telephone-fill"></i>
            </a>
        @endif
        @if ($siteLinks['zalo'])
            <a class="floating-action floating-zalo" href="{{ $siteLinks['zalo'] }}" target="_blank" rel="noopener noreferrer" aria-label="Zalo" data-bs-toggle="tooltip" data-bs-title="Chat Zalo">
                <img class="floating-zalo-icon" src="{{ asset('images/zalo.svg') }}" alt="" width="50" height="50">
            </a>
        @endif
        @if ($siteLinks['whatsapp'])
            <a class="floating-action floating-whatsapp" href="{{ $siteLinks['whatsapp'] }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" data-bs-toggle="tooltip" data-bs-title="Chat WhatsApp"><i class="bi bi-whatsapp"></i></a>
        @endif
        @if ($siteLinks['messenger'])
            <a class="floating-action floating-messenger" href="{{ $siteLinks['messenger'] }}" target="_blank" rel="noopener noreferrer" aria-label="Messenger" data-bs-toggle="tooltip" data-bs-title="Chat Messenger">
                <i class="bi bi-messenger"></i>
            </a>
        @endif
    </div>

    <button class="scroll-top" type="button" aria-label="Lên đầu trang">
        <img class="scroll-top-icon" src="{{ asset('images/scroll-top-airplane.png') }}" alt="" width="50" height="50">
    </button>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/site.js') }}"></script>
    @stack('scripts')
</body>

</html>
