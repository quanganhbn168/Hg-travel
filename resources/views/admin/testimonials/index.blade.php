@extends('layouts.admin')

@section('title', 'Cảm nhận khách hàng')
@section('page-title', 'Cảm nhận khách hàng')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Cảm nhận khách hàng</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách cảm nhận" description="Các cảm nhận được hiển thị tại trang chủ." icon="bi-chat-quote" :create-url="route('admin.testimonials.create')" create-label="Thêm cảm nhận" resource="testimonial" :order-start="$testimonials->firstItem() ?? 1">
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Khách hàng</th><th>Nội dung</th><th class="text-center">Đánh giá</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($testimonials as $testimonial)
            <tr data-record-id="{{ $testimonial->id }}"><td data-select-column class="text-center"><input form="admin-bulk-testimonial-form" type="checkbox" name="ids[]" value="{{ $testimonial->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $testimonial->customer_name }}"></td><td><strong>{{ $testimonial->customer_name }}</strong><small class="d-block">{{ $testimonial->customer_title }}</small></td><td>{{ \Illuminate\Support\Str::limit($testimonial->content, 90) }}</td><td class="text-center">{{ $testimonial->rating }}/5</td><td class="text-center"><x-toggle model="testimonial" :id="$testimonial->id" field="is_active" :checked="$testimonial->is_active" /></td><td class="text-end"><div class="btn-group btn-group-sm"><a class="btn btn-default" href="{{ route('admin.testimonials.edit', $testimonial) }}">Sửa</a><button type="submit" form="delete-testimonial-{{ $testimonial->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có cảm nhận.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($testimonials as $testimonial)<form id="delete-testimonial-{{ $testimonial->id }}" action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa cảm nhận này?" data-delete-warning="Cảm nhận đã xóa không thể khôi phục.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($testimonials->hasPages()){{ $testimonials->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
