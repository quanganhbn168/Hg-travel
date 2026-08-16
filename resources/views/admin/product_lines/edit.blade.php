@extends('layouts.admin')

@section('title', 'Sửa giải pháp du lịch')
@section('page-title', 'Sửa giải pháp du lịch')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.product-lines.index') }}">Giải pháp du lịch</a></li><li class="breadcrumb-item active">{{ $productLine->name }}</li></ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.product-lines.update', $productLine) }}" method="POST">@csrf @method('PUT') @include('admin.product_lines._form', ['submitLabel' => 'Lưu thay đổi'])</form>
@endsection
