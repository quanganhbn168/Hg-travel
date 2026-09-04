@extends('layouts.admin')

@section('title', 'Chỉnh sửa điểm đến')
@section('page-title', 'Chỉnh sửa điểm đến')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.destinations.index') }}">Điểm đến</a></li>
        <li class="breadcrumb-item active">Chỉnh sửa</li>
    </ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.destinations.update', $destination) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            @if($destination->is_system)
                <div class="col-xl-8">
                    <x-card type="secondary" title="Thông tin điểm đến hệ thống" :collapsible="true">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-label font-weight-bold">Tên điểm đến</div>
                                <div class="form-control bg-body-secondary">{{ $destination->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label font-weight-bold">Slug</div>
                                <div class="form-control bg-body-secondary"><code>{{ $destination->slug }}</code></div>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            Điểm đến này thuộc dữ liệu hệ thống nên chỉ cho phép thay ảnh cover. Tên, slug, nội dung, cấu hình hiển thị và SEO được giữ cố định.
                        </div>
                    </x-card>
                </div>
                <div class="col-xl-4">
                    <x-card type="info" title="Ảnh cover" :collapsible="true">
                        <x-image-upload name="cover_image" label="Ảnh cover" :value="$destination->cover_image" />
                    </x-card>
                </div>
            @else
            <div class="col-xl-8">
                <x-card type="primary" title="Thông tin điểm đến" :collapsible="true">
                    <x-input name="name" id="destination-name" label="Tên điểm đến" :value="$destination->name" required />
                    <x-slug name="slug" label="Slug" :value="$destination->slug" source="destination-name" required />
                    <x-textarea name="summary" label="Mô tả ngắn" :value="$destination->summary" rows="3" />
                    <x-tinymce name="description" label="Mô tả chi tiết" :value="$destination->description" rows="8" />
                </x-card>
            </div>
            <div class="col-xl-4">
                <x-card type="info" title="Cấu hình hiển thị" :collapsible="true" class="mb-3">
                    <x-select name="parent_id" label="Điểm đến cha" :options="$parents->pluck('name', 'id')->all()" :selected="$destination->parent_id" placeholder="Không có điểm đến cha" />
                    <x-image-upload name="cover_image" label="Ảnh cover" :value="$destination->cover_image" />
                    <x-input name="sort_order" type="number" label="Thứ tự hiển thị" :value="$destination->sort_order" />
                    <div class="border-top pt-3 mb-3"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $destination->is_active))><span class="form-check-label fw-semibold">Kích hoạt</span></label></div>
                    <div class="mb-3"><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $destination->is_featured))><span class="form-check-label fw-semibold">Điểm đến nổi bật</span></label></div>
                </x-card>
                <x-card type="secondary" title="Tối ưu SEO" :collapsible="true">
                    <x-input name="seo_title" label="SEO Title" :value="$destination->seo_title" />
                    <x-textarea name="seo_description" label="SEO Description" :value="$destination->seo_description" rows="3" />
                </x-card>
            </div>
            @endif
            <div class="col-12"><div class="card"><div class="card-body d-flex justify-content-end gap-2"><a href="{{ route('admin.destinations.index') }}" class="btn btn-default">Hủy bỏ</a><button class="btn btn-primary">{{ $destination->is_system ? 'Lưu ảnh cover' : 'Lưu thay đổi' }}</button></div></div></div>
        </div>
    </form>
@endsection
