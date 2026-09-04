@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Tổng quan vận hành')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
    </ol>
@endsection

@section('content')
    @php
        $statCards = [
            ['label' => 'Tour đang bán', 'value' => $openTours, 'note' => $totalTours.' tour trong hệ thống', 'icon' => 'bi-map', 'color' => 'primary', 'url' => route('admin.tours.index')],
            ['label' => 'Điểm đến', 'value' => $totalDestinations, 'note' => 'Địa danh đang quản lý', 'icon' => 'bi-geo-alt', 'color' => 'success', 'url' => route('admin.destinations.index')],
            ['label' => 'Booking tháng này', 'value' => $bookingsThisMonth, 'note' => $totalBookings.' booking toàn thời gian', 'icon' => 'bi-calendar-check', 'color' => 'info', 'url' => route('admin.bookings.index')],
            ['label' => 'Chờ xử lý', 'value' => $pendingBookings, 'note' => 'Chưa thanh toán: '.$unpaidBookings, 'icon' => 'bi-hourglass-split', 'color' => 'warning', 'url' => route('admin.bookings.index', ['status' => 'pending'])],
            ['label' => 'Doanh thu xác nhận', 'value' => number_format((float) $confirmedRevenue, 0, ',', '.').' ₫', 'note' => $confirmedBookings.' booking xác nhận/hoàn tất', 'icon' => 'bi-cash-coin', 'color' => 'success', 'url' => route('admin.bookings.index', ['status' => 'confirmed'])],
            ['label' => 'Lịch đang mở', 'value' => $openScheduleCount, 'note' => $occupancyRate === null ? 'Chưa chốt sức chứa' : 'Đã lấp đầy '.$occupancyRate.'%', 'icon' => 'bi-calendar2-week', 'color' => 'secondary', 'url' => route('admin.tours.index')],
        ];
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($statCards as $card)
            <div class="col-md-6 col-xl-2">
                <a href="{{ $card['url'] }}" class="dashboard-stat-link text-decoration-none">
                    <div class="card h-100 card-outline card-{{ $card['color'] }}">
                        <div class="card-body d-flex justify-content-between gap-3">
                            <div class="min-w-0"><div class="text-body-secondary small">{{ $card['label'] }}</div><div class="fs-4 fw-bold mt-1 text-body text-truncate">{{ $card['value'] }}</div><small class="text-body-secondary">{{ $card['note'] }}</small></div>
                            <i class="bi {{ $card['icon'] }} fs-2 text-{{ $card['color'] }}"></i>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <h3 class="card-title mb-0">Booking 14 ngày qua</h3>
                    <span class="badge text-bg-light">{{ $bookingTrendTotal }} booking</span>
                </div>
                <div class="card-body">
                    @if($bookingTrendTotal > 0)
                        <div class="dashboard-trend" role="img" aria-label="Biểu đồ số booking trong 14 ngày qua">
                            <div class="dashboard-trend__plot">
                                @foreach($bookingTrend as $point)
                                    <div class="dashboard-trend__item" title="{{ $point['label'] }}: {{ $point['count'] }} booking · {{ number_format($point['revenue'], 0, ',', '.') }} ₫ đã xác nhận">
                                        <div class="dashboard-trend__bar-wrap"><span class="dashboard-trend__bar" style="height: {{ $point['height'] }}%"></span></div>
                                        <small>{{ $point['label'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="dashboard-empty-state">Chưa phát sinh booking trong 14 ngày qua.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title mb-0">Phân bổ booking</h3></div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        @foreach($bookingStatusBreakdown as $status)
                            <div>
                                <div class="d-flex justify-content-between gap-2 small mb-1"><span>{{ $status['label'] }}</span><strong>{{ $status['count'] }} <span class="text-body-secondary fw-normal">({{ $status['percentage'] }}%)</span></strong></div>
                                <div class="progress" role="progressbar" aria-label="{{ $status['label'] }}" aria-valuenow="{{ $status['percentage'] }}" aria-valuemin="0" aria-valuemax="100" style="height: 6px"><div class="progress-bar bg-{{ $status['color'] }}" style="width: {{ $status['percentage'] }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-top mt-4 pt-3">
                        <div class="small text-body-secondary mb-2">Tình hình thanh toán</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($paymentStatusBreakdown as $payment)
                                <span class="badge text-bg-light">{{ $payment['label'] }}: {{ $payment['count'] }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-3"><h3 class="card-title mb-0">Booking gần đây</h3><a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-primary">Xem & xử lý booking</a></div>
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
                <div class="card-header d-flex justify-content-between align-items-center gap-3"><h3 class="card-title mb-0">Lịch khởi hành sắp tới</h3><a href="{{ route('admin.tours.index') }}" class="btn btn-sm btn-outline-secondary">Quản lý tour</a></div>
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
