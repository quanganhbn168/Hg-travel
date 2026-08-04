@extends('layouts.admin')
@section('title','Thêm tài khoản')
@section('page-title','Thêm tài khoản admin')
@section('content')
<form action="{{ route('admin.users.store') }}" method="POST">@csrf<div class="row g-3"><div class="col-xl-8"><x-card type="primary" title="Thông tin tài khoản"><x-input name="name" label="Họ tên" required/><x-input type="email" name="email" label="Email" required/><x-input name="phone" label="Điện thoại"/><x-input type="password" name="password" label="Mật khẩu" required/><x-input type="password" name="password_confirmation" label="Nhập lại mật khẩu" required/></x-card></div><div class="col-xl-4"><x-card type="info" title="Quyền truy cập"><x-select name="role" label="Vai trò" :options="$roles->all()" required/><label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><span class="form-check-label">Kích hoạt</span></label></x-card></div><div class="col-12 text-end"><a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Hủy</a> <button class="btn btn-primary">Lưu tài khoản</button></div></div></form>
@endsection
