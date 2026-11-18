<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--export= : Output file path; defaults to public/sitemap.xml}';

    protected $description = 'Generate the static public sitemap from published content.';

    public function handle(SitemapService $sitemap): int
    {
        ['path' => $path, 'count' => $count] = $sitemap->generate($this->option('export') ?: null);
        $this->info("Static sitemap generated: {$path} ({$count} URLs).");

        return self::SUCCESS;
    }
}
