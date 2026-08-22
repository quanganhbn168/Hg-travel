@props(['schedules' => null])

@php
    $items = collect(old('schedules', $schedules instanceof \Illuminate\Support\Collection ? $schedules->map(fn ($schedule): array => [
        'id' => $schedule->id,
        'departure_date' => $schedule->departure_date?->format('Y-m-d'),
        'return_date' => $schedule->return_date?->format('Y-m-d'),
        'seats_total' => $schedule->seats_total,
        'seats_reserved' => $schedule->seats_reserved,
        'price' => number_format((float) $schedule->price, 0, '.', ''),
        'sale_price' => $schedule->sale_price === null ? '' : number_format((float) $schedule->sale_price, 0, '.', ''),
        'transport' => $schedule->transport,
        'status' => $schedule->status,
        'notes' => $schedule->notes,
        'source_seat_info_raw' => $schedule->source_seat_info_raw,
    ])->all() : (array) $schedules));
@endphp

<div data-tour-schedule-editor>
    @if ($items->isEmpty())
        <div class="border rounded-3 p-4 mb-3 bg-body-tertiary text-center" data-tour-schedule-empty>
            <i class="bi bi-calendar-plus text-primary fs-2 d-block mb-2"></i>
            <strong class="d-block">Chưa có ngày khởi hành</strong>
            <span class="small text-body-secondary">Bấm “Thêm lịch khởi hành” để thiết lập ngày đi, ngày về, giá niêm yết, giá giảm và số khách tối đa.</span>
        </div>
    @endif
    <div class="d-grid gap-3" data-tour-schedule-list>
        @foreach ($items as $index => $item)
            @php($item = (array) $item)
            <div class="border rounded-3 p-3 bg-body-tertiary" data-tour-schedule-row>
                <input type="hidden" name="schedules[{{ $index }}][id]" value="{{ $item['id'] ?? '' }}">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <strong class="text-primary">Đợt khởi hành {{ $loop->iteration }}</strong>
                    <label class="form-check mb-0 text-danger"><input class="form-check-input" type="checkbox" name="schedules[{{ $index }}][remove]" value="1" @checked($item['remove'] ?? false)> Xóa lịch này</label>
                </div>
                <div class="row g-3">
                    <div class="col-md-3"><x-input name="schedules[{{ $index }}][departure_date]" type="date" label="Ngày khởi hành" :value="$item['departure_date'] ?? ''" required /></div>
                    <div class="col-md-3"><x-input name="schedules[{{ $index }}][return_date]" type="date" label="Ngày về" :value="$item['return_date'] ?? ''" /></div>
                    <div class="col-md-2"><x-input name="schedules[{{ $index }}][seats_total]" type="number" min="0" label="Số khách tối đa" :value="$item['seats_total'] ?? 0" required /></div>
                    <div class="col-md-2"><label class="form-label">Đã giữ / còn</label><div class="form-control-plaintext fw-semibold">{{ $item['seats_reserved'] ?? 0 }} / {{ (int) ($item['seats_total'] ?? 0) > 0 ? max(0, (int) $item['seats_total'] - (int) ($item['seats_reserved'] ?? 0)) : '—' }}</div></div>
                    <div class="col-md-2"><x-select name="schedules[{{ $index }}][status]" label="Trạng thái" :options="['open' => 'Mở bán', 'closed' => 'Đóng', 'cancelled' => 'Hủy']" :selected="$item['status'] ?? 'open'" :tom-select="false" /></div>
                    <div class="col-md-4"><x-money-input name="schedules[{{ $index }}][price]" label="Giá niêm yết / khách" :value="$item['price'] ?? 0" :min="0" required /></div>
                    <div class="col-md-4"><x-money-input name="schedules[{{ $index }}][sale_price]" label="Giá giảm / khách" :value="$item['sale_price'] ?? ''" :min="0" placeholder="Để trống nếu không giảm" /></div>
                    <div class="col-md-4"><x-input name="schedules[{{ $index }}][transport]" label="Phương tiện đợt này" :value="$item['transport'] ?? ''" placeholder="Để trống để dùng phương tiện chung" /></div>
                    <div class="col-12"><x-textarea name="schedules[{{ $index }}][notes]" label="Ghi chú công khai" :value="$item['notes'] ?? ''" rows="2" placeholder="Ví dụ: Điểm hẹn, ghi chú giá hoặc dịch vụ kèm theo" /></div>
                </div>
                @if (! empty($item['source_seat_info_raw']))<p class="small text-body-secondary mb-0">Dữ liệu import ban đầu: {{ $item['source_seat_info_raw'] }}</p>@endif
            </div>
        @endforeach
    </div>
    <template data-tour-schedule-template>
        <div class="border rounded-3 p-3 bg-body-tertiary" data-tour-schedule-row>
            <input type="hidden" name="schedules[__INDEX__][id]" value="">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3"><strong class="text-primary">Đợt khởi hành mới</strong><button type="button" class="btn btn-sm btn-outline-danger" data-tour-schedule-remove><i class="bi bi-trash me-1"></i>Xóa</button></div>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Ngày khởi hành <span class="text-danger">*</span></label><input class="form-control" type="date" name="schedules[__INDEX__][departure_date]" required></div>
                <div class="col-md-3"><label class="form-label">Ngày về</label><input class="form-control" type="date" name="schedules[__INDEX__][return_date]"></div>
                <div class="col-md-2"><label class="form-label">Số khách tối đa <span class="text-danger">*</span></label><input class="form-control" type="number" min="0" name="schedules[__INDEX__][seats_total]" value="0" required></div>
                <div class="col-md-2"><label class="form-label">Đã giữ / còn</label><div class="form-control-plaintext fw-semibold">0 / —</div></div>
                <div class="col-md-2"><label class="form-label">Trạng thái</label><select class="form-select" name="schedules[__INDEX__][status]"><option value="open">Mở bán</option><option value="closed">Đóng</option><option value="cancelled">Hủy</option></select></div>
                <div class="col-md-4"><x-money-input name="schedules[__INDEX__][price]" label="Giá niêm yết / khách" value="0" :min="0" required /></div>
                <div class="col-md-4"><x-money-input name="schedules[__INDEX__][sale_price]" label="Giá giảm / khách" :min="0" placeholder="Để trống nếu không giảm" /></div>
                <div class="col-md-4"><label class="form-label">Phương tiện đợt này</label><input class="form-control" name="schedules[__INDEX__][transport]" placeholder="Để trống để dùng phương tiện chung"></div>
                <div class="col-12"><label class="form-label">Ghi chú công khai</label><textarea class="form-control" name="schedules[__INDEX__][notes]" rows="2"></textarea></div>
            </div>
        </div>
    </template>
    <button class="btn btn-outline-primary mt-3" type="button" data-tour-schedule-add><i class="bi bi-plus-circle me-1"></i>Thêm lịch khởi hành</button>
</div>

@pushOnce('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-tour-schedule-editor]').forEach(function (root) {
            const list = root.querySelector('[data-tour-schedule-list]');
            const template = root.querySelector('[data-tour-schedule-template]');
            const addButton = root.querySelector('[data-tour-schedule-add]');
            let nextIndex = list.querySelectorAll('[data-tour-schedule-row]').length;

            root.addEventListener('click', function (event) {
                const remove = event.target.closest('[data-tour-schedule-remove]');
                if (remove) remove.closest('[data-tour-schedule-row]')?.remove();
            });

            addButton.addEventListener('click', function () {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                const row = wrapper.firstElementChild;
                list.appendChild(row);
                window.initHgMoneyInputs?.(row);
                root.querySelector('[data-tour-schedule-empty]')?.remove();
            });
        });
    });
</script>
@endpushOnce
