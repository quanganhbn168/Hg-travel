<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::where('email', 'admin@example.com')->value('id');
        $categories = PostCategory::whereIn('slug', ['cam-nang-du-lich', 'trai-nghiem', 'visa-thu-tuc', 'diem-den'])->pluck('id', 'slug');

        foreach ([
            ['slug' => 'kinh-nghiem-chuan-bi-cho-chuyen-di-nhat-ban', 'name' => 'Kinh nghiệm chuẩn bị cho chuyến đi Nhật Bản', 'category' => 'cam-nang-du-lich', 'image' => 'https://images.unsplash.com/photo-1528360983277-13d401cdc186?auto=format&fit=crop&w=1000&q=85'],
            ['slug' => 'checklist-visa-du-lich-han-quoc', 'name' => 'Checklist hồ sơ visa du lịch Hàn Quốc', 'category' => 'cam-nang-du-lich', 'image' => 'https://images.unsplash.com/photo-1534274867514-d5b47ef89ed7?auto=format&fit=crop&w=1000&q=85'],
            ['slug' => 'mot-ngay-cham-lai-o-kyoto', 'name' => 'Một ngày chậm lại ở Kyoto', 'category' => 'trai-nghiem', 'image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1000&q=85'],
            ['slug' => 'kinh-nghiem-di-tour-mua-thu-ha-giang', 'name' => 'Kinh nghiệm đi tour Hà Giang mùa thu', 'category' => 'diem-den', 'image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=1000&q=85'],
            ['slug' => 'nhung-giay-to-can-chuan-bi-khi-xin-visa', 'name' => 'Những giấy tờ nên chuẩn bị khi xin visa du lịch', 'category' => 'visa-thu-tuc', 'image' => 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=1000&q=85'],
            ['slug' => 'goi-y-hanh-trinh-da-nang-hoi-an', 'name' => 'Gợi ý hành trình Đà Nẵng · Hội An cho người đi lần đầu', 'category' => 'diem-den', 'image' => 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=1000&q=85'],
            ['slug' => 'bi-quyet-xep-hanh-ly-di-tour-nuoc-ngoai', 'name' => 'Bí quyết xếp hành lý gọn nhẹ khi đi tour nước ngoài', 'category' => 'cam-nang-du-lich', 'image' => 'https://images.unsplash.com/photo-1486911278844-a81c5267e227?auto=format&fit=crop&w=1000&q=85'],
            ['slug' => 'mot-chuyen-nghi-duong-dung-nghia-o-bali', 'name' => 'Một chuyến nghỉ dưỡng đúng nghĩa ở Bali', 'category' => 'trai-nghiem', 'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=85'],
        ] as $index => $data) {
            Post::updateOrCreate(['slug' => $data['slug']], [
                'post_category_id' => $categories[$data['category']],
                'created_by' => $authorId,
                'name' => $data['name'],
                'summary' => 'Một vài gợi ý thực tế giúp bạn chuẩn bị hành trình chủ động và nhẹ nhàng hơn.',
                'content' => 'Nội dung mẫu để đội ngũ quản trị tiếp tục biên tập và hoàn thiện trước khi xuất bản chính thức.',
                'cover_image' => $data['image'],
                'is_featured' => $index === 0,
                'is_active' => true,
                'published_at' => now()->subDays($index + 1),
            ]);
        }
    }
}
