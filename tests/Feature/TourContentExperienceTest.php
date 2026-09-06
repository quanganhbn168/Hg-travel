<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\User;
use App\Services\TourService;
use App\Support\TourContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TourContentExperienceTest extends TestCase
{
    use RefreshDatabase;

    private function tour(): Tour
    {
        return Tour::create(['code' => 'UX-TEST', 'name' => 'Hành trình thử', 'slug' => 'hanh-trinh-thu',
            'duration_days' => 2, 'duration_nights' => 1, 'starting_price' => 1000000, 'currency' => 'VND',
            'status' => 'published', 'is_active' => true, 'booking_open' => true,
            'description' => "Giới thiệu giữ nguyên\nDòng thứ hai"]);
    }

    private function editor(): void
    {
        $role = Role::findOrCreate('tour-editor-test', 'web');
        $role->syncPermissions(collect(['admin.access', 'tours.create', 'tours.update'])
            ->map(fn ($name) => Permission::findOrCreate($name, 'web')));
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);
        $this->actingAs($user, 'admin');
    }

    public function test_public_content_is_grouped_without_rewriting_stored_content(): void
    {
        $tour = $this->tour();
        $tour->sections()->createMany([
            ['type' => 'visa', 'title' => 'Hồ sơ của tour', 'content' => '<p>Visa nguyên bản</p>', 'sort_order' => 1],
            ['type' => 'highlights', 'title' => 'Điểm nhấn của tour', 'content' => "Ý đầu\nÝ sau", 'sort_order' => 2],
            ['type' => 'custom_legacy', 'title' => 'Thông tin cũ', 'content' => 'Giữ mục tùy chỉnh', 'sort_order' => 3],
        ]);
        $tour->itineraries()->create(['day_number' => 1, 'title' => 'Ngày trải nghiệm', 'description' => '<p><strong>Nội dung đã nhập</strong></p>', 'meals' => 'Sáng | Trưa', 'accommodation' => "20:00 nhận phòng | Khách sạn giữ nguyên\nLưu ý thêm"]);
        $tour->inclusions()->createMany([['type' => 'excluded', 'content' => 'Chi phí cá nhân'], ['type' => 'included', 'content' => 'Vé tham quan']]);
        $response = $this->get(route('tours.show', $tour))->assertOk();
        $response->assertSeeInOrder(['id="tong-quan"', 'id="diem-noi-bat"', 'id="lich-trinh"', 'id="dich-vu"'], false)
            ->assertSee('tour-policy-item', false)->assertSee('Giá tour không bao gồm')->assertSee('Giữ mục tùy chỉnh')
            ->assertSee('<strong>Nội dung đã nhập</strong>', false)->assertSee("Ý đầu<br />\nÝ sau", false)
            ->assertDontSee('20:00 nhận phòng | Khách sạn giữ nguyên');
        $this->assertSame(['20:00 nhận phòng', 'Khách sạn giữ nguyên', 'Lưu ý thêm'], $response->viewData('tour')['itineraries'][0]['accommodation_lines']);
        $this->assertSame("20:00 nhận phòng | Khách sạn giữ nguyên\nLưu ý thêm", $tour->itineraries()->first()->accommodation);
        $this->assertSame("Ý đầu\nÝ sau", $tour->sections()->where('type', 'highlights')->first()->content);
    }

    public function test_editor_accepts_reordered_days_and_preserves_long_existing_text_and_sections(): void
    {
        $this->editor();
        $tour = $this->tour();
        $long = trim(str_repeat('Nội dung lưu trú đã nhập. ', 20));
        $section = $tour->sections()->create(['type' => 'custom_legacy', 'title' => 'Nội dung cũ', 'content' => '<p>Giữ nguyên nội dung</p>']);
        $payload = $tour->only(['code', 'name', 'slug', 'description', 'duration_days', 'duration_nights', 'starting_price', 'currency', 'status', 'is_active', 'booking_open']);
        $payload['itineraries'] = [
            'itineraries_new_2' => ['day_number' => 1, 'title' => 'Ngày chuyển lên', 'description' => '<p>Mô tả giữ nguyên</p>', 'meals' => $long, 'accommodation' => $long],
            7 => ['day_number' => 2, 'title' => 'Ngày chuyển xuống', 'description' => "Dòng một\nDòng hai"],
        ];
        $payload['sections'] = [9 => $section->only(['id', 'type', 'title', 'content']), 'details_new_2' => ['type' => 'notes', 'title' => 'Mục mới', 'content' => '<ul><li>Ghi chú mới</li></ul>']];
        $this->put(route('admin.tours.update', $tour), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(['Ngày chuyển lên', 'Ngày chuyển xuống'], $tour->itineraries()->pluck('title')->all());
        $this->assertSame($long, $tour->itineraries()->first()->accommodation);
        $this->assertSame('<p>Giữ nguyên nội dung</p>', $section->fresh()->content);
        $this->assertSame('custom_legacy', $section->fresh()->type);
        $this->get(route('admin.tours.edit', $tour))->assertOk()->assertSee('Nhóm đã lưu: custom_legacy')->assertSee($long)
            ->assertSee('data-tour-form', false)->assertSee('admin-tour.js');
    }

    public function test_failed_save_keeps_sparse_indices_content_and_error_navigation(): void
    {
        $this->editor();
        $tour = $this->tour();
        $input = ['sections' => [7 => ['type' => 'highlights', 'title' => 'Chưa lưu điểm nổi bật', 'content' => '<p>Bản nhập</p>'], 'details_new_4' => ['type' => 'visa', 'title' => 'Chưa lưu visa', 'content' => 'Giữ lại']],
            'itineraries' => [12 => ['day_number' => 1, 'title' => '', 'description' => 'Lịch trình chưa lưu', 'accommodation' => 'Lưu trú chưa lưu']]];
        $payload = $tour->only(['code', 'name', 'slug', 'description', 'duration_days', 'duration_nights', 'starting_price', 'currency', 'status', 'is_active', 'booking_open']) + $input;
        $this->from(route('admin.tours.edit', $tour))->put(route('admin.tours.update', $tour), $payload)
            ->assertSessionHasErrors('itineraries.12.title');
        $this->withViewErrors(['itineraries.12.title' => 'Vui lòng nhập tiêu đề ngày.'])
            ->view('admin.tours.edit', app(TourService::class)->formContext($tour))
            ->assertSee('data-error-field="itineraries.12.title"', false)->assertSee('Chưa lưu điểm nổi bật')->assertSee('Chưa lưu visa')
            ->assertSee('name="sections[7][content]"', false)->assertSee('name="sections[details_new_4][content]"', false)
            ->assertSee('Lịch trình chưa lưu')->assertSee('Lưu trú chưa lưu');
        $this->assertSame(0, $tour->sections()->count());
    }

    public function test_empty_tour_has_no_required_placeholder_day_and_plain_text_keeps_breaks(): void
    {
        $this->editor();
        $this->get(route('admin.tours.create'))->assertOk()->assertSee('Chưa có lịch trình.')
            ->assertDontSee('name="itineraries[0][title]"', false);
        $this->assertSame([], TourContent::lines(null));
        $this->assertSame('<p>Dòng &amp; ký hiệu<br />'."\n".'Dòng sau</p>', TourContent::html("Dòng & ký hiệu\nDòng sau"));
    }
}
