@extends('layouts.admin')
@section('title', 'Thêm dịch vụ')
@section('page-title', 'Thêm dịch vụ')
@section('breadcrumbs')<ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Dịch vụ</a></li><li class="breadcrumb-item active">Thêm mới</li></ol>@endsection
@section('content')<form action="{{ route('admin.services.store') }}" method="POST">@csrf @include('admin.services._form', ['submitLabel' => 'Lưu dịch vụ'])</form>@endsection
