@extends('layouts.admin')

@section('title', $moment->exists ? 'Sửa khoảnh khắc' : 'Thêm khoảnh khắc')
@section('page-title', $moment->exists ? 'Sửa khoảnh khắc' : 'Thêm khoảnh khắc')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.travel-moments.index') }}">Khoảnh khắc</a></li><li class="breadcrumb-item active">{{ $moment->exists ? 'Chỉnh sửa' : 'Thêm mới' }}</li></ol>
@endsection

@section('content')
<form method="POST" action="{{ $moment->exists ? route('admin.travel-moments.update', $moment) : route('admin.travel-moments.store') }}">
    @csrf
    @if($moment->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-lg-8"><x-card type="primary" title="Nội dung khoảnh khắc"><x-input name="title" label="Tiêu đề" :value="$moment->title" required /><x-input name="slug" label="Slug" :value="$moment->slug" /><x-textarea name="caption" label="Chú thích" :value="$moment->caption" rows="4" /></x-card></div>
        <div class="col-lg-4"><x-card type="info" title="Ảnh và nhóm"><x-select name="group_id" label="Nhóm hiển thị" :options="$groups->pluck('name', 'id')->all()" :selected="old('group_id', $moment->group_id)" required /><x-image-upload name="image_url" label="Ảnh khoảnh khắc" :value="$moment->image_url" required /><x-input name="alt_text" label="Alt ảnh" :value="$moment->alt_text" /><x-input type="number" name="sort_order" label="Thứ tự" :value="$moment->sort_order" /><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $moment->is_active))><span class="form-check-label">Hiển thị trên website</span></label></x-card></div>
        <div class="col-12 text-end"><a class="btn btn-default" href="{{ route('admin.travel-moments.index') }}">Hủy</a><button class="btn btn-primary">Lưu khoảnh khắc</button></div>
    </div>
</form>
@endsection
