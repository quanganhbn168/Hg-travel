@extends('layouts.admin')

@section('title', 'Thêm vai trò')
@section('page-title', 'Thêm vai trò')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Phân quyền</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thêm mới</li>
    </ol>
@endsection

@section('content')
    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        @include('admin.roles.partials.form', ['submitLabel' => 'Lưu vai trò'])
    </form>
@endsection
