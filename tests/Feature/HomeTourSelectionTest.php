<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTourSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_tours_keep_editorial_priority_and_fill_three_places(): void
    {
        $first = $this->tour('first');
        $second = $this->tour('second');
        $featured = $this->tour('featured', ['is_featured' => true, 'sort_order' => 100]);
        $this->tour('extra');
        $this->tour('draft', ['status' => 'draft', 'is_featured' => true, 'sort_order' => -1]);
        $this->tour('inactive', ['is_active' => false, 'is_featured' => true, 'sort_order' => -1]);
        $this->tour('future', ['published_at' => now()->addDay(), 'is_featured' => true, 'sort_order' => -1]);

        $this->get('/')->assertOk()->assertViewHas('featuredTours', fn (array $tours) => array_column($tours, 'id') === [$featured->id, $first->id, $second->id]);
    }

    public function test_three_selected_featured_tours_are_not_replaced_by_fallbacks(): void
    {
        $this->tour('ordinary');
        $selected = collect(range(1, 3))->map(fn ($i) => $this->tour('selected-'.$i, ['is_featured' => true, 'sort_order' => $i]));

        $this->get('/')->assertOk()->assertViewHas('featuredTours', fn (array $tours) => array_column($tours, 'id') === $selected->pluck('id')->all());
    }

    public function test_sparse_promotions_add_available_tours_without_inventing_discounts(): void
    {
        $promoted = $this->tour('promoted');
        $this->promotion($promoted);
        $ordinary = $this->tour('ordinary');
        $expired = $this->tour('expired');
        $this->promotion($expired, ['ends_at' => now()->subDay()]);
        $this->tour('booking-closed', ['booking_open' => false, 'sort_order' => -1]);
        $full = $this->tour('full', ['sort_order' => -1]);
        $full->schedules()->update(['seats_total' => 10, 'seats_reserved' => 10]);
        $past = $this->tour('past', ['sort_order' => -1]);
        $past->schedules()->update(['departure_date' => today()->subDay()]);
        $closed = $this->tour('closed', ['sort_order' => -1]);
        $closed->schedules()->update(['status' => 'closed']);
        $this->tour('draft', ['status' => 'draft', 'sort_order' => -1]);
        $this->tour('inactive', ['is_active' => false, 'sort_order' => -1]);
        $this->tour('future', ['published_at' => now()->addDay(), 'sort_order' => -1]);
        $this->tour('deleted', ['sort_order' => -1])->delete();

        $response = $this->get('/')->assertOk()
            ->assertViewHas('promotionTitle', 'Ưu đãi & khởi hành sắp tới');
        $tours = $response->viewData('promotionalTours');
        $this->assertSame([$promoted->id, $ordinary->id, $expired->id], array_column($tours, 'id'));
        $this->assertSame(10, $tours[0]['discount_percent']);
        $this->assertSame('9.000.000đ', $tours[0]['sale_price_label']);
        foreach (array_slice($tours, 1) as $tour) {
            $this->assertNull($tour['discount_percent']);
            $this->assertNull($tour['sale_price_label']);
            $this->assertSame('10.000.000đ', $tour['price_label']);
        }
        $this->assertSame(0, $ordinary->promotions()->count());
    }

    public function test_full_promotion_selection_and_empty_catalog_remain_truthful(): void
    {
        $this->get('/')->assertOk()
            ->assertViewHas('featuredTours', [])
            ->assertViewHas('promotionalTours', []);

        foreach (range(1, 6) as $i) {
            $this->promotion($this->tour('sale-'.$i));
        }
        $this->tour('ordinary');

        $this->get('/')->assertOk()
            ->assertViewHas('promotionTitle', 'Ưu đãi đang diễn ra')
            ->assertViewHas('promotionalTours', fn (array $tours) => count($tours) === 6);
    }

    public function test_upcoming_tours_use_an_accurate_heading_when_no_promotion_is_active(): void
    {
        $tour = $this->tour('no-sale');
        $this->promotion($tour, ['starts_at' => now()->addDay()]);

        $this->get('/')->assertOk()
            ->assertViewHas('promotionTitle', 'Hành trình sắp khởi hành')
            ->assertViewHas('promotionalTours', fn (array $tours) => count($tours) === 1 && $tours[0]['sale_price_label'] === null);
    }

    private function tour(string $slug, array $attributes = []): Tour
    {
        $tour = Tour::create($attributes + [
            'code' => $slug, 'name' => $slug, 'slug' => $slug, 'status' => 'published', 'is_active' => true,
            'booking_open' => true, 'is_featured' => false, 'sort_order' => 0,
            'starting_price' => 10000000, 'currency' => 'VND', 'duration_days' => 4,
        ]);
        $tour->schedules()->create([
            'departure_date' => today()->addWeek(), 'status' => 'open',
            'seats_total' => 0, 'seats_reserved' => 0, 'price' => 10000000,
        ]);

        return $tour;
    }

    private function promotion(Tour $tour, array $attributes = []): void
    {
        $promotion = Promotion::create($attributes + [
            'name' => 'Ưu đãi đã cấu hình', 'discount_type' => 'percentage', 'discount_value' => 10,
            'is_active' => true, 'starts_at' => now()->subDay(), 'ends_at' => now()->addMonth(),
        ]);
        $tour->promotions()->attach($promotion);
    }
}
