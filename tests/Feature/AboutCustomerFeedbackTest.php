<?php

namespace Tests\Feature;

use App\Services\AboutPageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutCustomerFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_updates_existing_content_and_keeps_cms_ownership(): void
    {
        $page = app(AboutPageService::class)->current();
        $content = $page->profile_content;
        $content['organisation']['eyebrow'] = 'BỘ MÁY VÀ HIỆN DIỆ';
        $content['company_intro']['content'] = 'Nội dung riêng của khách hàng.';
        $page->update([
            'profile_content' => $content,
            'hero_title' => 'Tiêu đề đã biên tập',
            'hero_image' => 'images/about/hg-trip/letter-travel.jpg',
            'background_image' => 'images/about/hg-trip/journey-beijing.jpg',
            'story_image' => 'images/about/hg-trip/journey-kazakhstan.jpg',
        ]);

        $migration = require database_path('migrations/2026_09_06_000100_apply_about_customer_feedback.php');
        $migration->up();
        $page->refresh();

        $this->assertSame('BỘ MÁY VÀ HIỆN DIỆN', $page->profile_content['organisation']['eyebrow']);
        $this->assertSame('Nội dung riêng của khách hàng.', $page->profile_content['company_intro']['content']);
        $this->assertSame('Tiêu đề đã biên tập', $page->hero_title);
        $this->assertSame('images/about/hg-trip/letter-travel.jpg', $page->hero_image);
        $this->assertSame('images/about/hg-trip/hg-trip-traveller.png', $page->background_image);
        $this->assertSame('images/about/hg-trip/hg-trip-south-africa.jpg', $page->story_image);
        $this->get('/gioi-thieu')->assertOk()
            ->assertSee('hg-trip-traveller.png')->assertSee('hg-trip-south-africa.jpg');

        $page->update(['story_image' => 'images/about/hg-trip/hero-team.jpg']);
        $this->get('/gioi-thieu')->assertOk()->assertSee('hero-team.jpg')->assertDontSee('hg-trip-south-africa.jpg');
        $migration->down();
        $this->assertSame('images/about/hg-trip/hero-team.jpg', $page->fresh()->story_image);
    }
}
