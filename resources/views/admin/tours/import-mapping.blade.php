@extends('layouts.admin')

@section('title', 'Đối chiếu lịch khởi hành')
@section('page-title', 'Đối chiếu lịch khởi hành')
@section('breadcrumbs')
    <ol class="breadcrumb float-sm-end mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.tours.index') }}">Tour du lịch</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.tours.import.create') }}">Nhập dữ liệu</a></li>
        <li class="breadcrumb-item active">Đối chiếu</li>
    </ol>
@endsection

@section('content')
    <form action="{{ route('admin.tours.import.schedules.confirm') }}" method="POST">
        @csrf
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Ghép tour trước khi nhập lịch</h3>
            </div>
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    Đã đọc <strong>{{ number_format($preview['schedule_count']) }}</strong> ngày khởi hành từ tab <strong>{{ $preview['sheet_name'] }}</strong>, hàng tiêu đề {{ $preview['header_row'] }}. Các ngày có năm ghi rõ trong file được giữ nguyên; các ngày chỉ có “Tháng …” dùng năm {{ $preview['default_year'] }}.
                    @if($preview['warnings'] !== [])
                        <div class="alert alert-warning mt-3 mb-0">
                            @foreach($preview['warnings'] as $warning)
                                <div>{{ $warning }}</div>
                            @endforeach
                        </div>
                    @endif
                    @error('mappings')<div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>@enderror
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tour trong file</th>
                                <th>Thông tin nguồn</th>
                                <th>Lịch đọc được</th>
                                <th style="min-width: 280px">Ghép với tour trong quản trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview['sources'] as $sourceKey => $source)
                                @php($suggested = old("mappings.$sourceKey", $suggestions[$sourceKey] ?? ''))
                                <tr>
                                    <td class="fw-semibold">{{ $source['name'] }}</td>
                                    <td>
                                        <div>{{ $source['duration'] ?: 'Chưa có thời lượng' }}</div>
                                        <div class="text-body-secondary small">{{ $source['transport'] ?: 'Chưa có phương tiện' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ count($source['schedules']) }} ngày</div>
                                        <div class="text-body-secondary small">
                                            {{ collect($source['schedules'])->pluck('departure_date')->sort()->take(4)->map(fn ($date) => \Carbon\Carbon::parse($date)->format('d/m/Y'))->join(' · ') }}{{ count($source['schedules']) > 4 ? ' …' : '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <select name="mappings[{{ $sourceKey }}]" class="form-select">
                                            <option value="">Bỏ qua tour này</option>
                                            @foreach($tours as $tour)
                                                <option value="{{ $tour->id }}" @selected((string) $suggested === (string) $tour->id)>{{ $tour->code }} — {{ $tour->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap align-items-center gap-3">
                <div class="form-check me-auto">
                    <input id="confirm_schedule_import" name="confirm_schedule_import" value="1" type="checkbox" class="form-check-input @error('confirm_schedule_import') is-invalid @enderror" required>
                    <label for="confirm_schedule_import" class="form-check-label">Tôi xác nhận cập nhật các ngày đã ghép; ngày không ghép sẽ được bỏ qua.</label>
                    @error('confirm_schedule_import')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <a href="{{ route('admin.tours.import.create') }}" class="btn btn-default">Tải lại file</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Nhập lịch đã ghép</button>
            </div>
        </div>
    </form>
@endsection
