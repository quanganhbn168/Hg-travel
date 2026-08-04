@extends('layouts.master')

@section('title', ($post->seo_title ?: $post->name).' | '.$siteSettings->site_name)
@section('meta_description', $post->seo_description ?: $post->summary)
@section('meta_keywords', $post->seo_keywords ?: 'cẩm nang du lịch, kinh nghiệm du lịch')
@section('canonical', route('posts.show', $post))
@section('body_class', 'blog-page blog-detail-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
@endpush

@section('breadcrumb')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item"><a href="{{ route('posts.index') }}">Cẩm nang du lịch</a></li><li class="breadcrumb-item active" aria-current="page">{{ $post->name }}</li></ol></nav>
@endsection

@section('content')
    @php($coverImage = filled($post->cover_image) ? (\Illuminate\Support\Str::startsWith($post->cover_image, ['http://', 'https://', '/']) ? $post->cover_image : asset($post->cover_image)) : null)
    <div class="breadcrumb-bar"><div class="container">@yield('breadcrumb')</div></div>

    <section class="section-space post-detail-section">
        <div class="container">
            <div class="row g-4 g-xl-5 align-items-start">
                <main class="col-lg-9">
                    <article class="post-detail">
                        <header class="post-detail-heading">
                            <span class="section-eyebrow">{{ $post->category?->name ?: 'Cẩm nang du lịch' }}</span>
                            <h1>{{ $post->name }}</h1>
                            <div class="post-detail-meta">
                                @if ($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}"><i class="bi bi-calendar3"></i>{{ $post->published_at->format('d/m/Y') }}</time>@endif
                                @if ($post->author)<span><i class="bi bi-person"></i>{{ $post->author->name }}</span>@endif
                            </div>
                            @if ($post->summary)<p>{{ $post->summary }}</p>@endif
                        </header>

                        @if ($coverImage)<figure class="post-detail-cover"><img src="{{ $coverImage }}" alt="{{ $post->name }}"></figure>@endif

                        <div class="post-detail-content">{!! nl2br(e($post->content ?: $post->summary)) !!}</div>

                        <div class="post-detail-actions">
                            <a class="btn btn-outline-brand" href="{{ route('posts.index') }}"><i class="bi bi-arrow-left"></i>Quay lại cẩm nang</a>
                            <a class="btn btn-brand" href="{{ route('contact') }}">Nhận tư vấn hành trình</a>
                        </div>
                    </article>

                    @if ($relatedPosts->isNotEmpty())
                        <section class="post-related-inline">
                            <div class="section-heading d-flex align-items-end justify-content-between gap-3">
                                <div><span class="section-eyebrow">Đọc thêm</span><h2 class="section-title">Bài viết liên quan</h2></div>
                                <a class="section-more" href="{{ route('posts.index') }}">Xem tất cả <i class="bi bi-arrow-up-right"></i></a>
                            </div>
                            <div class="row g-4">
                                @foreach ($relatedPosts as $relatedPost)
                                    @php($relatedImage = filled($relatedPost->cover_image) ? (\Illuminate\Support\Str::startsWith($relatedPost->cover_image, ['http://', 'https://', '/']) ? $relatedPost->cover_image : asset($relatedPost->cover_image)) : null)
                                    <div class="col-md-4"><article class="blog-card"><a class="blog-card-image" href="{{ route('posts.show', $relatedPost) }}">@if ($relatedImage)<img src="{{ $relatedImage }}" alt="{{ $relatedPost->name }}" loading="lazy">@else<span><i class="bi bi-journal-richtext"></i></span>@endif</a><div class="blog-card-body"><div class="blog-card-meta"><span>{{ $relatedPost->category?->name ?: 'Cẩm nang' }}</span>@if ($relatedPost->published_at)<time datetime="{{ $relatedPost->published_at->toDateString() }}">{{ $relatedPost->published_at->format('d/m/Y') }}</time>@endif</div><h3><a href="{{ route('posts.show', $relatedPost) }}">{{ $relatedPost->name }}</a></h3><a class="blog-card-more" href="{{ route('posts.show', $relatedPost) }}">Đọc bài viết <i class="bi bi-arrow-up-right"></i></a></div></article></div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </main>

                <aside class="col-lg-3">
                    <div class="post-sidebar">
                        @if ($sidebarCategories->isNotEmpty())
                            <section class="post-sidebar-card">
                                <h2>Chủ đề cẩm nang</h2>
                                <nav class="post-sidebar-topics" aria-label="Chủ đề cẩm nang">
                                    @foreach ($sidebarCategories as $category)
                                        <a href="{{ route('posts.index', ['category' => $category->slug]) }}"><span>{{ $category->name }}</span><small>{{ $category->posts_count }}</small></a>
                                    @endforeach
                                </nav>
                            </section>
                        @endif

                        @if ($sidebarPosts->isNotEmpty())
                            <section class="post-sidebar-card">
                                <h2>Bài viết mới</h2>
                                <div class="post-sidebar-list">
                                    @foreach ($sidebarPosts as $sidebarPost)
                                        @php($sidebarImage = filled($sidebarPost->cover_image) ? (\Illuminate\Support\Str::startsWith($sidebarPost->cover_image, ['http://', 'https://', '/']) ? $sidebarPost->cover_image : asset($sidebarPost->cover_image)) : null)
                                        <a class="post-sidebar-item" href="{{ route('posts.show', $sidebarPost) }}">
                                            @if ($sidebarImage)<img src="{{ $sidebarImage }}" alt="" loading="lazy">@endif
                                            <span><strong>{{ $sidebarPost->name }}</strong>@if ($sidebarPost->published_at)<small>{{ $sidebarPost->published_at->format('d/m/Y') }}</small>@endif</span>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        <section class="post-sidebar-cta">
                            <span class="section-eyebrow">HG đồng hành</span>
                            <h2>Cần tư vấn cho hành trình của bạn?</h2>
                            <p>Liên hệ HG để nhận gợi ý phù hợp cùng thông tin tour rõ ràng.</p>
                            @if ($siteLinks['phone_href'])
                                <a class="btn btn-brand w-100" href="{{ $siteLinks['phone_href'] }}">Gọi HG tư vấn</a>
                            @else
                                <a class="btn btn-brand w-100" href="{{ route('contact') }}">Liên hệ HG</a>
                            @endif
                        </section>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
