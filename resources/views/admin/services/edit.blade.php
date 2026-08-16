@extends('layouts.admin')
@section('title', 'Chỉnh sửa dịch vụ')
@section('page-title', 'Chỉnh sửa dịch vụ')
@section('breadcrumbs')<ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Dịch vụ</a></li><li class="breadcrumb-item active">Chỉnh sửa</li></ol>@endsection
@section('content')<form action="{{ route('admin.services.update', $service) }}" method="POST">@csrf @method('PUT') @include('admin.services._form', ['submitLabel' => 'Lưu thay đổi'])</form>@endsection
