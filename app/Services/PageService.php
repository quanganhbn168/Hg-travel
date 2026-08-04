<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PageService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Page::query()->when(filled($filters['search'] ?? null), fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', '%'.trim($filters['search']).'%')->orWhere('slug', 'like', '%'.trim($filters['search']).'%')))->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))->orderBy('sort_order')->latest()->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();
    }
    public function formContext(?Page $page = null): array { return ['page' => $page ?: new Page(['template' => 'default', 'is_active' => true])]; }
    public function create(array $data): Page { return Page::create($this->payload($data)); }
    public function update(Page $page, array $data): void { $page->update($this->payload($data)); }
    public function delete(Page $page): void { $page->delete(); }
    private function payload(array $data): array { return ['template' => $data['template'], 'name' => trim($data['name']), 'slug' => $data['slug'] ?: Str::slug($data['name']), 'sub_title' => $data['sub_title'] ?? null, 'content' => $data['content'] ?? null, 'seo_title' => $data['seo_title'] ?? null, 'seo_description' => $data['seo_description'] ?? null, 'seo_keywords' => $data['seo_keywords'] ?? null, 'is_active' => (bool) ($data['is_active'] ?? false), 'sort_order' => (int) ($data['sort_order'] ?? 0), 'published_at' => $data['published_at'] ?? null]; }
}
