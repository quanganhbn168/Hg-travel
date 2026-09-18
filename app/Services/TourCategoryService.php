<?php

namespace App\Services;

use App\Models\TourCategory;
use App\Services\Admin\HierarchyTreeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TourCategoryService
{
    public function __construct(
        private readonly HierarchyTreeService $hierarchy,
        private readonly MediaReferenceService $mediaReferences,
    ) {}

    public function indexContext(array $filters): array
    {
        $nodes = TourCategory::query()
            ->with('parent')
            ->withCount(['children', 'tours'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $this->decorate($nodes);

        return [
            'categories' => $this->paginate($filters),
            'categoryTree' => $this->hierarchy->rows($nodes),
            'categoryStats' => [
                'total' => $nodes->count(),
                'roots' => $nodes->whereNull('parent_id')->count(),
                'children' => $nodes->whereNotNull('parent_id')->count(),
                'missing_images' => $nodes->filter(
                    fn (TourCategory $category): bool => blank($category->cover_image)
                )->count(),
            ],
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = TourCategory::with('parent')
            ->withCount(['children', 'tours'])
            ->orderBy('sort_order')
            ->orderBy('name');

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(
                fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
            );
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

        $paginator = $query
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        $this->decorate($paginator->getCollection());

        return $paginator;
    }

    public function formContext(?TourCategory $category = null): array
    {
        return [
            'category' => $category ?: new TourCategory,
            'parents' => TourCategory::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->when($category, fn ($query) => $query->whereKeyNot($category->id))
                ->orderBy('name')
                ->get(),
        ];
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
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Không thể xóa loại hình đang có loại hình con.',
            ]);
        }

        if ($category->tours()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Không thể xóa loại hình đang được gắn với tour.',
            ]);
        }

        $category->delete();
    }

    private function payload(array $data, ?TourCategory $category = null): array
    {
        $parentId = filled($data['parent_id'] ?? null)
            ? (int) $data['parent_id']
            : null;

        $this->ensureParentIsValid($parentId, $category);

        return [
            'parent_id' => $parentId,
            'name' => trim($data['name']),
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'cover_image' => $this->mediaReferences->field(
                $data,
                'cover_image',
                $category?->cover_image,
            ),
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_home' => (bool) ($data['is_home'] ?? false),
        ];
    }

    private function ensureParentIsValid(?int $parentId, ?TourCategory $category): void
    {
        if (! $parentId) {
            return;
        }

        if ($category && $parentId === (int) $category->getKey()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Loại hình không thể là cha của chính nó.',
            ]);
        }

        $parent = TourCategory::query()->find($parentId);

        if (! $parent || $parent->parent_id !== null) {
            throw ValidationException::withMessages([
                'parent_id' => 'Loại hình cha phải là một loại hình gốc.',
            ]);
        }

        if ($category && $category->children()->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Loại hình đang có node con nên không thể chuyển xuống dưới một loại hình khác.',
            ]);
        }
    }

    private function decorate(Collection $categories): void
    {
        $categories->each(function (TourCategory $category): void {
            $category->setAttribute(
                'cover_url',
                $this->mediaReferences->url($category->cover_image, null, true),
            );
        });
    }
}
