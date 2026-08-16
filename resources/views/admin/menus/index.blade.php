@extends('layouts.admin')

@section('title', 'Menu website')
@section('page-title', 'Menu website')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Menu website</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Danh sách menu" description="Quản lý điều hướng header và footer. Mỗi vị trí chỉ có một menu." icon="bi-list-nested" :create-url="route('admin.menus.create')" create-label="Thêm menu" resource="menu">
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Tên</th><th>Vị trí</th><th>Số mục</th><th class="text-center">Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($menus as $menu)
            <tr data-record-id="{{ $menu->id }}"><td data-select-column class="text-center"><input form="admin-bulk-menu-form" type="checkbox" name="ids[]" value="{{ $menu->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $menu->name }}"></td><td>{{ $menu->name }}</td><td><code>{{ $menu->location }}</code></td><td>{{ $menu->items_count }}</td><td class="text-center"><span class="badge text-bg-{{ $menu->is_active ? 'success' : 'secondary' }}">{{ $menu->is_active ? 'Hiển thị' : 'Đang tắt' }}</span></td><td class="text-end"><div class="btn-group btn-group-sm"><a class="btn btn-default" href="{{ route('admin.menus.edit', $menu) }}"><i class="bi bi-pencil-square"></i></a><button type="submit" form="delete-menu-{{ $menu->id }}" class="btn btn-default text-danger"><i class="bi bi-trash"></i></button></div></td></tr>
        @empty
            <tr><td colspan="7" class="text-center py-5">Chưa có menu.</td></tr>
        @endforelse
    </tbody></table></div>
    @foreach($menus as $menu)<form id="delete-menu-{{ $menu->id }}" action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="d-none" data-admin-delete-form data-delete-title="Xóa menu này?" data-delete-warning="Các mục menu liên quan cũng sẽ bị ảnh hưởng.">@csrf @method('DELETE')</form>@endforeach
    <x-slot:footer>@if($menus->hasPages()){{ $menus->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
