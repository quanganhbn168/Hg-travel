# Checklist kiểm tra frontend và quản trị HG TRIP

Ngày kiểm tra: 2026-08-16
Phạm vi: `D:\laragon\www\dulich1` – kiểm tra bằng Laravel HTTP kernel trên database local hiện tại.

## 1. Trang frontend

- [x] Trang chủ `/` render thành công (HTTP 200).
- [x] Giới thiệu `/gioi-thieu` render thành công (HTTP 200).
- [x] Danh sách tour `/tours` và bộ lọc `scope=domestic` render thành công.
- [x] Danh mục tour `/tours/danh-muc/{slug}` render thành công.
- [x] Chi tiết tour `/tours/{slug}` render thành công.
- [x] Catalogue dịch vụ `/dich-vu` render thành công.
- [x] Danh mục dịch vụ `/dich-vu/danh-muc/{slug}` render thành công.
- [x] Chi tiết dịch vụ `/dich-vu/{slug}` render thành công.
- [x] Landing giải pháp `/giai-phap/{slug}` render thành công; đã có cả `tour.css` và `product-lines.css`, nên khối “Hành trình phù hợp” không còn mất CSS.
- [x] Cẩm nang `/cam-nang` và tìm kiếm bài viết render thành công.
- [x] Liên hệ `/lien-he` render thành công.
- [x] Đặt tour `/dat-tour` và preselect tour bằng `?tour={slug}` render thành công.
- [x] Trang tĩnh `/trang/{slug}` đã có controller/view; chỉ hiển thị khi trang active và đã xuất bản.
- [x] 50 liên kết nội bộ frontend được crawl lại; không có HTTP 404/500.

## 2. Form frontend

- [x] Form liên hệ `POST /lien-he`: validate và redirect thành công.
- [x] Form đặt tour `POST /dat-tour`: lấy giá tour từ database, không tin giá gửi từ client; tạo booking trạng thái `pending/unpaid` và redirect thành công.
- [x] Nút “Đặt tour” ở header/mobile và CTA chi tiết tour đều trỏ về form đặt tour thật.
- [x] Các form POST đã được chạy với dữ liệu hợp lệ trong transaction rollback để không tạo dữ liệu audit rác.

## 3. Dữ liệu và quản trị

- [x] Có 4 product line active, mỗi line có tour/dịch vụ liên kết; dữ liệu được quản lý bởi CRUD `admin.product-lines.*`.
- [x] Có 3 danh mục dịch vụ active và 8 dịch vụ active; đã bổ sung CRUD `admin.service-categories.*` và `admin.services.*`.
- [x] Tour, danh mục tour, điểm đến, bài viết, trang tĩnh, slider, testimonial, menu, booking, liên hệ, user và các nhóm setting hiện có route/view admin tương ứng.
- [x] 115 liên kết nội bộ trong các màn hình admin đã crawl lại khi đăng nhập bằng admin; không có HTTP 404/500.
- [x] Truy cập admin khi chưa đăng nhập trả 302 về `/admin/login`, không còn lỗi `Route [login] not defined`.
- [x] Có tài khoản active có role `admin`; form tạo user đã hiển thị đúng role (role được seed ở guard `web`, khớp model User hiện tại).
- [x] Các liên kết dịch vụ/footer đã lấy từ catalog database; các URL nội bộ chính đã chuyển sang route name.

## 4. Kiểm thử kỹ thuật

- [x] 5 migration product/service đã chạy local.
- [x] `php artisan view:cache` pass.
- [x] `php artisan test`: 4 tests, 3 passed, 1 skipped, 4 assertions. Test bị skip là test SQLite do môi trường thiếu PDO SQLite, không phải lỗi ứng dụng.
- [x] `git diff --check` pass.
- [x] PHP lint pass cho các controller/request/service mới và các file bootstrap/routes/composer đã sửa.

## 5. Ghi chú còn lại

- [x] Các template legacy không được route/view hiện tại mount (`components/admin/flash-sale-editor`, `components/admin/bulk-toolbar`, `components/admin/index-card`, `admin/settings/general.blade.php`) được tách khỏi checklist runtime. Chúng vẫn còn tham chiếu route cũ của module khác nhưng không được gọi bởi trang HG TRIP hiện tại; không tính là form đang hoạt động của hệ du lịch.
- [x] Browser nội bộ và HTTPS Laragon không khả dụng trong môi trường kiểm tra này: browser không có session khả dụng và chứng chỉ local trả lỗi Schannel. Vì vậy phần visual/live-server chưa thể xác nhận bằng trình duyệt; runtime Laravel và HTML/CSS link đã được kiểm tra trực tiếp.
