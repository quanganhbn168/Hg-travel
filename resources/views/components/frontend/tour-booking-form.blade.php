@props(['tour'])

@php
    $availableSchedules = collect($tour['schedules'] ?? [])->filter(fn (array $schedule): bool => (bool) ($schedule['is_available'] ?? false));
    $defaultSchedule = $availableSchedules->first();
    $selectedScheduleId = old('tour_schedule_id', $defaultSchedule['id'] ?? null);
    $selectedSchedule = $availableSchedules->first(fn (array $schedule): bool => (string) $schedule['id'] === (string) $selectedScheduleId) ?: $defaultSchedule;
    $hasSchedules = collect($tour['schedules'] ?? [])->isNotEmpty();
    $shouldOpen = session()->has('booking_success') || $errors->any();
@endphp

<div class="modal fade tour-booking-modal" id="tour-booking-modal" tabindex="-1" aria-labelledby="tour-booking-modal-title" aria-hidden="true" @if ($shouldOpen) data-open-on-load @endif>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h2 class="h4 mb-0" id="tour-booking-modal-title">{{ $tour['name'] }}</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form class="tour-booking-form" id="tour-booking-form" action="{{ route('booking.store') }}" method="POST" data-tour-booking-form novalidate>
                @csrf
                <div class="modal-body">
                    <p class="text-body-secondary mb-4">Điền thông tin để HG giữ chỗ và xác nhận lại lịch khởi hành với anh/chị.</p>

                    @if (session('booking_success'))
                        <div class="alert alert-success" role="status">{{ session('booking_success') }}</div>
                    @endif

                    <input type="hidden" name="source" value="tour_detail">
                    <input type="hidden" name="tour_id" value="{{ $tour['id'] }}">
                    @if ($hasSchedules)
                        <input type="hidden" name="tour_schedule_id" value="{{ $selectedScheduleId }}" data-tour-booking-schedule>
                    @endif

                    <div class="row g-3">
                        @if ($hasSchedules)
                            <div class="col-12">
                                <div class="tour-booking-selected-schedule {{ $selectedSchedule ? '' : 'is-empty' }}" data-tour-booking-schedule-summary>
                                    <i class="bi {{ $selectedSchedule ? 'bi-calendar2-check' : 'bi-exclamation-circle' }}"></i>
                                    <span data-tour-booking-schedule-label>{{ $selectedSchedule ? 'Đã chọn '.$selectedSchedule['departure_date'].' · '.$selectedSchedule['slot_label'] : 'Chưa có lịch còn chỗ để đăng ký.' }}</span>
                                </div>
                                @error('tour_schedule_id')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            </div>
                        @else
                            <div class="col-md-6"><label class="form-label" for="tour-booking-departure">Ngày khởi hành dự kiến *</label><input id="tour-booking-departure" class="form-control @error('departure_date') is-invalid @enderror" type="date" name="departure_date" value="{{ old('departure_date') }}" min="{{ now()->toDateString() }}" required>@error('departure_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        @endif
                        <div class="col-md-6"><label class="form-label" for="tour-booking-name">Họ và tên *</label><input id="tour-booking-name" class="form-control @error('customer_name') is-invalid @enderror" name="customer_name" value="{{ old('customer_name') }}" required>@error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label class="form-label" for="tour-booking-phone">Số điện thoại *</label><input id="tour-booking-phone" class="form-control @error('customer_phone') is-invalid @enderror" name="customer_phone" value="{{ old('customer_phone') }}" inputmode="tel" required>@error('customer_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label class="form-label" for="tour-booking-email">Email *</label><input id="tour-booking-email" class="form-control @error('customer_email') is-invalid @enderror" type="email" name="customer_email" value="{{ old('customer_email') }}" required>@error('customer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-3"><label class="form-label" for="tour-booking-adults">Người lớn *</label><input id="tour-booking-adults" class="form-control @error('adults') is-invalid @enderror" type="number" min="1" max="100" name="adults" value="{{ old('adults', 1) }}" required>@error('adults')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-3"><label class="form-label" for="tour-booking-children">Trẻ em</label><input id="tour-booking-children" class="form-control @error('children') is-invalid @enderror" type="number" min="0" max="100" name="children" value="{{ old('children', 0) }}">@error('children')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-12"><label class="form-label" for="tour-booking-address">Địa chỉ</label><input id="tour-booking-address" class="form-control @error('customer_address') is-invalid @enderror" name="customer_address" value="{{ old('customer_address') }}">@error('customer_address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-12"><label class="form-label" for="tour-booking-notes">Ghi chú</label><textarea id="tour-booking-notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="4" placeholder="Ví dụ: điểm đón, loại phòng, nhu cầu riêng...">{{ old('notes') }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column flex-sm-row gap-3 align-items-sm-center justify-content-between">
                    <small class="text-body-secondary"><i class="bi bi-shield-check me-1"></i>Chưa phát sinh thanh toán tại đây.</small>
                    <div class="d-flex gap-2"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Để sau</button><button class="btn btn-brand" type="submit" @disabled($hasSchedules && ! $selectedSchedule)><i class="bi bi-calendar2-check me-2"></i>Gửi yêu cầu giữ chỗ</button></div>
                </div>
            </form>
        </div>
    </div>
</div>
