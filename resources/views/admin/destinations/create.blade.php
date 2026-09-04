@extends('layouts.admin')

@section('title', 'Thêm điểm đến')
@section('page-title', 'Thêm điểm đến')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.destinations.index') }}">Điểm đến</a></li>
        <li class="breadcrumb-item active">Thêm mới</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.destinations.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="Thông tin điểm đến" :collapsible="true">
                    <x-input name="name" id="destination-name" label="Tên điểm đến" required />
                    <x-slug name="slug" label="Slug" source="destination-name" required />
                    <x-textarea name="summary" label="Mô tả ngắn" rows="3" />
                    <x-tinymce name="description" label="Mô tả chi tiết" rows="8" />
                </x-card>
            </div>
            <div class="col-xl-4">
                <x-card type="info" title="Cấu hình hiển thị" :collapsible="true" class="mb-3">
                    <x-select name="parent_id" label="Điểm đến cha" :options="collect($parentOptions)->mapWithKeys(fn (array $option): array => [$option['id'] => $option['label']])->all()" :selected="old('parent_id')" placeholder="Không có điểm đến cha" />
                    <div class="row g-2">
                        <div class="col-md-6"><x-select name="type" label="Loại điểm đến" :options="$types" :selected="old('type', 'city')" required /></div>
                        <div class="col-md-6"><x-select name="market" label="Thị trường" :options="$markets" :selected="old('market', 'international')" required /></div>
                    </div>
                    <x-image-upload name="cover_image" label="Ảnh cover" />
                    <x-input name="sort_order" type="number" label="Thứ tự hiển thị" value="0" />
                    <div class="border-top pt-3 mb-3">
                        <label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><span class="form-check-label fw-semibold">Kích hoạt</span></label>
                    </div>
                    <div class="mb-3"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_featured" value="1"><span class="form-check-label fw-semibold">Điểm đến nổi bật</span></label></div>
                    <div class="mb-0"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="landing_enabled" value="1" @checked(old('landing_enabled'))><span class="form-check-label fw-semibold">Cho phép trang landing</span></label><div class="form-text">Chỉ hiển thị public khi điểm đến có tour đã xuất bản.</div></div>
                </x-card>
                <x-card type="secondary" title="Tối ưu SEO" :collapsible="true">
                    <x-input name="seo_title" label="SEO Title" />
                    <x-textarea name="seo_description" label="SEO Description" rows="3" />
                </x-card>
            </div>
            <div class="col-12"><div class="card"><div class="card-body d-flex justify-content-end gap-2"><a href="{{ route('admin.destinations.index') }}" class="btn btn-default">Hủy bỏ</a><button name="submit_action" value="save_and_create" class="btn btn-outline-primary">Lưu & tạo mới</button><button class="btn btn-primary">Lưu điểm đến</button></div></div></div>
        </div>
    </form>
@endsection
