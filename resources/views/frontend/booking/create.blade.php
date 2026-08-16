@extends('layouts.master')

@section('title', 'Đặt tour | '.$siteSettings->site_name)
@section('meta_description', 'Gửi yêu cầu đặt tour để HG tư vấn lịch khởi hành, chi phí và các dịch vụ phù hợp.')
@section('canonical', route('booking.create'))
@section('body_class', 'booking-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}?v={{ filemtime(public_path('css/contact.css')) }}">
@endpush

@section('content')
    <section class="page-banner" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');">
        <div class="container">
            <span class="section-eyebrow section-eyebrow-light">Đặt tour cùng HG</span>
            <h1>Chia sẻ kế hoạch, HG chuẩn bị phần còn lại.</h1>
            <p>Gửi thông tin cơ bản để đội ngũ tư vấn kiểm tra lịch khởi hành và liên hệ xác nhận.</p>
        </div>
    </section>
    <div class="breadcrumb-bar"><div class="container"><nav aria-label="Breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active" aria-current="page">Đặt tour</li></ol></nav></div></div>

    <section class="section-space">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-5">
                    <span class="section-eyebrow">Yêu cầu đặt tour</span>
                    <h2 class="section-title">Một biểu mẫu ngắn để bắt đầu</h2>
                    <p class="section-description">Thông tin gửi về sẽ được lưu trong khu vực quản trị Booking để đội ngũ HG tiếp nhận và cập nhật trạng thái.</p>
                    <ul class="list-unstyled d-grid gap-3 mt-4">
                        <li><i class="bi bi-check2-circle text-success me-2"></i>Chọn trực tiếp hành trình đang mở booking</li>
                        <li><i class="bi bi-check2-circle text-success me-2"></i>Giá tham khảo lấy từ dữ liệu tour hiện tại</li>
                        <li><i class="bi bi-check2-circle text-success me-2"></i>Chưa phát sinh thanh toán ở bước này</li>
                    </ul>
                </div>
                <div class="col-lg-7">
                    <div class="contact-form-card">
                        @if (session('success'))
                            <div class="alert alert-success" role="status">{{ session('success') }}</div>
                        @endif

                        @if ($tours->isEmpty())
                            <div class="alert alert-info mb-0">Hiện chưa có tour mở booking. Anh/chị có thể <a href="{{ route('contact') }}">gửi yêu cầu tư vấn</a> để HG hỗ trợ.</div>
                        @else
                            <form action="{{ route('booking.store') }}" method="POST" novalidate>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label" for="booking-name">Họ và tên *</label><input id="booking-name" class="form-control @error('customer_name') is-invalid @enderror" name="customer_name" value="{{ old('customer_name') }}" required>@error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-6"><label class="form-label" for="booking-phone">Số điện thoại *</label><input id="booking-phone" class="form-control @error('customer_phone') is-invalid @enderror" name="customer_phone" value="{{ old('customer_phone') }}" inputmode="tel" required>@error('customer_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-6"><label class="form-label" for="booking-email">Email *</label><input id="booking-email" type="email" class="form-control @error('customer_email') is-invalid @enderror" name="customer_email" value="{{ old('customer_email') }}" required>@error('customer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-6"><label class="form-label" for="booking-tour">Hành trình *</label><select id="booking-tour" class="form-select @error('tour_id') is-invalid @enderror" name="tour_id" required><option value="">Chọn tour</option>@foreach ($tours as $tour)<option value="{{ $tour->id }}" @selected((string) old('tour_id', $selectedTour?->id) === (string) $tour->id)>{{ $tour->name }} — từ {{ number_format((float) $tour->starting_price, 0, ',', '.') }} {{ $tour->currency }}</option>@endforeach</select>@error('tour_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-6"><label class="form-label" for="booking-date">Ngày khởi hành dự kiến</label><input id="booking-date" type="date" class="form-control @error('departure_date') is-invalid @enderror" name="departure_date" value="{{ old('departure_date') }}">@error('departure_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-3"><label class="form-label" for="booking-adults">Người lớn *</label><input id="booking-adults" type="number" min="1" max="100" class="form-control @error('adults') is-invalid @enderror" name="adults" value="{{ old('adults', 1) }}" required>@error('adults')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-3"><label class="form-label" for="booking-children">Trẻ em</label><input id="booking-children" type="number" min="0" max="100" class="form-control @error('children') is-invalid @enderror" name="children" value="{{ old('children', 0) }}">@error('children')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-12"><label class="form-label" for="booking-address">Địa chỉ</label><input id="booking-address" class="form-control" name="customer_address" value="{{ old('customer_address') }}"></div>
                                    <div class="col-12"><label class="form-label" for="booking-notes">Ghi chú</label><textarea id="booking-notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="4" placeholder="Ví dụ: nhu cầu phòng, điểm đón, dịch vụ muốn kết hợp...">{{ old('notes') }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-12"><button class="btn btn-brand" type="submit">Gửi yêu cầu đặt tour <i class="bi bi-arrow-right"></i></button></div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
