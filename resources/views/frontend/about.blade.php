@extends('layouts.master')

@section('title', $about->seo_title ?: $about->hero_title)
@section('meta_description', $about->seo_description ?: $about->hero_intro ?: '')
@section('body_class', 'about-page')

@push('page_styles')
    <link rel="stylesheet" href="{{ asset('css/about.css') }}?v={{ filemtime(public_path('css/about.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/about-profile.css') }}?v={{ filemtime(public_path('css/about-profile.css')) }}">
@endpush

@section('content')
    <section class="about-hero" style="--about-hero: url('{{ $heroImage }}')">
        <div class="about-hero-shade"></div>
        <div class="container about-hero-copy">
            <span class="about-kicker"><i class="bi bi-stars"></i> {{ $content['hero']['kicker'] }}</span>
            <h1>{{ $about->hero_title }}</h1>
            <p>{{ $about->hero_intro }}</p>
            <a class="about-hero-link" href="#thu-ngo">{{ $content['hero']['cta_label'] }} <i class="bi bi-arrow-down"></i></a>
        </div>
        <div class="about-hero-mark" aria-hidden="true"><span>EST.</span><strong>HG</strong><span>TRIP</span></div>
    </section>

    <section class="about-letter" id="thu-ngo">
        <div class="container">
            <div class="about-letter-layout">
                <div class="about-letter-image"><img src="{{ $letterImage }}" alt="Khách hàng cầm cờ HG TRIP trong hành trình khám phá" loading="lazy" width="1024" height="1536"></div>
                <article class="about-letter-card">
                    <span class="section-eyebrow">{{ $about->letter_title }}</span>
                    <div class="about-rich-copy">{!! nl2br(e($about->letter_content)) !!}</div>
                    <div class="about-letter-signature"><span>{{ $content['letter']['signature_name'] }}</span><small>{{ $content['letter']['signature_tagline'] }}</small></div>
                </article>
            </div>
        </div>
    </section>

    <section class="about-company-intro">
        <div class="container">
            <div class="about-company-intro-grid">
                <div>
                    <span class="section-eyebrow">{{ $intro['eyebrow'] }}</span>
                    <h2>{!! nl2br(e($intro['title'])) !!}</h2>
                </div>
                <div class="about-company-intro-copy">
                    <p><strong class="about-company-name">{{ $companyName }}</strong> {{ $intro['lead'] }}</p>
                    <p>{{ $intro['content'] }}</p>
                </div>
            </div>
            <div class="about-company-facts">
                @foreach($impactStats as $stat)
                    <div>
                        <strong>{{ $stat['number'] }}</strong><span>{{ $stat['label'] }}</span></div>
                @endforeach
            </div>
            <div class="about-company-credentials">@foreach($intro['credentials'] as $credential)<span><i class="bi bi-patch-check-fill"></i>{{ $credential }}</span>@endforeach</div>
        </div>
    </section>

    <section class="section-space about-story-section">
        <div class="container about-story">
            <div class="about-story-copy">
                <span class="section-eyebrow">{{ $story['eyebrow'] }}</span>
                <h2>{{ $about->story_title }}</h2>
                <p>{!! nl2br(e($about->story_content)) !!}</p>
                <div class="about-story-fact"><i class="bi bi-people-fill"></i><span>{{ $story['fact'] }}</span></div>
            </div>
            <figure class="about-story-photo">
                <img src="{{ $storyImage }}" alt="{{ $story['photo_alt'] }}" loading="lazy" width="1115" height="1982">
                <figcaption>{{ $story['photo_caption'] }}</figcaption>
            </figure>
        </div>
    </section>

    <section class="about-story-steps-section">
        <div class="container">
            <div class="about-story-steps-heading">
                <span class="section-eyebrow">{{ $storySteps['eyebrow'] }}</span>
                <p>{{ $storySteps['intro'] }}</p>
            </div>
            <div class="about-story-steps">
                @foreach($storySteps['items'] as $step)
                    <article>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['description'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
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
        <div class="container"><div class="about-markets-heading"><div><span class="section-eyebrow">{{ $about->markets_eyebrow }}</span><h2>{!! nl2br(e($about->markets_title)) !!}</h2></div><p>{{ $about->markets_intro }}</p></div><div class="about-markets-grid">@foreach($marketCards as $market)<article><span>0{{ $loop->iteration }}</span><h3>{{ $market['name'] }}</h3><p>{{ $market['detail'] }}</p></article>@endforeach</div></div>
    </section>

    <section class="about-values section-space">
        <div class="container">
            <div class="about-section-heading"><div><span class="section-eyebrow section-eyebrow-light">{{ $values['eyebrow'] }}</span><h2>{!! nl2br(e($values['title'])) !!}</h2></div><p>{{ $values['intro'] }}</p></div>
            <div class="about-values-grid">@foreach($about->core_values ?: [] as $value)<article class="about-value"><span>0{{ $loop->iteration }}</span><h3>{{ $value['title'] }}</h3><p>{{ $value['description'] }}</p></article>@endforeach</div>
        </div>
    </section>

    <section class="about-products-section">
        <div class="container"><div class="about-products-heading"><div><span class="section-eyebrow">{{ $products['eyebrow'] }}</span><h2>{!! nl2br(e($products['title'])) !!}</h2></div><p>{{ $products['intro'] }}</p></div><div class="about-featured-products">@foreach($products['items'] as $product)<article><i class="bi {{ $product['icon'] }}"></i><h3>{{ $product['title'] }}</h3><p>{{ $product['description'] }}</p><a href="{{ route('contact', ['subject' => $product['title']]) }}">Nhận tư vấn <i class="bi bi-arrow-up-right"></i></a></article>@endforeach</div></div>
    </section>

    <section class="about-support-section">
        <div class="container"><div class="about-support-heading"><span class="section-eyebrow section-eyebrow-light">{{ $support['eyebrow'] }}</span><h2>{!! nl2br(e($support['title'])) !!}</h2><p>{{ $support['intro'] }}</p></div><div class="about-support-grid">@foreach($supportServices as $service)<article><i class="bi {{ $service->icon ?: 'bi-check2-circle' }}"></i><div><h3>{{ $service->name }}</h3><p>{{ $service->description }}</p></div></article>@endforeach</div></div>
    </section>

    <section class="about-leaders-section">
        <div class="container"><div class="about-leaders"><div class="about-leaders-intro"><span class="section-eyebrow">{{ $leaders['eyebrow'] }}</span><h2>{{ $leaders['title'] }}</h2><p>{{ $leaders['intro'] }}</p></div><div class="about-leader-list"><article><h3>{{ $about->ceo_name }}</h3><div class="about-leader-divider"></div><span>{{ $leaders['ceo_role'] }}</span><p>{{ $about->ceo_bio }}</p></article><article><h3>{{ $about->deputy_name }}</h3><div class="about-leader-divider"></div><span>{{ $leaders['deputy_role'] }}</span><p>{{ $about->deputy_bio }}</p></article></div></div></div>
    </section>

    <section class="about-lists">
        <div class="container"><div><small><i class="bi bi-globe2"></i> THỊ TRƯỜNG HOẠT ĐỘNG</small><div>@foreach($marketCards as $market)<span>{{ $market['name'] }}</span>@endforeach</div></div><div><small><i class="bi bi-patch-check"></i> CAM KẾT</small><ul>@foreach($about->commitments ?: [] as $item)<li>{{ $item }}</li>@endforeach</ul></div><div><small><i class="bi bi-person-hearts"></i> KHÁCH HÀNG HƯỚNG TỚI</small><div>@foreach($about->audiences ?: [] as $item)<span>{{ $item }}</span>@endforeach</div></div></div>
    </section>

    <section class="about-clients-section">
        <div class="container"><div class="about-clients-heading"><div><span class="section-eyebrow">{{ $clients['eyebrow'] }}</span><h2>{!! nl2br(e($clients['title'])) !!}</h2></div><p>{{ $clients['intro'] }}</p></div><div class="about-client-logos">@foreach($clients['items'] as $client)<div><img src="{{ $client['image_url'] }}" alt="{{ $client['name'] }}" loading="lazy"></div>@endforeach</div></div>
    </section>

    <section class="about-organisation-section">
        <div class="container"><div class="about-organisation"><div class="about-organisation-copy"><span class="section-eyebrow section-eyebrow-light">{{ $organisation['eyebrow'] }}</span><h2>{{ $organisation['title'] }}</h2><p>{{ $organisation['intro'] }}</p><a class="btn btn-light" href="{{ route('contact') }}">{{ $organisation['cta_label'] }} <i class="bi bi-arrow-right"></i></a></div><div class="about-organisation-detail"><div class="about-departments">@foreach($organisation['departments'] as $department)<div><i class="bi bi-diagram-3"></i><span>{{ $department }}</span></div>@endforeach</div><div class="about-offices">@foreach($organisation['offices'] as $office)<article><i class="bi {{ $office['icon'] }}"></i><div><small>{{ $office['label'] }}</small><p>{{ $office['address'] }}</p></div></article>@endforeach</div></div></div></div>
    </section>

    <section class="about-contact-strip">
        <div class="container"><div class="about-contact-brand"><span class="about-contact-company">{{ $companyName }}</span><p class="about-contact-tagline">{{ $content['contact']['tagline'] }}</p></div><div class="about-contact-details">@if($siteLinks['phones'])<a href="{{ $siteLinks['phones'][0]['href'] }}"><i class="bi bi-telephone"></i> {{ collect($siteLinks['phones'])->pluck('value')->implode(' · ') }}</a>@endif @if($siteLinks['email'])<a href="mailto:{{ $siteLinks['email'] }}"><i class="bi bi-envelope"></i> {{ $siteLinks['email'] }}</a>@endif<a href="{{ url('/') }}"><i class="bi bi-globe2"></i> {{ parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.name', 'HG TRIP') }}</a></div></div>
    </section>
@endsection
