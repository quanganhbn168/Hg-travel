@extends('layouts.admin')

@section('title', 'Tour du lịch')
@section('page-title', 'Tour du lịch')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Tour du lịch</li></ol>
@endsection

@section('content')
    <x-admin.index-header description="Quản lý chương trình, lịch khởi hành và trạng thái nhận booking." :create-url="route('admin.tours.create')" create-label="Thêm tour" />
    <x-admin.filter-panel title="Bộ lọc tour">
        <form action="{{ route('admin.tours.index') }}" class="row g-3 align-items-end"><div class="col-lg-5"><label class="form-label" for="tour-search">Từ khóa</label><input id="tour-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Mã, tên tour hoặc slug"></div><div class="col-lg-3"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><option value="">Tất cả</option>@foreach(['draft' => 'Nháp', 'published' => 'Đã xuất bản', 'archived' => 'Lưu trữ'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div><div class="col-lg-2"><label class="form-label">Hoạt động</label><select name="active" class="form-select"><option value="">Tất cả</option><option value="1" @selected(request('active') === '1')>Đang bật</option><option value="0" @selected(request('active') === '0')>Đang tắt</option></select></div><div class="col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1">Lọc</button><a href="{{ route('admin.tours.index') }}" class="btn btn-default"><i class="bi bi-arrow-counterclockwise"></i></a></div></form>
    </x-admin.filter-panel>
    <x-admin.table-card title="Danh sách tour">
        <x-slot:tools><span class="badge text-bg-light">{{ $tours->total() }} bản ghi</span></x-slot:tools>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>#</th><th>Mã / tên tour</th><th>Danh mục</th><th>Điểm đến</th><th>Thời lượng</th><th>Giá từ</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>@forelse($tours as $tour)<tr><td class="text-body-secondary">#{{ $tour->id }}</td><td><a href="{{ route('admin.tours.edit', $tour) }}" class="fw-semibold text-decoration-none"><code>{{ $tour->code }}</code><span class="d-block">{{ $tour->name }}</span></a></td><td>{{ $tour->category?->name ?? '—' }}</td><td>{{ $tour->destination?->name ?? '—' }}</td><td>{{ $tour->duration_days }} ngày / {{ $tour->duration_nights }} đêm</td><td class="text-nowrap">{{ number_format($tour->starting_price, 0, ',', '.') }} ₫</td><td><span class="badge text-bg-{{ $tour->is_active ? 'success' : 'secondary' }}">{{ $tour->status }}</span></td><td class="text-end"><a href="{{ route('admin.tours.edit', $tour) }}" class="btn btn-default btn-sm"><i class="bi bi-pencil-square"></i></a><button form="delete-tour-{{ $tour->id }}" class="btn btn-default btn-sm text-danger"><i class="bi bi-trash"></i></button></td></tr>@empty<tr><td colspan="8" class="text-center py-5">Chưa có tour.</td></tr>@endforelse</tbody></table></div>
        @foreach($tours as $tour)<form id="delete-tour-{{ $tour->id }}" action="{{ route('admin.tours.destroy', $tour) }}" method="POST" class="d-none" onsubmit="return confirm('Xóa tour này?')">@csrf @method('DELETE')</form>@endforeach
        <x-slot:footer>@if($tours->hasPages()){{ $tours->links() }}@endif</x-slot:footer>
    </x-admin.table-card>
@endsection
