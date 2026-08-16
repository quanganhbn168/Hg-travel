@extends('layouts.admin')

@section('title', 'Mã giảm giá')
@section('page-title', 'Mã giảm giá')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Mã giảm giá</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách coupon" description="Quản lý coupon theo từng tour hoặc toàn bộ hệ thống." icon="bi-ticket-perforated" :create-url="route('admin.coupons.create')" create-label="Thêm mã giảm giá" resource="coupon">
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Mã</th><th>Tên</th><th>Giảm</th><th>Đã dùng</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($coupons as $coupon)
            <tr data-record-id="{{ $coupon->id }}"><td data-select-column class="text-center"><input form="admin-bulk-coupon-form" type="checkbox" name="ids[]" value="{{ $coupon->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $coupon->code }}"></td><td><code>{{ $coupon->code }}</code></td><td>{{ $coupon->name }}</td><td>{{ $coupon->discount_type === 'percentage' ? $coupon->discount_value.'%' : number_format($coupon->discount_value,0,',','.') . 'đ' }}</td><td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? '/'.$coupon->usage_limit : '' }}</td><td class="text-center"><span class="badge text-bg-{{ $coupon->is_active ? 'success' : 'secondary' }}">{{ $coupon->is_active ? 'Hoạt động' : 'Đang tắt' }}</span></td><td class="text-end"><div class="btn-group btn-group-sm"><a class="btn btn-default" href="{{ route('admin.coupons.edit', $coupon) }}"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-coupon-{{ $coupon->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="8" class="text-center py-5">Chưa có coupon.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($coupons as $coupon)<form id="delete-coupon-{{ $coupon->id }}" action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa mã giảm giá này?" data-delete-warning="Mã giảm giá đã xóa không thể khôi phục.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($coupons->hasPages()){{ $coupons->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
