<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $roots = collect([
            ['name' => 'Việt Nam', 'slug' => 'viet-nam', 'sort_order' => 1],
            ['name' => 'Châu Á', 'slug' => 'chau-a', 'sort_order' => 2],
            ['name' => 'Châu Âu', 'slug' => 'chau-au', 'sort_order' => 3],
            ['name' => 'Châu Úc', 'slug' => 'chau-uc', 'sort_order' => 4],
            ['name' => 'Châu Mỹ', 'slug' => 'chau-my', 'sort_order' => 5],
            ['name' => 'Châu Phi', 'slug' => 'chau-phi', 'sort_order' => 6],
        ])->mapWithKeys(function (array $destination): array {
            $model = $this->seedDestination(['slug' => $destination['slug']], $destination + [
                'parent_id' => null,
                'summary' => 'Những điểm đến được HG tuyển chọn.',
                'is_featured' => false,
                'is_active' => true,
                'is_system' => true,
            ]);

            return [$destination['slug'] => $model];
        });

        $regions = collect([
            ['name' => 'Miền Bắc', 'slug' => 'mien-bac', 'parent' => 'viet-nam', 'sort_order' => 1],
            ['name' => 'Miền Trung', 'slug' => 'mien-trung', 'parent' => 'viet-nam', 'sort_order' => 2],
            ['name' => 'Miền Nam', 'slug' => 'mien-nam', 'parent' => 'viet-nam', 'sort_order' => 3],
            ['name' => 'Miền Tây', 'slug' => 'mien-tay', 'parent' => 'viet-nam', 'sort_order' => 4],
        ])->mapWithKeys(function (array $region) use ($roots): array {
            $model = $this->seedDestination(['slug' => $region['slug']], [
                'parent_id' => $roots[$region['parent']]->id,
                'name' => $region['name'],
                'summary' => 'Các điểm đến thuộc '.$region['name'].'.',
                'sort_order' => $region['sort_order'],
                'is_featured' => false,
                'is_active' => true,
                'is_system' => true,
            ]);

            return [$region['slug'] => $model];
        });

        $countries = collect([
            ['name' => 'Trung Quốc', 'slug' => 'trung-quoc', 'parent' => 'chau-a', 'sort_order' => 1],
            ['name' => 'Nhật Bản', 'slug' => 'nhat-ban', 'parent' => 'chau-a', 'sort_order' => 2],
            ['name' => 'Hàn Quốc', 'slug' => 'han-quoc', 'parent' => 'chau-a', 'sort_order' => 3],
            ['name' => 'Thái Lan', 'slug' => 'thai-lan', 'parent' => 'chau-a', 'sort_order' => 4],
            ['name' => 'Indonesia', 'slug' => 'indonesia', 'parent' => 'chau-a', 'sort_order' => 5],
            ['name' => 'Pháp', 'slug' => 'phap', 'parent' => 'chau-au', 'sort_order' => 1],
            ['name' => 'Bỉ', 'slug' => 'bi', 'parent' => 'chau-au', 'sort_order' => 2],
            ['name' => 'Hà Lan', 'slug' => 'ha-lan', 'parent' => 'chau-au', 'sort_order' => 3],
            ['name' => 'Đức', 'slug' => 'duc', 'parent' => 'chau-au', 'sort_order' => 4],
            ['name' => 'Ý', 'slug' => 'y', 'parent' => 'chau-au', 'sort_order' => 5],
            ['name' => 'Thụy Sĩ', 'slug' => 'thuy-si', 'parent' => 'chau-au', 'sort_order' => 6],
            ['name' => 'Úc', 'slug' => 'uc', 'parent' => 'chau-uc', 'sort_order' => 1],
            ['name' => 'Canada', 'slug' => 'canada', 'parent' => 'chau-my', 'sort_order' => 1],
            ['name' => 'Ai Cập', 'slug' => 'ai-cap', 'parent' => 'chau-phi', 'sort_order' => 1],
        ])->mapWithKeys(function (array $country) use ($roots): array {
            $model = $this->seedDestination(['slug' => $country['slug']], [
                'parent_id' => $roots[$country['parent']]->id,
                'name' => $country['name'],
                'summary' => 'Các điểm đến thuộc '.$country['name'].'.',
                'sort_order' => $country['sort_order'],
                'is_featured' => false,
                'is_active' => true,
                'is_system' => true,
            ]);

            return [$country['slug'] => $model];
        });

        $parents = $roots->all() + $regions->all() + $countries->all();

        foreach ([
            ['name' => 'Đà Nẵng', 'slug' => 'da-nang', 'parent' => 'mien-trung', 'summary' => 'Biển trong xanh, nhịp sống trẻ và hành trình nối liền Hội An đầy cảm hứng.', 'image' => 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Hội An', 'slug' => 'hoi-an', 'parent' => 'mien-trung', 'summary' => 'Phố cổ, đèn lồng, ẩm thực địa phương và nhịp sống chậm bên sông Hoài.', 'image' => 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Quy Nhơn', 'slug' => 'quy-nhon', 'parent' => 'mien-trung', 'summary' => 'Biển xanh trong, những cung đường ven biển và nhịp sống bình yên.', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Phú Yên', 'slug' => 'phu-yen', 'parent' => 'mien-trung', 'summary' => 'Bãi biển mộc mạc, ghềnh đá và những cung đường ven biển đầy nắng.', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Hà Giang', 'slug' => 'ha-giang', 'parent' => 'mien-bac', 'summary' => 'Cao nguyên đá hùng vĩ, cung đường uốn lượn và những bản làng đậm sắc màu.', 'image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Sa Pa', 'slug' => 'sa-pa', 'parent' => 'mien-bac', 'summary' => 'Mây núi, ruộng bậc thang và nhịp sống bản làng vùng Tây Bắc.', 'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Hạ Long', 'slug' => 'ha-long', 'parent' => 'mien-bac', 'summary' => 'Vịnh biển kỳ quan với hành trình nghỉ dưỡng trên du thuyền.', 'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Phú Quốc', 'slug' => 'phu-quoc', 'parent' => 'mien-nam', 'summary' => 'Đảo ngọc với biển xanh, resort thư thái và nhiều trải nghiệm dành cho gia đình.', 'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Cần Thơ', 'slug' => 'can-tho', 'parent' => 'mien-tay', 'summary' => 'Chợ nổi, vườn cây và nhịp sống hiền hòa của miền sông nước.', 'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Thượng Hải', 'slug' => 'thuong-hai', 'parent' => 'trung-quoc', 'summary' => 'Đô thị sôi động, kiến trúc giao thoa và nhịp sống hiện đại của Trung Quốc.', 'image' => 'https://images.unsplash.com/photo-1548919973-5cef591cdbc9?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Hàng Châu', 'slug' => 'hang-chau', 'parent' => 'trung-quoc', 'summary' => 'Tây Hồ thơ mộng, phố cổ và cảnh sắc Giang Nam.', 'image' => 'https://images.unsplash.com/photo-1548919973-5cef591cdbc9?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Bắc Kinh', 'slug' => 'bac-kinh', 'parent' => 'trung-quoc', 'summary' => 'Cố Cung, Vạn Lý Trường Thành và dấu ấn lịch sử sâu đậm.', 'image' => 'https://images.unsplash.com/photo-1508804185872-d7badad00f7d?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Trùng Khánh', 'slug' => 'trung-khanh', 'parent' => 'trung-quoc', 'summary' => 'Thành phố núi, ẩm thực cay nồng và nhịp sống rực rỡ về đêm.', 'image' => 'https://images.unsplash.com/photo-1548919973-5cef591cdbc9?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Thành Đô', 'slug' => 'thanh-do', 'parent' => 'trung-quoc', 'summary' => 'Nhịp sống chậm, ẩm thực Tứ Xuyên và hành trình kết nối Cửu Trại Câu.', 'image' => 'https://images.unsplash.com/photo-1548919973-5cef591cdbc9?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Cửu Trại Câu', 'slug' => 'cuu-trai-cau', 'parent' => 'trung-quoc', 'summary' => 'Hồ nước xanh ngọc, thác nước và thiên nhiên hùng vĩ vùng Tứ Xuyên.', 'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Lệ Giang', 'slug' => 'le-giang', 'parent' => 'trung-quoc', 'summary' => 'Phố cổ, núi tuyết và những trải nghiệm đậm sắc màu Vân Nam.', 'image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Tokyo', 'slug' => 'tokyo', 'parent' => 'nhat-ban', 'summary' => 'Nhịp sống hiện đại, văn hóa truyền thống và những mùa hoa đáng nhớ.', 'image' => 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Kyoto', 'slug' => 'kyoto', 'parent' => 'nhat-ban', 'summary' => 'Không gian cổ kính, đền chùa thanh tĩnh và trải nghiệm Nhật Bản sâu sắc.', 'image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Hokkaido', 'slug' => 'hokkaido', 'parent' => 'nhat-ban', 'summary' => 'Thiên nhiên trong lành, những cánh đồng hoa và mùa tuyết đặc trưng của Nhật Bản.', 'image' => 'https://images.unsplash.com/photo-1490806843957-31f4c9a91c65?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Seoul', 'slug' => 'seoul', 'parent' => 'han-quoc', 'summary' => 'Thành phố của thời trang, ẩm thực và những góc phố đầy cảm hứng.', 'image' => 'https://images.unsplash.com/photo-1538485399081-7c897f7a1f4d?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Jeju', 'slug' => 'jeju', 'parent' => 'han-quoc', 'summary' => 'Đảo xanh dịu dàng dành cho những ngày nghỉ thật nhẹ tênh.', 'image' => 'https://images.unsplash.com/photo-1578637387939-43c525550085?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Bangkok', 'slug' => 'bangkok', 'parent' => 'thai-lan', 'summary' => 'Thành phố sôi động, ẩm thực đường phố và các điểm mua sắm hấp dẫn.', 'image' => 'https://images.unsplash.com/photo-1563492065599-3520f775eeed?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Pattaya', 'slug' => 'pattaya', 'parent' => 'thai-lan', 'summary' => 'Biển, hoạt động giải trí và những hành trình nghỉ dưỡng sôi động.', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Singapore', 'slug' => 'singapore', 'parent' => 'chau-a', 'summary' => 'Một điểm đến xanh, hiện đại và thuận tiện cho cả gia đình.', 'image' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Bali', 'slug' => 'bali', 'parent' => 'indonesia', 'summary' => 'Biển xanh, resort thư thái và những trải nghiệm bản địa đáng nhớ.', 'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Paris', 'slug' => 'paris', 'parent' => 'phap', 'summary' => 'Kinh đô ánh sáng với nghệ thuật, kiến trúc và những buổi chiều lãng mạn.', 'image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Rome', 'slug' => 'rome', 'parent' => 'y', 'summary' => 'Dấu tích đế chế, ẩm thực trứ danh và nhịp sống rất riêng của nước Ý.', 'image' => 'https://images.unsplash.com/photo-1529260830199-42c24126f198?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Vatican', 'slug' => 'vatican', 'parent' => 'y', 'summary' => 'Không gian nghệ thuật, kiến trúc và những giá trị lịch sử đặc biệt.', 'image' => 'https://images.unsplash.com/photo-1531572753322-ad063cecc140?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Sydney', 'slug' => 'sydney', 'parent' => 'uc', 'summary' => 'Thành phố cảng rực rỡ, bãi biển đẹp và nhịp sống hiện đại của nước Úc.', 'image' => 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Melbourne', 'slug' => 'melbourne', 'parent' => 'uc', 'summary' => 'Không gian nghệ thuật, cà phê và nhịp sống thanh lịch của nước Úc.', 'image' => 'https://images.unsplash.com/photo-1514395462725-fb4566210144?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Toronto', 'slug' => 'toronto', 'parent' => 'canada', 'summary' => 'Thành phố đa sắc màu, thuận tiện để nối hành trình khám phá Canada.', 'image' => 'https://images.unsplash.com/photo-1517935706615-2717063c2225?auto=format&fit=crop&w=1000&q=85'],
            ['name' => 'Cairo', 'slug' => 'cairo', 'parent' => 'ai-cap', 'summary' => 'Dấu ấn Ai Cập cổ đại, sa mạc và nền văn hóa rực rỡ.', 'image' => 'https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?auto=format&fit=crop&w=1000&q=85'],
        ] as $index => $destination) {
            $parent = $parents[$destination['parent']];

            $this->seedDestination(['slug' => $destination['slug']], [
                'parent_id' => $parent->id,
                'name' => $destination['name'],
                'summary' => $destination['summary'],
                'cover_image' => $destination['image'],
                'sort_order' => $index + 1,
                'is_featured' => true,
                'is_active' => true,
                'is_system' => false,
            ]);
        }
    }

    /** @param array<string, mixed> $attributes @param array<string, mixed> $values */
    private function seedDestination(array $attributes, array $values): Destination
    {
        $destination = Destination::withTrashed()->updateOrCreate($attributes, $values);

        if ($destination->trashed()) {
            $destination->restore();
        }

        return $destination;
    }
}
