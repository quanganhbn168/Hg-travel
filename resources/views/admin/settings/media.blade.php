@extends('layouts.admin')

@section('title', 'Cài đặt media')
@section('page-title', 'Cài đặt media')

@section('content')
    @include('admin.settings.partials.navigation')
    <form action="{{ route('admin.settings.media.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3"><div class="col-xl-8"><x-card type="primary" title="Nhận diện thương hiệu"><div class="row"><div class="col-md-6"><x-image-upload name="logo_url" label="Logo" :value="$settings->logo_url" /></div><div class="col-md-6"><x-image-upload name="favicon_url" label="Favicon" :value="$settings->favicon_url" /></div></div><div class="row"><div class="col-md-6"><x-image-upload name="image_share_url" label="Ảnh chia sẻ OG/Twitter" :value="$settings->image_share_url" /></div><div class="col-md-6"><x-image-upload name="page_banner_url" label="Banner các trang trong" :value="$settings->page_banner_url" /></div></div></x-card><x-card type="info" title="Ảnh trang chủ" class="mt-3"><div class="row"><div class="col-md-6"><x-image-upload name="homepage_hero_url" label="Ảnh nền banner trang chủ" :value="$settings->homepage_hero_url" /></div><div class="col-md-6"><x-image-upload name="about_image_url" label="Ảnh giới thiệu HG" :value="$settings->about_image_url" /></div></div></x-card></div><div class="col-xl-4"><x-card type="secondary" title="Quy tắc tải lên"><x-input name="media_allowed_extensions" label="Định dạng cho phép" :value="$settings->media_allowed_extensions" required /><x-input type="number" name="media_max_size" label="Dung lượng tối đa (MB)" :value="$settings->media_max_size" required /></x-card><div class="card"><div class="card-body text-end"><button class="btn btn-primary">Lưu cài đặt media</button></div></div></div></div>
    </form>
@endsection
