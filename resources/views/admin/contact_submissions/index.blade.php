@extends('layouts.admin')

@section('title', 'Yêu cầu liên hệ')
@section('page-title', 'Yêu cầu liên hệ')

@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Yêu cầu liên hệ</li>
    </ol>
@endsection

@section('content')
<x-admin.index-card
    title="Hộp thư liên hệ"
    description="Theo dõi và xử lý các yêu cầu gửi từ biểu mẫu liên hệ."
>
    <x-slot:filters>
        <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.contact-submissions.index') }}">
            <div class="col-lg-5">
                <label class="form-label" for="submission-search">Từ khóa</label>
                <input id="submission-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên, điện thoại, email hoặc chủ đề">
            </div>

            <div class="col-lg-2">
                <label class="form-label" for="submission-status">Trạng thái</label>
                <select id="submission-status" class="form-select" name="status">
                    <option value="">Tất cả trạng thái</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <x-admin.per-page />
            </div>

            <div class="col-lg-3">
                <x-admin.filter-actions :reset-url="route('admin.contact-submissions.index')" />
            </div>
        </form>
    </x-slot:filters>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>Nội dung</th>
                    <th>Trạng thái</th>
                    <th>Gửi lúc</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $submission)
                    <tr>
                        <td>
                            <strong>{{ $submission->name }}</strong>
                            <small class="d-block text-body-secondary">
                                {{ $submission->phone ?: '—' }}
                                @if($submission->email)
                                    · {{ $submission->email }}
                                @endif
                            </small>
                        </td>

                        <td>
                            <strong>{{ $submission->subject ?: 'Không có chủ đề' }}</strong>
                            <small class="d-block text-body-secondary">
                                {{ IlluminateSupportStr::limit($submission->message, 100) }}
                            </small>
                        </td>

                        <td>
                            <span class="badge text-bg-light border">
                                {{ $statuses[$submission->status] ?? $submission->status }}
                            </span>
                        </td>

                        <td>{{ $submission->created_at->format('d/m/Y H:i') }}</td>

                        <td class="text-end">
                            @if(auth('admin')->user()?->hasPermissionTo('contact-submissions.update', 'web'))
                                <a class="btn btn-default btn-sm" href="{{ route('admin.contact-submissions.edit', $submission) }}">
                                    <i class="bi bi-pencil-square me-1"></i>
                                    Xử lý
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-admin.empty-state message="Chưa có yêu cầu liên hệ." />
                @endforelse
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        @if($submissions->hasPages())
            {{ $submissions->links() }}
        @endif
    </x-slot:footer>
</x-admin.index-card>
@endsection
