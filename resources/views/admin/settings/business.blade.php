@extends('layouts.admin')

@section('title', 'Cài đặt doanh nghiệp')
@section('page-title', 'Cài đặt doanh nghiệp')

@section('content')
    @include('admin.settings.partials.navigation')
    <form action="{{ route('admin.settings.business.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row"><div class="col-xl-8"><x-card type="primary" title="Thông tin pháp lý"><x-input name="company_name" label="Tên pháp lý doanh nghiệp" :value="$settings->company_name" /><x-input name="legal_representative" label="Người đại diện pháp luật" :value="$settings->legal_representative" /><x-input name="tax_code" label="Mã số thuế" :value="$settings->tax_code" /></x-card></div><div class="col-xl-8 text-end"><button class="btn btn-primary">Lưu cài đặt doanh nghiệp</button></div></div>
    </form>
@endsection
