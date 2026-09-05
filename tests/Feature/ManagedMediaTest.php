<?php

namespace Tests\Feature;

use App\Jobs\OptimizeMedia;
use App\Models\Page;
use App\Models\Post;
use App\Models\ServiceCategory;
use App\Models\Slider;
use App\Models\Tour;
use App\Models\TravelMoment;
use App\Models\TravelMomentGroup;
use App\Models\User;
use App\Services\AboutPageService;
use App\Services\DestinationService;
use App\Services\MediaOptimizationService;
use App\Services\MediaReferenceService;
use App\Services\MediaService;
use App\Services\MediaUsageService;
use App\Services\PostService;
use App\Services\ServiceCatalogAdminService;
use App\Services\SettingService;
use App\Services\SiteSettingsService;
use App\Services\TestimonialService;
use App\Services\TourCategoryService;
use App\Services\TourService;
use App\Settings\MediaSettings;
use App\View\Components\Admin\TourMediaEditor;
use App\View\Components\ImageUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\DiskCannotBeAccessed;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManagedMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://example.test', 'settings.cache.enabled' => false]);
        Storage::fake('public_media');
        Queue::fake();
        $permissions = collect(['admin.access', 'media.view', 'media.upload', 'media.delete', 'settings.view', 'settings.update', 'posts.update', 'testimonials.update', 'travel-moments.update', 'sliders.update', 'about.update'])
            ->map(fn ($name) => Permission::findOrCreate($name, 'web'));
        $role = Role::findOrCreate('media-test', 'web');
        $role->syncPermissions($permissions);
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);
        $this->actingAs($user, 'admin');
    }

    private function image(bool $pending = true): Media
    {
        return app(MediaService::class)->upload(UploadedFile::fake()->image('test.png', 800, 600), $pending);
    }

    private function tour(string $code = 'MEDIA'): Tour
    {
        return Tour::create(['code' => $code, 'name' => $code, 'slug' => strtolower($code), 'duration_days' => 2, 'starting_price' => 1000, 'currency' => 'VND', 'status' => 'draft']);
    }

    private function updateTour(Tour $tour, array $data): void
    {
        app(TourService::class)->update($tour, $data + $tour->getAttributes());
    }

    public function test_upload_keeps_original_bytes_and_records_dimensions_and_owner(): void
    {
        $file = UploadedFile::fake()->image('holiday.png', 920, 615);
        $hash = hash_file('sha256', $file->getPathname());
        $response = $this->postJson(route('admin.media.upload.temp'), ['file' => $file])->assertOk();
        $media = Media::findOrFail($response->json('media.id'));
        $this->assertSame('media:'.$media->id, $response->json('path'));
        $this->assertSame($hash, hash_file('sha256', $media->getPath()));
        $this->assertSame(920, $media->getCustomProperty('width'));
        $this->assertSame(615, $media->getCustomProperty('height'));
        $this->assertSame('pending', $media->getCustomProperty('state'));
        $this->assertSame(auth('admin')->id(), $media->getCustomProperty('uploaded_by'));
        Queue::assertPushed(OptimizeMedia::class);
    }

    public function test_backend_uses_settings_limit_and_rejects_documents_in_image_picker(): void
    {
        $settings = app(MediaSettings::class);
        $settings->media_max_size = 1;
        $settings->save();
        $this->postJson(route('admin.media.upload.temp'), ['file' => UploadedFile::fake()->image('big.jpg')->size(2048)])->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->postJson(route('admin.media.upload.temp'), ['file' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf')])->assertUnprocessable();
        $this->postJson(route('admin.media.upload.library'), ['file' => UploadedFile::fake()->create('file.pdf', 10, 'application/pdf')])->assertOk();
        $this->getJson(route('admin.media.list'))->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_settings_can_raise_the_upload_limit_above_ten_megabytes(): void
    {
        $settings = app(MediaSettings::class);
        $settings->media_max_size = 12;
        $settings->save();
        $this->postJson(route('admin.media.upload.library'), ['file' => UploadedFile::fake()->create('large.pdf', 11 * 1024, 'application/pdf')])->assertOk();
    }

    public function test_storage_failure_returns_a_useful_json_message_and_guests_cannot_upload(): void
    {
        $this->mock(MediaService::class)->shouldReceive('upload')->once()->andThrow(DiskCannotBeAccessed::create('public_media'));
        $this->postJson(route('admin.media.upload.temp'), ['file' => UploadedFile::fake()->image('disk.png')])->assertStatus(503)->assertJsonStructure(['message']);
        auth('admin')->logout();
        $this->postJson(route('admin.media.upload.temp'), ['file' => UploadedFile::fake()->image('guest.png')])->assertUnauthorized();
    }

    public function test_cleanup_does_not_remove_other_files_in_a_shared_legacy_directory(): void
    {
        $media = $this->image();
        $disk = Storage::disk('public_media');
        $disk->move($media->getPathRelativeToRoot(), 'shared/expired.png');
        $disk->put('shared/keep.png', $disk->get('shared/expired.png'));
        // Simulate imported metadata, not a Spatie rename event (which itself moves the file).
        DB::table('media')->where('id', $media->id)->update(['file_name' => 'expired.png', 'created_at' => now()->subDays(3), 'custom_properties' => json_encode(array_merge($media->custom_properties, ['imported_path' => 'shared/expired.png']))]);
        app(MediaReferenceService::class)->forget();
        $this->artisan('media:cleanup --apply')->assertSuccessful();
        $disk->assertMissing('shared/expired.png');
        $disk->assertExists('shared/keep.png');
    }

    public function test_image_dimensions_limit_and_disallowed_extensions_are_enforced(): void
    {
        config(['media.max_pixels' => 100]);
        $this->postJson(route('admin.media.upload.temp'), ['file' => UploadedFile::fake()->image('large.png', 20, 20)])->assertUnprocessable();
        $this->postJson(route('admin.media.upload.temp'), ['file' => UploadedFile::fake()->create('script.php', 2, 'text/x-php')])->assertUnprocessable();
    }

    public function test_post_save_preserves_image_then_explicit_removal_only_detaches(): void
    {
        $media = $this->image();
        $post = app(PostService::class)->create(['name' => 'Media post', 'slug' => 'media-post', 'cover_image' => 'media:'.$media->id]);
        $path = $post->cover_image;
        app(PostService::class)->update($post, ['name' => 'Updated', 'slug' => 'media-post', 'cover_image' => null]);
        $this->assertSame($path, $post->fresh()->cover_image);
        $this->assertEquals($media->id, $post->fresh()->cover_media_id);
        $this->assertSame('ready', $media->fresh()->getCustomProperty('state'));
        app(PostService::class)->update($post, ['name' => 'Updated', 'slug' => 'media-post', 'cover_image_remove' => true]);
        $this->assertNull($post->fresh()->cover_image);
        $this->assertNull($post->fresh()->cover_media_id);
        Storage::disk('public_media')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_testimonial_preserves_and_replaces_its_avatar(): void
    {
        $first = $this->image();
        $second = $this->image();
        $service = app(TestimonialService::class);
        $data = ['customer_name' => 'QA', 'content' => 'Content', 'rating' => 5, 'avatar_path' => 'media:'.$first->id];
        $testimonial = $service->save($data);
        $service->save(['avatar_path' => null] + $data, $testimonial);
        $this->assertEquals($first->id, $testimonial->fresh()->avatar_media_id);
        $service->save(['avatar_path' => 'media:'.$second->id] + $data, $testimonial);
        $this->assertEquals($second->id, $testimonial->fresh()->avatar_media_id);
        Storage::disk('public_media')->assertExists($first->getPathRelativeToRoot());
    }

    public function test_media_settings_keep_replace_and_remove_are_persisted(): void
    {
        $media = $this->image();
        $service = app(SettingService::class);
        $service->updateMedia(['logo_url' => 'media:'.$media->id]);
        $path = app(SiteSettingsService::class)->media()->logo_url;
        $service->updateMedia(['logo_url' => null, 'media_max_size' => 8]);
        $this->assertSame($path, app(SiteSettingsService::class)->media()->logo_url);
        $this->assertEquals($media->id, app(SiteSettingsService::class)->media()->media_ids['logo_url']);
        $this->assertContains('settings.media.logo_url', app(MediaUsageService::class)->usages($media));
        $service->updateMedia(['logo_url_remove' => true]);
        $this->assertNull(app(SiteSettingsService::class)->media()->logo_url);
        Storage::disk('public_media')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_shared_media_cannot_be_archived_and_soft_deleted_content_is_protected(): void
    {
        $media = $this->image(false);
        $first = Post::create(['name' => 'First', 'slug' => 'first', 'cover_image' => 'media:'.$media->id]);
        $second = Post::create(['name' => 'Second', 'slug' => 'second', 'cover_image' => 'media:'.$media->id]);
        $first->update(['cover_image' => null]);
        $second->delete();
        $this->deleteJson(route('admin.media.destroy', $media))->assertUnprocessable();
        Storage::disk('public_media')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_editor_references_are_portable_and_protect_pending_upload_from_cleanup(): void
    {
        $media = $this->image();
        $path = app(MediaReferenceService::class)->originalPath($media);
        $page = Page::create(['name' => 'Editor', 'slug' => 'editor', 'content' => '<p><img src="http://example.test/'.$path.'"></p>']);
        $this->assertStringContainsString('src="/'.$path.'"', $page->content);
        $this->assertContains('pages#'.$page->id.'.content', app(MediaUsageService::class)->usages($media));
        $this->assertSame('ready', $media->fresh()->getCustomProperty('state'));
        $this->deleteJson(route('admin.media.destroy', $media))->assertUnprocessable();
    }

    public function test_cannot_claim_another_users_pending_upload_or_an_invalid_id(): void
    {
        $media = $this->image();
        $media->setCustomProperty('uploaded_by', auth('admin')->id() + 1)->save();
        $this->getJson(route('admin.media.list'))->assertJsonCount(0, 'data');
        foreach (['media:'.$media->id, 'media:99999'] as $reference) {
            try {
                app(MediaReferenceService::class)->resolve($reference);
                $this->fail('Reference must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    public function test_cleanup_only_deletes_expired_unreferenced_pending_files(): void
    {
        $expired = $this->image();
        $recent = $this->image();
        $library = $this->image(false);
        $used = $this->image();
        foreach ([$expired, $library, $used] as $media) {
            $media->created_at = now()->subDays(3);
            $media->save();
        }
        DB::table('pages')->insert(['name' => 'Legacy', 'slug' => 'legacy', 'content' => '<img src="/'.app(MediaReferenceService::class)->originalPath($used).'">']);
        $this->artisan('media:cleanup')->assertSuccessful();
        $this->assertNotNull($expired->fresh());
        $this->artisan('media:cleanup --apply')->assertSuccessful();
        $this->assertNull($expired->fresh());
        $this->assertNotNull($recent->fresh());
        $this->assertNotNull($library->fresh());
        $this->assertNotNull($used->fresh());
        Storage::disk('public_media')->assertMissing($expired->getPathRelativeToRoot());
    }

    public function test_cover_replace_is_idempotent_and_does_not_add_old_cover_to_gallery(): void
    {
        $tour = $this->tour();
        $first = $this->image(false);
        $second = $this->image(false);
        $this->updateTour($tour, ['cover_image' => 'media:'.$first->id]);
        $this->updateTour($tour, ['cover_image' => 'media:'.$first->id]);
        $this->assertSame(1, $tour->images()->count());
        $this->updateTour($tour, ['cover_image' => 'media:'.$second->id]);
        $this->assertSame(1, $tour->images()->count());
        $this->assertEquals($second->id, $tour->images()->first()->media_id);
        Storage::disk('public_media')->assertExists($first->getPathRelativeToRoot());
    }

    public function test_gallery_deduplicates_enforces_batch_limit_and_scopes_ids(): void
    {
        $tour = $this->tour();
        $other = $this->tour('OTHER');
        $media = $this->image(false);
        $this->updateTour($tour, ['gallery_images' => 'media:'.$media->id]);
        $this->updateTour($tour, ['gallery_images' => 'media:'.$media->id]);
        $this->assertSame(1, $tour->images()->count());
        $foreign = $other->images()->create(['path' => 'media:'.$media->id]);
        foreach ([['cover_image_id' => $foreign->id], ['remove_image_ids' => [$foreign->id]], ['gallery_images' => implode('|', array_map(fn ($id) => 'media:'.$id, range(1, 13)))]] as $invalid) {
            try {
                $this->updateTour($tour, $invalid);
                $this->fail('Invalid gallery must fail.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
        $this->assertSame(1, $tour->images()->count());
    }

    public function test_gallery_order_can_be_saved_while_removing_an_image(): void
    {
        $tour = $this->tour();
        $one = $this->image(false);
        $two = $this->image(false);
        $this->updateTour($tour, ['gallery_images' => 'media:'.$one->id.'|media:'.$two->id]);
        $ids = $tour->images()->pluck('id')->all();
        $this->updateTour($tour, ['image_order' => array_reverse($ids), 'remove_image_ids' => [$ids[0]]]);
        $this->assertSame(1, $tour->images()->count());
        $this->assertSame(1, $tour->images()->first()->sort_order);
    }

    public function test_gallery_component_prepares_previews_and_keeps_submitted_order(): void
    {
        $this->app['request']->setLaravelSession($this->app['session.store']);
        $tour = $this->tour();
        $one = $this->image(false);
        $two = $this->image(false);
        $this->updateTour($tour, ['gallery_images' => 'media:'.$one->id.'|media:'.$two->id]);
        $ids = $tour->images()->pluck('id')->all();
        session()->flash('_old_input', ['image_order' => array_reverse($ids)]);
        $component = new TourMediaEditor($tour->load('images'));
        $this->assertSame(array_reverse($ids), $component->gallery->pluck('id')->all());
        foreach ($component->gallery as $image) {
            $this->assertStringStartsWith('http://example.test/media/', $image->preview_url);
        }
        View::share('errors', new ViewErrorBag);
        $this->blade('<x-admin.tour-media-editor :tour="$tour" />', ['tour' => $tour])->assertSee('data-tour-gallery-sort', false);
    }

    public function test_conversion_keeps_original_pixels_and_generates_separate_thumbnail(): void
    {
        $media = $this->image(false);
        $hash = hash_file('sha256', $media->getPath());
        app(MediaOptimizationService::class)->optimize($media);
        $media->refresh();
        $this->assertSame($hash, hash_file('sha256', $media->getPath()));
        $this->assertSame([800, 600], array_slice(getimagesize($media->getPath()), 0, 2));
        $thumb = Storage::disk('public_media')->path($media->getCustomProperty('thumbnail_path'));
        $this->assertSame([320, 240], array_slice(getimagesize($thumb), 0, 2));
        if ($path = $media->getCustomProperty('webp_path')) {
            $this->assertSame([800, 600], array_slice(getimagesize(Storage::disk('public_media')->path($path)), 0, 2));
            $this->assertLessThan($media->size, Storage::disk('public_media')->size($path));
        }
    }

    public function test_urls_use_current_domain_and_missing_files_return_fallback(): void
    {
        $media = $this->image(false);
        $path = app(MediaReferenceService::class)->originalPath($media);
        config(['app.url' => 'https://new.example']);
        $this->assertSame('https://new.example/'.$path, app(MediaReferenceService::class)->url('https://dulich1.test/'.$path));
        $this->assertNull(app(MediaReferenceService::class)->url('https://dulich1.test/media/999/missing.jpg'));
        $this->assertStringEndsWith('/images/logo-hgtrip.png', app(MediaReferenceService::class)->url('media/999/missing.jpg', 'images/logo-hgtrip.png'));
    }

    public function test_media_index_renders_real_paginator_and_picker_search_is_paginated(): void
    {
        $this->image(false);
        $this->get(route('admin.media.index'))->assertOk()->assertSee('data-media-usage=', false)->assertSee('placeholder="Tìm tên tệp"', false);
        $this->getJson(route('admin.media.list', ['q' => 'test']))->assertOk()->assertJsonCount(1, 'data')->assertJsonStructure(['data' => [['id', 'reference', 'url', 'thumbnail_url']], 'next_page_url', 'total']);
    }

    public function test_public_about_and_post_views_render_prepared_media_data(): void
    {
        $this->get(route('about'))->assertOk()->assertSee('about-client-logos', false);
        $media = $this->image(false);
        $post = Post::create(['name' => 'Visible post', 'slug' => 'visible-post', 'is_active' => true, 'published_at' => now()->subDay(), 'cover_image' => 'media:'.$media->id]);
        $this->get(route('posts.index'))->assertOk()->assertSee($media->file_name);
        $this->get(route('posts.show', $post))->assertOk()->assertSee($media->file_name);
    }

    public function test_component_retains_existing_and_old_input_after_validation_error(): void
    {
        $this->app['request']->setLaravelSession($this->app['session.store']);
        $media = $this->image(false);
        $path = app(MediaReferenceService::class)->originalPath($media);
        $component = new ImageUpload('cover_image', value: $path);
        $this->assertSame($path, $component->inputValue);
        session()->flash('_old_input', ['cover_image' => 'media:'.$media->id, 'cover_image_remove' => '0']);
        $component = new ImageUpload('cover_image', value: 'old.png');
        $this->assertSame('media:'.$media->id, $component->inputValue);
        session()->flash('_old_input', ['cover_image' => '', 'cover_image_remove' => '1']);
        $component = new ImageUpload('cover_image', value: $path);
        $this->assertSame('', $component->inputValue);
        $this->assertTrue($component->removed);
    }

    public function test_archive_is_reversible_and_cannot_promote_pending_or_missing_files(): void
    {
        $media = $this->image(false);
        $this->deleteJson(route('admin.media.destroy', $media))->assertOk();
        $this->getJson(route('admin.media.list'))->assertJsonCount(0, 'data');
        $this->get(route('admin.media.index', ['archived' => 1]))->assertOk()->assertSee('Khôi phục');
        Storage::disk('public_media')->assertExists($media->getPathRelativeToRoot());
        $this->putJson(route('admin.media.restore', $media))->assertOk();
        $this->getJson(route('admin.media.list'))->assertJsonCount(1, 'data');
        $pending = $this->image();
        $this->putJson(route('admin.media.restore', $pending))->assertUnprocessable();
        $this->deleteJson(route('admin.media.destroy', $media))->assertOk();
        Storage::disk('public_media')->delete($media->getPathRelativeToRoot());
        $this->putJson(route('admin.media.restore', $media))->assertUnprocessable();
    }

    public function test_media_ids_are_authoritative_for_models_and_settings(): void
    {
        $media = $this->image(false);
        $post = Post::create(['name' => 'ID source', 'slug' => 'id-source', 'cover_image' => 'media:'.$media->id]);
        DB::table('posts')->where('id', $post->id)->update(['cover_image' => 'media/stale/path.jpg']);
        $this->assertSame(app(MediaReferenceService::class)->originalPath($media), $post->fresh()->cover_image);
        $settings = app(MediaSettings::class);
        $settings->logo_url = 'media/stale/logo.png';
        $settings->media_ids = ['logo_url' => $media->id];
        $settings->save();
        $this->assertSame(app(MediaReferenceService::class)->originalPath($media), app(SiteSettingsService::class)->media()->logo_url);
    }

    public function test_category_service_and_destination_keep_replace_and_explicitly_remove_covers(): void
    {
        $media = $this->image(false);
        $replacement = $this->image(false);
        $category = ServiceCategory::create(['name' => 'Category', 'slug' => 'category']);
        $cases = [
            [app(TourCategoryService::class), 'create', 'update', ['name' => 'Tour kind', 'slug' => 'tour-kind']],
            [app(ServiceCatalogAdminService::class), 'createService', 'updateService', ['name' => 'Service', 'slug' => 'service', 'service_category_id' => $category->id]],
            [app(DestinationService::class), 'create', 'update', ['name' => 'Place', 'slug' => 'place']],
        ];
        foreach ($cases as [$service, $create, $update, $data]) {
            $model = $service->$create($data + ['cover_image' => 'media:'.$media->id]);
            $service->$update($model, $data + ['cover_image' => null]);
            $this->assertEquals($media->id, $model->fresh()->cover_media_id);
            $service->$update($model, $data + ['cover_image' => 'media:'.$replacement->id]);
            $this->assertEquals($replacement->id, $model->fresh()->cover_media_id);
            $service->$update($model, $data + ['cover_image' => 'media:'.$replacement->id, 'cover_image_remove' => true]);
            $this->assertNull($model->fresh()->cover_media_id);
            $this->assertNull($model->fresh()->cover_image);
        }
        Storage::disk('public_media')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_required_moment_and_slider_images_cannot_be_removed_without_replacement(): void
    {
        $media = $this->image(false);
        $group = TravelMomentGroup::create(['name' => 'QA', 'slug' => 'qa']);
        $moment = TravelMoment::create(['group_id' => $group->id, 'title' => 'Moment', 'slug' => 'moment', 'image_url' => 'media:'.$media->id]);
        $data = ['group_id' => $group->id, 'title' => 'Moment'];
        $this->putJson(route('admin.travel-moments.update', $moment), $data)->assertRedirect();
        $this->assertEquals($media->id, $moment->fresh()->media_id);
        $this->putJson(route('admin.travel-moments.update', $moment), $data + ['image_url_remove' => true])->assertUnprocessable()->assertJsonValidationErrors('image_url');
        $slider = Slider::create(['name' => 'QA', 'key' => 'qa']);
        $item = $slider->items()->create(['image_path' => 'media:'.$media->id]);
        $this->putJson(route('admin.sliders.items.update', [$slider, $item]), ['title' => 'Changed'])->assertRedirect();
        $this->assertEquals($media->id, $item->fresh()->media_id);
        $this->putJson(route('admin.sliders.items.update', [$slider, $item]), ['image_path_remove' => true])->assertUnprocessable();
        Storage::disk('public_media')->assertExists($media->getPathRelativeToRoot());
    }

    public function test_about_images_preserve_on_text_save_and_remove_explicitly(): void
    {
        $media = $this->image(false);
        $replacement = $this->image(false);
        $about = app(AboutPageService::class)->current();
        $fields = ['hero_image', 'background_image', 'story_image'];
        $about->update(array_fill_keys($fields, 'media:'.$media->id));
        $this->putJson(route('admin.about.update'), ['hero_title' => 'QA'])->assertRedirect();
        foreach ($fields as $field) {
            $this->assertSame(app(MediaReferenceService::class)->originalPath($media), $about->fresh()->$field);
        }
        $this->putJson(route('admin.about.update'), ['hero_title' => 'QA', 'hero_image' => 'media:'.$replacement->id, 'story_image_remove' => true])->assertRedirect();
        $this->assertEquals($replacement->id, $about->fresh()->hero_media_id);
        $this->assertNull($about->fresh()->story_media_id);
        $this->assertEquals($media->id, $about->fresh()->background_media_id);
    }

    public function test_editor_upload_contract_and_unsafe_paths_are_rejected(): void
    {
        $this->postJson(route('admin.media.upload.editor'), ['file' => UploadedFile::fake()->image('editor.png', 512, 512)])
            ->assertOk()->assertJsonStructure(['location', 'media' => ['id']]);
        foreach (['javascript:alert(1)', '../.env', 'media/%2e%2e/.env', 'media:999999'] as $value) {
            try {
                app(MediaReferenceService::class)->resolve($value);
                $this->fail('Unsafe reference accepted');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    public function test_favicons_use_fixed_filenames_and_leave_uploaded_original_unchanged(): void
    {
        Storage::fake('favicon_test');
        $publicPath = public_path();
        $this->app->usePublicPath(Storage::disk('favicon_test')->path(''));
        try {
            $media = $this->image(false);
            $hash = hash_file('sha256', $media->getPath());
            app(SettingService::class)->updateMedia(['favicon_master' => 'media:'.$media->id]);
            foreach (['favicon-master.png' => 512, 'favicon-16x16.png' => 16, 'favicon-32x32.png' => 32, 'apple-touch-icon.png' => 180, 'android-chrome-192x192.png' => 192, 'android-chrome-512x512.png' => 512] as $filename => $size) {
                $this->assertSame([$size, $size], array_slice(getimagesize(public_path($filename)), 0, 2));
            }
            $this->assertSame($hash, hash_file('sha256', $media->getPath()));
            $this->assertEquals($media->id, app(MediaSettings::class)->media_ids['favicon_master']);
            $this->assertFileExists(public_path('favicon.ico'));
            $this->assertFileExists(public_path('site.webmanifest'));
        } finally {
            $this->app->usePublicPath($publicPath);
        }
    }

    public function test_transparency_is_preserved_and_gif_is_not_flattened(): void
    {
        $source = imagecreatetruecolor(40, 30);
        imagealphablending($source, false);
        imagesavealpha($source, true);
        imagefill($source, 0, 0, imagecolorallocatealpha($source, 0, 0, 0, 127));
        ob_start();
        imagepng($source);
        $png = ob_get_clean();
        imagedestroy($source);
        $media = app(MediaService::class)->upload(UploadedFile::fake()->createWithContent('alpha.png', $png), false);
        app(MediaOptimizationService::class)->optimize($media);
        $media->refresh();
        $thumb = imagecreatefromwebp(Storage::disk('public_media')->path($media->getCustomProperty('thumbnail_path')));
        $this->assertSame(127, imagecolorsforindex($thumb, imagecolorat($thumb, 0, 0))['alpha']);
        $this->assertSame(40, imagesx($thumb));
        imagedestroy($thumb);
        $gif = app(MediaService::class)->upload(UploadedFile::fake()->image('animation.gif', 20, 20), false);
        $hash = hash_file('sha256', $gif->getPath());
        app(MediaOptimizationService::class)->optimize($gif);
        $this->assertSame($hash, hash_file('sha256', $gif->getPath()));
        $this->assertNull($gif->fresh()->getCustomProperty('webp_path'));
    }
}
