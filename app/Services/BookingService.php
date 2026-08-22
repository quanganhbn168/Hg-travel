<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Tour;
use App\Models\TourSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public const STATUSES = ['pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 'cancelled' => 'Đã hủy', 'completed' => 'Hoàn tất'];
    public const PAYMENT_STATUSES = ['unpaid' => 'Chưa thanh toán', 'pending' => 'Đang chờ', 'paid' => 'Đã thanh toán', 'refunded' => 'Đã hoàn tiền'];

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Booking::with('items.tour')->latest();
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') $query->where(fn ($q) => $q->where('booking_code', 'like', "%{$search}%")->orWhere('customer_name', 'like', "%{$search}%")->orWhere('customer_phone', 'like', "%{$search}%"));
        if (! empty($filters['status'])) $query->where('status', $filters['status']);
        return $query->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }

    public function formContext(?Booking $booking = null): array { return ['booking' => $booking ?: new Booking(['status' => 'pending', 'payment_status' => 'unpaid', 'currency' => 'VND']), 'tours' => Tour::where('is_active', true)->where('booking_open', true)->orderBy('name')->get(), 'statuses' => self::STATUSES, 'paymentStatuses' => self::PAYMENT_STATUSES]; }

    public function publicFormContext(?Tour $selectedTour = null, ?TourSchedule $selectedSchedule = null): array
    {
        return [
            'tours' => Tour::query()->where('is_active', true)->where('booking_open', true)->where('status', 'published')->orderBy('name')->get(),
            'selectedTour' => $selectedTour,
            'selectedSchedule' => $selectedSchedule,
        ];
    }

    public function findPublicSchedule(?Tour $tour, int $scheduleId): ?TourSchedule
    {
        if (! $tour) {
            return null;
        }

        $schedule = $tour->schedules()
            ->whereKey($scheduleId)
            ->where('status', 'open')
            ->whereDate('departure_date', '>=', today())
            ->first();

        return $schedule?->isAvailable() ? $schedule : null;
    }

    public function createPublic(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $tour = Tour::query()
                ->whereKey($data['tour_id'])
                ->where('is_active', true)
                ->where('booking_open', true)
                ->where('status', 'published')
                ->firstOrFail();
            $partySize = (int) $data['adults'] + (int) ($data['children'] ?? 0);
            $schedule = null;

            if (filled($data['tour_schedule_id'] ?? null)) {
                $schedule = TourSchedule::query()
                    ->whereKey((int) $data['tour_schedule_id'])
                    ->where('tour_id', $tour->getKey())
                    ->where('status', 'open')
                    ->whereDate('departure_date', '>=', today())
                    ->lockForUpdate()
                    ->first();

                if (! $schedule || ! $schedule->isAvailable()) {
                    throw ValidationException::withMessages(['tour_schedule_id' => 'Lịch khởi hành đã chọn không còn nhận đăng ký.']);
                }

                if ($schedule->seatsLeft() !== null && $partySize > $schedule->seatsLeft()) {
                    throw ValidationException::withMessages(['tour_schedule_id' => 'Số chỗ còn lại không đủ cho số lượng khách đã chọn.']);
                }

                $schedule->increment('seats_reserved', $partySize);
            }

            return $this->createRecord($tour, [
                ...$data,
                'tour_schedule_id' => $schedule?->getKey(),
                'departure_date' => $schedule?->departure_date?->toDateString() ?: ($data['departure_date'] ?? null),
                'unit_price' => $schedule && $schedule->effectivePrice() > 0 ? $schedule->effectivePrice() : $tour->starting_price,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);
        });
    }

    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $tour = Tour::findOrFail($data['tour_id']);
            return $this->createRecord($tour, $data);
        });
    }

    public function update(Booking $booking, array $data): void
    {
        DB::transaction(function () use ($booking, $data): void {
            $fromStatus = $booking->status;
            $toStatus = $data['status'];

            if (($fromStatus === 'cancelled') !== ($toStatus === 'cancelled')) {
                $booking->loadMissing('items');

                foreach ($booking->items as $item) {
                    if (! $item->tour_schedule_id) {
                        continue;
                    }

                    $schedule = TourSchedule::query()->lockForUpdate()->find($item->tour_schedule_id);
                    if (! $schedule) {
                        continue;
                    }

                    $partySize = (int) $item->adults + (int) $item->children;
                    if ($toStatus === 'cancelled') {
                        $schedule->update(['seats_reserved' => max(0, (int) $schedule->seats_reserved - $partySize)]);
                        continue;
                    }

                    if ($schedule->seatsLeft() !== null && $partySize > $schedule->seatsLeft()) {
                        throw ValidationException::withMessages(['status' => 'Không thể khôi phục booking vì lịch khởi hành không còn đủ chỗ.']);
                    }

                    $schedule->increment('seats_reserved', $partySize);
                }
            }

            $booking->update([
                'status' => $toStatus,
                'payment_status' => $data['payment_status'],
                'notes' => $data['notes'] ?? null,
            ]);

            if ($fromStatus !== $toStatus) {
                $booking->statusHistories()->create([
                    'changed_by' => auth('admin')->id() ?? auth()->id(),
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'note' => $data['notes'] ?? null,
                ]);
            }
        });
    }

    /** @param array<string, mixed> $data */
    private function createRecord(Tour $tour, array $data): Booking
    {
        $adults = (int) $data['adults'];
        $children = (int) ($data['children'] ?? 0);
        $total = (float) $data['unit_price'] * ($adults + $children);
        $booking = Booking::create([
            'booking_code' => 'BK-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'customer_address' => $data['customer_address'] ?? null,
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
            'subtotal' => $total,
            'discount_amount' => 0,
            'total_amount' => $total,
            'currency' => 'VND',
            'notes' => $data['notes'] ?? null,
            'booked_at' => now(),
        ]);
        $booking->items()->create([
            'tour_id' => $tour->id,
            'tour_schedule_id' => $data['tour_schedule_id'] ?? null,
            'tour_name' => $tour->name,
            'departure_date' => $data['departure_date'] ?? null,
            'adults' => $adults,
            'children' => $children,
            'unit_price' => $data['unit_price'],
            'total_price' => $total,
        ]);
        $booking->statusHistories()->create([
            'changed_by' => auth('admin')->id() ?? auth()->id(),
            'from_status' => null,
            'to_status' => $booking->status,
            'note' => 'Tạo booking',
        ]);

        return $booking;
    }
}
