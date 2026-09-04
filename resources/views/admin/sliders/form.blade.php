@extends('layouts.admin')

@section('title', $slider->exists ? 'Sửa slider' : 'Thêm slider')
@section('page-title', $slider->exists ? 'Sửa slider' : 'Thêm slider')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.sliders.index') }}">Slider</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $slider->exists ? 'Chỉnh sửa' : 'Thêm mới' }}</li>
    </ol>
@endsection

@section('content')
    <form method="POST" action="{{ $slider->exists ? route('admin.sliders.update', $slider) : route('admin.sliders.store') }}">
        @csrf
        @if($slider->exists) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="Thông tin slider" :collapsible="true">
                    <x-input name="name" label="Tên slider" :value="$slider->name" required />
                    <x-input name="key" label="Key" :value="$slider->key" required :readonly="$slider->exists" aria-describedby="slider-key-help" />
                    <div id="slider-key-help" class="form-text mb-3">
                        @if($slider->exists)
                            Key đang được trang chủ sử dụng nên không cho đổi sau khi tạo.
                        @else
                            Dùng chữ thường, số và dấu gạch ngang; hệ thống sẽ tự chuẩn hóa.
                        @endif
                    </div>
                    <label class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $slider->is_active))>
                        <span class="form-check-label fw-semibold">Hiển thị slider</span>
                    </label>
                </x-card>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-end gap-2">
                        <a class="btn btn-default" href="{{ route('admin.sliders.index') }}">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-1"></i>{{ $slider->exists ? 'Lưu thay đổi' : 'Tạo slider' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($slider->exists)
        <div class="row g-3 mt-1">
            <div class="col-12">
                <x-card type="secondary" title="Các slide" :collapsible="true">
                    @if($items->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 180px">Ảnh</th>
                                        <th>Nội dung</th>
                                        <th>CTA</th>
                                        <th class="text-center">Thứ tự</th>
                                        <th class="text-center">Hiển thị</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        @php
                                            $itemImageUrl = \Illuminate\Support\Str::startsWith((string) $item->image_path, ['http://', 'https://'])
                                                ? $item->image_path
                                                : asset($item->image_path);
                                        @endphp
                                        <tr data-record-id="{{ $item->id }}">
                                            <td>
                                                <img src="{{ $itemImageUrl }}" alt="{{ $item->title ?: 'Ảnh slide' }}" class="slider-item-thumb rounded border">
                                            </td>
                                            <td>
                                                <strong>{{ $item->title ?: 'Không tiêu đề' }}</strong>
                                                @if($item->subtitle)<small class="d-block text-body-secondary text-truncate" style="max-width: 360px">{{ $item->subtitle }}</small>@endif
                                            </td>
                                            <td>
                                                @if($item->button_label)<span>{{ $item->button_label }}</span>@endif
                                                @if($item->button_url)<small class="d-block text-body-secondary text-truncate" style="max-width: 180px">{{ $item->button_url }}</small>@endif
                                            </td>
                                            <td class="text-center">{{ $item->sort_order }}</td>
                                            <td class="text-center">
                                                <x-toggle model="slider_item" :id="$item->id" field="is_active" :checked="$item->is_active" label="" />
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a class="btn btn-default" href="{{ route('admin.sliders.items.edit', [$slider, $item]) }}" title="Sửa slide" aria-label="Sửa slide">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form action="{{ route('admin.sliders.items.destroy', [$slider, $item]) }}" method="POST" data-admin-delete-form data-delete-title="Xóa slide này?" data-delete-warning="Slide sẽ bị xóa khỏi slider.">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" title="Xóa slide" aria-label="Xóa slide">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-body-secondary">Slider chưa có slide nào.</div>
                    @endif
                </x-card>
            </div>

            <div class="col-12">
                <x-card type="primary" title="Thêm slide" :collapsible="true">
                    <form method="POST" action="{{ route('admin.sliders.items.store', $slider) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-xl-6">
                                <x-input name="title" label="Tiêu đề" />
                                <x-textarea name="subtitle" label="Mô tả" rows="4" />
                                <x-image-upload name="image_path" label="Ảnh slide" required />
                            </div>
                            <div class="col-xl-6">
                                <x-input name="button_label" label="Nhãn nút" placeholder="Ví dụ: Khám phá hành trình" />
                                <x-input name="button_url" label="Đường dẫn nút" placeholder="/tours hoặc https://..." />
                                <x-input type="number" name="sort_order" label="Thứ tự" value="{{ old('sort_order', $items->count() + 1) }}" min="0" />
                                <label class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                                    <span class="form-check-label fw-semibold">Hiển thị slide</span>
                                </label>
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Thêm slide</button>
                        </div>
                    </form>
                </x-card>
            </div>
        </div>
    @endif
@endsection

@pushOnce('css')
    <style>
        .slider-item-thumb { width: 160px; height: 90px; object-fit: cover; }
    </style>
@endpushOnce
