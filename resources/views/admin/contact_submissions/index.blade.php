@extends('layouts.admin')

@section('title', 'Yêu cầu liên hệ')
@section('page-title', 'Yêu cầu liên hệ')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active" aria-current="page">Yêu cầu liên hệ</li></ol>
@endsection

@section('content')
<x-admin.index-card title="Hộp thư liên hệ" description="Theo dõi và xử lý các yêu cầu gửi từ biểu mẫu liên hệ." icon="bi-envelope-open">
    <x-slot:filters>
        <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.contact-submissions.index') }}">
            <div class="col-lg-4"><label class="form-label" for="submission-status">Trạng thái</label><select id="submission-status" class="form-select" name="status"><option value="">Tất cả trạng thái</option>@foreach(['new'=>'Mới','processing'=>'Đang xử lý','resolved'=>'Đã xử lý','closed'=>'Đã đóng'] as $value=>$label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-lg-2"><button class="btn btn-primary" type="submit">Lọc</button></div>
        </form>
    </x-slot:filters>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Khách hàng</th><th>Nội dung</th><th>Trạng thái</th><th>Gửi lúc</th><th class="text-end">Thao tác</th></tr></thead><tbody>
        @forelse($submissions as $submission)
            <tr><td><strong>{{ $submission->name }}</strong><small class="d-block">{{ $submission->phone }} · {{ $submission->email }}</small></td><td><strong>{{ $submission->subject }}</strong><small class="d-block">{{ \Illuminate\Support\Str::limit($submission->message, 100) }}</small></td><td><span class="badge text-bg-light">{{ $submission->status }}</span></td><td>{{ $submission->created_at->format('d/m/Y H:i') }}</td><td class="text-end"><a class="btn btn-default btn-sm" href="{{ route('admin.contact-submissions.edit', $submission) }}">Xử lý</a></td></tr>
        @empty
            <tr><td colspan="5" class="text-center py-5">Chưa có yêu cầu liên hệ.</td></tr>
        @endforelse
    </tbody></table></div>
    <x-slot:footer>@if($submissions->hasPages()){{ $submissions->links() }}@endif</x-slot:footer>
</x-admin.index-card>
@endsection
