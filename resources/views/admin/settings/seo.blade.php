@extends('layouts.admin')

@section('title', 'Cài đặt SEO')
@section('page-title', 'Cài đặt')

@section('content')
    <x-admin.settings-layout title="Cài đặt SEO" description="Thiết lập các giá trị SEO mặc định; nội dung SEO riêng của từng trang, tour hoặc bài viết sẽ được ưu tiên.">
    <x-slot:actions>
        <button form="admin-settings-seo-form" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Lưu cài đặt SEO</button>
    </x-slot:actions>
    <form id="admin-settings-seo-form" action="{{ route('admin.settings.seo.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="SEO mặc định">
                    <x-input name="seo_title" label="SEO title mặc định" :value="$settings->seo_title" />
                    <x-textarea name="seo_description" label="SEO description mặc định" :value="$settings->seo_description" rows="4" />
                    <x-textarea name="seo_keywords" label="SEO keywords mặc định" :value="$settings->seo_keywords" rows="3" />
                    <p class="text-muted small mb-0">Trang, tour và bài viết có SEO riêng sẽ được ưu tiên hơn các giá trị mặc định này.</p>
                </x-card>
            </div>
        </div>
    </form>
    </x-admin.settings-layout>
@endsection
