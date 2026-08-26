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
    icon="bi-compass"
    :create-url="route('admin.tours.create')"
    create-label="Thêm tour"
    resource="tour"
    :order-start="$tours->firstItem() ?? 1"
>
    <x-slot:actions>
        <a href="{{ route('admin.tours.import.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-arrow-up me-1"></i>Nhập lịch Excel
        </a>
    </x-slot:actions>

    <x-slot:filters>
        <form action="{{ route('admin.tours.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label" for="tour-search">Từ khóa</label>
                <input id="tour-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Mã, tên tour hoặc slug">
            </div>
            <div class="col-lg-3">
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
            <div class="col-lg-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">Lọc</button>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-default" title="Xóa bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th data-select-column class="text-center" style="width:48px">
                        <input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả">
                    </th>
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
                        <td data-select-column class="text-center">
                            <input form="admin-bulk-tour-form" type="checkbox" name="ids[]" value="{{ $tour->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $tour->name }}">
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
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.tours.edit', $tour) }}" class="btn btn-default" title="Chỉnh sửa">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="submit" form="delete-tour-{{ $tour->id }}" class="btn btn-default text-danger" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">Chưa có tour.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @foreach($tours as $tour)
        <form
            id="delete-tour-{{ $tour->id }}"
            action="{{ route('admin.tours.destroy', $tour) }}"
            method="POST"
            class="d-none"
            data-admin-delete-form
            data-delete-title="Xóa tour này?"
            data-delete-warning="Dữ liệu tour đã xóa không thể khôi phục."
        >
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <x-slot:footer>
        @if($tours->hasPages())
            {{ $tours->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
