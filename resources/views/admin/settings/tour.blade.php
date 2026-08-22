@extends('layouts.admin')

@section('title', 'Cài đặt tour')
@section('page-title', 'Cài đặt')

@section('content')
    <x-admin.settings-layout title="Cài đặt tour" description="Điều khiển cách lịch khởi hành và tình trạng chỗ được hiển thị trên trang tour.">
    <x-slot:actions>
        <button form="admin-settings-tour-form" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Lưu cài đặt tour</button>
    </x-slot:actions>
    <form id="admin-settings-tour-form" action="{{ route('admin.settings.tour.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="Lịch khởi hành trên website">
                    <label class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="show_schedules" value="1" @checked(old('show_schedules', $settings->show_schedules))>
                        <span class="form-check-label">Hiển thị lịch khởi hành trên trang chi tiết tour</span>
                    </label>
                    <label class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="show_seat_availability" value="1" @checked(old('show_seat_availability', $settings->show_seat_availability))>
                        <span class="form-check-label">Hiển thị số chỗ còn lại / tổng chỗ</span>
                    </label>
                </x-card>
                <x-card type="info" title="Ghi chú lịch khởi hành" class="mt-3">
                    <x-textarea name="schedule_note" label="Ghi chú hiển thị bên dưới lịch" :value="old('schedule_note', $settings->schedule_note)" rows="3" required />
                </x-card>
            </div>
            <div class="col-xl-4">
                <x-card type="secondary" title="Quy ước dữ liệu">
                    <p class="mb-0 text-muted small">Chỉ hiển thị số chỗ khi dữ liệu tổng chỗ đã được xác nhận. Dữ liệu lịch chưa rõ ý nghĩa sẽ hiện “Đang cập nhật”, không tự suy đoán.</p>
                </x-card>
            </div>
        </div>
    </form>
    </x-admin.settings-layout>
@endsection
