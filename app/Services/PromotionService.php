<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\Tour;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Promotion::query()
            ->withCount('tours')
            ->when(
                filled($filters['search'] ?? null),
                fn ($query) => $query->where('name', 'like', '%'.trim($filters['search']).'%')
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

    public function formContext(?Promotion $promotion = null): array
    {
        return [
            'promotion' => $promotion
                ? $promotion->load('tours')
                : new Promotion([
                    'discount_type' => 'percentage',
                    'is_active' => true,
                ]),
            'tours' => Tour::query()
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ];
    }

    public function create(array $data): Promotion
    {
        return DB::transaction(function () use ($data): Promotion {
            $promotion = Promotion::create($this->payload($data));
            $promotion->tours()->sync($data['tour_ids'] ?? []);

            return $promotion;
        });
    }

    public function update(Promotion $promotion, array $data): void
    {
        DB::transaction(function () use ($promotion, $data): void {
            $promotion->update($this->payload($data));
            $promotion->tours()->sync($data['tour_ids'] ?? []);
        });
    }

    public function delete(Promotion $promotion): void
    {
        $promotion->delete();
    }

    private function payload(array $data): array
    {
        return [
            'name' => trim($data['name']),
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'usage_limit' => $data['usage_limit'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
