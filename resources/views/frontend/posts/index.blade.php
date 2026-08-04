@extends('layouts.master')

@section('title', 'Cẩm nang du lịch | '.$siteSettings->site_name)
@section('meta_description', 'Cập nhật kinh nghiệm, cảm hứng và thông tin hữu ích để chuẩn bị hành trình cùng '.$siteSettings->site_name.'.')
@section('canonical', route('posts.index'))
@section('body_class', 'blog-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
@endpush

@section('breadcrumb')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active" aria-current="page">Cẩm nang du lịch</li></ol></nav>
@endsection

@section('content')
    <section class="blog-hero page-banner" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');">
        <div class="container"><div class="blog-hero-copy"><span class="section-eyebrow section-eyebrow-light">Cảm hứng lên đường</span><h1>Cẩm nang du lịch</h1><p>Kinh nghiệm thực tế, gợi ý chuẩn bị và những câu chuyện giúp hành trình của bạn chủ động hơn.</p></div></div>
    </section>
    <div class="breadcrumb-bar"><div class="container">@yield('breadcrumb')</div></div>

    <section class="section-space blog-listing-section">
        <div class="container">
            <div class="blog-filter-panel">
                <form class="row g-3 align-items-end" action="{{ route('posts.index') }}" method="GET">
                    <div class="col-lg-6"><label class="form-label" for="blog-search">Tìm trong cẩm nang</label><input class="form-control" id="blog-search" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Kinh nghiệm, visa, điểm đến..."></div>
                    <div class="col-lg-4"><label class="form-label" for="blog-category">Chủ đề</label><select class="form-select" id="blog-category" name="category"><option value="">Tất cả chủ đề</option>@foreach ($categories as $category)<option value="{{ $category->slug }}" @selected($filters['category'] === $category->slug)>{{ $category->name }} ({{ $category->posts_count }})</option>@endforeach</select></div>
                    <div class="col-lg-2"><button class="btn btn-brand w-100" type="submit">Tìm bài viết</button></div>
                </form>
            </div>

            @if ($categories->isNotEmpty())
                <div class="blog-topic-list" aria-label="Chủ đề cẩm nang"><a class="blog-topic {{ $filters['category'] === '' ? 'is-active' : '' }}" href="{{ route('posts.index') }}">Tất cả</a>@foreach ($categories as $category)<a class="blog-topic {{ $filters['category'] === $category->slug ? 'is-active' : '' }}" href="{{ route('posts.index', ['category' => $category->slug]) }}">{{ $category->name }}</a>@endforeach</div>
            @endif

            <div class="blog-list-heading"><span>{{ number_format($posts->total()) }} bài viết</span>@if ($filters['q'] || $filters['category'])<a href="{{ route('posts.index') }}">Xóa bộ lọc</a>@endif</div>
            <div class="row g-4">
                @forelse ($posts as $post)
                    @php($coverImage = filled($post->cover_image) ? (\Illuminate\Support\Str::startsWith($post->cover_image, ['http://', 'https://', '/']) ? $post->cover_image : asset($post->cover_image)) : null)
                    <div class="col-md-6 col-xl-4"><article class="blog-card"><a class="blog-card-image" href="{{ route('posts.show', $post) }}">@if ($coverImage)<img src="{{ $coverImage }}" alt="{{ $post->name }}" loading="lazy">@else<span><i class="bi bi-journal-richtext"></i></span>@endif</a><div class="blog-card-body"><div class="blog-card-meta"><span>{{ $post->category?->name ?: 'Cẩm nang' }}</span>@if ($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d/m/Y') }}</time>@endif</div><h2><a href="{{ route('posts.show', $post) }}">{{ $post->name }}</a></h2>@if ($post->summary)<p>{{ \Illuminate\Support\Str::limit($post->summary, 140) }}</p>@endif<a class="blog-card-more" href="{{ route('posts.show', $post) }}">Đọc bài viết <i class="bi bi-arrow-up-right"></i></a></div></article></div>
                @empty
                    <div class="col-12"><div class="blog-empty"><i class="bi bi-journal-x"></i><strong>Chưa có bài viết phù hợp</strong><span>Thử tìm một từ khóa khác hoặc quay về tất cả bài viết.</span><a class="btn btn-brand" href="{{ route('posts.index') }}">Xem tất cả bài viết</a></div></div>
                @endforelse
            </div>

            @if ($posts->hasPages())<nav class="blog-pagination mt-5" aria-label="Phân trang bài viết">{{ $posts->onEachSide(1)->links() }}</nav>@endif
        </div>
    </section>
@endsection
