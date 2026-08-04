<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $header = Menu::where('location', 'header')->firstOrFail();
        foreach ([
            ['title' => 'Trang chủ', 'route_name' => 'home'],
            ['title' => 'Tour nước ngoài', 'url' => '/tours?scope=international'],
            ['title' => 'Tour trong nước', 'url' => '/tours?scope=domestic'],
            ['title' => 'Dịch vụ', 'url' => '/dich-vu', 'children' => [
                ['title' => 'Tour du lịch trọn gói', 'url' => '/dich-vu#tour-tron-goi'],
                ['title' => 'Tour thiết kế riêng', 'url' => '/dich-vu#tour-thiet-ke-rieng'],
                ['title' => 'Visa các nước', 'url' => '/dich-vu#visa-cac-nuoc'],
                ['title' => 'Vé máy bay', 'url' => '/dich-vu#ve-may-bay'],
                ['title' => 'Đặt phòng khách sạn', 'url' => '/dich-vu#dat-phong-khach-san'],
            ]],
            ['title' => 'Blog & cẩm nang', 'url' => '/cam-nang'],
        ] as $index => $item) {
            $children = $item['children'] ?? [];
            unset($item['children']);
            $parent = $this->saveItem($header->id, $index + 1, $item);

            foreach ($children as $childIndex => $child) {
                $this->saveItem($header->id, $childIndex + 1, $child, $parent->id);
            }
        }

        $footer = Menu::where('location', 'footer')->firstOrFail();
        foreach ([
            ['title' => 'Tour du lịch', 'route_name' => 'tours.index'],
            ['title' => 'Cẩm nang du lịch', 'url' => '/cam-nang'],
            ['title' => 'Về chúng tôi', 'url' => '/gioi-thieu'],
        ] as $index => $item) {
            $this->saveItem($footer->id, $index + 1, $item);
        }
        MenuItem::where('menu_id', $footer->id)->whereNull('parent_id')->where('position', '>', 3)->delete();

        Cache::forget('frontend.menu.v1.header');
        Cache::forget('frontend.menu.v1.footer');
    }

    private function saveItem(int $menuId, int $position, array $data, ?int $parentId = null): MenuItem
    {
        return MenuItem::updateOrCreate(['menu_id' => $menuId, 'parent_id' => $parentId, 'position' => $position], [
            'parent_id' => $parentId,
            'title' => $data['title'],
            'url' => $data['url'] ?? null,
            'route_name' => $data['route_name'] ?? null,
            'target' => $data['target'] ?? '_self',
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
