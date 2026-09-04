<?php

namespace App\Services;

use App\Models\DestinationAlias;
use App\Models\Tour;
use Illuminate\Support\Collection;

final class DestinationCoverageService
{
    private ?Collection $nodes = null;

    private ?Collection $aliases = null;

    public function __construct(private readonly DestinationTreeService $destinationTree) {}

    /** @return array<int, int> */
    public function syncTour(Tour $tour): array
    {
        $tour->loadMissing('itineraries');
        $nodes = $this->nodes();
        $identity = DestinationAlias::normalize(collect([
            $tour->name,
            $tour->source_ref,
        ])->filter()->implode(' '));
        $text = DestinationAlias::normalize(collect([
            $tour->name,
            $tour->source_ref,
            ...$tour->itineraries->pluck('title')->all(),
        ])->filter()->implode(' '));
        $matches = [];

        foreach ($this->aliases() as $alias) {
            $needle = DestinationAlias::normalize((string) $alias->alias);

            if (mb_strlen($needle) < 3) {
                continue;
            }

            if ($this->isTransitAlias($needle, $identity)) {
                continue;
            }

            $position = strpos(' '.$text.' ', ' '.$needle.' ');

            if ($position === false) {
                continue;
            }

            $destinationId = (int) $alias->destination_id;
            $matches[$destinationId] = min($matches[$destinationId] ?? PHP_INT_MAX, $position);
        }

        if ($matches === []) {
            return $tour->destinations()->pluck('destinations.id')->map(fn ($id): int => (int) $id)->all();
        }

        $destinationIds = $this->destinationTree->removeAncestorIds(array_keys($matches), $nodes);
        usort($destinationIds, fn (int $left, int $right): int => ($matches[$left] ?? PHP_INT_MAX) <=> ($matches[$right] ?? PHP_INT_MAX));

        $pivot = [];

        foreach ($destinationIds as $index => $destinationId) {
            $pivot[$destinationId] = [
                'sort_order' => $index + 1,
                'is_primary' => $index === 0,
            ];
        }

        $tour->destinations()->sync($pivot);
        $landingIds = collect($destinationIds)
            ->flatMap(fn (int $destinationId): array => $this->destinationTree->ancestorIds($destinationId, $nodes))
            ->unique()
            ->values();

        $this->nodes()
            ->whereIn('id', $landingIds)
            ->filter(fn ($destination): bool => ! $destination->landing_enabled)
            ->each(fn ($destination) => $destination->update(['landing_enabled' => true]));

        return $destinationIds;
    }

    public function syncAll(): int
    {
        $count = 0;

        Tour::query()
            ->with('itineraries')
            ->orderBy('id')
            ->each(function (Tour $tour) use (&$count): void {
                $this->syncTour($tour);
                $count++;
            });

        return $count;
    }

    private function nodes(): Collection
    {
        return $this->nodes ??= $this->destinationTree->activeNodes();
    }

    private function aliases(): Collection
    {
        return $this->aliases ??= DestinationAlias::query()
            ->where('locale', app()->getLocale())
            ->whereHas('destination', fn ($query) => $query->where('is_active', true))
            ->get();
    }

    private function isTransitAlias(string $alias, string $tourIdentity): bool
    {
        // Istanbul is used as an airline connection in several European
        // packages. Keep it only when Turkey is part of the package identity.
        return $alias === 'ISTANBUL'
            && ! preg_match('/(?:THO NHI KY|TURKEY|TURKIYE)/u', $tourIdentity);
    }
}
