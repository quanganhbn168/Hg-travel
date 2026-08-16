@extends('layouts.admin')
@section('title', 'Thêm danh mục dịch vụ')
@section('page-title', 'Thêm danh mục dịch vụ')
@section('breadcrumbs')<ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.service-categories.index') }}">Danh mục dịch vụ</a></li><li class="breadcrumb-item active">Thêm mới</li></ol>@endsection
@section('content')<form action="{{ route('admin.service-categories.store') }}" method="POST">@csrf @include('admin.service_categories._form', ['submitLabel' => 'Lưu danh mục'])</form>@endsection
