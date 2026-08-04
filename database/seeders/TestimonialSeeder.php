<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['customer_name' => 'Nguyễn Minh Anh', 'customer_title' => 'Khách hàng tour Nhật Bản', 'content' => 'Lịch trình vừa đủ, tư vấn rất rõ ràng và cả đoàn có một chuyến đi thật trọn vẹn.', 'rating' => 5],
            ['customer_name' => 'Trần Quốc Bảo', 'customer_title' => 'Khách hàng tour Hàn Quốc', 'content' => 'HG hỗ trợ nhanh, thông tin minh bạch và hướng dẫn viên nhiệt tình.', 'rating' => 5],
            ['customer_name' => 'Lê Thu Hà', 'customer_title' => 'Khách hàng tour châu Âu', 'content' => 'Đây là lần đầu gia đình đi châu Âu nhưng mọi khâu đều được đồng hành rất chu đáo.', 'rating' => 5],
        ] as $index => $data) {
            Testimonial::updateOrCreate(['customer_name' => $data['customer_name']], $data + [
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
