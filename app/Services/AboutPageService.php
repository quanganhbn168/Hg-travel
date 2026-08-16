<?php

namespace App\Services;

use App\Models\AboutPage;

class AboutPageService
{
    public function current(): AboutPage
    {
        return AboutPage::firstOrCreate(['key' => 'about'], $this->defaults());
    }
    public function update(array $data): AboutPage
    {
        $page = $this->current();
        $page->update($data);
        return $page;
    }
    private function defaults(): array
    {
        return ['hero_title' => 'Mỗi hành trình bắt đầu từ một câu chuyện.', 'hero_intro' => 'HG TRIP thiết kế những hành trình riêng, chỉn chu và đáng nhớ cho mỗi khách hàng.', 'letter_title' => 'Thư ngỏ', 'letter_content' => "Kính gửi Quý Khách hàng và Quý Đối tác,\n\nMỗi chuyến đi đều mang trong mình một câu chuyện. Tại HG TRIP, chúng tôi tin rằng giá trị của một hành trình không chỉ nằm ở điểm đến, mà còn ở cách hành trình ấy được thiết kế và trải nghiệm.\n\nDù là chuyến công tác, chương trình khen thưởng cho doanh nghiệp, kỳ nghỉ gia đình hay hành trình khám phá thế giới, chúng tôi luôn hướng tới việc tạo ra những trải nghiệm chỉn chu, cá nhân hóa và đáng nhớ.\n\nVới đội ngũ có nhiều năm kinh nghiệm trong lĩnh vực du lịch quốc tế và mạng lưới đối tác rộng khắp, HG TRIP cam kết mang đến dịch vụ chuyên nghiệp, minh bạch và đồng hành cùng khách hàng ở mọi chặng đường.", 'story_title' => 'Câu chuyện HG TRIP', 'story_content' => 'Chúng tôi bắt đầu với một câu hỏi rất đơn giản: nếu mỗi khách hàng đều có một câu chuyện riêng, tại sao hành trình của họ lại phải giống nhau? HG TRIP lựa chọn lắng nghe để thấu hiểu, từ đó tạo nên những chuyến đi phù hợp với từng nhu cầu và những khoảnh khắc đáng nhớ.', 'vision' => 'Trở thành thương hiệu du lịch cao cấp được khách hàng và đối tác tin cậy tại Việt Nam và khu vực.', 'mission' => 'Mang đến những hành trình được thiết kế riêng, tối ưu trải nghiệm và tạo ra giá trị bền vững cho khách hàng.', 'core_values' => [['title'=>'Chuyên nghiệp','description'=>'Quy trình rõ ràng, tác phong chuyên nghiệp và luôn nâng cao chất lượng trong từng dịch vụ.'],['title'=>'Uy tín','description'=>'Cam kết dịch vụ minh bạch, đúng như thỏa thuận và bảo đảm chất lượng.'],['title'=>'Tận tâm','description'=>'Lấy khách hàng làm trung tâm, lắng nghe và đáp ứng tối đa nhu cầu.'],['title'=>'Sáng tạo','description'=>'Cải tiến sản phẩm cá nhân hóa để tạo trải nghiệm khác biệt.'],['title'=>'Chia sẻ','description'=>'Luôn đồng hành, chia sẻ trách nhiệm và thành công cùng khách hàng, đối tác.']], 'markets' => ['Nội địa','Inbound','Outbound','Middle East'], 'commitments' => ['Không phát sinh chi phí bất hợp lý','Hỗ trợ khẩn cấp 24/7','Lịch trình tối ưu','Đối tác được lựa chọn kỹ','Trải nghiệm cá nhân hóa','Bảo hiểm đầy đủ'], 'audiences' => ['Doanh nghiệp','Ngân hàng','Bảo hiểm','Gia đình','Cặp đôi','Nhóm bạn','Khách VIP'], 'ceo_name' => 'Bà Nguyễn Thị Hương Giang', 'ceo_bio' => 'Với trên 20 năm kinh nghiệm trong lĩnh vực du lịch, bà từng có gần 10 năm giữ vị trí Giám đốc Công ty Cổ phần Du lịch Hapro thuộc Tổng công ty Thương mại Hà Nội; sau đó là thành viên Hội đồng thành viên, Phó Giám đốc Công ty Cổ phần Lữ hành Nam Cường đến hết năm 2025.', 'deputy_name' => 'Bà Vũ Thị Thùy Hương', 'deputy_bio' => 'Có 10 năm kinh nghiệm chuyên sâu trong công tác điều hành tour, từng là Trưởng phòng Điều hành tại Công ty Cổ phần Lữ hành Nam Cường; trực tiếp tổ chức, phối hợp lịch trình, điều phối dịch vụ và quản lý vận hành hành trình.', 'background_image' => null, 'hero_image' => null, 'seo_title' => 'Về HG TRIP', 'seo_description' => 'Tìm hiểu câu chuyện, đội ngũ và cam kết dịch vụ của HG TRIP.'];
    }
}
