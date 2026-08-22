@props([
    'schedules' => [],
    'bookingOpen' => false,
    'showSeatAvailability' => true,
    'note' => 'Giá, lịch và số chỗ được cập nhật theo từng đợt khởi hành.',
])

@php
    $scheduleItems = collect($schedules);
    $availableScheduleCount = $scheduleItems->where('is_available', true)->count();
    $defaultSchedule = $scheduleItems->first(fn (array $schedule): bool => (bool) ($schedule['is_available'] ?? false));
    $selectedScheduleId = old('tour_schedule_id', $defaultSchedule['id'] ?? null);
    $selectedSchedule = $scheduleItems->first(fn (array $schedule): bool => (bool) ($schedule['is_available'] ?? false) && (string) ($schedule['id'] ?? '') === (string) $selectedScheduleId) ?: $defaultSchedule;
@endphp

<div class="tour-schedule-picker" data-tour-schedule-picker>
    <div class="tour-schedule-picker-heading">
        <div>
            <span class="section-eyebrow">Chọn lịch phù hợp</span>
            <p class="mb-0">Chọn ngày khởi hành trước khi gửi yêu cầu đặt tour.</p>
        </div>
        <span class="tour-schedule-picker-count"><i class="bi bi-calendar3"></i>{{ $availableScheduleCount }} lịch có thể chọn</span>
    </div>

    <div class="tour-departure-table-wrap">
        <table class="tour-departure-table tour-schedule-table">
            <thead>
                <tr>
                    <th scope="col"><span class="visually-hidden">Chọn</span></th>
                    <th scope="col">Ngày khởi hành</th>
                    <th scope="col">Ngày về</th>
                    <th scope="col">Giá / khách</th>
                    @if ($showSeatAvailability)<th scope="col">Chỗ</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($scheduleItems as $schedule)
                    @php
                        $isAvailable = (bool) ($schedule['is_available'] ?? false);
                        $isDefault = $selectedSchedule && (int) $selectedSchedule['id'] === (int) ($schedule['id'] ?? 0);
                    @endphp
                    <tr class="{{ $isDefault ? 'is-selected' : '' }} {{ $isAvailable ? '' : 'is-full' }}" data-schedule-row>
                        <td>
                            <label class="tour-schedule-choice" @if (!$isAvailable) title="Lịch này đã hết chỗ" @endif>
                                <input
                                    type="radio"
                                    name="tour_schedule_selection"
                                    value="{{ $schedule['id'] }}"
                                    data-schedule-option
                                    data-schedule-label="{{ $schedule['departure_date'] }} · {{ $schedule['price_label'] }}@if ($showSeatAvailability) · {{ $schedule['slot_label'] }}@endif"
                                    @checked($isDefault)
                                    @disabled(!$isAvailable || !$bookingOpen)
                                >
                                <span class="tour-schedule-choice-mark" aria-hidden="true"><i class="bi bi-check2"></i></span>
                                <span class="visually-hidden">{{ $isAvailable ? 'Chọn ngày '.$schedule['departure_date'] : 'Lịch đã hết chỗ' }}</span>
                            </label>
                        </td>
                        <td><strong>{{ $schedule['departure_date'] }}</strong></td>
                        <td>{{ $schedule['return_date'] ?: 'Đang cập nhật' }}</td>
                        <td>
                            @if ($schedule['has_sale_price'] ?? false)
                                <del class="tour-schedule-price-old">{{ $schedule['regular_price_label'] }}</del>
                                <strong class="tour-departure-price">{{ $schedule['sale_price_label'] }}</strong>
                            @else
                                <strong class="tour-departure-price">{{ $schedule['price_label'] }}</strong>
                            @endif
                        </td>
                        @if ($showSeatAvailability)
                            <td>
                                <span class="tour-schedule-slot {{ $schedule['slot_class'] ?? 'is-unknown' }}">
                                    <i class="bi {{ $isAvailable ? 'bi-people' : 'bi-slash-circle' }}"></i>
                                    <strong>{{ $schedule['slot_label'] }}</strong>
                                </span>
                                @if (($schedule['seats_reserved'] ?? 0) > 0)
                                    <small class="tour-schedule-slot-note">{{ $schedule['seats_reserved'] }} chỗ đã đăng ký</small>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($bookingOpen)
        <div class="tour-schedule-picker-footer">
            <p class="tour-schedule-selected mb-0" data-schedule-selected-label>
                @if ($defaultSchedule)
                    <i class="bi bi-check-circle"></i> Đã chọn {{ $selectedSchedule['departure_date'] }} · {{ $selectedSchedule['price_label'] }}@if ($showSeatAvailability) · {{ $selectedSchedule['slot_label'] }}@endif
                @else
                    <i class="bi bi-info-circle"></i> Chưa có lịch còn chỗ để chọn.
                @endif
            </p>
            <a class="btn btn-brand" href="#tour-booking-modal" data-bs-toggle="modal" data-bs-target="#tour-booking-modal" data-schedule-booking-cta @if (!$selectedSchedule) aria-disabled="true" @endif>
                <i class="bi bi-calendar2-check me-2"></i>Đặt ngay ngày này
            </a>
        </div>
    @endif

    <p class="tour-departure-note mb-0">{{ $note }}</p>
</div>
