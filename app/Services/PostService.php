<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PostService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Post::with(['category', 'author'])->when(filled($filters['search'] ?? null), fn ($q) => $q->where(fn ($inner) => $inner->where('name', 'like', '%'.trim($filters['search']).'%')->orWhere('slug', 'like', '%'.trim($filters['search']).'%')))->when(filled($filters['category'] ?? null), fn ($q) => $q->where('post_category_id', $filters['category']))->when(($filters['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))->when(($filters['status'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))->latest()->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }

    public function formContext(?Post $post = null): array
    {
        return [
            'post' => $post ?: new Post(['is_active' => true]),
            'contentHtml' => app(PostContentService::class)->toHtml($post?->content),
            'categories' => PostCategory::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    public function create(array $data): Post
    {
        return Post::create($this->payload($data) + ['created_by' => auth('admin')->id()]);
    }

    public function update(Post $post, array $data): void
    {
        $post->update($this->payload($data, $post));
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    private function payload(array $data, ?Post $post = null): array
    {
        $data['content'] = app(PostContentService::class)->toHtml($data['content'] ?? null);

        return ['post_category_id' => $data['post_category_id'] ?? null, 'name' => trim($data['name']), 'slug' => $data['slug'] ?: Str::slug($data['name']), 'summary' => $data['summary'] ?? null, 'content' => $data['content'] ?? null, 'cover_image' => app(MediaReferenceService::class)->field($data, 'cover_image', $post?->cover_image), 'seo_title' => $data['seo_title'] ?? null, 'seo_description' => $data['seo_description'] ?? null, 'seo_keywords' => $data['seo_keywords'] ?? null, 'is_featured' => (bool) ($data['is_featured'] ?? false), 'is_active' => (bool) ($data['is_active'] ?? false), 'published_at' => $data['published_at'] ?? null];
    }
}
