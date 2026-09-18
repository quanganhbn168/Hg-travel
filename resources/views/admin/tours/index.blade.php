@extends('layouts.admin')

@section('title', 'Tour du lịch')
@section('page-title', 'Tour du lịch')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tour du lịch</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Danh sách tour"
    description="Quản lý chương trình, lịch khởi hành và trạng thái nhận booking."
    :create-url="route('admin.tours.create')"
    create-label="Thêm tour"
    resource="tour"
    :order-start="$tours->firstItem() ?? 1"
>
    <x-slot:actions>
        @if(auth('admin')->user()?->hasPermissionTo('tours.update', 'web'))
            <a href="{{ route('admin.tours.import.create') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-arrow-up me-1"></i>
                Nhập lịch Excel
            </a>
        @endif
    </x-slot:actions>

    <x-slot:filters>
        <form action="{{ route('admin.tours.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label" for="tour-search">Từ khóa</label>
                <input id="tour-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Mã, tên tour hoặc slug">
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="tour-status">Trạng thái</label>
                <select id="tour-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach(['draft' => 'Nháp', 'published' => 'Đã xuất bản', 'archived' => 'Lưu trữ'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="tour-active">Hoạt động</label>
                <select id="tour-active" name="active" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="1" @selected(request('active') === '1')>Đang bật</option>
                    <option value="0" @selected(request('active') === '0')>Đang tắt</option>
                </select>
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-2">
                <x-admin.filter-actions :reset-url="route('admin.tours.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <x-admin.select-all resource="tour" />
                    <th style="width:76px">Ảnh</th>
                    <th>Mã / tên tour</th>
                    <th>Danh mục</th>
                    <th>Điểm đến</th>
                    <th>Thời lượng</th>
                    <th>Giá từ</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tours as $tour)
                    <tr data-record-id="{{ $tour->id }}">
                        <x-admin.select-item resource="tour" :id="$tour->id" :label="$tour->name" />

                        <td>
                            <x-admin.thumbnail
                                :src="$tour->cover_url"
                                :alt="$tour->name"
                                :href="route('admin.tours.edit', $tour)"
                                :size="56"
                            />
                        </td>

                        <td>
                            <a href="{{ route('admin.tours.edit', $tour) }}" class="fw-semibold text-decoration-none">
                                <code>{{ $tour->code }}</code>
                                <span class="d-block">{{ $tour->name }}</span>
                            </a>
                        </td>

                        <td>{{ $tour->categories->pluck('name')->join(' · ') ?: '—' }}</td>
                        <td>{{ $tour->destinations->pluck('name')->join(' · ') ?: '—' }}</td>
                        <td>{{ $tour->duration_days }} ngày / {{ $tour->duration_nights }} đêm</td>
                        <td class="text-nowrap">{{ number_format($tour->starting_price, 0, ',', '.') }} ₫</td>

                        <td>
                            @php($status = [
                                'draft' => ['label' => 'Nháp', 'class' => 'secondary'],
                                'published' => ['label' => 'Đã xuất bản', 'class' => 'success'],
                                'archived' => ['label' => 'Lưu trữ', 'class' => 'dark'],
                            ][$tour->status] ?? ['label' => $tour->status, 'class' => 'secondary'])
                            <span class="badge text-bg-{{ $status['class'] }}">{{ $status['label'] }}</span>
                        </td>

                        <td class="text-end">
                            <x-admin.row-actions
                                resource="tour"
                                :view-url="route('tours.show', ['tour' => $tour->slug])"
                                :edit-url="route('admin.tours.edit', $tour)"
                                :delete-url="route('admin.tours.destroy', $tour)"
                                delete-title="Xóa tour này?"
                            />
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có tour." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($tours->hasPages())
            {{ $tours->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
