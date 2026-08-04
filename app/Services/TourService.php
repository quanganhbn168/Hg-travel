<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TourService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Tour::with(['category', 'destination'])->latest();
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        if (! empty($filters['status'])) $query->where('status', $filters['status']);
        if (array_key_exists('active', $filters) && $filters['active'] !== null && $filters['active'] !== '') $query->where('is_active', (bool) $filters['active']);
        return $query->paginate((int) ($filters['per_page'] ?? 15))->withQueryString();
    }

    public function formContext(?Tour $tour = null): array { return ['tour' => $tour ?: new Tour(['status' => 'draft', 'currency' => 'VND', 'is_active' => true, 'booking_open' => true]), 'categories' => TourCategory::where('is_active', true)->orderBy('name')->get(), 'destinations' => Destination::where('is_active', true)->orderBy('name')->get()]; }
    public function create(array $data): Tour { return Tour::create($this->payload($data)); }
    public function update(Tour $tour, array $data): void { $tour->update($this->payload($data)); }
    public function delete(Tour $tour): void { $tour->delete(); }
    private function payload(array $data): array { return ['tour_category_id' => $data['tour_category_id'] ?? null, 'destination_id' => $data['destination_id'] ?? null, 'code' => trim($data['code']), 'name' => trim($data['name']), 'slug' => $data['slug'] ?: Str::slug($data['name']), 'summary' => $data['summary'] ?? null, 'description' => $data['description'] ?? null, 'duration_days' => (int) $data['duration_days'], 'duration_nights' => (int) ($data['duration_nights'] ?? 0), 'starting_price' => $data['starting_price'], 'currency' => strtoupper($data['currency']), 'max_guests' => $data['max_guests'] ?? null, 'status' => $data['status'], 'is_featured' => (bool) ($data['is_featured'] ?? false), 'is_active' => (bool) ($data['is_active'] ?? false), 'booking_open' => (bool) ($data['booking_open'] ?? false), 'seo_title' => $data['seo_title'] ?? null, 'seo_description' => $data['seo_description'] ?? null, 'published_at' => $data['published_at'] ?? null, 'sort_order' => (int) ($data['sort_order'] ?? 0)]; }
}
