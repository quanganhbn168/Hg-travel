@extends('layouts.admin')
@section('title', 'Chỉnh sửa danh mục dịch vụ')
@section('page-title', 'Chỉnh sửa danh mục dịch vụ')
@section('breadcrumbs')<ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.service-categories.index') }}">Danh mục dịch vụ</a></li><li class="breadcrumb-item active">Chỉnh sửa</li></ol>@endsection
@section('content')<form action="{{ route('admin.service-categories.update', $category) }}" method="POST">@csrf @method('PUT') @include('admin.service_categories._form', ['submitLabel' => 'Lưu thay đổi'])</form>@endsection
