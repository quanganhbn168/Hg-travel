@extends('layouts.admin')

@section('title', 'Chỉnh sửa loại hình tour')
@section('page-title', 'Chỉnh sửa loại hình tour')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.tour-categories.index') }}">Loại hình tour</a></li><li class="breadcrumb-item active">Chỉnh sửa</li></ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.tour-categories.update', $category) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-xl-8"><x-card type="primary" title="Thông tin loại hình" :collapsible="true"><x-input name="name" label="Tên loại hình" :value="$category->name" required /><x-input name="slug" label="Slug" :value="$category->slug" required /><x-tinymce name="description" label="Mô tả" :value="$category->description" rows="7" /></x-card></div>
            <div class="col-xl-4">
                <x-card type="info" title="Cấu hình hiển thị" :collapsible="true" class="mb-3">
                    <x-select name="parent_id" label="Danh mục cha" :options="$parents->pluck('name', 'id')->all()" :selected="$category->parent_id" placeholder="Không có danh mục cha" />
                    <x-image-upload name="cover_image" label="Ảnh cover" :value="$category->cover_image" />
                    <x-input name="sort_order" type="number" label="Thứ tự hiển thị" :value="$category->sort_order" />
                    <div class="border-top pt-3">
                        <label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))><span class="form-check-label fw-semibold">Kích hoạt</span></label>
                        <label class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox" name="is_home" value="1" @checked(old('is_home', $category->is_home))><span class="form-check-label fw-semibold">Hiển thị trên trang chủ</span></label>
                    </div>
                </x-card>
                <x-card type="secondary" title="Tối ưu SEO" :collapsible="true"><x-input name="seo_title" label="SEO Title" :value="$category->seo_title" /><x-textarea name="seo_description" label="SEO Description" :value="$category->seo_description" rows="3" /></x-card>
            </div>
            <div class="col-12"><div class="card"><div class="card-body d-flex justify-content-end gap-2"><a href="{{ route('admin.tour-categories.index') }}" class="btn btn-default">Hủy bỏ</a><button class="btn btn-primary">Lưu thay đổi</button></div></div></div>
        </div>
    </form>
@endsection
