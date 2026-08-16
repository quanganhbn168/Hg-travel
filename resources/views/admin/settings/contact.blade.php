@extends('layouts.admin')

@section('title', 'Cài đặt liên lạc')
@section('page-title', 'Cài đặt liên lạc')

@section('content')
    @include('admin.settings.partials.navigation')
    <form action="{{ route('admin.settings.contact.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3"><div class="col-xl-8"><x-card type="primary" title="Điện thoại, email và địa chỉ"><div class="row"><div class="col-md-6"><x-input name="contact_phone" label="Số điện thoại chính" :value="$settings->contact_phone" /></div><div class="col-md-6"><x-input name="contact_phone_secondary" label="Số điện thoại phụ" :value="$settings->contact_phone_secondary" /></div></div><div class="row"><div class="col-md-4"><x-input type="email" name="contact_email" label="Email chính" :value="$settings->contact_email" /></div><div class="col-md-4"><x-input type="email" name="contact_email_secondary" label="Email phụ" :value="$settings->contact_email_secondary" /></div><div class="col-md-4"><x-input type="email" name="contact_email_tertiary" label="Email thứ ba" :value="$settings->contact_email_tertiary" /></div></div><x-textarea name="office_address" label="Địa chỉ văn phòng" :value="$settings->office_address" rows="3" /></x-card></div><div class="col-xl-4"><x-card type="info" title="Kênh mạng xã hội"><x-input type="url" name="facebook_url" label="Facebook URL" :value="$settings->facebook_url" /><x-input type="url" name="instagram_url" label="Instagram URL" :value="$settings->instagram_url" /><x-input type="url" name="youtube_url" label="Youtube URL" :value="$settings->youtube_url" /><x-input type="url" name="zalo_url" label="Zalo URL" :value="$settings->zalo_url" /><x-input type="url" name="messenger_url" label="Messenger URL" :value="$settings->messenger_url" /><x-input type="url" name="whatsapp_url" label="WhatsApp URL" :value="$settings->whatsapp_url" /></x-card><div class="card"><div class="card-body text-end"><button class="btn btn-primary">Lưu cài đặt liên lạc</button></div></div></div></div>
    </form>
@endsection
