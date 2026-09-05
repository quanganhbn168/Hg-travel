<?php

namespace App\Services;

use App\Models\TourCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TourCategoryService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = TourCategory::with('parent')->orderBy('sort_order')->orderBy('name');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        }
        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        }
        if (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }
        if (($filters['home'] ?? null) === 'yes') {
            $query->where('is_home', true);
        }
        if (($filters['home'] ?? null) === 'no') {
            $query->where('is_home', false);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15))->withQueryString();
    }

    public function formContext(?TourCategory $category = null): array
    {
        return ['category' => $category ?: new TourCategory, 'parents' => TourCategory::where('is_active', true)->whereNull('parent_id')->when($category, fn ($q) => $q->whereKeyNot($category->id))->orderBy('name')->get()];
    }

    public function create(array $data): TourCategory
    {
        return TourCategory::create($this->payload($data));
    }

    public function update(TourCategory $category, array $data): void
    {
        $category->update($this->payload($data, $category));
    }

    public function delete(TourCategory $category): void
    {
        $category->delete();
    }

    private function payload(array $data, ?TourCategory $category = null): array
    {
        return ['parent_id' => $data['parent_id'] ?? null, 'name' => trim($data['name']), 'slug' => $data['slug'] ?: Str::slug($data['name']), 'description' => $data['description'] ?? null, 'cover_image' => app(MediaReferenceService::class)->field($data, 'cover_image', $category?->cover_image), 'seo_title' => $data['seo_title'] ?? null, 'seo_description' => $data['seo_description'] ?? null, 'sort_order' => (int) ($data['sort_order'] ?? 0), 'is_active' => (bool) ($data['is_active'] ?? false), 'is_home' => (bool) ($data['is_home'] ?? false)];
    }
}
