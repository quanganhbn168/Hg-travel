@extends('layouts.admin')

@section('title', 'Khoảnh khắc')
@section('page-title', 'Khoảnh khắc')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Khoảnh khắc</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Thư viện khoảnh khắc" description="Quản lý ảnh cảm hứng trên trang chủ và nhóm hiển thị của GLightbox." icon="bi-images" :create-url="route('admin.travel-moments.create')" create-label="Thêm khoảnh khắc" resource="travel_moment" :order-start="$moments->firstItem() ?? 1">
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th style="width:90px">Ảnh</th><th>Khoảnh khắc</th><th>Nhóm GLightbox</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($moments as $moment)
            <tr data-record-id="{{ $moment->id }}"><td data-select-column class="text-center"><input form="admin-bulk-travel_moment-form" type="checkbox" name="ids[]" value="{{ $moment->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $moment->title }}"></td><td><img src="{{ \Illuminate\Support\Str::startsWith($moment->image_url, ['http://', 'https://', '/']) ? $moment->image_url : asset($moment->image_url) }}" alt="" width="72" height="52" class="rounded object-fit-cover"></td><td><a href="{{ route('admin.travel-moments.edit', $moment) }}" class="fw-semibold text-decoration-none">{{ $moment->title }}</a><small class="d-block text-body-secondary">Thứ tự {{ $moment->sort_order }}</small></td><td><span class="badge text-bg-light border">{{ $moment->group?->name }}</span><small class="d-block text-body-secondary">data-gallery: moments-{{ $moment->group?->slug }}</small></td><td class="text-center"><x-toggle model="travel_moment" :id="$moment->id" field="is_active" :checked="$moment->is_active" /></td><td class="text-end"><div class="btn-group btn-group-sm"><a class="btn btn-default" href="{{ route('admin.travel-moments.edit', $moment) }}">Sửa</a><button type="submit" form="delete-travel-moment-{{ $moment->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="6" class="text-center py-5">Chưa có khoảnh khắc.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($moments as $moment)<form id="delete-travel-moment-{{ $moment->id }}" action="{{ route('admin.travel-moments.destroy', $moment) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa khoảnh khắc này?" data-delete-warning="Khoảnh khắc đã xóa không thể khôi phục.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($moments->hasPages()){{ $moments->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
