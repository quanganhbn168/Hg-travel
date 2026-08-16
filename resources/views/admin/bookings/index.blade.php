@extends('layouts.admin')

@section('title', 'Booking')
@section('page-title', 'Booking')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Booking</li></ol>
@endsection
@section('content')
<x-admin.index-card title="Danh sách booking" description="Theo dõi và xử lý yêu cầu đặt tour." icon="bi-calendar-check" :create-url="route('admin.bookings.create')" create-label="Thêm booking" resource="booking">
    <x-slot:filters>
        <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.bookings.index') }}">
            <div class="col-lg-4"><label class="form-label" for="booking-search">Từ khóa</label><input id="booking-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Mã booking, tên hoặc điện thoại"></div>
            <div class="col-lg-4"><label class="form-label" for="booking-status">Trạng thái booking</label><select id="booking-status" name="status" class="form-select"><option value="">Tất cả trạng thái</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-lg-2"><label class="form-label" for="booking-per-page">Số dòng</label><select id="booking-per-page" name="per_page" class="form-select">@foreach([10,20,50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>@endforeach</select></div>
            <div class="col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit">Lọc</button><a href="{{ route('admin.bookings.index') }}" class="btn btn-default" title="Xóa bộ lọc"><i class="bi bi-arrow-counterclockwise"></i></a></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th data-select-column class="text-center" style="width:48px"><input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả"></th><th>Mã booking</th><th>Khách hàng</th><th>Tour</th><th>Ngày đặt</th><th>Thanh toán</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($bookings as $booking)
            <tr data-record-id="{{ $booking->id }}"><td data-select-column class="text-center"><input form="admin-bulk-booking-form" type="checkbox" name="ids[]" value="{{ $booking->id }}" class="form-check-input" data-check-item aria-label="Chọn {{ $booking->booking_code }}"></td><td><a href="{{ route('admin.bookings.edit', $booking) }}" class="fw-semibold text-decoration-none">{{ $booking->booking_code }}</a></td><td>{{ $booking->customer_name }}<small class="d-block text-body-secondary">{{ $booking->customer_phone }}</small></td><td>{{ $booking->items->first()?->tour_name ?? '—' }}</td><td>{{ $booking->booked_at?->format('d/m/Y H:i') ?? $booking->created_at?->format('d/m/Y H:i') }}</td><td>{{ $booking->payment_status }}</td><td><span class="badge text-bg-light">{{ $statuses[$booking->status] ?? $booking->status }}</span></td><td class="text-end"><a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-default btn-sm" title="Xử lý"><i class="bi bi-pencil-square"></i></a></td></tr>
        @empty
            <tr><td colspan="8" class="text-center py-5">Chưa có booking.</td></tr>
        @endforelse
    </tbody></table></div>
    <x-slot:footer>@if($bookings->hasPages()){{ $bookings->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
