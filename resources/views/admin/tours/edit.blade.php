@extends('layouts.admin')

@section('title', 'Chỉnh sửa tour')
@section('page-title', 'Chỉnh sửa tour du lịch')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.tours.index') }}">Tour du lịch</a></li><li class="breadcrumb-item active">Chỉnh sửa</li></ol>
@endsection

@section('content')
    @include('admin.tours.form', ['isEditing' => true])
@endsection
