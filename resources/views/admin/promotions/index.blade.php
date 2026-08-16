@extends('layouts.admin')

@section('title', 'Ưu đãi')
@section('page-title', 'Ưu đãi tour')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Ưu đãi</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách ưu đãi" description="Ưu đãi đang hiệu lực sẽ tự xuất hiện tại trang chủ và tour liên quan." icon="bi-percent" :create-url="route('admin.promotions.create')" create-label="Thêm ưu đãi" resource="promotion">
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Tên</th><th>Giảm</th><th>Thời gian</th><th>Tour</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($promotions as $promotion)
            <tr data-record-id="{{ $promotion->id }}"><td data-select-column class="text-center"><input form="admin-bulk-promotion-form" type="checkbox" name="ids[]" value="{{ $promotion->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $promotion->name }}"></td><td>{{ $promotion->name }}</td><td>{{ $promotion->discount_type === 'percentage' ? $promotion->discount_value.'%' : number_format($promotion->discount_value,0,',','.') . 'đ' }}</td><td>{{ optional($promotion->starts_at)->format('d/m/Y') }} — {{ optional($promotion->ends_at)->format('d/m/Y') }}</td><td>{{ $promotion->tours_count }}</td><td class="text-center"><span class="badge text-bg-{{ $promotion->is_active ? 'success' : 'secondary' }}">{{ $promotion->is_active ? 'Hoạt động' : 'Đang tắt' }}</span></td><td class="text-end"><div class="btn-group btn-group-sm"><a class="btn btn-default" href="{{ route('admin.promotions.edit', $promotion) }}"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-promotion-{{ $promotion->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="8" class="text-center py-5">Chưa có ưu đãi.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($promotions as $promotion)<form id="delete-promotion-{{ $promotion->id }}" action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa ưu đãi này?" data-delete-warning="Ưu đãi đã xóa không thể khôi phục.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($promotions->hasPages()){{ $promotions->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
