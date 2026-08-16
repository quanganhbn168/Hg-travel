@extends('layouts.admin')

@section('title', 'Cài đặt SEO')
@section('page-title', 'Cài đặt SEO')

@section('content')
    @include('admin.settings.partials.navigation')
    <form action="{{ route('admin.settings.seo.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row"><div class="col-xl-8"><x-card type="primary" title="SEO mặc định"><x-input name="seo_title" label="SEO title mặc định" :value="$settings->seo_title" /><x-textarea name="seo_description" label="SEO description mặc định" :value="$settings->seo_description" rows="4" /><x-textarea name="seo_keywords" label="SEO keywords mặc định" :value="$settings->seo_keywords" rows="3" /><p class="text-muted small mb-0">Trang, tour và bài viết có SEO riêng sẽ được ưu tiên hơn các giá trị mặc định này.</p></x-card></div><div class="col-xl-8 text-end"><button class="btn btn-primary">Lưu cài đặt SEO</button></div></div>
    </form>
@endsection
