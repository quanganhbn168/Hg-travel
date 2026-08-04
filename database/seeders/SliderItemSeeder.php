<?php

namespace Database\Seeders;

use App\Models\Slider;
use App\Models\SliderItem;
use Illuminate\Database\Seeder;

class SliderItemSeeder extends Seeder
{
    public function run(): void
    {
        $slider = Slider::where('key', 'home')->firstOrFail();
        foreach ([
            ['title' => 'Chạm vào những miền đất đáng nhớ', 'subtitle' => 'Những hành trình được thiết kế chỉn chu, chân thành và đầy cảm hứng.', 'image_path' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1920&q=85', 'button_label' => 'Khám phá hành trình', 'button_url' => '/tours'],
            ['title' => 'Mỗi hành trình, một câu chuyện', 'subtitle' => 'Đi xa hơn cùng lịch trình rõ ràng và sự đồng hành tận tâm từ HG.', 'image_path' => 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1920&q=85', 'button_label' => 'Xem tour nổi bật', 'button_url' => '/tours'],
        ] as $index => $data) {
            SliderItem::updateOrCreate(['slider_id' => $slider->id, 'sort_order' => $index + 1], $data + ['is_active' => true]);
        }
    }
}
