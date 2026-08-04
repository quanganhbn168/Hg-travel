@extends('layouts.admin')
@section('page-title', 'Xử lý booking ' . $booking->booking_code)
@section('content')
    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin khách hàng</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Họ tên</dt>
                        <dd class="col-sm-8">{{ $booking->customer_name }}</dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $booking->customer_email }}</dd>
                        <dt class="col-sm-4">Điện thoại</dt>
                        <dd class="col-sm-8">{{ $booking->customer_phone }}</dd>
                        <dt class="col-sm-4">Địa chỉ</dt>
                        <dd class="col-sm-8">{{ $booking->customer_address ?: '—' }}</dd>
                    </dl>
                    <hr>
                    <h6>Tour đã đặt</h6>
                    @foreach ($booking->items as $item)
                        <div class="border rounded p-3 mb-2"><strong>{{ $item->tour_name }}</strong>
                            <div class="text-muted">{{ $item->adults }} người lớn, {{ $item->children }} trẻ em ·
                                {{ number_format($item->total_price, 0, ',', '.') }} ₫</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <form method="post" action="{{ route('admin.bookings.update', $booking) }}" class="card">
                <div class="card-header">
                    <h3 class="card-title">Cập nhật trạng thái</h3>
                </div>
                <div class="card-body">@csrf @method('PUT')<div class="mb-3"><label class="form-label">Trạng thái
                            booking</label><select name="status" class="form-select">
                            @foreach (['pending', 'confirmed', 'cancelled', 'completed'] as $status)
                                <option value="{{ $status }}" @selected($booking->status === $status)>{{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Trạng thái thanh toán</label><select name="payment_status"
                            class="form-select">
                            @foreach (['unpaid', 'pending', 'paid', 'refunded'] as $status)
                                <option value="{{ $status }}" @selected($booking->payment_status === $status)>{{ $status }}
                                </option>
                            @endforeach
                        </select></div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label>
                        <textarea name="notes" rows="4" class="form-control">{{ $booking->notes }}</textarea>
                    </div>
                    <div class="fs-5 text-end">Tổng tiền: <strong>{{ number_format($booking->total_amount, 0, ',', '.') }}
                            ₫</strong></div>
                </div>
                <div class="card-footer d-flex justify-content-between"><a href="{{ route('admin.bookings.index') }}"
                        class="btn btn-outline-secondary">Quay lại</a><button class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
@endsection
