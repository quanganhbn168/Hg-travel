<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function publicFormContext(?Tour $selectedTour = null): array
    {
        return [
            'tours' => Tour::query()->where('is_active', true)->where('booking_open', true)->where('status', 'published')->orderBy('name')->get(),
            'selectedTour' => $selectedTour,
        ];
    }

    public function createPublic(array $data): Booking
    {
        $tour = Tour::query()->whereKey($data['tour_id'])->where('is_active', true)->where('booking_open', true)->where('status', 'published')->firstOrFail();

        return $this->create([
            ...$data,
            'unit_price' => $tour->starting_price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
    }

    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data): Booking {
            $tour = Tour::findOrFail($data['tour_id']);
            $adults = (int) $data['adults'];
            $children = (int) ($data['children'] ?? 0);
            $total = (float) $data['unit_price'] * ($adults + $children);
            $booking = Booking::create(['booking_code' => 'BK-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)), 'customer_name' => $data['customer_name'], 'customer_email' => $data['customer_email'], 'customer_phone' => $data['customer_phone'], 'customer_address' => $data['customer_address'] ?? null, 'status' => $data['status'], 'payment_status' => $data['payment_status'], 'subtotal' => $total, 'discount_amount' => 0, 'total_amount' => $total, 'currency' => 'VND', 'notes' => $data['notes'] ?? null, 'booked_at' => now()]);
            $booking->items()->create(['tour_id' => $tour->id, 'tour_name' => $tour->name, 'departure_date' => $data['departure_date'] ?? null, 'adults' => $adults, 'children' => $children, 'unit_price' => $data['unit_price'], 'total_price' => $total]);
            return $booking;
        });
    }

    public function update(Booking $booking, array $data): void { $booking->update(['status' => $data['status'], 'payment_status' => $data['payment_status'], 'notes' => $data['notes'] ?? null]); }
}
