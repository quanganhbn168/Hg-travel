<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RobotsFileService;
use App\Settings\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRobotsFileTest extends TestCase
{
    use RefreshDatabase;

    private string $originalPublicPath;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://canonical.example', 'settings.cache.enabled' => false]);
        $this->originalPublicPath = public_path();
        $disk = Storage::fake('robots_editor_public');
        $this->app->usePublicPath($disk->path(''));
        $disk->put('js/admin.js', '// test fixture');
        $disk->put('js/media.js', '// test fixture');
        $permissions = collect(['admin.access', 'settings.view', 'settings.update'])
            ->map(fn ($name) => Permission::findOrCreate($name, 'web'));
        $role = Role::findOrCreate('robots-editor', 'web');
        $role->syncPermissions($permissions);
        $this->editor = User::factory()->create(['is_active' => true]);
        $this->editor->assignRole($role);
    }

    protected function tearDown(): void
    {
        $this->app->usePublicPath($this->originalPublicPath);
        parent::tearDown();
    }

    public function test_seo_screen_reads_current_file_and_has_a_separate_robots_form(): void
    {
        $content = "# Existing content\nUser-agent: *\nAllow: /\n";
        File::put(public_path('robots.txt'), $content);

        $this->actingAs($this->editor, 'admin')->get(route('admin.settings.seo'))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('name="robots_content"', false)
            ->assertSee('name="robots_revision"', false)
            ->assertSee(hash('sha256', $content))
            ->assertSee('# Existing content')
            ->assertSee('form="admin-settings-robots-form"', false)
            ->assertSee(route('admin.settings.robots.update'), false);
        $this->assertSame($content, File::get(public_path('robots.txt')));
    }

    public function test_missing_file_displays_defaults_without_writing_on_get(): void
    {
        $this->actingAs($this->editor, 'admin')->get(route('admin.settings.seo'))->assertOk()
            ->assertSee('Chưa có file robots.txt')
            ->assertSee('Sitemap: https://canonical.example/sitemap.xml');
        $this->assertFileDoesNotExist(public_path('robots.txt'));
    }

    public function test_authorized_save_creates_static_file_normalizes_lines_and_preserves_seo_settings(): void
    {
        $before = app(SeoSettings::class)->toArray();
        $content = "# CMS robots\r\n".str_replace("\n", "\r\n", app(RobotsFileService::class)->defaults());
        $this->actingAs($this->editor, 'admin')->put(route('admin.settings.robots.update'), [
            'robots_content' => $content,
            'robots_revision' => 'missing',
            'path' => '../unexpected.txt',
            'seo_title' => 'Must not change metadata',
        ])->assertRedirect(route('admin.settings.seo').'#robots-settings')->assertSessionHasNoErrors()->assertSessionHas('success');

        $expected = str_replace("\r\n", "\n", $content);
        $this->assertSame($expected, File::get(public_path('robots.txt')));
        $this->assertSame($before, app(SeoSettings::class)->toArray());
        $this->get(route('admin.settings.seo'))->assertOk()->assertSee('# CMS robots');
        $response = $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertSame(realpath(public_path('robots.txt')), $response->baseResponse->getFile()->getRealPath());
        $this->assertArrayNotHasKey('robots:generate', Artisan::all());
    }

    public function test_seo_metadata_save_does_not_overwrite_robots_file(): void
    {
        $content = app(RobotsFileService::class)->defaults();
        File::put(public_path('robots.txt'), $content);
        $this->actingAs($this->editor, 'admin')->put(route('admin.settings.seo.update'), [
            'seo_title' => 'Updated metadata',
            'robots_content' => 'Must not overwrite robots',
        ])->assertSessionHasNoErrors();
        $this->assertSame($content, File::get(public_path('robots.txt')));
        $this->assertSame('Updated metadata', app(SeoSettings::class)->seo_title);
    }

    public function test_guest_and_read_only_admin_cannot_write(): void
    {
        $data = ['robots_content' => app(RobotsFileService::class)->defaults(), 'robots_revision' => 'missing'];
        $this->putJson(route('admin.settings.robots.update'), $data)->assertUnauthorized();

        $role = Role::findOrCreate('robots-reader', 'web');
        $role->syncPermissions(['admin.access', 'settings.view']);
        $reader = User::factory()->create(['is_active' => true]);
        $reader->assignRole($role);
        $this->actingAs($reader, 'admin')->get(route('admin.settings.seo'))->assertOk()
            ->assertDontSee('form="admin-settings-robots-form"', false);
        $this->putJson(route('admin.settings.robots.update'), $data)->assertForbidden();
        $this->assertFileDoesNotExist(public_path('robots.txt'));
    }

    public static function invalidContents(): array
    {
        return [
            'empty' => [''],
            'html' => ['<html>not robots</html>'],
            'php' => ['<?php echo "bad";'],
            'unsupported directive' => ["User-agent: *\nNoindex: /"],
            'missing user agent' => ['Allow: /'],
            'relative sitemap' => ["User-agent: *\nSitemap: /sitemap.xml"],
            'invalid path' => ["User-agent: *\nDisallow: admin"],
            'control character' => ["User-agent: *\nDisallow: /\x01"],
            'too large' => ["User-agent: *\n#".str_repeat('x', 50001)],
        ];
    }

    #[DataProvider('invalidContents')]
    public function test_invalid_content_keeps_file_and_returns_input_errors(string $content): void
    {
        $before = app(RobotsFileService::class)->defaults();
        File::put(public_path('robots.txt'), $before);
        $this->actingAs($this->editor, 'admin')->from(route('admin.settings.seo'))
            ->put(route('admin.settings.robots.update'), [
                'robots_content' => $content,
                'robots_revision' => hash('sha256', $before),
            ])->assertRedirect(route('admin.settings.seo'))->assertSessionHasErrors('robots_content');
        $this->assertSame($before, File::get(public_path('robots.txt')));
    }

    public function test_stale_form_cannot_overwrite_a_newer_file(): void
    {
        $before = app(RobotsFileService::class)->defaults();
        $newer = "# Updated by another editor\n".$before;
        File::put(public_path('robots.txt'), $newer);
        $this->actingAs($this->editor, 'admin')->from(route('admin.settings.seo'))
            ->put(route('admin.settings.robots.update'), [
                'robots_content' => $before,
                'robots_revision' => hash('sha256', $before),
            ])->assertSessionHasErrors('robots_content');
        $this->assertSame($newer, File::get(public_path('robots.txt')));
        $this->assertSame(trim($before), session()->getOldInput('robots_content'));
    }

    public function test_file_write_failure_does_not_report_success_or_modify_previous_content(): void
    {
        $before = app(RobotsFileService::class)->defaults();
        File::put(public_path('robots.txt'), $before);
        File::partialMock()->shouldReceive('replace')->once()->andThrow(new RuntimeException('Permission denied'));

        $this->actingAs($this->editor, 'admin')->from(route('admin.settings.seo'))
            ->put(route('admin.settings.robots.update'), [
                'robots_content' => "# Updated\n".$before,
                'robots_revision' => hash('sha256', $before),
            ])->assertSessionHasErrors('robots_content')->assertSessionMissing('success');
        $this->assertSame($before, File::get(public_path('robots.txt')));
    }
}
