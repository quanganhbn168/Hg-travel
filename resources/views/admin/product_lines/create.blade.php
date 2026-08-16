@extends('layouts.admin')

@section('title', 'Thêm giải pháp du lịch')
@section('page-title', 'Thêm giải pháp du lịch')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.product-lines.index') }}">Giải pháp du lịch</a></li><li class="breadcrumb-item active">Thêm mới</li></ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.product-lines.store') }}" method="POST">@csrf @include('admin.product_lines._form', ['submitLabel' => 'Lưu giải pháp'])</form>
@endsection
