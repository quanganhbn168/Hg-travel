@extends('layouts.admin')

@section('title', 'Cài đặt media')
@section('page-title', 'Cài đặt')

@section('content')
    <x-admin.settings-layout title="Cài đặt media" description="Quản lý logo, favicon, ảnh chia sẻ và quy tắc upload media cho toàn hệ thống.">
    <x-slot:actions>
        <button form="admin-settings-media-form" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Lưu cài đặt media</button>
    </x-slot:actions>
    <form id="admin-settings-media-form" action="{{ route('admin.settings.media.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="Nhận diện thương hiệu">
                    <div class="row g-3"><div class="col-md-6"><x-image-upload name="logo_url" label="Logo" :value="$settings->logo_url" /></div><div class="col-md-6"><x-image-upload name="favicon_url" label="Favicon" :value="$settings->favicon_url" /></div></div>
                    <div class="row g-3"><div class="col-md-6"><x-image-upload name="image_share_url" label="Ảnh chia sẻ OG/Twitter" :value="$settings->image_share_url" /></div><div class="col-md-6"><x-image-upload name="page_banner_url" label="Banner các trang trong" :value="$settings->page_banner_url" /></div></div>
                </x-card>
                <x-card type="info" title="Ảnh trang chủ" class="mt-3">
                    <div class="row g-3"><div class="col-md-6"><x-image-upload name="homepage_hero_url" label="Ảnh nền banner trang chủ" :value="$settings->homepage_hero_url" /></div><div class="col-md-6"><x-image-upload name="about_image_url" label="Ảnh giới thiệu HG" :value="$settings->about_image_url" /></div></div>
                </x-card>
            </div>
            <div class="col-xl-4">
                <x-card type="secondary" title="Quy tắc tải lên">
                    <x-input name="media_allowed_extensions" label="Định dạng cho phép" :value="$settings->media_allowed_extensions" required />
                    <x-input type="number" name="media_max_size" label="Dung lượng tối đa (MB)" :value="$settings->media_max_size" required />
                </x-card>
            </div>
        </div>
    </form>
    </x-admin.settings-layout>
@endsection
