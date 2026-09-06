# HG TRIP — feedback lần 2

Nguồn: `Chỉnh sửa web HGTrip 2.docx`, tài liệu khách gửi ngày 03/09/2026.

## Thay đổi

- Sửa mặc định “BỘ MÁY VÀ HIỆN DIỆN”; migration chỉ sửa câu sai chính tả tương ứng trong nội dung hiện có, giữ các nội dung biên tập khác.
- Tách tên công ty thành dòng riêng, cân cỡ chữ và chiều rộng cột Giới thiệu. Cân dòng tiêu đề và đoạn văn; các bước chuyển thành một cột trên điện thoại. Khối Cam kết có thêm chiều rộng, nút tư vấn nằm trong luồng nội dung để không đè đoạn văn.
- Thay ảnh Thư ngỏ và Câu chuyện bằng đúng hai ảnh trong tài liệu. File gốc được giữ nguyên: `hg-trip-traveller.png` (1024 × 1536) và `hg-trip-south-africa.jpg` (1115 × 1982), trong `public/images/about/hg-trip/`. Hiển thị toàn ảnh; không cắt ảnh đoàn khách. Hai trường ảnh vẫn chỉnh được trong quản trị Giới thiệu. Hero giữ nguyên.
- Khối tiêu biểu ưu tiên tour được quản trị đánh dấu nổi bật, bổ sung tour đã xuất bản, đang mở đặt và có lịch còn chỗ nếu chưa đủ ba thẻ.
- Khối ưu đãi giữ các chương trình đang hiệu lực (tối đa sáu tour), bổ sung tour sắp khởi hành nếu chưa đủ ba. Tour bổ sung dùng nguyên giá, không gắn giảm giá giả và không thay đổi quan hệ khuyến mại trong DB. Tiêu đề phân biệt nhóm có ưu đãi, nhóm hỗn hợp và nhóm chỉ có lịch sắp khởi hành. Nếu tổng dữ liệu hợp lệ ít hơn ba, chỉ hiển thị số tour thực có.

## Đối chiếu trước sửa

Bản `hgtrip.vn` ngày 06/09 đã có đủ ba tour tiêu biểu và đã sửa chính tả trong CMS; khối ưu đãi có hai tour. Thay đổi giữ ưu tiên lựa chọn sẵn có, không ghi đè danh sách tour/giá/lịch của server bằng dữ liệu local.

## Cập nhật server

Sau khi pull code, cần chạy migration để lưu hai ảnh mới và sửa chính tả vào dữ liệu CMS hiện có:

```sh
git pull --ff-only origin main
php artisan migrate --force
php artisan optimize:clear
php artisan view:cache
```

Migration nội dung không khôi phục ảnh cũ khi rollback, để giữ các chỉnh sửa CMS về sau. Các file ảnh cũ vẫn được giữ lại. Không cần build frontend hoặc upload riêng hai ảnh mới vì chúng đã nằm trong Git.

## Kiểm tra

- `php artisan test --compact`: 97 tests, 789 assertions. PHP local cần bật `pdo_sqlite` và `sqlite3` cho tiến trình test; dữ liệu test dùng SQLite trong bộ nhớ, không dùng DB nội dung.
- `php artisan view:cache`, Pint cho PHP thay đổi và `git diff --check`.
- Đối chiếu SHA-256 hai ảnh với bản trích xuất từ DOCX: trùng khớp.
- Kiểm tra browser local trên desktop và các khung responsive; đây không phải xác nhận đã deploy production.
