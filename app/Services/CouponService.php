<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Tour;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return Coupon::query()
            ->withCount('tours')
            ->when(
                $search !== '',
                fn ($query) => $query->where(
                    fn ($inner) => $inner
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                )
            )
            ->when(
                ($filters['status'] ?? null) === 'active',
                fn ($query) => $query->where('is_active', true)
            )
            ->when(
                ($filters['status'] ?? null) === 'inactive',
                fn ($query) => $query->where('is_active', false)
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function formContext(?Coupon $coupon = null): array
    {
        return [
            'coupon' => $coupon
                ? $coupon->load('tours')
                : new Coupon([
                    'discount_type' => 'percentage',
                    'is_active' => true,
                ]),
            'tours' => Tour::query()
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ];
    }

    public function create(array $data): Coupon
    {
        return DB::transaction(function () use ($data): Coupon {
            $coupon = Coupon::create($this->payload($data));
            $coupon->tours()->sync($data['tour_ids'] ?? []);

            return $coupon;
        });
    }

    public function update(Coupon $coupon, array $data): void
    {
        DB::transaction(function () use ($coupon, $data): void {
            $coupon->update($this->payload($data));
            $coupon->tours()->sync($data['tour_ids'] ?? []);
        });
    }

    public function delete(Coupon $coupon): void
    {
        $coupon->delete();
    }

    private function payload(array $data): array
    {
        return [
            'code' => $data['code'],
            'name' => trim($data['name']),
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'minimum_booking_amount' => $data['minimum_booking_amount'] ?? null,
            'maximum_discount_amount' => $data['maximum_discount_amount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
