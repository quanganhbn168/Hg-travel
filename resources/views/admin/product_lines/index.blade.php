@extends('layouts.admin')

@section('title', 'Giải pháp du lịch')
@section('page-title', 'Giải pháp du lịch trọng tâm')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Giải pháp du lịch</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách giải pháp" description="Quản lý các dòng sản phẩm chủ lực và landing page trên website." icon="bi-grid-1x2" :create-url="route('admin.product-lines.create')" create-label="Thêm giải pháp" resource="product_line" :order-start="$productLines->firstItem() ?? 1">
    <x-slot:filters>
        <form action="{{ route('admin.product-lines.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5"><label class="form-label" for="product-line-search">Từ khóa</label><input id="product-line-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên giải pháp hoặc slug"></div>
            <div class="col-lg-3"><label class="form-label" for="product-line-status">Trạng thái</label><select id="product-line-status" name="status" class="form-select"><option value="">Tất cả</option><option value="active" @selected(request('status') === 'active')>Đang hoạt động</option><option value="inactive" @selected(request('status') === 'inactive')>Đang tắt</option></select></div>
            <div class="col-lg-2"><label class="form-label" for="product-line-home">Trang chủ</label><select id="product-line-home" name="home" class="form-select"><option value="">Tất cả</option><option value="yes" @selected(request('home') === 'yes')>Đang hiển thị</option><option value="no" @selected(request('home') === 'no')>Không hiển thị</option></select></div>
            <div class="col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit">Lọc</button><a href="{{ route('admin.product-lines.index') }}" class="btn btn-default" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Giải pháp</th><th>Liên kết</th><th class="text-center">Trang chủ</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($productLines as $productLine)
            <tr data-record-id="{{ $productLine->id }}"><td data-select-column class="text-center"><input form="admin-bulk-product_line-form" type="checkbox" name="ids[]" value="{{ $productLine->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $productLine->name }}"></td><td><a href="{{ route('admin.product-lines.edit', $productLine) }}" class="fw-semibold text-decoration-none">{{ $productLine->name }}</a><small class="d-block text-body-secondary">{{ $productLine->slug }} · Thứ tự {{ $productLine->sort_order }}</small></td><td><span class="badge text-bg-light me-1">{{ $productLine->tours_count }} tour</span><span class="badge text-bg-light">{{ $productLine->services_count }} dịch vụ</span></td><td class="text-center"><span class="badge text-bg-{{ $productLine->is_home ? 'primary' : 'light' }}">{{ $productLine->is_home ? 'Hiển thị' : 'Không' }}</span></td><td class="text-center"><span class="badge text-bg-{{ $productLine->is_active ? 'success' : 'secondary' }}">{{ $productLine->is_active ? 'Hoạt động' : 'Đang tắt' }}</span></td><td class="text-end"><div class="btn-group btn-group-sm"><a href="{{ route('product-lines.show', ['productLine' => $productLine->slug]) }}" target="_blank" class="btn btn-default" title="Xem trang"><i class="bi bi-box-arrow-up-right"></i></a><a href="{{ route('admin.product-lines.edit', $productLine) }}" class="btn btn-default" title="Chỉnh sửa"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-product-line-{{ $productLine->id }}" class="btn btn-default text-danger" title="Xóa"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có giải pháp.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($productLines as $productLine)<form id="delete-product-line-{{ $productLine->id }}" action="{{ route('admin.product-lines.destroy', $productLine) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa giải pháp này?" data-delete-warning="Giải pháp đã xóa không thể khôi phục.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($productLines->hasPages()){{ $productLines->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
