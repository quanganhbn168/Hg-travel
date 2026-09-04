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
        return [
            'destination' => $destination ?: new Destination(),
            'parents' => Destination::query()
                ->system()
                ->where('is_active', true)
                ->when($destination, fn ($q) => $q->whereKeyNot($destination->id))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ];
    }

    public function create(array $data): Destination
    {
        return Destination::create($this->payload($data));
    }

    public function update(Destination $destination, array $data): void
    {
        if ($destination->is_system) {
            $this->updateSystemCoverImage($destination, $data);

            return;
        }

        $this->ensureEditable($destination);
        $payload = $this->payload($data);
        if (! ($data['cover_image_remove'] ?? false) && blank($data['cover_image'] ?? null)) {
            $payload['cover_image'] = $destination->cover_image;
        }
        $destination->update($payload);
    }

    private function updateSystemCoverImage(Destination $destination, array $data): void
    {
        if ((bool) ($data['cover_image_remove'] ?? false)) {
            $destination->update(['cover_image' => null]);

            return;
        }

        $coverImage = trim((string) ($data['cover_image'] ?? ''));

        if ($coverImage !== '') {
            $destination->update(['cover_image' => $coverImage]);
        }
    }

    public function delete(Destination $destination): void
    {
        $this->ensureEditable($destination);
        $destination->delete();
    }

    private function payload(array $data): array
    {
        $parentId = $data['parent_id'] ?? null;

        if ($parentId && ! Destination::query()->system()->whereKey($parentId)->exists()) {
            abort(422, 'Điểm đến cha phải là một nhóm địa lý cố định.');
        }

        return ['parent_id' => $parentId, 'name' => trim($data['name']), 'slug' => $data['slug'] ?: Str::slug($data['name']), 'summary' => $data['summary'] ?? null, 'description' => $data['description'] ?? null, 'cover_image' => $data['cover_image'] ?? null, 'seo_title' => $data['seo_title'] ?? null, 'seo_description' => $data['seo_description'] ?? null, 'sort_order' => (int) ($data['sort_order'] ?? 0), 'is_featured' => (bool) ($data['is_featured'] ?? false), 'is_active' => (bool) ($data['is_active'] ?? false), 'is_system' => false];
    }

    private function ensureEditable(Destination $destination): void
    {
        if ($destination->is_system) {
            abort(422, 'Nhóm địa lý hệ thống không được chỉnh sửa hoặc xóa.');
        }
    }
}
