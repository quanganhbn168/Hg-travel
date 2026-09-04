<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class DestinationTreeService
{
    public const TYPES = [
        'continent' => 'Châu lục',
        'country' => 'Quốc gia',
        'region' => 'Khu vực',
        'city' => 'Thành phố / điểm đến',
    ];

    public const MARKETS = [
        'domestic' => 'Trong nước',
        'international' => 'Nước ngoài',
    ];

    public function activeNodes(): Collection
    {
        return Destination::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function allNodes(): Collection
    {
        return Destination::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /** @return array<int, array{id: int, label: string, path: string, type: string, market: string, disabled: bool}> */
    public function selectOptions(?Collection $nodes = null, ?Destination $exclude = null): array
    {
        $nodes ??= $this->activeNodes();
        $byParent = $nodes->groupBy(fn (Destination $node): string => (string) ($node->parent_id ?? 0));
        $excludedIds = $exclude ? $this->descendantIds($exclude, $nodes) : [];
        $build = function (int $parentId, int $depth = 0) use (&$build, $byParent, $excludedIds, $nodes): array {
            return collect($byParent->get((string) $parentId, []))
                ->flatMap(function (Destination $node) use (&$build, $depth, $excludedIds, $nodes): array {
                    $path = $this->path($node, $nodes);
                    $row = [[
                        'id' => (int) $node->getKey(),
                        'label' => str_repeat('— ', $depth).$node->name,
                        'path' => $path,
                        'type' => (string) $node->type,
                        'market' => (string) $node->market,
                        'disabled' => in_array((int) $node->getKey(), $excludedIds, true),
                    ]];

                    return [...$row, ...$build((int) $node->getKey(), $depth + 1)];
                })
                ->values()
                ->all();
        };

        return $build(0);
    }

    /** @return array<int, int> */
    public function descendantIds(Destination|int $destination, ?Collection $nodes = null): array
    {
        $nodes ??= $this->activeNodes();
        $id = $destination instanceof Destination ? (int) $destination->getKey() : (int) $destination;
        $byParent = $nodes->groupBy(fn (Destination $node): string => (string) ($node->parent_id ?? 0));
        $ids = [$id];
        $visit = function (int $parentId) use (&$visit, &$ids, $byParent): void {
            foreach ($byParent->get((string) $parentId, []) as $child) {
                $childId = (int) $child->getKey();

                if (in_array($childId, $ids, true)) {
                    continue;
                }

                $ids[] = $childId;
                $visit($childId);
            }
        };
        $visit($id);

        return $ids;
    }

    /** @return array<int, int> */
    public function ancestorIds(Destination|int $destination, ?Collection $nodes = null): array
    {
        $nodes ??= $this->activeNodes();
        $byId = $nodes->keyBy(fn (Destination $node): int => (int) $node->getKey());
        $current = $destination instanceof Destination
            ? $destination
            : $byId->get((int) $destination);
        $ids = [];
        $guard = 0;

        while ($current && $guard++ < 20) {
            $id = (int) $current->getKey();

            if (! in_array($id, $ids, true) && isset($byId[$id])) {
                $ids[] = $id;
            }

            $current = $current->parent_id ? $byId->get((int) $current->parent_id) : null;
        }

        return $ids;
    }

    /** @param array<int, int> $ids @return array<int, int> */
    public function removeAncestorIds(array $ids, Collection $nodes): array
    {
        $selected = array_values(array_unique(array_map('intval', $ids)));
        $byId = $nodes->keyBy(fn (Destination $node): int => (int) $node->getKey());
        $result = [];

        foreach ($selected as $candidateId) {
            $hasSelectedDescendant = false;

            foreach ($selected as $otherId) {
                if ($candidateId === $otherId || ! isset($byId[$otherId])) {
                    continue;
                }

                $parentId = $byId[$otherId]->parent_id;
                $guard = 0;

                while ($parentId && $guard++ < 20) {
                    if ((int) $parentId === $candidateId) {
                        $hasSelectedDescendant = true;
                        break;
                    }

                    $parentId = $byId->get((int) $parentId)?->parent_id;
                }

                if ($hasSelectedDescendant) {
                    break;
                }
            }

            if (! $hasSelectedDescendant) {
                $result[] = $candidateId;
            }
        }

        return $result;
    }

    public function root(Destination $destination, Collection $nodes): Destination
    {
        $byId = $nodes->keyBy(fn (Destination $node): int => (int) $node->getKey());
        $current = $destination;
        $guard = 0;

        while ($current->parent_id && isset($byId[$current->parent_id]) && $guard++ < 20) {
            $current = $byId[$current->parent_id];
        }

        return $current;
    }

    /** @return array<int, array{name: string, url: string}> */
    public function breadcrumb(Destination $destination, Collection $nodes): array
    {
        $byId = $nodes->keyBy(fn (Destination $node): int => (int) $node->getKey());
        $items = [];
        $current = $destination;
        $guard = 0;

        while ($current && $guard++ < 20) {
            array_unshift($items, [
                'name' => $current->name,
                'url' => $current->is($destination)
                    ? route('destinations.show', ['destination' => $current->slug])
                    : route('tours.index', ['destination' => $current->slug]),
            ]);
            $current = $current->parent_id ? $byId->get((int) $current->parent_id) : null;
        }

        return $items;
    }

    /** @param array<int, int> $counts @return array<int, array{name: string, slug: string}> */
    public function optionsWithTours(Collection $nodes, array $counts, ?string $market = null): array
    {
        $byId = $nodes->keyBy(fn (Destination $node): int => (int) $node->getKey());

        return collect($this->selectOptions($nodes))
            ->filter(function (array $option) use ($counts, $market): bool {
                return ($market === null || $option['market'] === $market)
                    && (($counts[$option['id']] ?? 0) > 0);
            })
            ->map(fn (array $option): array => [
                'name' => $option['label'],
                'slug' => $byId->get($option['id'])?->slug,
            ])
            ->filter(fn (array $option): bool => filled($option['slug']))
            ->values()
            ->all();
    }

    /** @return array<int, int> */
    public function publishedTourCounts(Collection $nodes, ?Collection $tours = null): array
    {
        $tours ??= Tour::query()
            ->where('is_active', true)
            ->where('status', 'published')
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with('destinations:id,parent_id')
            ->get(['id']);

        $counts = $nodes->mapWithKeys(fn (Destination $node): array => [(int) $node->getKey() => 0])->all();

        foreach ($tours as $tour) {
            $seen = [];

            foreach ($tour->destinations as $destination) {
                foreach ($this->ancestorIds($destination, $nodes) as $id) {
                    if (isset($seen[$id])) {
                        continue;
                    }

                    $seen[$id] = true;
                    $counts[$id] = ($counts[$id] ?? 0) + 1;
                }
            }
        }

        return $counts;
    }

    /** @return array<int, int> */
    private function path(Destination $destination, ?Collection $nodes): string
    {
        if ($nodes === null) {
            return $destination->name;
        }

        return collect($this->breadcrumb($destination, $nodes))->pluck('name')->implode(' / ');
    }
}
