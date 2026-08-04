<?php

namespace App\Services;

use App\Models\Destination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class DestinationService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Destination::with('parent')->orderBy('sort_order')->orderBy('name');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        if (($filters['status'] ?? null) === 'active') $query->where('is_active', true);
        if (($filters['status'] ?? null) === 'inactive') $query->where('is_active', false);
        return $query->paginate((int) ($filters['per_page'] ?? 15))->withQueryString();
    }

    public function formContext(?Destination $destination = null): array
    {
        return ['destination' => $destination ?: new Destination(), 'parents' => Destination::whereNull('parent_id')->when($destination, fn ($q) => $q->whereKeyNot($destination->id))->orderBy('name')->get()];
    }

    public function create(array $data): Destination { return Destination::create($this->payload($data)); }
    public function update(Destination $destination, array $data): void { $destination->update($this->payload($data)); }
    public function delete(Destination $destination): void { $destination->delete(); }
    private function payload(array $data): array { return ['parent_id' => $data['parent_id'] ?? null, 'name' => trim($data['name']), 'slug' => $data['slug'] ?: Str::slug($data['name']), 'summary' => $data['summary'] ?? null, 'description' => $data['description'] ?? null, 'cover_image' => $data['cover_image'] ?? null, 'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null, 'seo_title' => $data['seo_title'] ?? null, 'seo_description' => $data['seo_description'] ?? null, 'sort_order' => (int) ($data['sort_order'] ?? 0), 'is_featured' => (bool) ($data['is_featured'] ?? false), 'is_active' => (bool) ($data['is_active'] ?? false)]; }
}
