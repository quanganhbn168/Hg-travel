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
            ['label' => 'Booking tháng này', 'value' => $bookingsThisMonth, 'note' => $totalBookings.' booking toàn thời gian', 'icon' => 'bi-calendar-check', 'color' => 'info', 'url' => route('admin.bookings.index')],
            ['label' => 'Chờ xử lý', 'value' => $pendingBookings, 'note' => 'Chưa thanh toán: '.$unpaidBookings, 'icon' => 'bi-hourglass-split', 'color' => 'warning', 'url' => route('admin.bookings.index', ['status' => 'pending'])],
            ['label' => 'Doanh thu xác nhận', 'value' => number_format((float) $confirmedRevenue, 0, ',', '.').' ₫', 'note' => $confirmedBookings.' booking xác nhận/hoàn tất', 'icon' => 'bi-cash-coin', 'color' => 'success', 'url' => route('admin.bookings.index', ['status' => 'confirmed'])],
            ['label' => 'Lịch đang mở', 'value' => $openScheduleCount, 'note' => $occupancyRate === null ? 'Chưa chốt sức chứa' : 'Đã lấp đầy '.$occupancyRate.'%', 'icon' => 'bi-calendar2-week', 'color' => 'danger', 'url' => route('admin.tours.index')],
        ];
    @endphp

    <div class="row">
        @foreach ($statCards as $card)
            <div class="col-sm-6 col-lg-4 col-xl">
                <a href="{{ $card['url'] }}" class="small-box text-bg-{{ $card['color'] }} text-decoration-none">
                    <div class="inner">
                        <h3>{{ $card['value'] }}</h3>
                        <p>{{ $card['label'] }}</p>
                        <p class="mb-0"><small>{{ $card['note'] }}</small></p>
                    </div>
                    <i class="small-box-icon bi {{ $card['icon'] }}" aria-hidden="true"></i>
                    <span class="small-box-footer">Mở chi tiết <i class="bi bi-arrow-right"></i></span>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Booking 14 ngày qua</h3>
                    <div class="card-tools">
                        <span class="badge text-bg-light me-1">{{ $bookingTrendTotal }} booking</span>
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Thu gọn biểu đồ booking">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                    </div>
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
                <div class="card-header">
                    <h3 class="card-title">Phân bổ booking</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-tool" title="Xem danh sách booking" aria-label="Xem danh sách booking"><i class="bi bi-arrow-right"></i></a>
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Thu gọn phân bổ booking">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                </div>
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
                <div class="card-header">
                    <h3 class="card-title">Booking gần đây</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-tool" title="Xem và xử lý booking" aria-label="Xem và xử lý booking"><i class="bi bi-arrow-right"></i></a>
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Thu gọn booking gần đây">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                </div>
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
                <div class="card-header">
                    <h3 class="card-title">Lịch khởi hành sắp tới</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.tours.index') }}" class="btn btn-tool" title="Quản lý tour" aria-label="Quản lý tour"><i class="bi bi-arrow-right"></i></a>
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="Thu gọn lịch khởi hành">
                            <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                            <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                </div>
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
