@extends('layouts.admin')

@section('title', 'Thêm booking')
@section('page-title', 'Thêm booking')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Booking</a></li><li class="breadcrumb-item active">Thêm mới</li></ol>
@endsection

@section('content')
    <form id="admin-save-form" action="{{ route('admin.bookings.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="Thông tin khách hàng" :collapsible="true">
                    <div class="row"><div class="col-md-6"><x-input name="customer_name" label="Họ và tên" required /></div><div class="col-md-6"><x-input name="customer_phone" label="Số điện thoại" required /></div></div>
                    <x-input name="customer_email" type="email" label="Email" required />
                    <x-textarea name="customer_address" label="Địa chỉ" rows="3" />
                    <x-textarea name="notes" label="Ghi chú booking" rows="4" />
                </x-card>
            </div>
            <div class="col-xl-4">
                <x-card type="info" title="Thông tin đặt tour" :collapsible="true" class="mb-3">
                    <x-select name="tour_id" label="Tour" :options="$tours->pluck('name', 'id')->all()" placeholder="Chọn tour" required />
                    <x-input name="departure_date" type="date" label="Ngày khởi hành" />
                    <div class="row"><div class="col-6"><x-input name="adults" type="number" label="Người lớn" value="1" required /></div><div class="col-6"><x-input name="children" type="number" label="Trẻ em" value="0" /></div></div>
                    <x-input name="unit_price" type="number" label="Đơn giá / người" value="0" required />
                </x-card>
                <x-card type="secondary" title="Trạng thái" :collapsible="true">
                    <x-select name="status" label="Booking" :options="['pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 'completed' => 'Hoàn tất', 'cancelled' => 'Đã hủy']" selected="pending" />
                    <x-select name="payment_status" label="Thanh toán" :options="['unpaid' => 'Chưa thanh toán', 'pending' => 'Đang chờ', 'paid' => 'Đã thanh toán', 'refunded' => 'Đã hoàn tiền']" selected="unpaid" />
                </x-card>
            </div>
            <div class="col-12"><div class="card"><div class="card-body d-flex justify-content-end gap-2"><a href="{{ route('admin.bookings.index') }}" class="btn btn-default">Hủy bỏ</a><button class="btn btn-primary">Tạo booking</button></div></div></div>
        </div>
    </form>
@endsection
