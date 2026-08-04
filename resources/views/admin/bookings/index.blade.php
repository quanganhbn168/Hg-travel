@extends('layouts.admin')

@section('title', 'Booking')
@section('page-title', 'Booking')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Booking</li></ol>
@endsection

@section('content')
    <x-admin.index-header description="Theo dõi và xử lý yêu cầu đặt tour." :create-url="route('admin.bookings.create')" create-label="Thêm booking" />
    <x-admin.filter-panel title="Bộ lọc booking">
        <form class="row g-3 align-items-end" method="get" action="{{ route('admin.bookings.index') }}"><div class="col-lg-4"><label class="form-label" for="booking-status">Trạng thái booking</label><select id="booking-status" name="status" class="form-select"><option value="">Tất cả trạng thái</option>@foreach(['pending','confirmed','cancelled','completed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div><div class="col-lg-2"><button class="btn btn-primary">Lọc</button></div></form>
    </x-admin.filter-panel>
    <x-admin.table-card title="Danh sách booking">
        <x-slot:tools><span class="badge text-bg-light">{{ $bookings->total() }} bản ghi</span></x-slot:tools>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Mã booking</th><th>Khách hàng</th><th>Tour</th><th>Ngày đặt</th><th>Thanh toán</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>@forelse($bookings as $booking)<tr><td><a href="{{ route('admin.bookings.edit', $booking) }}" class="fw-semibold text-decoration-none">{{ $booking->booking_code }}</a></td><td>{{ $booking->customer_name }}<small class="d-block text-body-secondary">{{ $booking->customer_phone }}</small></td><td>{{ $booking->items->first()?->tour_name ?? '—' }}</td><td>{{ $booking->booked_at?->format('d/m/Y H:i') ?? $booking->created_at?->format('d/m/Y H:i') }}</td><td>{{ $booking->payment_status }}</td><td><span class="badge text-bg-light">{{ $booking->status }}</span></td><td class="text-end"><a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-default btn-sm" title="Xử lý"><i class="bi bi-pencil-square"></i></a></td></tr>@empty<tr><td colspan="7" class="text-center py-5">Chưa có booking.</td></tr>@endforelse</tbody></table></div>
        <x-slot:footer>@if($bookings->hasPages()){{ $bookings->links() }}@endif</x-slot:footer>
    </x-admin.table-card>
@endsection
