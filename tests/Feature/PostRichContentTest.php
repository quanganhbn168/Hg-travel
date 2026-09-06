<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Services\MediaService;
use App\Services\MediaUsageService;
use App\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PostRichContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://example.test', 'settings.cache.enabled' => false]);
        Storage::fake('public_media');
        Queue::fake();
        $role = Role::findOrCreate('post-editor-test', 'web');
        $role->syncPermissions(collect(['admin.access', 'posts.create', 'posts.update'])
            ->map(fn ($name) => Permission::findOrCreate($name, 'web')));
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);
        $this->actingAs($user, 'admin');
    }

    public function test_article_can_be_created_edited_and_rendered_with_formatting_and_managed_images(): void
    {
        $this->get(route('admin.posts.create'))->assertOk()
            ->assertSee('id="post_content"', false)->assertSee('tinymce.min.js');

        $media = app(MediaService::class)->upload(UploadedFile::fake()->image('article.png', 800, 600));
        $imageUrl = app(MediaService::class)->item($media)['original_url'];
        $content = '<h2>Hành trình mùa thu</h2><p style="text-align: center;"><strong>Đi cùng HG Trip</strong></p>'
            .'<ul><li>Mang hộ chiếu</li></ul><figure class="image"><img src="'.$imageUrl.'" alt="Hành trình" />'
            .'<figcaption>Ảnh hành trình</figcaption></figure><table><tbody><tr><td>Lịch trình</td></tr></tbody></table>';
        $this->post(route('admin.posts.store'), ['name' => 'Bài viết mới', 'content' => $content, 'is_active' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $post = Post::firstOrFail();
        $this->assertContains('posts#'.$post->id.'.content', app(MediaUsageService::class)->usages($media));
        $this->get(route('admin.posts.edit', $post))->assertOk()
            ->assertSee('tinymce.min.js')->assertSee('<h2>Hành trình mùa thu</h2>');
        $this->get(route('posts.show', $post))->assertOk()
            ->assertSee('<h2>Hành trình mùa thu</h2>', false)
            ->assertSee('<strong>Đi cùng HG Trip</strong>', false)
            ->assertSee('text-align:center;', false)
            ->assertSee('<li>Mang hộ chiếu</li>', false)
            ->assertSee('<figcaption>Ảnh hành trình</figcaption>', false)
            ->assertSee('<td>Lịch trình</td>', false)->assertSee($media->file_name);

        $this->put(route('admin.posts.update', $post), ['name' => $post->name, 'slug' => $post->slug,
            'content' => '<p>Nội dung <em>đã sửa</em></p>', 'is_active' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->get(route('posts.show', $post))->assertOk()
            ->assertSee('<p>Nội dung <em>đã sửa</em></p>', false)->assertDontSee('Hành trình mùa thu');
    }

    public function test_legacy_plain_text_keeps_line_breaks_when_opened_saved_and_published(): void
    {
        $post = Post::create(['name' => 'Bài cũ', 'slug' => 'bai-cu', 'content' => "Dòng một & hai\nDòng ba\n\nDòng cuối", 'is_active' => true]);
        $html = app(PostService::class)->formContext($post)['contentHtml'];
        $this->assertStringContainsString("Dòng một &amp; hai<br />\nDòng ba<br />\n<br />\nDòng cuối", $html);
        $this->get(route('posts.show', $post))->assertOk()->assertSee($html, false);
        $this->put(route('admin.posts.update', $post), ['name' => $post->name, 'slug' => $post->slug, 'content' => $html, 'is_active' => 1])
            ->assertSessionHasNoErrors();
        $this->assertSame($html, $post->fresh()->content);
    }

    public function test_unsafe_markup_is_removed_on_save_and_when_reading_existing_articles(): void
    {
        $unsafe = '<p style="position:fixed;color:#ff0000" onclick="alert(1)">Nội dung tốt</p>'
            .'<script>alert(1)</script><a href="javascript:alert(1)">Liên kết</a>'
            .'<img src="/images/example.jpg" onerror="alert(1)" alt="Ảnh" />'
            .'<iframe src="https://evil.example/embed"></iframe>';
        $post = Post::create(['name' => 'Bài nhập cũ', 'slug' => 'bai-nhap-cu', 'content' => $unsafe, 'is_active' => true]);
        $this->get(route('posts.show', $post))->assertOk()->assertSee('Nội dung tốt')
            ->assertDontSee('alert(1)', false)->assertDontSee('evil.example', false)->assertDontSee('position:fixed', false);
        $this->put(route('admin.posts.update', $post), ['name' => $post->name, 'slug' => $post->slug, 'content' => $unsafe, 'is_active' => 1])
            ->assertSessionHasNoErrors();
        $saved = $post->fresh()->content;
        $this->assertStringNotContainsString('alert(1)', $saved);
        $this->assertStringNotContainsString('evil.example', $saved);
        $this->assertStringContainsString('Nội dung tốt', $saved);
    }

    public function test_summary_fallback_remains_plain_text(): void
    {
        $post = Post::create(['name' => 'Chỉ có mô tả', 'slug' => 'chi-co-mo-ta', 'summary' => "<b>Văn bản</b>\nDòng hai", 'is_active' => true]);
        $this->get(route('posts.show', $post))->assertOk()
            ->assertSee('&lt;b&gt;Văn bản&lt;/b&gt;<br />', false);
    }
}
