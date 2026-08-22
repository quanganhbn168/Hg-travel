<?php

namespace Tests\Unit;

use App\Support\AdminIndexRegistry;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TourBulkPublishingWorkflowTest extends TestCase
{
    #[Test]
    public function publishing_a_tour_sets_all_required_publication_attributes(): void
    {
        $workflow = AdminIndexRegistry::statusUpdatesFor('tour')['publish'];

        $this->assertSame([
            'status' => 'published',
            'is_active' => true,
            'booking_open' => true,
        ], $workflow['attributes']);
    }

    #[Test]
    public function unpublishing_a_tour_returns_it_to_a_private_draft(): void
    {
        $workflow = AdminIndexRegistry::statusUpdatesFor('tour')['unpublish'];

        $this->assertSame([
            'status' => 'draft',
            'is_active' => false,
            'booking_open' => false,
        ], $workflow['attributes']);
    }
}
