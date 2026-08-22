@extends('layouts.admin')

@section('title', 'Cài đặt doanh nghiệp')
@section('page-title', 'Cài đặt')

@section('content')
    <x-admin.settings-layout title="Cài đặt doanh nghiệp" description="Thông tin pháp lý và nhận diện doanh nghiệp dùng trong nội dung, liên hệ và hồ sơ website.">
    <x-slot:actions>
        <button form="admin-settings-business-form" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Lưu cài đặt doanh nghiệp</button>
    </x-slot:actions>
    <form id="admin-settings-business-form" action="{{ route('admin.settings.business.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="Thông tin pháp lý">
                    <x-input name="company_name" label="Tên pháp lý doanh nghiệp" :value="$settings->company_name" />
                    <x-input name="legal_representative" label="Người đại diện pháp luật" :value="$settings->legal_representative" />
                    <x-input name="tax_code" label="Mã số thuế" :value="$settings->tax_code" />
                </x-card>
            </div>
        </div>
    </form>
    </x-admin.settings-layout>
@endsection
