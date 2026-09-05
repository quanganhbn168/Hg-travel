# HG Trip — Media, kiến trúc và SEO: nghiệm thu local

Ngày kiểm tra: 05/09/2026. Phạm vi duy nhất: `D:\laragon\www\dulich1`.
Không deploy, không SSH, không push. Chưa tuyên bố đóng toàn bộ checklist: bước upload file qua trình duyệt còn chờ quyền/thao tác của người dùng.

Git: thay đổi đã được stage để lưu checkpoint, nhưng **chưa tạo commit**. Auto-review chặn commit trực tiếp trên `main` vì 229 đường dẫn staged, gồm 138 mục bỏ theo dõi media. Không thử nhánh khác hoặc cách gián tiếp để vượt chặn; cần người dùng xác nhận phạm vi trước khi commit. File ảnh thật vẫn còn; đây là xóa khỏi Git index, không xóa file trên ổ đĩa. Cập nhật báo cáo cuối sau thời điểm stage nằm trong working tree.

## Checklist triển khai và kiểm chứng

- [x] Sao lưu database + media, đối chiếu SHA-256; bản sao cuối có cả bộ favicon cố định.
- [x] Dùng lại Spatie Media Library, một upload service/policy, một picker cho Dropzone và TinyMCE.
- [x] Đồng nhất loại file, MIME và dung lượng theo Spatie Settings (1–100 MB); riêng vị trí ảnh không nhận tài liệu.
- [x] Chặn ảnh không đọc được, quá 40 triệu pixel, token không tồn tại, path traversal và upload pending của người khác.
- [x] Giữ ảnh khi sửa nội dung; thay/gỡ là thao tác rõ ràng. Model và settings lưu media ID, đường dẫn cũ chỉ phục vụ tương thích.
- [x] Gỡ ảnh chỉ tháo liên kết; không xóa file gốc đang dùng chung. Quét cả nội dung HTML/JSON và bản ghi soft-delete trước khi lưu trữ/dọn tệp.
- [x] Thư viện tìm kiếm/phân trang, xem nơi sử dụng, lưu trữ và khôi phục; thao tác này không xóa file vật lý.
- [x] Pending upload có chủ sở hữu, được giữ khi lưu nội dung; cleanup sau 48 giờ chỉ áp dụng pending không được sử dụng. Mặc định lệnh là dry-run.
- [x] Giữ nguyên byte và kích thước ảnh gốc. WebP cùng kích thước chỉ được chọn khi nhỏ hơn; thumbnail riêng, rộng tối đa 320 px, không upscale.
- [x] PNG trong suốt giữ alpha. GIF, WebP có sẵn, APNG và JPEG có EXIF xoay không bị flatten/đổi kích thước bởi pipeline này.
- [x] Queue `media`, retry/backoff; khi chưa có bản tối ưu luôn dùng bản gốc. Đã chạy hết queue local, không có failed media job tại lúc nghiệm thu.
- [x] Tour cover/gallery: không tự thêm cover cũ vào album, chống trùng, tối đa 12 ảnh mỗi đợt thêm, ID thuộc đúng tour, lưu thứ tự kéo thả và giữ thứ tự sau lỗi validation.
- [x] Khoảnh khắc/slide bắt buộc có ảnh: báo validation khi gỡ mà không thay, tránh lỗi database.
- [x] Favicon được sinh vào tên file cố định ở `public/`; master tối thiểu 512×512, bộ icon được crop/resize theo quy tắc favicon riêng. Upload gốc không đổi.
- [x] Adopt 136 ảnh tour cũ và 4 ảnh phục hồi vào thư viện; không di chuyển file, không đổi URL gốc, không reset database.
- [x] Bỏ theo dõi 138 file runtime media khỏi Git, giữ nguyên file trên ổ đĩa. Không đưa backup, `.env`, upload hoặc derivatives vào commit.
- [x] Blade media/editor chỉ render; chuẩn bị ảnh/previews trong component PHP, controller hoặc service. Không thêm resource/action map ngoài `AdminIndexRegistry`.
- [x] Spatie Settings vẫn là nguồn dữ liệu duy nhất. Không nuốt mọi exception thành giá trị mặc định; lỗi thiếu setting được báo, admin không âm thầm lưu fallback.
- [x] Theo yêu cầu mới, Spatie Sitemap sinh file thật `public/sitemap.xml` bằng `sitemap:generate`, thay file nguyên khối; lịch sinh mỗi giờ khi scheduler chạy. Không sinh XML từ database trong HTTP request; bỏ cache/observer sitemap động.
- [x] `public/robots.txt` là file tĩnh, đọc/ghi trực tiếp tại **Cài đặt → SEO → File robots.txt**, nút **Lưu robots.txt**. Đã bỏ command robots. Mẫu mặc định `Allow: /`, chỉ disallow đúng admin và URL con; không chặn đặt tour, ảnh, CSS/JS hoặc toàn bộ local/staging. Cả robots và sitemap đều được Git ignore.
- [x] Layout public và header trả `index, follow`; layout admin và login có meta `noindex, nofollow`, header áp dụng cả redirect/lỗi admin. Canonical, description, Open Graph và Twitter vẫn có trong layout public.
- [x] `php artisan view:cache`, toàn bộ test, `git diff --check`; media index render với paginator thật, admin index contract được giữ nguyên.
- [x] Browser local: thư viện/search/nơi sử dụng desktop và 390px; TinyMCE chọn ảnh cũ và chèn thành công; tạo cảm nhận test, sửa chữ giữ ảnh, gỡ ảnh rồi lưu; Home và About render thực tế.
- [ ] Upload tệp mới bằng file chooser/Dropzone/TinyMCE trong browser, kiểm tra thao tác hủy/retry và lưu rồi tải lại với chính tệp vừa chọn. Quyền chọn/upload tệp đã bị từ chối; không thử đi đường vòng. Backend upload/validation/lifecycle đã có test, nhưng không thay thế nghiệm thu thao tác này.
- [ ] Lưu commit checkpoint trên `main` local: chờ xác nhận phạm vi sau khi auto-review chặn; không push.

Deploy/production không nằm trong checklist local theo yêu cầu người dùng.

## Bằng chứng dữ liệu và kiểm thử

- Sau khi đưa editor robots vào quản trị: **91 tests, 751 assertions**, không failed/risky; chạy `php artisan test --compact --fail-on-risky`, tiếp theo `php artisan view:cache` và `git diff --check` đều thành công.
- Browser local đã lưu robots từ form, hiện thông báo thành công; mtime file thay đổi, SHA-256 giữ nguyên quy tắc đã duyệt. HTTP `/robots.txt` trả 200, nội dung khớp file trên ổ đĩa. Test riêng bao phủ sửa nội dung, tạo file thiếu, validation, phân quyền, lỗi ghi và xung đột phiên bản.
- Audit trường ảnh + settings: 194 tham chiếu; 146 local hợp lệ, 48 URL ngoài được giữ nguyên; không còn file local bị thiếu trong tập trường này. Không tuyên bố đã tải/kiểm tra toàn bộ ảnh ngoài Internet.
- 138/138 original trong inventory trước sửa vẫn khớp SHA-256.
- 143 original hiện có; 106 WebP display, 117 thumbnail. Tất cả WebP display cùng kích thước nguồn; thumbnail không vượt 320 px chiều rộng.
- Tổng dung lượng tập original: 39,063,506 byte; tập display tương ứng: 13,480,284 byte (giảm 65.49%). Đây là so sánh file, không phải đo tốc độ tải trang hay Core Web Vitals.
- Kiểm tra lại sau khi đổi SEO: 116/116 URL trong sitemap và `/dat-tour` (117 trang) trả HTTP 200, một meta robots `index, follow`, header đồng nhất, một canonical và description không rỗng. XML được test canonical host, unique URL, loại draft/future/inactive.
- Browser Home: 44 thẻ ảnh; không có ảnh local đã tải xong nhưng lỗi; không tràn ngang desktop. Media grid được xem trực quan ở 390px.
- Bản ghi cảm nhận test riêng của assistant đã được dọn. Media #150 phát sinh trong phiên, không do assistant chọn, được giữ nguyên; đang pending, chịu chính sách 48 giờ nếu không được gắn nội dung. Không xóa dữ liệu upload của người dùng.
- Bộ test chạy bằng SQLite `:memory:` và filesystem fake, không reset hoặc chạy seeder lên database thật. SQLite được bật riêng cho tiến trình test, không sửa php.ini toàn máy.

Có thể chạy trực tiếp nếu PHP CLI chưa bật SQLite: `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit --fail-on-risky`. Lượt Artisan sử dụng `PHP_INI_SCAN_DIR` trỏ tới `storage/app/private/qa-php-ini` chứa đúng hai extension này.

Lỗi bắt được khi nghiệm thu và đã sửa: biến ảnh logo khách hàng cũ trong About Blade; section metadata nhận null làm rò output buffer; giới hạn cứng 10 MB bên Spatie; gỡ ảnh bắt buộc gây NULL database. Sitemap hiện dùng file tĩnh: bulk update được phản ánh khi chạy lại command/lịch sinh, không phụ thuộc observer. Có regression test tương ứng.

## Controller / service / model / Blade hiện tại

| Lớp | Trách nhiệm sau thay đổi |
| --- | --- |
| Controller + Form Request | MediaController nhận request/trả JSON hoặc view; policy và quyền dùng chung. Testimonial có Form Request/service riêng. |
| Service + Job | MediaService lưu trữ/lifecycle; MediaPolicy validation; MediaReferenceService ID/path/URL; MediaUsageService kiểm tra nơi dùng; MediaOptimizationService + OptimizeMedia tối ưu; SettingService/FaviconService lưu cài đặt và file cố định. |
| Model | Nullable FK media và cast dùng ID làm nguồn chính; ManagedMediaObserver chuẩn hóa ảnh/content. Không nhét xử lý resize vào Blade. |
| Blade + JS | ImageUpload, Tinymce, TourMediaEditor có class chuẩn bị dữ liệu; `media.js` quản lý picker/upload/progress, khóa submit trong lúc upload. About và Post nhận URL chuẩn bị sẵn. |
| SEO | SitemapService và command ghi sitemap tĩnh. RobotsFileService đọc/ghi robots qua form SEO, chỉ cố định public/robots.txt, có kiểm tra phiên bản và lock. SEO metadata vẫn do Spatie Settings quản lý; không lưu thêm bản sao robots trong database. SeoController chỉ đọc file có sẵn; ApplyRobotsDirectives đồng nhất header public/admin. |

Đánh giá: phần media được tập trung và có kiểm chứng; không có nghĩa toàn bộ ứng dụng đã được refactor hoàn toàn. About admin, Slider và TravelMoment còn validation/payload trong controller; một số view legacy còn PHP trình bày. Component product-image-upload cũ không được gọi bởi các màn hình hiện tại, chưa xóa. Các collection Spatie cũ được giữ cho tương thích; pipeline mới dùng library.

## Sao lưu và vận hành local

- Trước sửa: `storage/app/private/media-backups/20260905_093156-jclntq/` (database.sql + 138 file + inventory.json).
- Sau nghiệm thu dữ liệu: `storage/app/private/media-backups/20260905_102612-ek8eb8/` (database.sql + 366 file media gốc/derived, SHA-256; generated-assets và inventory riêng cho favicon).
- Sitemap cũ vẫn còn trong backup đầu dưới tên `previous-sitemap.xml`. Đã sinh lại file tĩnh hiện hành ở `public/sitemap.xml` theo yêu cầu mới; không xóa bản backup.
- Bốn ảnh bị thiếu được khôi phục chính xác từ Git commit `95ac0c9`; commit cũ `72c9b03` đã xóa các file dù còn tham chiếu. Đã đối chiếu blob hash từng file sau khôi phục.
- `MEDIA_ROOT` mặc định `public/media`; nếu sau này đổi sang storage bên ngoài thì web server phải map URL `/media/` tương ứng. Không tự đổi URL của website.
- `php artisan media:check`: thử ghi/đọc/xóa probe riêng, kiểm tra storage/PHP GD. Local đã pass.
- `php artisan media:audit --json`: chỉ kiểm tra. `--adopt` đăng ký media cũ; cần backup trước khi dùng. `--optimize` xếp job tối ưu.
- `php artisan queue:work --queue=media --tries=3 --timeout=120`: xử lý riêng job media local; upload vẫn hiển thị ảnh gốc nếu worker chưa chạy.
- `php artisan media:cleanup`: xem trước; `--apply` mới xóa pending hết hạn và không còn được sử dụng. Không chạy xóa hàng loạt ready/archived.
- `php artisan schedule:work`: chạy lịch ở local khi cần; chỉ khai báo schedule trong code không có nghĩa scheduler hệ điều hành đã được cài.
- `php artisan sitemap:generate`: mặc định sinh lại `public/sitemap.xml`; `--export` chỉ dùng khi muốn xuất sang đường dẫn khác. Sau thay đổi nội dung cần chạy command hoặc chờ lịch sinh.
- Robots: vào `/admin/settings/seo`, sửa **Nội dung robots.txt**, nhấn **Lưu robots.txt**. File là nguồn duy nhất; không cần command. Khi file chưa có, form hiện mẫu theo `APP_URL` nhưng chỉ tạo khi lưu. Khi đổi domain, sửa lại dòng Sitemap trong editor.
- Hướng dẫn và checklist riêng cho cấu hình SEO mới: [SEO_STATIC_FILES.md](SEO_STATIC_FILES.md).

Khi triển khai trong tương lai cần backup/giữ uploads trước lần pull có commit bỏ theo dõi media; không được xem việc file bị bỏ khỏi Git là quyền xóa media server. Không chạy `migrate:fresh`, reset hoặc clean trên dữ liệu thật.

## Cách đóng bước browser còn mở

Người dùng chọn một ảnh thử trong form local, chờ upload xong, lưu một bản ghi thử và tải lại; kiểm tra ảnh còn, kích thước nguồn không đổi. Thử file sai định dạng/quá dung lượng, hủy rồi thử lại. Sau đó gỡ liên kết khỏi bản ghi thử; ảnh gốc dùng chung phải còn. Không dùng ảnh hoặc bản ghi thật để thử xóa vĩnh viễn.

## Tài liệu đối chiếu

Spatie lưu ý chiến lược xóa mặc định có thể xóa cả thư mục; project dùng FileBaseFileRemover cho legacy folder dùng chung: [custom removal strategy](https://spatie.be/docs/laravel-medialibrary/v11/advanced-usage/using-a-custom-file-removal-strategy).

Robots không phải cơ chế bảo mật; quyền admin vẫn được middleware bảo vệ. [Google robots.txt](https://developers.google.com/crawling/docs/robots-txt/create-robots-txt).
