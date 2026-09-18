@extends('layouts.admin')

@section('title', 'Dịch vụ')
@section('page-title', 'Dịch vụ')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Dịch vụ</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách dịch vụ"
    description="Quản lý từng dịch vụ thuộc catalogue, landing page và các giải pháp trọng tâm."
    :create-url="route('admin.services.create')"
    create-label="Thêm dịch vụ"
    resource="service"
    :order-start="$services->firstItem() ?? 1"
>
    <x-slot:filters>
        <form action="{{ route('admin.services.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label" for="service-search">Từ khóa</label>
                <input id="service-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên hoặc slug">
            </div>

            <div class="col-lg-3">
                <label class="form-label" for="service-category">Danh mục</label>
                <select id="service-category" name="category" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="service-status">Trạng thái</label>
                <select id="service-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('status') === 'active')>Hoạt động</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option>
                </select>
            </div>

            <div class="col-lg-1">
                <x-admin.per-page />
            </div>

            <div class="col-lg-2">
                <x-admin.filter-actions :reset-url="route('admin.services.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="service" />
                    <th style="width:76px">Ảnh</th>
                    <th>Dịch vụ</th>
                    <th>Danh mục</th>
                    <th>Slug</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                    <tr data-record-id="{{ $service->id }}">
                        <x-admin.select-item resource="service" :id="$service->id" :label="$service->name" />

                        <td>
                            <x-admin.thumbnail
                                :src="$service->cover_url"
                                :alt="$service->name"
                                :href="route('admin.services.edit', $service)"
                                :size="56"
                            />
                        </td>

                        <td>
                            <a class="fw-semibold text-decoration-none" href="{{ route('admin.services.edit', $service) }}">
                                {{ $service->name }}
                            </a>
                            <small class="d-block text-body-secondary">
                                {{ $service->description ? IlluminateSupportStr::limit($service->description, 90) : 'Chưa có mô tả' }}
                            </small>
                        </td>

                        <td>{{ $service->category?->name ?: '—' }}</td>
                        <td><code>{{ $service->slug }}</code></td>

                        <td class="text-center">
                            <x-toggle model="service" :id="$service->id" field="is_active" :checked="$service->is_active" />
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="service"
                                :view-url="route('services.show', ['service' => $service->slug])"
                                :edit-url="route('admin.services.edit', $service)"
                                :delete-url="route('admin.services.destroy', $service)"
                                delete-title="Xóa dịch vụ này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có dịch vụ." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($services->hasPages())
            {{ $services->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
