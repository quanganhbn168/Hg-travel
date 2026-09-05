<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IndexingPolicyTest extends TestCase
{
    use RefreshDatabase;

    public static function publicPages(): array
    {
        $cases = [];
        foreach (['local', 'production'] as $environment) {
            foreach (['/', '/gioi-thieu', '/tours', '/dich-vu', '/cam-nang', '/lien-he', '/dat-tour'] as $path) {
                $cases[$environment.' '.$path] = [$environment, $path];
            }
        }

        return $cases;
    }

    #[DataProvider('publicPages')]
    public function test_public_layout_and_headers_allow_indexing(string $environment, string $path): void
    {
        $this->app->instance('env', $environment);
        $response = $this->get($path.'?utm_source=seo-test')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'index, follow')
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<meta name="twitter:card"', false);

        $head = explode('</head>', $response->getContent(), 2)[0];
        $this->assertSame(1, substr_count($head, 'name="robots"'));
        $this->assertStringNotContainsString('noindex', $head);
        preg_match('/<link rel="canonical" href="([^"]+)"/', $head, $canonical);
        $this->assertArrayHasKey(1, $canonical);
        $this->assertStringNotContainsString('utm_source', $canonical[1]);
    }

    public function test_public_post_with_an_admin_prefix_is_not_blocked(): void
    {
        Post::create(['name' => 'Administrator guide', 'slug' => 'administrator-guide', 'is_active' => true]);
        $this->get('/administrator-guide')->assertOk()
            ->assertHeader('X-Robots-Tag', 'index, follow')
            ->assertSee('<meta name="robots" content="index, follow">', false);
    }

    public function test_admin_login_redirects_and_errors_remain_noindex(): void
    {
        $this->get('/admin/login')->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        foreach (['/admin', '/admin?tab=test'] as $path) {
            $this->get($path)->assertNotFound()
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }

        $this->get('/admin/dashboard')->assertRedirectToRoute('admin.login')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->getJson('/admin/media')->assertUnauthorized()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/admin/does-not-exist')->assertNotFound()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
