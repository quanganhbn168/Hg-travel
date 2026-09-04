<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\DestinationAlias;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        $roots = collect([
            ['name' => 'Việt Nam', 'slug' => 'viet-nam', 'type' => 'country', 'market' => 'domestic', 'sort_order' => 1],
            ['name' => 'Châu Á', 'slug' => 'chau-a', 'type' => 'continent', 'market' => 'international', 'sort_order' => 2],
            ['name' => 'Châu Âu', 'slug' => 'chau-au', 'type' => 'continent', 'market' => 'international', 'sort_order' => 3],
            ['name' => 'Châu Úc', 'slug' => 'chau-uc', 'type' => 'continent', 'market' => 'international', 'sort_order' => 4],
            ['name' => 'Châu Mỹ', 'slug' => 'chau-my', 'type' => 'continent', 'market' => 'international', 'sort_order' => 5],
            ['name' => 'Châu Phi', 'slug' => 'chau-phi', 'type' => 'continent', 'market' => 'international', 'sort_order' => 6],
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
            ['name' => 'Miền Bắc', 'slug' => 'mien-bac', 'parent' => 'viet-nam', 'type' => 'region', 'market' => 'domestic', 'sort_order' => 1],
            ['name' => 'Miền Trung', 'slug' => 'mien-trung', 'parent' => 'viet-nam', 'type' => 'region', 'market' => 'domestic', 'sort_order' => 2],
            ['name' => 'Miền Nam', 'slug' => 'mien-nam', 'parent' => 'viet-nam', 'type' => 'region', 'market' => 'domestic', 'sort_order' => 3],
            ['name' => 'Miền Tây', 'slug' => 'mien-tay', 'parent' => 'viet-nam', 'type' => 'region', 'market' => 'domestic', 'sort_order' => 4],
        ])->mapWithKeys(function (array $region) use ($roots): array {
            $model = $this->seedDestination(['slug' => $region['slug']], [
                'parent_id' => $roots[$region['parent']]->id,
                'name' => $region['name'],
                'type' => $region['type'],
                'market' => $region['market'],
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
            ['name' => 'Hy Lạp', 'slug' => 'hy-lap', 'parent' => 'chau-au', 'sort_order' => 7],
            ['name' => 'Thổ Nhĩ Kỳ', 'slug' => 'tho-nhi-ky', 'parent' => 'chau-au', 'sort_order' => 8],
            ['name' => 'Nga', 'slug' => 'nga', 'parent' => 'chau-au', 'sort_order' => 9],
            ['name' => 'Vương quốc Anh', 'slug' => 'anh', 'parent' => 'chau-au', 'sort_order' => 10],
            ['name' => 'Luxembourg', 'slug' => 'luxembourg', 'parent' => 'chau-au', 'sort_order' => 11],
            ['name' => 'Úc', 'slug' => 'uc', 'parent' => 'chau-uc', 'sort_order' => 1],
            ['name' => 'Canada', 'slug' => 'canada', 'parent' => 'chau-my', 'sort_order' => 1],
            ['name' => 'Ai Cập', 'slug' => 'ai-cap', 'parent' => 'chau-phi', 'sort_order' => 1],
            ['name' => 'Nam Phi', 'slug' => 'nam-phi', 'parent' => 'chau-phi', 'sort_order' => 2],
        ])->mapWithKeys(function (array $country) use ($roots): array {
            $aliases = match ($country['slug']) {
                'tho-nhi-ky' => ['Turkey', 'TURKEY'],
                'nga' => ['Russia', 'RUSSIA'],
                'anh' => ['England', 'United Kingdom', 'UK'],
                'nam-phi' => ['South Africa'],
                'thuy-si' => ['Thuỵ Sĩ', 'Switzerland'],
                'bi' => ['Belgium'],
                'duc' => ['Germany'],
                'y' => ['Italy'],
                'hy-lap' => ['Greece'],
                default => [],
            };
            $model = $this->seedDestination(['slug' => $country['slug']], [
                'parent_id' => $roots[$country['parent']]->id,
                'name' => $country['name'],
                'type' => 'country',
                'market' => 'international',
                'summary' => 'Các điểm đến thuộc '.$country['name'].'.',
                'sort_order' => $country['sort_order'],
                'is_featured' => false,
                'is_active' => true,
                'is_system' => true,
                'aliases' => $aliases,
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
            ['name' => 'Thượng Hải', 'slug' => 'thuong-hai', 'parent' => 'trung-quoc', 'summary' => 'Đô thị sôi động, kiến trúc giao thoa và nhịp sống hiện đại của Trung Quốc.', 'image' => 'https://images.unsplash.com/photo-1548919973-5cef591cdbc9?auto=format&fit=crop&w=1000&q=85', 'aliases' => ['Shanghai']],
            ['name' => 'Hàng Châu', 'slug' => 'hang-chau', 'parent' => 'trung-quoc', 'summary' => 'Tây Hồ thơ mộng, phố cổ và cảnh sắc Giang Nam.', 'image' => 'https://images.unsplash.com/photo-1548919973-5cef591cdbc9?auto=format&fit=crop&w=1000&q=85', 'aliases' => ['Hangzhou']],
            ['name' => 'Bắc Kinh', 'slug' => 'bac-kinh', 'parent' => 'trung-quoc', 'summary' => 'Cố Cung, Vạn Lý Trường Thành và dấu ấn lịch sử sâu đậm.', 'image' => 'https://images.unsplash.com/photo-1508804185872-d7badad00f7d?auto=format&fit=crop&w=1000&q=85', 'aliases' => ['Beijing']],
            ['name' => 'Trùng Khánh', 'slug' => 'trung-khanh', 'parent' => 'trung-quoc', 'summary' => 'Thành phố núi, ẩm thực cay nồng và nhịp sống rực rỡ về đêm.', 'image' => 'https://images.unsplash.com/photo-1548919973-5cef591cdbc9?auto=format&fit=crop&w=1000&q=85', 'aliases' => ['Chongqing']],
            ['name' => 'Thành Đô', 'slug' => 'thanh-do', 'parent' => 'trung-quoc', 'summary' => 'Nhịp sống chậm, ẩm thực Tứ Xuyên và hành trình kết nối Cửu Trại Câu.', 'image' => 'https://images.unsplash.com/photo-1548919973-5cef591cdbc9?auto=format&fit=crop&w=1000&q=85', 'aliases' => ['Chengdu']],
            ['name' => 'Cửu Trại Câu', 'slug' => 'cuu-trai-cau', 'parent' => 'trung-quoc', 'summary' => 'Hồ nước xanh ngọc, thác nước và thiên nhiên hùng vĩ vùng Tứ Xuyên.', 'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1000&q=85', 'aliases' => ['Jiuzhaigou']],
            ['name' => 'Lệ Giang', 'slug' => 'le-giang', 'parent' => 'trung-quoc', 'summary' => 'Phố cổ, núi tuyết và những trải nghiệm đậm sắc màu Vân Nam.', 'image' => 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=1000&q=85', 'aliases' => ['Lijiang']],
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
                'type' => 'city',
                'market' => $parent->market,
                'summary' => $destination['summary'],
                'cover_image' => $destination['image'],
                'sort_order' => $index + 1,
                'is_featured' => true,
                'is_active' => true,
                'is_system' => false,
                'landing_enabled' => true,
                'aliases' => $destination['aliases'] ?? [],
            ]);
        }

        foreach ([
            ['name' => 'Canberra', 'slug' => 'canberra', 'parent' => 'uc'],
            ['name' => 'Tô Châu', 'slug' => 'to-chau', 'parent' => 'trung-quoc', 'aliases' => ['Suzhou']],
            ['name' => 'Chu Gia Giác', 'slug' => 'chu-gia-giac', 'parent' => 'trung-quoc'],
            ['name' => 'Quảng Châu', 'slug' => 'quang-chau', 'parent' => 'trung-quoc', 'aliases' => ['Guangzhou']],
            ['name' => 'Nam Ninh', 'slug' => 'nam-ninh', 'parent' => 'trung-quoc', 'aliases' => ['Nanning']],
            ['name' => 'Hà Khẩu', 'slug' => 'ha-khau', 'parent' => 'trung-quoc'],
            ['name' => 'Mông Tự', 'slug' => 'mong-tu', 'parent' => 'trung-quoc'],
            ['name' => 'Đại Lý', 'slug' => 'dai-ly', 'parent' => 'trung-quoc', 'aliases' => ['Dali']],
            ['name' => 'Shangri-La', 'slug' => 'shangri-la', 'parent' => 'trung-quoc', 'aliases' => ['Shangrila', 'Shangri La']],
            ['name' => 'Côn Minh', 'slug' => 'con-minh', 'parent' => 'trung-quoc', 'aliases' => ['Kunming']],
            ['name' => 'Ô Trấn', 'slug' => 'o-tran', 'parent' => 'trung-quoc', 'aliases' => ['Wuzhen']],
            ['name' => 'Frankfurt', 'slug' => 'frankfurt', 'parent' => 'duc'],
            ['name' => 'Cologne', 'slug' => 'cologne', 'parent' => 'duc', 'aliases' => ['Köln']],
            ['name' => 'Amsterdam', 'slug' => 'amsterdam', 'parent' => 'ha-lan'],
            ['name' => 'Giethoorn', 'slug' => 'giethoorn', 'parent' => 'ha-lan'],
            ['name' => 'Brussels', 'slug' => 'brussels', 'parent' => 'bi', 'aliases' => ['Bruxelles']],
            ['name' => 'Colmar', 'slug' => 'colmar', 'parent' => 'phap'],
            ['name' => 'Reims', 'slug' => 'reims', 'parent' => 'phap', 'aliases' => ['Reim']],
            ['name' => 'Metz', 'slug' => 'metz', 'parent' => 'phap'],
            ['name' => 'Mulhouse', 'slug' => 'mulhouse', 'parent' => 'phap'],
            ['name' => 'Lucerne', 'slug' => 'lucerne', 'parent' => 'thuy-si', 'aliases' => ['Luzern']],
            ['name' => 'Engelberg', 'slug' => 'engelberg', 'parent' => 'thuy-si'],
            ['name' => 'Milan', 'slug' => 'milan', 'parent' => 'y', 'aliases' => ['Milano']],
            ['name' => 'Pisa', 'slug' => 'pisa', 'parent' => 'y'],
            ['name' => 'Livorno', 'slug' => 'livorno', 'parent' => 'y'],
            ['name' => 'Mestre', 'slug' => 'mestre', 'parent' => 'y'],
            ['name' => 'Padova', 'slug' => 'padova', 'parent' => 'y', 'aliases' => ['Padua']],
            ['name' => 'Edinburgh', 'slug' => 'edinburgh', 'parent' => 'anh'],
            ['name' => 'Inverness', 'slug' => 'inverness', 'parent' => 'anh'],
            ['name' => 'Manchester', 'slug' => 'manchester', 'parent' => 'anh'],
            ['name' => 'Oxford', 'slug' => 'oxford', 'parent' => 'anh'],
            ['name' => 'Cardiff', 'slug' => 'cardiff', 'parent' => 'anh'],
            ['name' => 'London', 'slug' => 'london', 'parent' => 'anh'],
            ['name' => 'Moscow', 'slug' => 'moscow', 'parent' => 'nga'],
            ['name' => 'Saint Petersburg', 'slug' => 'saint-petersburg', 'parent' => 'nga', 'aliases' => ['Saint Peterburg', 'St Petersburg']],
            ['name' => 'Istanbul', 'slug' => 'istanbul', 'parent' => 'tho-nhi-ky'],
            ['name' => 'Canakkale', 'slug' => 'canakkale', 'parent' => 'tho-nhi-ky', 'aliases' => ['Çanakkale']],
            ['name' => 'Izmir', 'slug' => 'izmir', 'parent' => 'tho-nhi-ky', 'aliases' => ['İzmir']],
            ['name' => 'Johannesburg', 'slug' => 'johannesburg', 'parent' => 'nam-phi'],
            ['name' => 'Pretoria', 'slug' => 'pretoria', 'parent' => 'nam-phi'],
            ['name' => 'Cape Town', 'slug' => 'cape-town', 'parent' => 'nam-phi'],
            ['name' => 'Hermanus', 'slug' => 'hermanus', 'parent' => 'nam-phi'],
        ] as $index => $destination) {
            $parent = $parents[$destination['parent']] ?? Destination::query()->where('slug', $destination['parent'])->firstOrFail();

            $this->seedDestination(['slug' => $destination['slug']], [
                'parent_id' => $parent->id,
                'name' => $destination['name'],
                'type' => 'city',
                'market' => $parent->market,
                'sort_order' => 100 + $index,
                'is_featured' => false,
                'is_active' => true,
                'is_system' => false,
                'aliases' => $destination['aliases'] ?? [],
            ]);
        }
    }

    /** @param array<string, mixed> $attributes @param array<string, mixed> $values */
    private function seedDestination(array $attributes, array $values): Destination
    {
        $aliases = $values['aliases'] ?? [];
        unset($values['aliases']);

        $destination = Destination::withTrashed()->where($attributes)->first();
        $parent = ! empty($values['parent_id']) ? Destination::query()->find($values['parent_id']) : null;
        $values += [
            'type' => 'city',
            'market' => $parent?->market ?: 'international',
            'landing_enabled' => false,
        ];

        if (! $destination) {
            $destination = Destination::create($values + $attributes);
        } else {
            if ($destination->trashed()) {
                $destination->restore();
            }

            $changes = [];

            foreach (['parent_id', 'type', 'market', 'is_system'] as $field) {
                if ($destination->getAttribute($field) !== $values[$field]) {
                    $changes[$field] = $values[$field];
                }
            }

            foreach (['summary', 'cover_image'] as $field) {
                if (blank($destination->getAttribute($field)) && filled($values[$field] ?? null)) {
                    $changes[$field] = $values[$field];
                }
            }

            if (($values['landing_enabled'] ?? false) && ! $destination->landing_enabled) {
                $changes['landing_enabled'] = true;
            }

            if ($changes !== []) {
                $destination->update($changes);
            }
        }

        $this->syncAliases($destination, [$destination->name, ...$aliases]);

        return $destination;
    }

    /** @param array<int, string> $aliases */
    private function syncAliases(Destination $destination, array $aliases): void
    {
        foreach (array_unique($aliases) as $alias) {
            $normalized = DestinationAlias::normalize((string) $alias);

            if (mb_strlen($normalized) < 3) {
                continue;
            }

            DestinationAlias::updateOrCreate(
                ['normalized_alias' => $normalized, 'locale' => app()->getLocale()],
                ['destination_id' => $destination->getKey(), 'alias' => trim((string) $alias), 'source' => 'seed'],
            );
        }
    }
}
