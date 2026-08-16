@extends('layouts.master')

@section('title', ($page->seo_title ?: $page->name).' | '.$siteSettings->site_name)
@section('meta_description', $page->seo_description ?: $page->sub_title ?: '')
@section('meta_keywords', $page->seo_keywords ?: '')
@section('canonical', route('pages.show', ['page' => $page->slug]))
@section('body_class', 'static-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/page.css') }}?v={{ filemtime(public_path('css/page.css')) }}">
@endpush

@section('content')
    <section class="page-banner static-page-hero" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');">
        <div class="container">
            <span class="section-eyebrow section-eyebrow-light">{{ $page->template === 'default' ? 'Thông tin' : ucfirst($page->template) }}</span>
            <h1>{{ $page->name }}</h1>
            @if ($page->sub_title)<p>{{ $page->sub_title }}</p>@endif
        </div>
    </section>
    <div class="breadcrumb-bar"><div class="container"><nav aria-label="Breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active" aria-current="page">{{ $page->name }}</li></ol></nav></div></div>

    <section class="section-space static-page-content">
        <div class="container">
            <article class="static-page-prose">
                {!! $page->content ?: '<p>Nội dung trang đang được cập nhật.</p>' !!}
            </article>
        </div>
    </section>
@endsection
