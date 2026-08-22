@extends('layouts.admin')

@section('page-title', 'Tổng quan vận hành')

@section('content')
    <div class="row g-3 mb-4">
        @foreach ([
            ['Tour đang bán', $openTours, $totalTours.' tour trong hệ thống', 'bi-map', 'primary'],
            ['Tổng booking', $totalBookings, $bookingsThisMonth.' booking trong tháng này', 'bi-calendar-check', 'info'],
            ['Chờ xử lý', $pendingBookings, 'Cần liên hệ xác nhận', 'bi-hourglass-split', 'warning'],
            ['Doanh thu đã xác nhận', number_format((float) $confirmedRevenue, 0, ',', '.').' ₫', $confirmedBookings.' booking xác nhận/hoàn tất', 'bi-cash-coin', 'success'],
        ] as [$label, $value, $note, $icon, $color])
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 card-outline card-{{ $color }}">
                    <div class="card-body d-flex justify-content-between gap-3">
                        <div><div class="text-body-secondary small">{{ $label }}</div><div class="fs-4 fw-bold mt-1">{{ $value }}</div><small class="text-body-secondary">{{ $note }}</small></div>
                        <i class="bi {{ $icon }} fs-2 text-{{ $color }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">Booking gần đây</h3><a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-primary float-end">Xem & xử lý booking</a></div>
                <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Mã booking</th><th>Khách hàng</th><th>Tour / lịch</th><th>Trạng thái</th><th class="text-end">Tổng tiền</th></tr></thead><tbody>
                    @forelse ($recentBookings as $booking)
                        @php($item = $booking->items->first())
                        <tr>
                            <td><a class="fw-semibold text-decoration-none" href="{{ route('admin.bookings.edit', $booking) }}">{{ $booking->booking_code }}</a><small class="d-block text-body-secondary">{{ ($booking->booked_at ?: $booking->created_at)?->format('d/m/Y H:i') }}</small></td>
                            <td>{{ $booking->customer_name }}<small class="d-block text-body-secondary">{{ $booking->customer_phone }}</small></td>
                            <td>{{ $item?->tour_name ?? '—' }}<small class="d-block text-body-secondary">{{ $item?->departure_date?->format('d/m/Y') ?: 'Chưa chọn lịch' }}</small></td>
                            <td><span class="badge text-bg-light">{{ $bookingStatuses[$booking->status] ?? $booking->status }}</span></td>
                            <td class="text-end fw-semibold">{{ number_format((float) $booking->total_amount, 0, ',', '.') }} ₫</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-body-secondary">Chưa có booking. Yêu cầu từ trang tour sẽ hiển thị ở đây.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title">Lịch khởi hành sắp tới</h3><a href="{{ route('admin.tours.index') }}" class="btn btn-sm btn-outline-secondary float-end">Quản lý tour</a></div>
                <div class="list-group list-group-flush">
                    @forelse ($upcomingSchedules as $schedule)
                        @php($seatsLeft = $schedule->seatsLeft())
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between gap-2"><strong class="small">{{ $schedule->tour?->name ?? 'Tour đã xóa' }}</strong><span class="text-nowrap text-primary fw-semibold">{{ $schedule->departure_date?->format('d/m') }}</span></div>
                            <small class="d-block text-body-secondary mt-1">{{ $schedule->return_date?->format('d/m/Y') ? 'Về '.$schedule->return_date->format('d/m/Y').' · ' : '' }}{{ $seatsLeft === null ? 'Chưa chốt sức chứa' : 'Còn '.$seatsLeft.'/'.$schedule->seats_total.' chỗ' }}</small>
                        </div>
                    @empty
                        <div class="list-group-item py-5 text-center text-body-secondary">Chưa có lịch khởi hành đang mở.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
