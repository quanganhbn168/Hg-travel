<?php

namespace Tests\Unit;

use App\Models\Destination;
use App\Models\Tour;
use App\Services\DestinationTreeService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestinationTreeServiceTest extends TestCase
{
    #[Test]
    public function it_resolves_descendants_and_removes_selected_ancestors(): void
    {
        $root = $this->destination(1, 'chau-a', 'Châu Á', null, 'continent');
        $country = $this->destination(2, 'trung-quoc', 'Trung Quốc', 1, 'country');
        $city = $this->destination(3, 'thuong-hai', 'Thượng Hải', 2, 'city');
        $nodes = new Collection([$root, $country, $city]);
        $tree = new DestinationTreeService();

        $this->assertSame([1, 2, 3], $tree->descendantIds($root, $nodes));
        $this->assertSame([3, 2, 1], $tree->ancestorIds($city, $nodes));
        $this->assertSame([3], $tree->removeAncestorIds([1, 2, 3], $nodes));
        $this->assertSame('Châu Á / Trung Quốc / Thượng Hải', $tree->selectOptions($nodes)[2]['path']);
    }

    #[Test]
    public function it_rolls_published_tour_counts_up_the_destination_tree(): void
    {
        $root = $this->destination(1, 'chau-a', 'Châu Á', null, 'continent');
        $country = $this->destination(2, 'trung-quoc', 'Trung Quốc', 1, 'country');
        $city = $this->destination(3, 'thuong-hai', 'Thượng Hải', 2, 'city');
        $nodes = new Collection([$root, $country, $city]);
        $tour = new Tour(['name' => 'Tour Thượng Hải']);
        $tour->setRelation('destinations', new Collection([$city]));

        $counts = (new DestinationTreeService())->publishedTourCounts($nodes, new Collection([$tour]));

        $this->assertSame([1 => 1, 2 => 1, 3 => 1], $counts);
    }

    private function destination(int $id, string $slug, string $name, ?int $parentId, string $type): Destination
    {
        $destination = new Destination([
            'slug' => $slug,
            'name' => $name,
            'parent_id' => $parentId,
            'type' => $type,
            'market' => 'international',
            'is_active' => true,
        ]);
        $destination->setAttribute('id', $id);

        return $destination;
    }
}
