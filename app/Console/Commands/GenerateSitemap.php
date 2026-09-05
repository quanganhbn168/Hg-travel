<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--export= : Output file path; defaults to public/sitemap.xml}';

    protected $description = 'Generate the static public sitemap from published content.';

    public function handle(SitemapService $sitemap): int
    {
        $document = $sitemap->build();
        $path = $this->option('export') ?: public_path('sitemap.xml');

        // Render first, then replace atomically so readers never receive partial XML.
        File::replace($path, $document->render(), 0644);

        $count = collect($document->getTags())->unique('url')->count();
        $this->info("Static sitemap generated: {$path} ({$count} URLs).");

        return self::SUCCESS;
    }
}
