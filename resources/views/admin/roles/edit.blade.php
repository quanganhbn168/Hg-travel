@extends('layouts.admin')

@section('title', 'Chỉnh sửa vai trò')
@section('page-title', 'Chỉnh sửa vai trò')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Phân quyền</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $role->name }}</li>
    </ol>
@endsection

@section('content')
    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf @method('PUT')
        @include('admin.roles.partials.form', ['submitLabel' => 'Lưu thay đổi'])
    </form>
@endsection
