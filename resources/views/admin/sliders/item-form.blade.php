@extends('layouts.admin')

@section('title', 'Sửa slide')
@section('page-title', 'Sửa slide')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.sliders.index') }}">Slider</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.sliders.edit', $slider) }}">{{ $slider->name }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Sửa slide</li>
    </ol>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.sliders.items.update', [$slider, $item]) }}">
        @csrf @method('PUT')

        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="Nội dung slide" :collapsible="true">
                    <x-input name="title" label="Tiêu đề" :value="$item->title" />
                    <x-textarea name="subtitle" label="Mô tả" :value="$item->subtitle" rows="5" />
                    <x-image-upload name="image_path" label="Ảnh slide" :value="$item->image_path" required />
                    <div class="form-text">Ảnh hiện tại sẽ được giữ nguyên nếu anh không chọn ảnh mới.</div>
                </x-card>
            </div>

            <div class="col-xl-4">
                <x-card type="info" title="Nút kêu gọi hành động" :collapsible="true" class="mb-3">
                    <x-input name="button_label" label="Nhãn nút" :value="$item->button_label" />
                    <x-input name="button_url" label="Đường dẫn nút" :value="$item->button_url" placeholder="/tours hoặc https://..." />
                </x-card>

                <x-card type="secondary" title="Hiển thị" :collapsible="true">
                    <x-input type="number" name="sort_order" label="Thứ tự" :value="$item->sort_order" min="0" />
                    <label class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))>
                        <span class="form-check-label fw-semibold">Hiển thị slide</span>
                    </label>
                </x-card>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-end gap-2">
                        <a class="btn btn-default" href="{{ route('admin.sliders.edit', $slider) }}">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Lưu slide</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
