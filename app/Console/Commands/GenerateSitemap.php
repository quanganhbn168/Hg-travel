<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tour;
use App\Models\TourCategory;
use DateTimeInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the public sitemap from published HG Trip content.';

    public function handle(): int
    {
        $sitemap = Sitemap::create();

        foreach ([
            [route('home'), Url::CHANGE_FREQUENCY_DAILY, 1.0],
            [route('about'), Url::CHANGE_FREQUENCY_MONTHLY, 0.8],
            [route('tours.index'), Url::CHANGE_FREQUENCY_DAILY, 0.9],
            [route('services.index'), Url::CHANGE_FREQUENCY_MONTHLY, 0.8],
            [route('posts.index'), Url::CHANGE_FREQUENCY_DAILY, 0.8],
            [route('contact'), Url::CHANGE_FREQUENCY_MONTHLY, 0.6],
        ] as [$url, $frequency, $priority]) {
            $sitemap->add($this->url($url, changeFrequency: $frequency, priority: $priority));
        }

        TourCategory::query()
            ->where('is_active', true)
            ->whereHas('tours', fn (Builder $query) => $this->publishedTours($query))
            ->orderBy('id')
            ->eachById(function (TourCategory $category) use ($sitemap): void {
                $sitemap->add($this->url(
                    route('tours.category', $category),
                    $category->updated_at,
                    Url::CHANGE_FREQUENCY_WEEKLY,
                    0.8,
                ));
            });

        $this->publishedTours()
            ->orderBy('id')
            ->eachById(function (Tour $tour) use ($sitemap): void {
                $sitemap->add($this->url(
                    route('tours.show', $tour),
                    $tour->updated_at,
                    Url::CHANGE_FREQUENCY_WEEKLY,
                    0.9,
                ));
            });

        ServiceCategory::query()
            ->where('is_active', true)
            ->whereHas('services', fn (Builder $query) => $query->where('is_active', true))
            ->orderBy('id')
            ->eachById(function (ServiceCategory $category) use ($sitemap): void {
                $sitemap->add($this->url(
                    route('services.category', $category->slug),
                    $category->updated_at,
                    Url::CHANGE_FREQUENCY_MONTHLY,
                    0.7,
                ));
            });

        Service::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->eachById(function (Service $service) use ($sitemap): void {
                $sitemap->add($this->url(
                    route('services.show', $service->slug),
                    $service->updated_at,
                    Url::CHANGE_FREQUENCY_MONTHLY,
                    0.7,
                ));
            });

        $this->publishedPosts()
            ->orderBy('id')
            ->eachById(function (Post $post) use ($sitemap): void {
                $sitemap->add($this->url(
                    route('posts.show', $post),
                    $post->updated_at,
                    Url::CHANGE_FREQUENCY_WEEKLY,
                    0.7,
                ));
            });

        Page::query()
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('id')
            ->eachById(function (Page $page) use ($sitemap): void {
                $sitemap->add($this->url(
                    route('pages.show', $page),
                    $page->updated_at,
                    Url::CHANGE_FREQUENCY_MONTHLY,
                    0.6,
                ));
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->components->info(sprintf('Generated public/sitemap.xml with %d URLs.', count($sitemap->getTags())));

        return self::SUCCESS;
    }

    private function publishedTours(?Builder $query = null): Builder
    {
        return ($query ?: Tour::query())
            ->where('is_active', true)
            ->where('status', 'published')
            ->where(fn (Builder $published) => $published->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    private function publishedPosts(?Builder $query = null): Builder
    {
        return ($query ?: Post::query())
            ->where('is_active', true)
            ->where(fn (Builder $published) => $published->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    private function url(
        string $url,
        ?DateTimeInterface $lastModified = null,
        ?string $changeFrequency = null,
        ?float $priority = null,
    ): Url {
        $tag = Url::create($url);

        if ($lastModified) {
            $tag->setLastModificationDate($lastModified);
        }

        if ($changeFrequency) {
            $tag->setChangeFrequency($changeFrequency);
        }

        if ($priority !== null) {
            $tag->setPriority($priority);
        }

        return $tag;
    }
}
