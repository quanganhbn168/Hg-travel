@extends('layouts.master')

@section('title', $about->seo_title ?: $about->hero_title)
@section('meta_description', $about->seo_description ?: $about->hero_intro)
@section('body_class', 'about-page')

@php
    $profileImage = static function (?string $path, string $fallback): string {
        if (blank($path)) {
            return asset($fallback);
        }

        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    };

    $heroImage = $profileImage($about->hero_image, 'images/about/hg-trip/letter-travel.jpg');
    $letterImage = $profileImage($about->background_image, 'images/about/hg-trip/journey-beijing.jpg');
@endphp

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}?v={{ filemtime(public_path('css/about.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/about-profile.css') }}?v={{ filemtime(public_path('css/about-profile.css')) }}">
@endpush

@section('content')
    <section class="about-hero" style="--about-hero: url('{{ $heroImage }}')">
        <div class="about-hero-shade"></div>
        <div class="container about-hero-copy">
            <span class="about-kicker"><i class="bi bi-stars"></i> HG TRIP · VỀ CHÚNG TÔI</span>
            <h1>{{ $about->hero_title }}</h1>
            <p>{{ $about->hero_intro }}</p>
            <a class="about-hero-link" href="#thu-ngo">Khám phá câu chuyện <i class="bi bi-arrow-down"></i></a>
        </div>
        <div class="about-hero-mark" aria-hidden="true"><span>EST.</span><strong>HG</strong><span>TRIP</span></div>
    </section>

    <section class="about-letter" id="thu-ngo" style="--about-letter-image: url('{{ $letterImage }}')">
        <div class="container">
            <div class="about-letter-layout">
                <div class="about-letter-image" aria-hidden="true"></div>
                <article class="about-letter-card">
                    <span class="section-eyebrow">{{ $about->letter_title }}</span>
                    <div class="about-rich-copy">{!! nl2br(e($about->letter_content)) !!}</div>
                    <div class="about-letter-signature"><span>HG TRIP</span><small>TRAVEL WITH PURPOSE</small></div>
                </article>
            </div>
        </div>
    </section>

    <section class="about-company-intro">
        <div class="container">
            <div class="about-company-intro-grid">
                <div><span class="section-eyebrow">VỀ CHÚNG TÔI</span><h2>Giải pháp du lịch toàn diện, thiết kế cho từng nhu cầu.</h2></div>
                <div class="about-company-intro-copy"><p>{{ $profile['company']['legal_name'] ?: $siteSettings->site_name }} hoạt động trong lĩnh vực dịch vụ du lịch, tập trung phục vụ khách hàng cá nhân, gia đình, nhóm khách và doanh nghiệp.</p><p>Đằng sau một thương hiệu mới là đội ngũ đã trực tiếp xây dựng, tổ chức và vận hành nhiều chương trình cho khách hàng trong nước, quốc tế và doanh nghiệp.</p></div>
            </div>
            <div class="about-company-facts">@foreach($impactStats as $stat)<div><strong>{{ $stat['number'] }}</strong><span>{{ $stat['label'] }}</span></div>@endforeach</div>
            <div class="about-company-credentials">@foreach($profile['credentials'] as $credential)<span><i class="bi bi-patch-check-fill"></i>{{ $credential }}</span>@endforeach</div>
        </div>
    </section>

    <section class="section-space about-story-section">
        <div class="container about-story">
            <div class="about-story-copy">
                <span class="section-eyebrow">HÀNH TRÌNH CỦA CHÚNG TÔI</span>
                <h2>{{ $about->story_title }}</h2>
                <p>{!! nl2br(e($about->story_content)) !!}</p>
                <div class="about-story-fact"><i class="bi bi-people-fill"></i><span>Những chuyến đi được tạo nên từ sự lắng nghe, kinh nghiệm và kết nối chân thành.</span></div>
            </div>
            <figure class="about-story-photo">
                <img src="{{ asset('images/about/hg-trip/journey-kazakhstan.jpg') }}" alt="Đoàn khách HG TRIP trong một hành trình quốc tế" loading="lazy" width="771" height="578">
                <figcaption>Những hành trình thực tế cùng khách hàng HG TRIP</figcaption>
            </figure>
        </div>
    </section>

    <section class="about-story-steps-section">
        <div class="container"><div class="about-story-steps-heading"><span class="section-eyebrow">CÁCH HG TRIP TẠO NÊN MỘT HÀNH TRÌNH</span><p>Một chuyến đi đáng nhớ không cần thật nhiều điểm đến; điều quan trọng là mỗi chi tiết đúng với người đi.</p></div><div class="about-story-steps">@foreach($profile['story_steps'] as $step)<article><span>{{ $step['number'] }}</span><h3>{{ $step['title'] }}</h3><p>{{ $step['description'] }}</p></article>@endforeach</div></div>
    </section>

    <section class="about-direction">
        <div class="container">
            <div class="about-direction-grid">
                <article><span class="about-direction-icon"><i class="bi bi-compass"></i></span><small>TẦM NHÌN</small><h2>{{ $about->vision }}</h2></article>
                <article><span class="about-direction-icon"><i class="bi bi-heart"></i></span><small>SỨ MỆNH</small><h2>{{ $about->mission }}</h2></article>
            </div>
        </div>
    </section>

    <section class="about-markets-section">
        <div class="container"><div class="about-markets-heading"><div><span class="section-eyebrow">CÁC THỊ TRƯỜNG HOẠT ĐỘNG</span><h2>Kết nối những hành trình<br>không giới hạn biên giới.</h2></div><p>Từ một chuyến đi trong nước đến những thị trường quốc tế, HG TRIP chuẩn bị đồng bộ trải nghiệm và vận hành.</p></div><div class="about-markets-grid">@foreach($profile['markets'] as $market)<article><span>0{{ $loop->iteration }}</span><h3>{{ $market['name'] }}</h3><p>{{ $market['detail'] }}</p></article>@endforeach</div></div>
    </section>

    <section class="about-values section-space">
        <div class="container">
            <div class="about-section-heading"><div><span class="section-eyebrow section-eyebrow-light">GIÁ TRỊ CỐT LÕI</span><h2>Điều định hướng<br>mọi hành trình</h2></div><p>Không chỉ tổ chức một chuyến đi, HG TRIP chăm chút cho cảm xúc và sự an tâm trong từng trải nghiệm.</p></div>
            <div class="about-values-grid">
                @foreach($about->core_values ?: [] as $value)
                    <article class="about-value"><span>0{{ $loop->iteration }}</span><h3>{{ $value['title'] }}</h3><p>{{ $value['description'] }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="about-products-section">
        <div class="container"><div class="about-products-heading"><div><span class="section-eyebrow">SẢN PHẨM ĐẶC TRƯNG</span><h2>Đúng mục tiêu chuyến đi,<br>đủ đầy từng dịch vụ.</h2></div><p>HG TRIP tổ chức chương trình trọn vẹn và linh hoạt, từ mục tiêu của doanh nghiệp đến những kỳ nghỉ dành riêng cho cá nhân, gia đình.</p></div><div class="about-featured-products">@foreach($profile['featured_products'] as $product)<article><i class="bi {{ $product['icon'] }}"></i><h3>{{ $product['title'] }}</h3><p>{{ $product['description'] }}</p><a href="{{ route('contact', ['subject' => $product['title']]) }}">Nhận tư vấn <i class="bi bi-arrow-up-right"></i></a></article>@endforeach</div></div>
    </section>

    <section class="about-support-section">
        <div class="container"><div class="about-support-heading"><span class="section-eyebrow section-eyebrow-light">HỆ SINH THÁI DỊCH VỤ</span><h2>Một đầu mối cho<br>toàn bộ hành trình.</h2><p>Tất cả hạng mục được kết nối trong một kế hoạch thống nhất để khách hàng chủ động và an tâm hơn.</p></div><div class="about-support-grid">@foreach($profile['support_services'] as $service)<article><i class="bi {{ $service['icon'] }}"></i><div><h3>{{ $service['title'] }}</h3><p>{{ $service['description'] }}</p></div></article>@endforeach</div></div>
    </section>

    <section class="section-space about-photo-journal">
        <div class="container">
            <div class="about-journal-heading"><div><span class="section-eyebrow">HÀNH TRÌNH ĐÁNG NHỚ</span><h2>Niềm vui là thước đo<br>của mỗi chuyến đi</h2></div><p>Hình ảnh từ các đoàn khách và hoạt động trong hồ sơ năng lực của HG TRIP.</p></div>
            <div class="about-photo-grid">
                <figure class="about-photo about-photo-large"><img src="{{ asset('images/about/hg-trip/journey-beijing.jpg') }}" alt="Đoàn khách HG TRIP tại Bắc Kinh" loading="lazy" width="807" height="605"><figcaption>Khách hàng là trung tâm của mọi hành trình</figcaption></figure>
                <figure class="about-photo"><img src="{{ asset('images/about/hg-trip/experience-conference.jpg') }}" alt="Hoạt động hội nghị và sự kiện của HG TRIP" loading="lazy" width="1372" height="780"></figure>
                <figure class="about-photo"><img src="{{ asset('images/about/hg-trip/experience-vip.jpg') }}" alt="Dịch vụ trải nghiệm VIP" loading="lazy" width="1100" height="825"></figure>
            </div>
        </div>
    </section>

    <section class="about-leaders-section">
        <div class="container">
            <div class="about-leaders">
                <div class="about-leaders-intro"><span class="section-eyebrow">ĐỘI NGŨ LÃNH ĐẠO</span><h2>Kinh nghiệm tạo nên sự an tâm</h2><p>Đội ngũ điều hành đồng hành trực tiếp từ khâu thiết kế đến khi hành trình khép lại.</p></div>
                <div class="about-leader-list">
                    <article><span>CEO HG TRIP</span><h3>{{ $about->ceo_name }}</h3><p>{{ $about->ceo_bio }}</p></article>
                    <article><span>PHÓ GIÁM ĐỐC</span><h3>{{ $about->deputy_name }}</h3><p>{{ $about->deputy_bio }}</p></article>
                </div>
            </div>
        </div>
    </section>

    <section class="about-lists">
        <div class="container">
            <div><small><i class="bi bi-globe2"></i> THỊ TRƯỜNG HOẠT ĐỘNG</small><div>@foreach($about->markets ?: [] as $item)<span>{{ $item }}</span>@endforeach</div></div>
            <div><small><i class="bi bi-patch-check"></i> CAM KẾT</small><ul>@foreach($about->commitments ?: [] as $item)<li>{{ $item }}</li>@endforeach</ul></div>
            <div><small><i class="bi bi-person-hearts"></i> KHÁCH HÀNG HƯỚNG TỚI</small><div>@foreach($about->audiences ?: [] as $item)<span>{{ $item }}</span>@endforeach</div></div>
        </div>
    </section>

    <section class="about-clients-section">
        <div class="container"><div class="about-clients-heading"><div><span class="section-eyebrow">KHÁCH HÀNG TIÊU BIỂU</span><h2>Được tin tưởng<br>trong những hành trình quan trọng.</h2></div><p>HG TRIP trân trọng sự đồng hành của các doanh nghiệp, tổ chức và đối tác đã lựa chọn dịch vụ của chúng tôi.</p></div><div class="about-client-logos">@foreach($profile['clients'] as $client)<div><img src="{{ asset($client['image']) }}" alt="{{ $client['name'] }}" loading="lazy"></div>@endforeach</div></div>
    </section>

    <section class="about-organisation-section">
        <div class="container"><div class="about-organisation"><div class="about-organisation-copy"><span class="section-eyebrow section-eyebrow-light">BỘ MÁY VÀ HIỆN DIỆ</span><h2>Một đội ngũ vận hành sát sao, kết nối đa thị trường.</h2><p>Từ tư vấn, kinh doanh, điều hành đến hướng dẫn viên và đội xe, các bộ phận cùng phối hợp để mỗi kế hoạch diễn ra thông suốt.</p><a class="btn btn-light" href="{{ route('contact') }}">Liên hệ HG TRIP <i class="bi bi-arrow-right"></i></a></div><div class="about-organisation-detail"><div class="about-departments">@foreach($profile['organisation'] as $department)<div><i class="bi bi-diagram-3"></i><span>{{ $department }}</span></div>@endforeach</div><div class="about-offices">@foreach($profile['offices'] as $office)<article><i class="bi {{ $office['icon'] }}"></i><div><small>{{ $office['label'] }}</small><p>{{ $office['address'] }}</p></div></article>@endforeach</div></div></div></div>
    </section>

    <section class="about-contact-strip">
        <div class="container"><div><span>{{ $profile['company']['legal_name'] ?: $siteSettings->site_name }}</span><h2>{{ $profile['company']['tagline'] }}</h2></div><div class="about-contact-details">@if($siteLinks['phones'])<a href="{{ $siteLinks['phones'][0]['href'] }}"><i class="bi bi-telephone"></i> {{ collect($siteLinks['phones'])->pluck('value')->implode(' · ') }}</a>@endif @if($siteLinks['email'])<a href="mailto:{{ $siteLinks['email'] }}"><i class="bi bi-envelope"></i> {{ $siteLinks['email'] }}</a>@endif<a href="{{ url('/') }}"><i class="bi bi-globe2"></i> {{ $profile['company']['website'] }}</a></div></div>
    </section>
@endsection
