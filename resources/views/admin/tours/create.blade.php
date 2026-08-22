@extends('layouts.admin')

@section('title', 'Thêm tour')
@section('page-title', 'Thêm tour du lịch')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.tours.index') }}">Tour du lịch</a></li><li class="breadcrumb-item active">Thêm mới</li></ol>
@endsection

@section('content')
    @include('admin.tours.form', ['isEditing' => false])
@endsection
