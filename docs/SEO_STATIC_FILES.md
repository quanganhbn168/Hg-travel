# HG Trip — File SEO tĩnh và chính sách index

Phạm vi: local `D:\laragon\www\dulich1`, ngày 05/09/2026. Đây là chính sách mới theo yêu cầu người dùng, thay cho sitemap/robots động và việc chặn index toàn bộ non-production trước đó.

## File thật và cách sinh lại

```sh
php artisan sitemap:generate
```

- `sitemap:generate` dùng Spatie Sitemap và SitemapService, mặc định ghi `public/sitemap.xml`. Chỉ lấy nội dung đã xuất bản, đang hiển thị, không bị soft-delete; không đưa admin vào sitemap. URL dùng domain `APP_URL`.
- Robots không dùng command nữa: vào **Cài đặt → SEO → File robots.txt** (`/admin/settings/seo`), chỉnh nội dung và nhấn **Lưu robots.txt**. Editor đọc trực tiếp file hiện tại và ghi nguyên khối vào `public/robots.txt`.
- Nút **Lưu SEO mặc định** chỉ lưu metadata vào Spatie Settings, không đụng robots. Nút **Lưu robots.txt** không sửa metadata/database. File là nguồn dữ liệu duy nhất cho robots, không có bản cấu hình trùng lặp trong database.
- Khi chưa có file, editor hiện mẫu theo `APP_URL`; GET không tạo file, nhấn lưu mới tạo. Sau khi đổi domain, sửa dòng Sitemap tại đây rồi lưu lại.
- Xem editor cần quyền `settings.view`; lưu cần `settings.update` cùng auth/admin access. Giới hạn 50.000 ký tự, kiểm tra directive/path/URL và từ chối HTML/PHP. Validation hoặc lỗi ghi hiển thị tại ô nội dung và giữ input; không báo thành công giả.
- Mỗi lần lưu kiểm tra hash phiên bản dưới lock, tránh hai form quản trị cũ ghi đè nhau. Nếu có xung đột, sao chép nội dung đang sửa, tải lại trang rồi đối chiếu trước khi lưu. Không tự ghi đè bản mới hơn.
- Render hoàn tất rồi thay file nguyên khối, hạn chế crawler đọc trúng XML đang ghi dở. Nếu khâu build sitemap lỗi, bản trước đó vẫn còn.
- `--export=/duong-dan/file.xml` là tùy chọn xuất riêng, không thay file mặc định.
- `.gitignore` đã loại cả `/public/sitemap.xml` và `/public/robots.txt`. Không force-add chúng vào Git; chỉ quản lý code sinh file.
- Lịch `sitemap:generate` mỗi giờ đã có trong `routes/console.php`, nhưng chỉ hoạt động khi scheduler thực sự chạy. Sửa nội dung không tự thay file ngay: chạy command để cập nhật tức thì, hoặc chờ lịch.
- Web root phải là thư mục `public`. Web server phục vụ file có sẵn trực tiếp; route SeoController chỉ đọc lại file có sẵn khi request đi qua Laravel. Thiếu file thì trả 404, không tự query database để sinh file từ HTTP.

## robots.txt hiện tại

```text
User-agent: *
Allow: /
Disallow: /admin$
Disallow: /admin?
Disallow: /admin/

Sitemap: https://dulich1.test/sitemap.xml
```

`/admin$` khớp đúng đường dẫn admin gốc; `/admin?` khớp admin gốc có query; `/admin/` khớp các URL con. Không chặn nhầm slug public kiểu `/administrator-guide`. Trang đặt tour, ảnh, CSS, JS và nội dung public vẫn được crawl.

Domain minh họa trên là `APP_URL` local hiện tại; không mang file chứa domain local lên production. Sinh lại bằng cấu hình đúng ở môi trường đích khi triển khai sau này.

## Layout và HTTP header

| Phạm vi | Meta robots | X-Robots-Tag |
| --- | --- | --- |
| Trang public dùng `layouts/master.blade.php`, gồm `/dat-tour` | `index, follow` | `index, follow` trên response HTML thành công |
| Layout admin và trang login riêng | `noindex, nofollow` | `noindex, nofollow` |
| Redirect, JSON hoặc lỗi dưới `/admin` | Không phụ thuộc HTML | `noindex, nofollow` |

Không còn nhánh tự noindex local/staging. Layout public vẫn có title, description, canonical, Open Graph và Twitter; canonical mặc định không lấy query tracking. Đây là cấu hình cho phép index, không phải xác nhận Google đã lập chỉ mục.

Robots.txt là quy tắc crawl, không thay thế xác thực/phân quyền. Admin vẫn được bảo vệ bởi auth và permission middleware. URL đã bị chặn crawl có thể vẫn xuất hiện trên Google dưới dạng URL; Google phải crawl được response mới đọc được meta/header noindex. Không tuyên bố robots.txt tự xóa URL đã được index. Tham chiếu: [Google robots.txt](https://developers.google.com/crawling/docs/robots-txt/robots-txt-spec), [Google robots meta và X-Robots-Tag](https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag).

## Checklist kiểm chứng

- [x] Sitemap được command sinh file thật ở `public`, có 116 URL; robots được lưu trực tiếp từ quản trị, không còn command robots.
- [x] Hai file tải qua HTTP local trả 200, nội dung khớp file trên ổ đĩa, không tạo session cookie.
- [x] Rà HTTP 116 URL sitemap và `/dat-tour`: 117/117 trả 200, đúng một meta robots `index, follow`, header cùng giá trị, đúng một canonical và description không rỗng.
- [x] `git check-ignore` nhận cả hai file; `git ls-files` không có hai file.
- [x] Test rule allow/disallow ở local, staging và production; kiểm tra đúng ranh giới admin và query.
- [x] Test sitemap loại draft/future/inactive/soft-delete; URL canonical, không trùng; bulk update chỉ thay sitemap khi sinh lại.
- [x] Test thiếu file không tự sinh qua HTTP, build lỗi giữ file trước đó, tùy chọn export không ghi đè file public.
- [x] Test meta và header public, gồm đặt tour; login, admin đăng nhập, redirect, 401 và 404 admin đều noindex.
- [x] Form robots đọc file hiện hành, có nút lưu riêng; user chỉ xem/khách không được ghi, thiếu file chỉ tạo sau khi lưu.
- [x] Test lưu nội dung mới rồi mở lại, chuẩn hóa xuống dòng, metadata không bị thay đổi, validation, lỗi ghi và ngăn ghi đè từ form cũ.
- [x] Browser desktop: editor/nút lưu hiển thị đúng; bấm lưu file thực tế thành công. Đối chiếu mtime cập nhật và SHA-256 vẫn giữ đúng quy tắc hiện hành; không đổi chính sách crawl khi thử nghiệm.
- [x] Toàn bộ 91 tests / 751 assertions qua; Blade cache và kiểm tra diff qua.

HTTP thực tế được kiểm tra trên server local `http://127.0.0.1:8123`; file sitemap vẫn dùng domain `APP_URL` là `https://dulich1.test`. Lần thử HTTPS trực tiếp tới domain local gặp lỗi TLS handshake, nên không ghi nhận đã kiểm chứng HTTPS/vhost Laragon. Không thay cấu hình TLS trong phạm vi này.

Không SSH, deploy, push hoặc tạo commit trong lượt này. Các mục media/browser và phê duyệt commit còn mở được giữ nguyên trong [MEDIA_CHECKLIST.md](MEDIA_CHECKLIST.md); hoàn tất SEO không có nghĩa đóng các mục đó.
