@extends('layouts.master')

@section('title', $page['title'].' | '.$siteSettings->site_name)
@section('meta_description', $page['description'])
@section('meta_keywords', 'tour du lịch, tour Việt Nam, '.$page['title'])
@section('body_class', 'tour-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/tour.css') }}?v={{ filemtime(public_path('css/tour.css')) }}">
@endpush

@section('structured_data')
    <script type="application/ld+json">
        {!! $structuredData !!}
    </script>
@endsection

@section('content')
    <section class="tour-page-header page-banner" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');">
        <div class="container">
            <div class="section-heading">
                <span class="section-eyebrow">{{ $page['eyebrow'] }}</span>
                <h1 class="section-title">{{ $page['title'] }}</h1>
                <p class="section-description">{{ $page['description'] }}</p>
            </div>
        </div>
    </section>

    <section class="section-space tour-listing-body">
        <div class="container">
            <div class="row g-4">
                <aside class="col-lg-3 tour-sidebar">
                    <div class="tour-filter">
                        <h2 class="tour-filter-title"><i class="bi bi-sliders2 me-2"></i>Bộ lọc hành trình</h2>
                        <form method="GET" action="{{ route('tours.index') }}">
                            <input type="hidden" name="scope" value="{{ $filters['scope'] }}">
                            <div class="tour-filter-group">
                                <label class="form-label" for="tour-search">Tìm kiếm</label>
                                <input class="form-control" id="tour-search" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Tên tour, mã tour...">
                            </div>

                            <div class="tour-filter-group">
                                <label class="form-label" for="tour-category">Loại hình tour</label>
                                <select class="form-select" id="tour-category" name="category">
                                    <option value="">Tất cả loại hình</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['slug'] }}" @selected($filters['category'] === $category['slug'])>{{ $category['name'] }} ({{ $category['tour_count'] }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="tour-filter-group">
                                <label class="form-label" for="tour-destination">Điểm đến</label>
                                <select class="form-select" id="tour-destination" name="destination">
                                    <option value="">Tất cả điểm đến</option>
                                    @foreach ($destinations as $destination)
                                        <option value="{{ $destination['slug'] }}" @selected($filters['destination'] === $destination['slug'])>{{ $destination['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="tour-filter-group">
                                <label class="form-label" for="tour-duration">Thời lượng</label>
                                <select class="form-select" id="tour-duration" name="duration">
                                    <option value="">Mọi thời lượng</option>
                                    <option value="1-3" @selected($filters['duration'] === '1-3')>1 - 3 ngày</option>
                                    <option value="4-7" @selected($filters['duration'] === '4-7')>4 - 7 ngày</option>
                                    <option value="8+" @selected($filters['duration'] === '8+')>Trên 7 ngày</option>
                                </select>
                            </div>

                            <div class="tour-filter-group">
                                <label class="form-label" for="tour-budget">Ngân sách</label>
                                <select class="form-select" id="tour-budget" name="budget">
                                    <option value="">Mọi mức giá</option>
                                    <option value="under-5" @selected($filters['budget'] === 'under-5')>Dưới 5 triệu</option>
                                    <option value="5-10" @selected($filters['budget'] === '5-10')>5 - 10 triệu</option>
                                    <option value="over-10" @selected($filters['budget'] === 'over-10')>Trên 10 triệu</option>
                                </select>
                            </div>

                            <button class="btn btn-brand w-100 mt-4" type="submit"><i class="bi bi-search me-1"></i>Áp dụng bộ lọc</button>
                            @if ($filters['q'] || $filters['category'] || $filters['destination'] || $filters['duration'] || $filters['budget'] || $filters['scope'])
                                <a class="btn btn-link text-muted w-100 mt-2" href="{{ route('tours.index') }}">Xóa bộ lọc</a>
                            @endif
                        </form>
                    </div>

                    @if ($categoryTree)
                        <div class="tour-category-list">
                            <h2 class="tour-category-list-title">Khám phá theo loại hình</h2>
                            <div class="tour-category-tree">
                            @foreach ($categoryTree as $category)
                                <div class="tour-category-tree-group">
                                    <a class="tour-category-link {{ $filters['category'] === $category['slug'] ? 'is-active' : '' }}" href="{{ route('tours.category', array_filter(['category' => $category['slug'], 'scope' => $filters['scope']])) }}">
                                        <span>{{ $category['name'] }}</span>
                                        <small>{{ $category['tour_count'] }}</small>
                                    </a>
                                    @if ($category['children'])
                                        <div class="tour-category-children">
                                        @foreach ($category['children'] as $child)
                                            <a class="tour-category-link {{ $filters['category'] === $child['slug'] ? 'is-active' : '' }}" href="{{ route('tours.category', array_filter(['category' => $child['slug'], 'scope' => $filters['scope']])) }}">
                                                <span>{{ $child['name'] }}</span>
                                                <small>{{ $child['tour_count'] }}</small>
                                            </a>
                                        @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                            </div>
                        </div>
                    @endif
                </aside>

                <div class="col-lg-9">
                    @if ($activeCategory && $activeCategory['description'])
                        <div class="tour-category-intro mb-4">
                            <strong>{{ $activeCategory['name'] }}</strong>
                            <div class="tour-category-intro-content">{!! $activeCategory['description'] !!}</div>
                        </div>
                    @endif

                    <div class="tour-toolbar mb-4">
                        <span class="tour-toolbar-count">{{ number_format($tours->total()) }} hành trình phù hợp</span>
                        <form id="tour-sort-form" class="tour-sort-form" method="GET" action="{{ route('tours.index') }}">
                            <input type="hidden" name="q" value="{{ $filters['q'] }}">
                            <input type="hidden" name="category" value="{{ $filters['category'] }}">
                            <input type="hidden" name="destination" value="{{ $filters['destination'] }}">
                            <input type="hidden" name="scope" value="{{ $filters['scope'] }}">
                            <input type="hidden" name="duration" value="{{ $filters['duration'] }}">
                            <input type="hidden" name="budget" value="{{ $filters['budget'] }}">
                            <label class="d-flex align-items-center gap-2 mb-0 small text-muted" for="tour-sort">
                                <span class="d-none d-sm-inline">Sắp xếp</span>
                                <select class="form-select form-select-sm" id="tour-sort" name="sort">
                                    <option value="featured" @selected($filters['sort'] === 'featured')>Nổi bật</option>
                                    <option value="newest" @selected($filters['sort'] === 'newest')>Mới nhất</option>
                                    <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Giá thấp đến cao</option>
                                    <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Giá cao đến thấp</option>
                                </select>
                            </label>
                            <button class="btn btn-sm btn-brand" type="submit" aria-label="Áp dụng sắp xếp"><i class="bi bi-arrow-down-up"></i></button>
                        </form>
                    </div>

                    <div class="row g-4">
                        @forelse ($tours as $tour)
                            <div class="col-md-6 col-xl-4">
                                @include('frontend.tours.partials.card', ['tour' => $tour])
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="home-empty-state tour-empty-state">
                                    <i class="bi bi-compass"></i>
                                    <strong>Chưa tìm thấy hành trình phù hợp</strong>
                                    <span>Thử nới rộng bộ lọc hoặc quay lại danh sách tất cả tour để xem thêm lựa chọn.</span>
                                    <a class="btn btn-brand mt-2" href="{{ route('tours.index') }}">Xem tất cả tour</a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if ($tours->hasPages())
                        <nav class="tour-pagination mt-5" aria-label="Phân trang tour">
                            {{ $tours->onEachSide(1)->links() }}
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
