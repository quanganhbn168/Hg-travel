<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ServiceCatalogAdminService
{
    public function categories(array $filters): LengthAwarePaginator
    {
        $query = ServiceCategory::query()->withCount('services')->orderBy('sort_order')->orderBy('name');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        }
        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        }
        if (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }

    public function categoryContext(?ServiceCategory $category = null): array
    {
        return ['category' => $category ?: new ServiceCategory(['is_active' => true])];
    }

    public function createCategory(array $data): ServiceCategory
    {
        return ServiceCategory::create($this->categoryPayload($data));
    }

    public function updateCategory(ServiceCategory $category, array $data): void
    {
        $category->update($this->categoryPayload($data));
    }

    public function deleteCategory(ServiceCategory $category): void
    {
        abort_if($category->services()->withTrashed()->exists(), 422, 'Không thể xóa danh mục đang có dịch vụ. Hãy tắt hiển thị hoặc chuyển dịch vụ trước.');
        $category->delete();
    }

    public function services(array $filters): LengthAwarePaginator
    {
        $query = Service::query()->with('category')->orderBy('sort_order')->orderBy('name');
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        }
        if (filled($filters['category'] ?? null)) {
            $query->where('service_category_id', $filters['category']);
        }
        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        }
        if (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }

    public function serviceContext(?Service $service = null): array
    {
        return [
            'service' => $service ?: new Service(['is_active' => true]),
            'categories' => ServiceCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }

    public function createService(array $data): Service
    {
        return Service::create($this->servicePayload($data));
    }

    public function updateService(Service $service, array $data): void
    {
        $service->update($this->servicePayload($data, $service));
    }

    public function deleteService(Service $service): void
    {
        $service->delete();
    }

    private function categoryPayload(array $data): array
    {
        return [
            'name' => trim((string) $data['name']),
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'kicker' => $data['kicker'] ?? null,
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function servicePayload(array $data, ?Service $service = null): array
    {
        $benefits = is_array($data['benefits'] ?? null)
            ? $data['benefits']
            : preg_split('/\R/u', (string) ($data['benefits'] ?? ''));

        return [
            'service_category_id' => (int) $data['service_category_id'],
            'name' => trim((string) $data['name']),
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'icon' => $data['icon'] ?? null,
            'description' => $data['description'] ?? null,
            'intro' => $data['intro'] ?? null,
            'benefits' => collect($benefits)->map(fn ($item) => trim((string) $item))->filter()->values()->all(),
            'cover_image' => app(MediaReferenceService::class)->field($data, 'cover_image', $service?->cover_image),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
