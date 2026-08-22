@extends('layouts.admin')

@section('title', 'Cài đặt liên lạc')
@section('page-title', 'Cài đặt')

@section('content')
    <x-admin.settings-layout title="Cài đặt liên hệ" description="Quản lý thông tin liên hệ và các kênh mạng xã hội được hiển thị trên website.">
    <x-slot:actions>
        <button form="admin-settings-contact-form" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Lưu cài đặt liên hệ</button>
    </x-slot:actions>
    <form id="admin-settings-contact-form" action="{{ route('admin.settings.contact.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-xl-8">
                <x-card type="primary" title="Điện thoại, email và địa chỉ">
                    <div class="row g-3"><div class="col-md-6"><x-input name="contact_phone" label="Số điện thoại chính" :value="$settings->contact_phone" /></div><div class="col-md-6"><x-input name="contact_phone_secondary" label="Số điện thoại phụ" :value="$settings->contact_phone_secondary" /></div></div>
                    <div class="row g-3"><div class="col-md-4"><x-input type="email" name="contact_email" label="Email chính" :value="$settings->contact_email" /></div><div class="col-md-4"><x-input type="email" name="contact_email_secondary" label="Email phụ" :value="$settings->contact_email_secondary" /></div><div class="col-md-4"><x-input type="email" name="contact_email_tertiary" label="Email thứ ba" :value="$settings->contact_email_tertiary" /></div></div>
                    <x-textarea name="office_address" label="Địa chỉ văn phòng" :value="$settings->office_address" rows="3" />
                </x-card>
            </div>
            <div class="col-xl-4">
                <x-card type="info" title="Kênh mạng xã hội">
                    <x-input type="url" name="facebook_url" label="Facebook URL" :value="$settings->facebook_url" />
                    <x-input type="url" name="instagram_url" label="Instagram URL" :value="$settings->instagram_url" />
                    <x-input type="url" name="youtube_url" label="Youtube URL" :value="$settings->youtube_url" />
                    <x-input type="url" name="zalo_url" label="Zalo URL" :value="$settings->zalo_url" />
                    <x-input type="url" name="messenger_url" label="Messenger URL" :value="$settings->messenger_url" />
                    <x-input type="url" name="whatsapp_url" label="WhatsApp URL" :value="$settings->whatsapp_url" />
                </x-card>
            </div>
        </div>
    </form>
    </x-admin.settings-layout>
@endsection
