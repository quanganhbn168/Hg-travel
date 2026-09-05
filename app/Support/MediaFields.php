<?php

namespace App\Support;

use App\Models;

/** Media references only; admin resource capabilities remain in AdminIndexRegistry. */
final class MediaFields
{
    public const IMAGES = [
        Models\TourImage::class => ['path' => 'media_id'],
        Models\Tour::class => ['banner_image' => 'banner_media_id'],
        Models\Destination::class => ['cover_image' => 'cover_media_id'],
        Models\TourCategory::class => ['cover_image' => 'cover_media_id'],
        Models\Service::class => ['cover_image' => 'cover_media_id'],
        Models\Post::class => ['cover_image' => 'cover_media_id'],
        Models\Testimonial::class => ['avatar_path' => 'avatar_media_id'],
        Models\TravelMoment::class => ['image_url' => 'media_id'],
        Models\SliderItem::class => ['image_path' => 'media_id'],
        Models\AboutPage::class => ['hero_image' => 'hero_media_id', 'background_image' => 'background_media_id', 'story_image' => 'story_media_id'],
    ];

    public const CONTENT = [
        Models\Post::class => ['content', 'summary'],
        Models\Page::class => ['content'],
        Models\Tour::class => ['description'],
        Models\TourSection::class => ['content'],
        Models\TourItinerary::class => ['description'],
        Models\Destination::class => ['description'],
        Models\TourCategory::class => ['description'],
        Models\Service::class => ['intro', 'description'],
        Models\AboutPage::class => ['profile_content', 'letter_content', 'story_content'],
    ];

    public const SETTINGS = ['logo_url', 'image_share_url', 'page_banner_url', 'homepage_hero_url', 'about_image_url'];

    public static function models(): array
    {
        return array_unique([...array_keys(self::IMAGES), ...array_keys(self::CONTENT)]);
    }
}
