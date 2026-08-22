<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Destination;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TravelMoment;
use App\Models\User;
use Spatie\Permission\Models\Role;

final class AdminIndexRegistry
{
    /**
     * The single contract shared by admin index views, bulk actions and reorder.
     * Keep resource names stable: they are also posted by the admin toolbar.
     */
    private const DEFINITIONS = [
        'destination' => [
            'model' => Destination::class,
            'table' => 'destinations',
            'label' => 'điểm đến',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Điểm đến đã xóa không thể khôi phục.',
        ],
        'travel_moment' => [
            'model' => TravelMoment::class,
            'table' => 'travel_moments',
            'label' => 'khoảnh khắc',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Khoảnh khắc đã xóa không thể khôi phục.',
            'order_column' => 'sort_order',
            'reorder_filters' => [],
        ],
        'tour_category' => [
            'model' => TourCategory::class,
            'table' => 'tour_categories',
            'label' => 'danh mục tour',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Loại hình tour đã xóa không thể khôi phục.',
            'order_column' => 'sort_order',
            'reorder_filters' => ['search', 'status', 'home'],
        ],
        'tour' => [
            'model' => Tour::class,
            'table' => 'tours',
            'label' => 'tour',
            'actions' => ['publish' => 'Xuất bản & nhận booking', 'unpublish' => 'Chuyển về nháp', 'delete' => 'Xóa'],
            'status_updates' => [
                'publish' => [
                    'attributes' => ['status' => 'published', 'is_active' => true, 'booking_open' => true],
                    'message' => 'Đã xuất bản và mở nhận booking cho các tour được chọn.',
                ],
                'unpublish' => [
                    'attributes' => ['status' => 'draft', 'is_active' => false, 'booking_open' => false],
                    'message' => 'Đã chuyển các tour được chọn về nháp và tắt nhận booking.',
                ],
            ],
            'delete_warning' => 'Dữ liệu tour đã xóa không thể khôi phục.',
            'order_column' => 'sort_order',
            'reorder_filters' => ['search', 'status', 'active'],
        ],
        'booking' => [
            'model' => Booking::class,
            'table' => 'bookings',
            'label' => 'booking',
            'actions' => ['confirm' => 'Xác nhận', 'cancel' => 'Hủy', 'complete' => 'Hoàn tất'],
            'status_updates' => [
                'confirm' => ['value' => 'confirmed', 'message' => 'Đã xác nhận các booking được chọn.'],
                'cancel' => ['value' => 'cancelled', 'message' => 'Đã hủy các booking được chọn.'],
                'complete' => ['value' => 'completed', 'message' => 'Đã hoàn tất các booking được chọn.'],
            ],
        ],
        'service_category' => [
            'model' => ServiceCategory::class,
            'table' => 'service_categories',
            'label' => 'danh mục dịch vụ',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Chỉ xóa được danh mục chưa có dịch vụ liên kết.',
            'order_column' => 'sort_order',
            'reorder_filters' => ['search', 'status'],
        ],
        'service' => [
            'model' => Service::class,
            'table' => 'services',
            'label' => 'dịch vụ',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Dịch vụ đã xóa không thể khôi phục.',
            'order_column' => 'sort_order',
            'reorder_filters' => ['search', 'category', 'status'],
        ],
        'page' => [
            'model' => Page::class,
            'table' => 'pages',
            'label' => 'trang',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Trang sẽ được đưa vào thùng rác.',
            'order_column' => 'sort_order',
            'reorder_filters' => ['search', 'status', 'per_page'],
        ],
        'post_category' => [
            'model' => PostCategory::class,
            'table' => 'post_categories',
            'label' => 'danh mục bài viết',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Danh mục sẽ được đưa vào thùng rác.',
            'order_column' => 'sort_order',
            'reorder_filters' => ['search', 'per_page'],
        ],
        'post' => [
            'model' => Post::class,
            'table' => 'posts',
            'label' => 'bài viết',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Bài viết sẽ được đưa vào thùng rác.',
        ],
        'slider' => [
            'model' => Slider::class,
            'table' => 'sliders',
            'label' => 'slider',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Slider và các slide liên quan sẽ bị xóa.',
        ],
        'testimonial' => [
            'model' => Testimonial::class,
            'table' => 'testimonials',
            'label' => 'cảm nhận',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Cảm nhận đã xóa không thể khôi phục.',
            'order_column' => 'sort_order',
            'reorder_filters' => [],
        ],
        'promotion' => [
            'model' => Promotion::class,
            'table' => 'promotions',
            'label' => 'ưu đãi',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Ưu đãi đã xóa không thể khôi phục.',
        ],
        'coupon' => [
            'model' => Coupon::class,
            'table' => 'coupons',
            'label' => 'mã giảm giá',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Mã giảm giá đã xóa không thể khôi phục.',
        ],
        'menu' => [
            'model' => Menu::class,
            'table' => 'menus',
            'label' => 'menu',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt', 'delete' => 'Xóa'],
            'delete_warning' => 'Các mục menu liên quan cũng sẽ bị ảnh hưởng.',
        ],
        'user' => [
            'model' => User::class,
            'table' => 'users',
            'label' => 'tài khoản',
            'actions' => ['activate' => 'Kích hoạt', 'deactivate' => 'Ngừng kích hoạt'],
        ],
        'role' => [
            'model' => Role::class,
            'table' => 'roles',
            'label' => 'vai trò',
            'actions' => [],
        ],
    ];

    public static function definition(string $resource): array
    {
        return self::DEFINITIONS[$resource] ?? [];
    }

    public static function resources(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    public static function bulkActionsFor(string $resource): array
    {
        return self::definition($resource)['actions'] ?? [];
    }

    public static function tableFor(string $resource): string
    {
        return (string) (self::definition($resource)['table'] ?? '');
    }

    public static function modelFor(string $resource): ?string
    {
        return self::definition($resource)['model'] ?? null;
    }

    public static function labelFor(string $resource): string
    {
        return (string) (self::definition($resource)['label'] ?? 'bản ghi');
    }

    public static function deleteWarningFor(string $resource): string
    {
        return (string) (self::definition($resource)['delete_warning'] ?? 'Dữ liệu đã xóa không thể khôi phục.');
    }

    public static function statusUpdatesFor(string $resource): array
    {
        return self::definition($resource)['status_updates'] ?? [];
    }

    public static function reorderResources(): array
    {
        return array_values(array_filter(
            self::resources(),
            fn (string $resource): bool => filled(self::definition($resource)['order_column'] ?? null),
        ));
    }

    public static function orderColumnFor(string $resource): ?string
    {
        return self::definition($resource)['order_column'] ?? null;
    }

    public static function reorderFiltersFor(string $resource): array
    {
        return self::definition($resource)['reorder_filters'] ?? [];
    }

    public static function formIdFor(string $resource): string
    {
        return 'admin-bulk-'.$resource.'-form';
    }
}
