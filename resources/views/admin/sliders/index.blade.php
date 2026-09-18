@extends('layouts.admin')

@section('title', 'Slider')
@section('page-title', 'Slider trang chủ')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Slider</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách slider"
    description="Quản lý các bộ slide; trang chủ sử dụng slider có key là home."
    :create-url="route('admin.sliders.create')"
    create-label="Thêm slider"
    resource="slider"
>
    <x-slot:filters>
        <form action="{{ route('admin.sliders.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label" for="slider-search">Từ khóa</label>
                <input id="slider-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên hoặc key slider">
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="slider-status">Trạng thái</label>
                <select id="slider-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Đang bật</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option>
                </select>
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-3">
                <x-admin.filter-actions :reset-url="route('admin.sliders.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="slider" />
                    <th>Tên</th>
                    <th>Key</th>
                    <th class="text-center">Slide</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sliders as $slider)
                    <tr data-record-id="{{ $slider->id }}">
                        <x-admin.select-item resource="slider" :id="$slider->id" :label="$slider->name" />

                        <td class="fw-semibold">{{ $slider->name }}</td>
                        <td><code>{{ $slider->key }}</code></td>
                        <td class="text-center">{{ $slider->items_count }}</td>

                        <td class="text-center">
                            <x-toggle model="slider" :id="$slider->id" field="is_active" :checked="$slider->is_active" />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="slider"
                                :edit-url="route('admin.sliders.edit', $slider)"
                                :delete-url="route('admin.sliders.destroy', $slider)"
                                delete-title="Xóa slider này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có slider." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($sliders->hasPages())
            {{ $sliders->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
