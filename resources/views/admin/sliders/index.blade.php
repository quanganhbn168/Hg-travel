@extends('layouts.admin')

@section('title', 'Slider')
@section('page-title', 'Slider trang chủ')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Slider</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách slider" description="Quản lý các bộ slide; trang chủ sử dụng slider có key là home." icon="bi-images" :create-url="route('admin.sliders.create')" create-label="Thêm slider" resource="slider">
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Tên</th><th>Key</th><th>Slide</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($sliders as $slider)
            <tr data-record-id="{{ $slider->id }}"><td data-select-column class="text-center"><input form="admin-bulk-slider-form" type="checkbox" name="ids[]" value="{{ $slider->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $slider->name }}"></td><td>{{ $slider->name }}</td><td><code>{{ $slider->key }}</code></td><td>{{ $slider->items_count }}</td><td class="text-center"><span class="badge text-bg-{{ $slider->is_active ? 'success' : 'secondary' }}">{{ $slider->is_active ? 'Hiển thị' : 'Đang tắt' }}</span></td><td class="text-end"><div class="btn-group btn-group-sm"><a class="btn btn-default" href="{{ route('admin.sliders.edit', $slider) }}"><i class="bi bi-pencil"></i></a><button type="submit" form="delete-slider-{{ $slider->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có slider.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($sliders as $slider)<form id="delete-slider-{{ $slider->id }}" action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa slider này?" data-delete-warning="Slider và các slide liên quan sẽ bị xóa.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($sliders->hasPages()){{ $sliders->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
