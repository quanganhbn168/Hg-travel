<?php

namespace App\Services;

use App\Models\Destination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class DestinationService
{
    public function __construct(private readonly DestinationTreeService $destinationTree) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Destination::with('parent')
            ->withCount('children')
            ->orderBy('market')
            ->orderBy('sort_order')
            ->orderBy('name');
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
        if (filled($filters['type'] ?? null)) {
            $query->where('type', $filters['type']);
        }
        if (filled($filters['market'] ?? null)) {
            $query->where('market', $filters['market']);
        }
        $paginator = $query->paginate((int) ($filters['per_page'] ?? 15))->withQueryString();
        $counts = $this->destinationTree->publishedTourCounts($this->destinationTree->activeNodes());
        $paginator->setCollection($paginator->getCollection()->each(function (Destination $destination) use ($counts): void {
            $destination->setAttribute('tours_count', $counts[(int) $destination->getKey()] ?? 0);
        }));

        return $paginator;
    }

    public function formContext(?Destination $destination = null): array
    {
        return [
            'destination' => $destination ?: new Destination,
            'parentOptions' => $this->destinationTree->selectOptions(null, $destination),
            'types' => DestinationTreeService::TYPES,
            'markets' => DestinationTreeService::MARKETS,
        ];
    }

    public function create(array $data): Destination
    {
        return Destination::create($this->payload($data));
    }

    public function update(Destination $destination, array $data): void
    {
        if ($destination->is_system) {
            $this->updateProtectedContent($destination, $data);

            return;
        }

        $this->ensureValidParent($destination, $data['parent_id'] ?? null);
        $payload = $this->payload($data);
        $payload['cover_image'] = app(MediaReferenceService::class)->field($data, 'cover_image', $destination->cover_image);
        $destination->update($payload);
    }

    private function updateProtectedContent(Destination $destination, array $data): void
    {
        $payload = [
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'landing_enabled' => (bool) ($data['landing_enabled'] ?? false),
        ];

        if ((bool) ($data['cover_image_remove'] ?? false)) {
            $payload['cover_image'] = null;
        } else {
            $coverImage = trim((string) ($data['cover_image'] ?? ''));

            if ($coverImage !== '') {
                $payload['cover_image'] = $coverImage;
            }
        }

        $destination->update($payload);
    }

    public function delete(Destination $destination): void
    {
        $this->ensureEditable($destination);
        $destination->delete();
    }

    private function payload(array $data): array
    {
        $parentId = $data['parent_id'] ?? null;
        $parent = $parentId ? Destination::query()->find($parentId) : null;
        $type = (string) ($data['type'] ?? 'city');

        if ($parent?->type === 'city') {
            abort(422, 'Điểm đến cha phải là châu lục, quốc gia hoặc khu vực.');
        }

        if ($type === 'continent' && $parentId) {
            abort(422, 'Châu lục không thể nằm dưới một điểm đến khác.');
        }

        if ($type === 'country' && $parent && $parent->type !== 'continent') {
            abort(422, 'Quốc gia phải nằm dưới một châu lục.');
        }

        if ($type === 'region' && $parent && $parent->type !== 'country') {
            abort(422, 'Khu vực phải nằm dưới một quốc gia.');
        }

        return [
            'parent_id' => $parentId,
            'type' => $type,
            'market' => $parent?->market ?: ($data['market'] ?? 'international'),
            'name' => trim($data['name']),
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_system' => false,
            'landing_enabled' => (bool) ($data['landing_enabled'] ?? false),
        ];
    }

    private function ensureEditable(Destination $destination): void
    {
        if ($destination->is_system) {
            abort(422, 'Nhóm địa lý hệ thống không được xóa.');
        }
    }

    private function ensureValidParent(Destination $destination, mixed $parentId): void
    {
        if (! $parentId) {
            return;
        }

        if ((int) $parentId === (int) $destination->getKey()) {
            abort(422, 'Điểm đến không thể là cha của chính nó.');
        }

        $nodes = $this->destinationTree->allNodes();

        if (in_array((int) $destination->getKey(), $this->destinationTree->descendantIds((int) $parentId, $nodes), true)) {
            abort(422, 'Không thể đưa điểm đến vào một node con của chính nó.');
        }
    }
}
