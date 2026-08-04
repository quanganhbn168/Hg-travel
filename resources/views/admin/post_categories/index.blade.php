@extends('layouts.admin')
@section('title', 'Danh mục bài viết')
@section('page-title', 'Danh mục bài viết')
@section('content')
<x-admin.index-header description="Phân loại bài viết tin tức và cẩm nang." :create-url="route('admin.post-categories.create')" create-label="Thêm danh mục" />
<x-admin.filter-panel title="Tìm kiếm"><div class="row g-2"><div class="col-md-8"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Tên danh mục"></div><div class="col-md-4"><button class="btn btn-primary">Lọc</button></div></div></x-admin.filter-panel>
<x-admin.table-card title="Danh sách"><table class="table mb-0"><thead><tr><th>Tên</th><th>Danh mục cha</th><th>Thứ tự</th><th>Trạng thái</th><th></th></tr></thead><tbody>@forelse($categories as $category)<tr><td>{{ $category->name }}</td><td>{{ $category->parent?->name ?: '—' }}</td><td>{{ $category->sort_order }}</td><td><span class="badge text-bg-{{ $category->is_active?'success':'secondary' }}">{{ $category->is_active?'Bật':'Tắt' }}</span></td><td class="text-end"><a href="{{ route('admin.post-categories.edit',$category) }}" class="btn btn-sm btn-outline-primary">Sửa</a></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Chưa có danh mục.</td></tr>@endforelse</tbody></table><x-slot:footer>{{ $categories->links() }}</x-slot:footer></x-admin.table-card>
@endsection
