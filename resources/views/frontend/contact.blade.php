@extends('layouts.master')

@section('title', 'Liên hệ | '.$siteSettings->site_name)
@section('meta_description', 'Liên hệ '.$siteSettings->site_name.' để được tư vấn tour, visa, vé máy bay và khách sạn.')
@section('canonical', route('contact'))
@section('body_class', 'contact-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endpush

@section('breadcrumb')
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li><li class="breadcrumb-item active" aria-current="page">Liên hệ</li></ol></nav>
@endsection

@section('content')
    @php($selectedSubject = old('subject', request('subject')))
    <section class="contact-hero page-banner" style="--page-banner-image: url('{{ $siteAssets['page_banner'] }}');"><div class="container"><div class="contact-hero-copy"><span class="section-eyebrow section-eyebrow-light">Liên hệ HG</span><h1>Cùng bắt đầu hành trình của bạn</h1><p>HG sẵn sàng tư vấn tour, visa, vé máy bay, khách sạn và hành trình thiết kế riêng.</p></div></div></section>
    <div class="breadcrumb-bar"><div class="container">@yield('breadcrumb')</div></div>

    <section class="section-space pt-0"><div class="container"><div class="row g-3 contact-info-grid">
        <div class="col-md-4"><div class="contact-info-card {{ $siteLinks['phones'] ? '' : 'is-pending' }}"><i class="bi bi-telephone"></i><span>Điện thoại</span><strong>
            @if ($siteLinks['phones'])
                @foreach ($siteLinks['phones'] as $phone)
                    <a href="{{ $phone['href'] }}">{{ $phone['label'] }}: {{ $phone['value'] }}</a>@if (! $loop->last)<br>@endif
                @endforeach
            @else
                Hotline đang cập nhật
            @endif
        </strong><small>{{ $siteLinks['phones'] ? 'Gọi để nhận tư vấn nhanh' : 'Cập nhật trong Cài đặt chung' }}</small></div></div>
        <div class="col-md-4"><div class="contact-info-card {{ $siteLinks['emails'] ? '' : 'is-pending' }}"><i class="bi bi-envelope"></i><span>Email</span><strong>
            @if ($siteLinks['emails'])
                @foreach ($siteLinks['emails'] as $email)
                    <a href="mailto:{{ $email }}">{{ $email }}</a>@if (! $loop->last)<br>@endif
                @endforeach
            @else
                Email đang cập nhật
            @endif
        </strong><small>{{ $siteLinks['emails'] ? 'Gửi yêu cầu chi tiết cho HG' : 'Cập nhật trong Cài đặt chung' }}</small></div></div>
        <div class="col-md-4"><div class="contact-info-card {{ $siteSettings->office_address ? '' : 'is-pending' }}"><i class="bi bi-geo-alt"></i><span>Văn phòng</span><strong>{{ $siteSettings->office_address ?: 'Địa chỉ đang cập nhật' }}</strong><small>{{ $siteSettings->office_address ? 'Hẹn trước để HG đón tiếp chu đáo' : 'Cập nhật trong Cài đặt chung' }}</small></div></div>
    </div>

        <div class="row g-5 align-items-start contact-main"><div class="col-lg-5"><div class="contact-copy"><span class="section-eyebrow">Gửi yêu cầu</span><h2>Chuyến đi của bạn cần gì?</h2><p>Chia sẻ điểm đến, thời gian dự kiến hoặc dịch vụ đang quan tâm. Đội ngũ HG sẽ phản hồi với phương án phù hợp.</p><ul><li><i class="bi bi-check2-circle"></i>Tư vấn tour trong nước và quốc tế</li><li><i class="bi bi-check2-circle"></i>Hỗ trợ visa, vé máy bay và khách sạn</li><li><i class="bi bi-check2-circle"></i>Thiết kế hành trình theo nhu cầu riêng</li></ul></div></div><div class="col-lg-7"><div class="contact-form-card">@if (session('success'))<div class="alert alert-success mb-4" role="status">{{ session('success') }}</div>@endif<form action="{{ route('contact.store') }}" method="POST" novalidate>@csrf<div class="row g-3"><div class="col-md-6"><label class="form-label" for="contact-name">Họ và tên *</label><input class="form-control @error('name') is-invalid @enderror" id="contact-name" name="name" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label" for="contact-phone">Số điện thoại</label><input class="form-control @error('phone') is-invalid @enderror" id="contact-phone" name="phone" value="{{ old('phone') }}" inputmode="tel">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label" for="contact-email">Email *</label><input class="form-control @error('email') is-invalid @enderror" id="contact-email" name="email" type="email" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label" for="contact-subject">Dịch vụ quan tâm</label><select class="form-select @error('subject') is-invalid @enderror" id="contact-subject" name="subject"><option value="">Chọn dịch vụ</option>@foreach (['Tư vấn tour', 'Visa & hộ chiếu', 'Vé máy bay', 'Khách sạn', 'Thiết kế tour theo yêu cầu'] as $subject)<option value="{{ $subject }}" @selected($selectedSubject === $subject)>{{ $subject }}</option>@endforeach</select>@error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-12"><label class="form-label" for="contact-message">Nội dung cần tư vấn *</label><textarea class="form-control @error('message') is-invalid @enderror" id="contact-message" name="message" rows="6" maxlength="3000" placeholder="Ví dụ: Tôi muốn đi Đà Nẵng 4 ngày vào tháng 10 cho 2 người..." required>{{ old('message') }}</textarea>@error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-12"><button class="btn btn-brand" type="submit">Gửi yêu cầu <i class="bi bi-arrow-right"></i></button></div></div></form></div></div></div>

        <div class="contact-map-block"><div class="contact-map-copy"><span class="section-eyebrow">Tìm đến HG</span><h2>Văn phòng của chúng tôi</h2><p>{{ $siteSettings->office_address ?: 'Bản đồ sẽ hiển thị ngay khi cập nhật địa chỉ văn phòng trong Cài đặt chung.' }}</p>@if ($siteSettings->office_address)<a class="btn btn-outline-brand" href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($siteSettings->office_address) }}" target="_blank" rel="noopener noreferrer">Mở Google Maps <i class="bi bi-arrow-up-right"></i></a>@endif</div><div class="contact-map">@if ($siteSettings->office_address)<iframe title="Bản đồ văn phòng {{ $siteSettings->site_name }}" src="https://www.google.com/maps?q={{ rawurlencode($siteSettings->office_address) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>@else<div class="contact-map-empty"><i class="bi bi-map"></i><span>Chưa có địa chỉ để hiển thị bản đồ</span></div>@endif</div></div>
    </div></section>
@endsection
