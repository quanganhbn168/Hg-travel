<?php

namespace App\Services;

use App\Support\MediaFields;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaUsageService
{
    public function describe(string $usage): string
    {
        $names = ['tour_images' => 'Ảnh tour', 'tours' => 'Tour', 'destinations' => 'Điểm đến', 'tour_categories' => 'Loại hình tour', 'services' => 'Dịch vụ', 'posts' => 'Bài viết', 'pages' => 'Trang nội dung', 'testimonials' => 'Cảm nhận khách hàng', 'travel_moments' => 'Khoảnh khắc', 'slider_items' => 'Slide', 'about_pages' => 'Trang giới thiệu', 'tour_sections' => 'Nội dung tour', 'tour_itineraries' => 'Lịch trình tour'];
        $fields = ['story_image' => 'Ảnh câu chuyện', 'hero_image' => 'Ảnh đầu trang', 'background_image' => 'Ảnh nền', 'avatar_path' => 'Ảnh đại diện', 'cover_image' => 'Ảnh đại diện', 'banner_image' => 'Banner', 'logo_url' => 'Logo', 'image_share_url' => 'Ảnh chia sẻ', 'page_banner_url' => 'Banner chung', 'homepage_hero_url' => 'Banner trang chủ', 'about_image_url' => 'Ảnh giới thiệu', 'favicon_master' => 'Favicon'];
        if (preg_match('/^([^#]+)#(\d+)\.(.+)$/', $usage, $match)) {
            return ($names[$match[1]] ?? 'Nội dung').' #'.$match[2].' · '.($fields[$match[3]] ?? 'Nội dung/ảnh');
        }

        return 'Cài đặt · '.($fields[basename(str_replace('.', '/', $usage))] ?? 'Media');
    }

    public function usages(Media $media): array
    {
        $paths = [app(MediaReferenceService::class)->originalPath($media), ...(array) $media->getCustomProperty('aliases', [])];
        $usages = [];
        foreach (MediaFields::models() as $class) {
            $model = new $class;
            $images = MediaFields::IMAGES[$class] ?? [];
            $fields = array_unique([...array_keys($images), ...(MediaFields::CONTENT[$class] ?? [])]);
            // Query builder deliberately includes soft-deleted drafts: restoration must retain their files.
            foreach (DB::table($model->getTable())->select(['id', ...$fields, ...array_values($images)])->cursor() as $row) {
                foreach ($fields as $field) {
                    $idColumn = $images[$field] ?? null;
                    if (($idColumn && (int) $row->$idColumn === $media->id) || $this->contains($row->$field, $paths)) {
                        $usages[] = $model->getTable().'#'.$row->id.'.'.$field;
                    }
                }
            }
        }
        foreach (DB::table('settings')->get(['group', 'name', 'payload']) as $setting) {
            if ($this->contains($setting->payload, $paths)) {
                $usages[] = 'settings.'.$setting->group.'.'.$setting->name;
            }
            if ($setting->group === 'media' && $setting->name === 'media_ids') {
                foreach ((array) json_decode($setting->payload, true) as $field => $id) {
                    if ((int) $id === $media->id) {
                        $usages[] = 'settings.media.'.$field;
                    }
                }
            }
        }

        return array_values(array_unique($usages));
    }

    private function contains(mixed $value, array $paths): bool
    {
        $value = rawurldecode(str_replace('\\/', '/', (string) $value));
        foreach ($paths as $path) {
            if (str_contains($value, $path)) {
                return true;
            }
        }

        return false;
    }
}
