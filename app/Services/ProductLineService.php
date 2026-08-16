<?php

namespace App\Services;

use App\Models\ProductLine;
use App\Models\Service;
use App\Models\Tour;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductLineService
{
    public function homeCards(): array
    {
        return ProductLine::query()
            ->where('is_active', true)
            ->where('is_home', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ProductLine $productLine, int $index): array => [
                'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'icon' => $productLine->icon ?: 'bi-compass',
                'title' => $productLine->name,
                'description' => $productLine->summary,
                'detail' => $productLine->kicker,
                'url' => route('product-lines.show', ['productLine' => $productLine->slug]),
            ])
            ->all();
    }

    public function contactSubjects(): array
    {
        return ProductLine::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name')
            ->all();
    }

    public function findActive(string $slug): ?ProductLine
    {
        return ProductLine::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->first();
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = ProductLine::query()->withCount(['tours', 'services'])->orderBy('sort_order')->orderBy('name');
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"));
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        if (($filters['home'] ?? null) === 'yes') {
            $query->where('is_home', true);
        } elseif (($filters['home'] ?? null) === 'no') {
            $query->where('is_home', false);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15))->withQueryString();
    }

    public function formContext(?ProductLine $productLine = null): array
    {
        $productLine?->loadMissing(['tours:id,name,code', 'services:id,service_category_id,name,slug']);

        return [
            'productLine' => $productLine ?: new ProductLine(['is_active' => true]),
            'tours' => Tour::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'services' => Service::query()->where('is_active', true)->with('category:id,name')->orderBy('name')->get(['id', 'service_category_id', 'name', 'slug']),
        ];
    }

    public function create(array $data): ProductLine
    {
        $productLine = ProductLine::create($this->payload($data));
        $this->syncRelations($productLine, $data);

        return $productLine;
    }

    public function update(ProductLine $productLine, array $data): void
    {
        $productLine->update($this->payload($data));
        $this->syncRelations($productLine, $data);
    }

    public function delete(ProductLine $productLine): void
    {
        $productLine->delete();
    }

    private function payload(array $data): array
    {
        return [
            'name' => trim((string) $data['name']),
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'kicker' => filled($data['kicker'] ?? null) ? trim((string) $data['kicker']) : null,
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'hero_image' => $data['hero_image'] ?? null,
            'benefits' => $this->benefits($data['benefits'] ?? null),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_home' => (bool) ($data['is_home'] ?? false),
        ];
    }

    private function syncRelations(ProductLine $productLine, array $data): void
    {
        $productLine->tours()->sync($this->pivotPayload($data['tours'] ?? []));
        $productLine->services()->sync($this->pivotPayload($data['services'] ?? []));
    }

    private function pivotPayload(array $ids): array
    {
        return collect($ids)
            ->filter(fn ($id): bool => filled($id))
            ->values()
            ->mapWithKeys(fn ($id, int $index): array => [(int) $id => ['sort_order' => $index + 1]])
            ->all();
    }

    private function benefits(mixed $benefits): array
    {
        if (is_array($benefits)) {
            return collect($benefits)->map(fn ($item): string => trim((string) $item))->filter()->values()->all();
        }

        return collect(preg_split('/\R/u', (string) $benefits))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
