<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Tour;
use App\Services\BulkActionService;
use App\Services\RobotsFileService;
use App\Services\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class SeoEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private string $originalPublicPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalPublicPath = public_path();
        $this->app->usePublicPath(Storage::fake('seo_public')->path(''));
        config(['app.url' => 'https://canonical.example']);
    }

    protected function tearDown(): void
    {
        $this->app->usePublicPath($this->originalPublicPath);

        parent::tearDown();
    }

    public function test_static_robots_allows_public_pages_and_assets_but_only_blocks_admin_in_all_environments(): void
    {
        foreach (['local', 'staging', 'production'] as $environment) {
            $this->app->instance('env', $environment);
            $robots = app(RobotsFileService::class);
            $robots->save($robots->defaults(), $robots->read()['revision']);

            $content = File::get(public_path('robots.txt'));
            $this->assertSame("User-agent: *\nAllow: /\nDisallow: /admin$\nDisallow: /admin?\nDisallow: /admin/\n\nSitemap: https://canonical.example/sitemap.xml\n", $content);
            foreach (['/', '/tours', '/dat-tour', '/dat-tour?tour=test', '/css/style.css', '/js/media.js', '/media/1/image.webp', '/administrator-guide', '/admin-guide', '/trang/admin', '/sitemap.xml'] as $path) {
                $this->assertFalse($this->matchesDisallowRule($content, $path), $environment.': '.$path);
            }

            foreach (['/admin', '/admin?', '/admin?redirect=dashboard', '/admin/', '/admin/login', '/admin/media', '/admin/dashboard?tab=1'] as $path) {
                $this->assertTrue($this->matchesDisallowRule($content, $path), $environment.': '.$path);
            }
        }
    }

    public function test_robots_remains_static_until_saved_even_when_configured_domain_changes(): void
    {
        $robots = app(RobotsFileService::class);
        $robots->save($robots->defaults(), 'missing');
        config(['app.url' => 'https://changed.example/']);

        $response = $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertSame(realpath(public_path('robots.txt')), $response->baseResponse->getFile()->getRealPath());
        $this->assertStringContainsString('https://canonical.example/sitemap.xml', File::get(public_path('robots.txt')));

        $robots->save($robots->defaults(), $robots->read()['revision']);
        $this->assertStringContainsString('https://changed.example/sitemap.xml', File::get(public_path('robots.txt')));
        $this->assertStringNotContainsString('canonical.example', File::get(public_path('robots.txt')));
    }

    public function test_sitemap_stays_static_until_command_runs_after_bulk_changes(): void
    {
        $tour = Tour::create(['code' => 'BULK', 'slug' => 'bulk-tour', 'name' => 'Bulk', 'is_active' => true, 'status' => 'published']);
        $this->artisan('sitemap:generate')->assertSuccessful();
        $before = File::get(public_path('sitemap.xml'));
        $this->assertStringContainsString('bulk-tour', $before);

        app(BulkActionService::class)->execute('tour', 'deactivate', [$tour->id]);
        $response = $this->get('/sitemap.xml')->assertOk();
        $this->assertSame(realpath(public_path('sitemap.xml')), $response->baseResponse->getFile()->getRealPath());
        $this->assertSame($before, File::get(public_path('sitemap.xml')));

        $this->artisan('sitemap:generate')->assertSuccessful();
        $this->assertStringNotContainsString('bulk-tour', File::get(public_path('sitemap.xml')));
    }

    public function test_sitemap_contains_only_published_canonical_unique_urls(): void
    {
        Tour::create(['code' => 'PUB', 'slug' => 'public-tour', 'name' => 'Public', 'is_active' => true, 'status' => 'published']);
        Tour::create(['code' => 'DRAFT', 'slug' => 'draft-tour', 'name' => 'Draft', 'is_active' => true, 'status' => 'draft']);
        Tour::create(['code' => 'FUTURE', 'slug' => 'future-tour', 'name' => 'Future', 'is_active' => true, 'status' => 'published', 'published_at' => now()->addDay()]);
        Tour::create(['code' => 'INACTIVE', 'slug' => 'inactive-tour', 'name' => 'Inactive', 'is_active' => false, 'status' => 'published']);
        Post::create(['name' => 'Public post', 'slug' => 'public-post', 'is_active' => true, 'published_at' => now()->subDay()]);
        Post::create(['name' => 'Future', 'slug' => 'future-post', 'is_active' => true, 'published_at' => now()->addDay()]);
        Post::create(['name' => 'Reserved', 'slug' => 'admin', 'is_active' => true]);
        Post::create(['name' => 'Deleted', 'slug' => 'deleted-post', 'is_active' => true])->delete();
        Page::create(['name' => 'Public page', 'slug' => 'public-page', 'is_active' => true]);
        Page::create(['name' => 'Hidden', 'slug' => 'hidden-page', 'is_active' => false]);
        Page::create(['name' => 'Future page', 'slug' => 'future-page', 'is_active' => true, 'published_at' => now()->addDay()]);

        $this->artisan('sitemap:generate')->assertSuccessful();
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $content = File::get(public_path('sitemap.xml'));
        $xml = simplexml_load_string($content);
        $this->assertNotFalse($xml);
        $locations = [];
        foreach ($xml->url as $url) {
            $locations[] = (string) $url->loc;
        }

        $this->assertContains('https://canonical.example/tours/public-tour', $locations);
        $this->assertContains('https://canonical.example/public-post', $locations);
        $this->assertContains('https://canonical.example/trang/public-page', $locations);
        $this->assertSame(count($locations), count(array_unique($locations)));
        foreach ($locations as $location) {
            $this->assertSame('canonical.example', parse_url($location, PHP_URL_HOST));
            $this->assertSame('https', parse_url($location, PHP_URL_SCHEME));
            $this->assertDoesNotMatchRegularExpression('~^/admin(?:/|$)~', parse_url($location, PHP_URL_PATH) ?? '/');
        }
        foreach (['draft-tour', 'future-tour', 'inactive-tour', 'future-post', 'deleted-post', 'hidden-page', 'future-page'] as $excluded) {
            $this->assertStringNotContainsString($excluded, $content);
        }
    }

    public function test_http_never_builds_a_sitemap_and_missing_files_return_not_found(): void
    {
        $this->mock(SitemapService::class)->shouldNotReceive('build');
        foreach (['sitemap.xml', 'robots.txt'] as $file) {
            $this->get('/'.$file)->assertNotFound();
            $this->assertFileDoesNotExist(public_path($file));
        }

        File::put(public_path('sitemap.xml'), '<urlset />');
        $this->get('/sitemap.xml')->assertOk();
        $this->assertSame('<urlset />', File::get(public_path('sitemap.xml')));
    }

    public function test_failed_generation_keeps_previous_static_sitemap(): void
    {
        $this->artisan('sitemap:generate')->assertSuccessful();
        $before = File::get(public_path('sitemap.xml'));
        $this->mock(SitemapService::class)->shouldReceive('build')->once()->andThrow(new RuntimeException('Generation failed'));

        try {
            $this->artisan('sitemap:generate')->run();
            $this->fail('Generation should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Generation failed', $exception->getMessage());
            $this->assertSame($before, File::get(public_path('sitemap.xml')));
        }
    }

    public function test_optional_export_does_not_overwrite_the_public_sitemap(): void
    {
        $this->artisan('sitemap:generate')->assertSuccessful();
        $before = File::get(public_path('sitemap.xml'));
        config(['app.url' => 'https://export.example']);

        $export = public_path('export.xml');
        $this->artisan('sitemap:generate', ['--export' => $export])->assertSuccessful();
        $this->assertStringContainsString('https://export.example', File::get($export));
        $this->assertSame($before, File::get(public_path('sitemap.xml')));
    }

    private function matchesDisallowRule(string $content, string $path): bool
    {
        // These rules are all more specific than Allow: /. Check REP prefix/end boundaries.
        preg_match_all('/^Disallow: (.+)$/m', $content, $rules);
        foreach ($rules[1] as $rule) {
            $exact = str_ends_with($rule, '$');
            $prefix = $exact ? substr($rule, 0, -1) : $rule;
            if (preg_match('~^'.preg_quote($prefix, '~').($exact ? '$' : '').'~', $path)) {
                return true;
            }
        }

        return false;
    }
}
