<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Tour;
use App\Services\DestinationTreeService;
use App\Services\StructuredDataService;
use App\Services\TourCardPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function __construct(
        private readonly DestinationTreeService $destinationTree,
        private readonly StructuredDataService $structuredData,
        private readonly TourCardPresenter $tourCards,
    ) {}

    public function show(Destination $destination): View
    {
        abort_unless($destination->is_active, 404);

        $nodes = $this->destinationTree->activeNodes();
        $counts = $this->destinationTree->publishedTourCounts($nodes);
        $destinationCount = $counts[(int) $destination->getKey()] ?? 0;

        abort_unless($destination->landing_enabled && $destinationCount > 0, 404);

        $descendantIds = $this->destinationTree->descendantIds($destination, $nodes);
        $tours = $this->publishedTours()
            ->whereHas('destinations', fn (Builder $query) => $query->whereIn('destinations.id', $descendantIds))
            ->with([
                'categories',
                'destinations',
                'images' => fn ($query) => $query->orderByDesc('is_cover')->orderBy('sort_order'),
            ])
            ->withMin([
                'schedules as next_departure_date' => fn (Builder $query) => $query
                    ->where('status', 'open')
                    ->whereDate('departure_date', '>=', today()),
            ], 'departure_date')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();
        $tours->setCollection($tours->getCollection()->map(fn (Tour $tour): array => $this->tourCards->present($tour)));

        $children = $nodes
            ->filter(fn (Destination $node): bool => (int) $node->parent_id === (int) $destination->getKey())
            ->filter(fn (Destination $node): bool => ($counts[(int) $node->getKey()] ?? 0) > 0)
            ->sortBy([['is_featured', 'desc'], ['sort_order', 'asc'], ['name', 'asc']])
            ->map(fn (Destination $node): array => [
                'name' => $node->name,
                'slug' => $node->slug,
                'summary' => $node->summary,
                'image_url' => $this->imageUrl($node) ?: $this->firstDescendantImage($node, $nodes),
                'tour_count' => $counts[(int) $node->getKey()] ?? 0,
                'url' => $node->landing_enabled
                    ? route('destinations.show', ['destination' => $node->slug])
                    : route('tours.index', ['destination' => $node->slug]),
            ])
            ->values()
            ->all();

        $page = [
            'title' => $destination->seo_title ?: $destination->name,
            'description' => $destination->seo_description ?: ($destination->summary ?: 'Khám phá các hành trình nổi bật tại '.$destination->name.'.'),
        ];
        $canonical = route('destinations.show', ['destination' => $destination->slug]);
        $coverImageUrl = $this->imageUrl($destination) ?: $this->firstDescendantImage($destination, $nodes);

        return view('frontend.destinations.show', [
            'destination' => $destination,
            'page' => $page,
            'canonical' => $canonical,
            'coverImageUrl' => $coverImageUrl,
            'breadcrumb' => $this->destinationTree->breadcrumb($destination, $nodes),
            'children' => $children,
            'tours' => $tours,
            'tourCount' => $destinationCount,
            'structuredData' => $this->structuredData->encode($this->structuredData->destinationLanding(
                $destination,
                $page,
                $tours,
                $this->destinationTree->breadcrumb($destination, $nodes),
            )),
        ]);
    }

    private function publishedTours(): Builder
    {
        return Tour::query()
            ->where('is_active', true)
            ->where('status', 'published')
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    private function imageUrl(Destination $destination): ?string
    {
        if (method_exists($destination, 'getFirstMediaUrl') && ($mediaUrl = $destination->getFirstMediaUrl('cover'))) {
            return $mediaUrl;
        }

        $path = $destination->cover_image;

        if (blank($path)) {
            return null;
        }

        return str_starts_with($path, '/') || filter_var($path, FILTER_VALIDATE_URL)
            ? $path
            : asset($path);
    }

    private function firstDescendantImage(Destination $destination, Collection $nodes): ?string
    {
        foreach ($this->destinationTree->descendantIds($destination, $nodes) as $id) {
            if ((int) $id === (int) $destination->getKey()) {
                continue;
            }

            $node = $nodes->firstWhere('id', $id);

            if (! $node) {
                continue;
            }

            $image = $this->imageUrl($node);

            if ($image) {
                return $image;
            }
        }

        return null;
    }
}
