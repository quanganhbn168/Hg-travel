<?php

namespace App\Services;

use App\Models\PostCategory;
use App\Services\Admin\HierarchyTreeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostCategoryService
{
    public function __construct(private readonly HierarchyTreeService $hierarchy) {}

    public function indexContext(array $filters): array
    {
        $nodes = PostCategory::query()
            ->with('parent')
            ->withCount(['children', 'posts'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return [
            'categories' => $this->paginate($filters),
            'categoryTree' => $this->hierarchy->rows($nodes),
            'categoryStats' => [
                'total' => $nodes->count(),
                'roots' => $nodes->whereNull('parent_id')->count(),
                'children' => $nodes->whereNotNull('parent_id')->count(),
                'posts' => $nodes->sum('posts_count'),
            ],
        ];
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return PostCategory::query()
            ->with('parent')
            ->withCount(['children', 'posts'])
            ->when(
                filled($filters['search'] ?? null),
                fn ($query) => $query->where(
                    fn ($inner) => $inner
                        ->where('name', 'like', '%'.trim($filters['search']).'%')
                        ->orWhere('slug', 'like', '%'.trim($filters['search']).'%')
                )
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function formContext(?PostCategory $category = null): array
    {
        return [
            'category' => $category ?: new PostCategory(['is_active' => true]),
            'parents' => PostCategory::query()
                ->whereNull('parent_id')
                ->when($category, fn ($query) => $query->whereKeyNot($category->id))
                ->orderBy('name')
                ->get(),
        ];
    }

    public function create(array $data): PostCategory
    {
        return PostCategory::create($this->payload($data));
    }

    public function update(PostCategory $category, array $data): void
    {
        $category->update($this->payload($data, $category));
    }

    public function delete(PostCategory $category): void
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Không thể xóa danh mục đang có danh mục con.',
            ]);
        }

        if ($category->posts()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Không thể xóa danh mục đang có bài viết.',
            ]);
        }

        $category->delete();
    }

    private function payload(array $data, ?PostCategory $category = null): array
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
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function ensureParentIsValid(?int $parentId, ?PostCategory $category): void
    {
        if (! $parentId) {
            return;
        }

        if ($category && $parentId === (int) $category->getKey()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Danh mục không thể là cha của chính nó.',
            ]);
        }

        $parent = PostCategory::query()->find($parentId);

        if (! $parent || $parent->parent_id !== null) {
            throw ValidationException::withMessages([
                'parent_id' => 'Danh mục cha phải là một danh mục gốc.',
            ]);
        }

        if ($category && $category->children()->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Danh mục đang có node con nên không thể chuyển xuống dưới một danh mục khác.',
            ]);
        }
    }
}
