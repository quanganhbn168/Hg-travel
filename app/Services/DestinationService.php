<?php

namespace App\Services;

use App\Models\Destination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DestinationService
{
    public function __construct(
        private readonly DestinationTreeService $destinationTree,
        private readonly MediaReferenceService $mediaReferences,
    ) {}

    public function indexContext(array $filters): array
    {
        $activeNodes = $this->destinationTree->activeNodes();
        $tourCounts = $this->destinationTree->publishedTourCounts($activeNodes);
        $nodes = $this->destinationTree->allNodes();

        $this->decorateDestinations($nodes, $tourCounts);

        $parentOptions = $this->parentOptions(null, $nodes);
        $quickParentOptions = $this->parentOptions(null, $activeNodes);

        $quickParentMeta = collect($quickParentOptions)->mapWithKeys(
            fn (array $option): array => [
                (string) $option['id'] => [
                    'name' => $option['path'],
                    'type' => $option['type'],
                    'market' => $option['market'],
                    'allowed_child_types' => $this->destinationTree->allowedChildTypes($option['type']),
                ],
            ],
        )->all();

        $quickParentMeta['__root__'] = [
            'name' => 'Điểm đến gốc',
            'type' => null,
            'market' => 'international',
            'allowed_child_types' => $this->destinationTree->allowedChildTypes(),
        ];

        return [
            'destinations' => $this->paginate($filters, $tourCounts),
            'destinationTree' => $this->destinationTree->treeRows($nodes),
            'parentOptions' => $parentOptions,
            'quickParentOptions' => $quickParentOptions,
            'quickParentMeta' => $quickParentMeta,
            'destinationStats' => [
                'total' => $nodes->count(),
                'countries' => $nodes->where('type', 'country')->count(),
                'places' => $nodes->whereIn('type', ['region', 'city'])->count(),
                'missing_images' => $nodes->filter(
                    fn (Destination $destination): bool => blank($destination->cover_image)
                )->count(),
            ],
            'types' => DestinationTreeService::TYPES,
            'markets' => DestinationTreeService::MARKETS,
        ];
    }

    public function paginate(array $filters, ?array $tourCounts = null): LengthAwarePaginator
    {
        $query = Destination::with('parent')
            ->withCount('children')
            ->orderBy('market')
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

        if (filled($filters['parent_id'] ?? null)) {
            $query->where('parent_id', (int) $filters['parent_id']);
        }

        if (filled($filters['type'] ?? null)) {
            $query->where('type', $filters['type']);
        }

        if (filled($filters['market'] ?? null)) {
            $query->where('market', $filters['market']);
        }

        $paginator = $query
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        $tourCounts ??= $this->destinationTree->publishedTourCounts(
            $this->destinationTree->activeNodes()
        );

        $this->decorateDestinations($paginator->getCollection(), $tourCounts);

        return $paginator;
    }

    public function formContext(?Destination $destination = null): array
    {
        return [
            'destination' => $destination ?: new Destination,
            'parentOptions' => $this->parentOptions($destination),
            'types' => DestinationTreeService::TYPES,
            'markets' => DestinationTreeService::MARKETS,
        ];
    }

    public function parentOptions(?Destination $exclude = null, ?Collection $nodes = null): array
    {
        return collect($this->destinationTree->selectOptions($nodes, $exclude))
            ->filter(
                fn (array $option): bool => $this->destinationTree->allowedChildTypes($option['type']) !== []
            )
            ->values()
            ->all();
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
        $payload['cover_image'] = $this->mediaReferences->field(
            $data,
            'cover_image',
            $destination->cover_image,
        );

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

        if ($parentId && ! $parent) {
            throw ValidationException::withMessages([
                'parent_id' => 'Điểm đến cha không tồn tại hoặc đã bị xóa.',
            ]);
        }

        $allowedChildTypes = $this->destinationTree->allowedChildTypes($parent);

        if (! in_array($type, $allowedChildTypes, true)) {
            if ($allowedChildTypes === []) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Điểm đến đã chọn không thể chứa thêm điểm đến con.',
                ]);
            }

            $allowedLabels = collect($allowedChildTypes)
                ->map(fn (string $allowedType): string => DestinationTreeService::TYPES[$allowedType] ?? $allowedType)
                ->implode(' hoặc ');

            throw ValidationException::withMessages([
                'type' => $parent
                    ? $parent->name.' chỉ có thể chứa '.$allowedLabels.'.'
                    : 'Điểm đến gốc chỉ có thể là '.$allowedLabels.'.',
            ]);
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

        $destinationId = (int) $destination->getKey();
        $parentId = (int) $parentId;

        if ($parentId === $destinationId) {
            throw ValidationException::withMessages([
                'parent_id' => 'Điểm đến không thể là cha của chính nó.',
            ]);
        }

        $nodes = $this->destinationTree->allNodes();
        $descendantIds = $this->destinationTree->descendantIds($destinationId, $nodes);

        if (in_array($parentId, $descendantIds, true)) {
            throw ValidationException::withMessages([
                'parent_id' => 'Không thể đưa điểm đến vào một node con của chính nó.',
            ]);
        }
    }

    private function decorateDestinations(Collection $destinations, array $tourCounts): void
    {
        $destinations->each(function (Destination $destination) use ($tourCounts): void {
            $destination->setAttribute(
                'tours_count',
                $tourCounts[(int) $destination->getKey()] ?? 0,
            );

            $destination->setAttribute(
                'cover_url',
                $this->mediaReferences->url($destination->cover_image, null, true),
            );
        });
    }
}
