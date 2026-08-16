@extends('layouts.admin')

@section('title', 'Cài đặt website')
@section('page-title', 'Cài đặt website')

@section('content')
    @include('admin.settings.partials.navigation')
    <form action="{{ route('admin.settings.website.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3"><div class="col-xl-8"><x-card type="primary" title="Thông tin hiển thị"><x-input name="site_name" label="Tên website / thương hiệu" :value="$settings->site_name" required /><p class="text-muted small mb-0">Tên này được dùng ở header, footer và metadata khi chưa có nội dung riêng cho từng trang.</p></x-card><x-card type="info" title="Giới thiệu HG" class="mt-3"><x-input name="about_title" label="Tiêu đề" :value="$settings->about_title" /><x-textarea name="about_paragraph_one" label="Đoạn giới thiệu 1" :value="$settings->about_paragraph_one" rows="4" /><x-textarea name="about_paragraph_two" label="Đoạn giới thiệu 2" :value="$settings->about_paragraph_two" rows="4" /></x-card><x-card type="secondary" title="Tour theo yêu cầu" class="mt-3"><x-input name="custom_tour_title" label="Tiêu đề" :value="$settings->custom_tour_title" /><x-textarea name="custom_tour_description" label="Mô tả" :value="$settings->custom_tour_description" rows="3" /></x-card></div><div class="col-xl-4"><x-card type="warning" title="HG trong những con số"><x-input name="impact_title" label="Tiêu đề khối" :value="$settings->impact_title" required />@foreach ([['one', 'Số 1'], ['two', 'Số 2'], ['three', 'Số 3'], ['four', 'Số 4']] as [$key, $label])<div class="border-top pt-3 mt-3"><strong class="small d-block mb-2">{{ $label }}</strong><x-input name="impact_stat_{{ $key }}_number" label="Con số" :value="$settings->{'impact_stat_'.$key.'_number'}" required /><x-input name="impact_stat_{{ $key }}_label" label="Diễn giải" :value="$settings->{'impact_stat_'.$key.'_label'}" required /></div>@endforeach</x-card><x-card type="dark" title="Đối tác" class="mt-3"><x-textarea name="partner_names" label="Mỗi đối tác một dòng" :value="$settings->partner_names" rows="8" /></x-card></div><div class="col-12 text-end"><button class="btn btn-primary">Lưu cài đặt website</button></div></div>
    </form>
@endsection
