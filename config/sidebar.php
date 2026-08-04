<?php

return [
    'menu' => [
        ['type' => 'link', 'label' => 'Tổng quan', 'route' => 'admin.dashboard', 'icon' => 'bi bi-speedometer2'],
        ['type' => 'header', 'label' => 'DU LỊCH'],
        ['type' => 'link', 'label' => 'Điểm đến', 'route' => 'admin.destinations.index', 'icon' => 'bi bi-geo-alt'],
        ['type' => 'link', 'label' => 'Danh mục tour', 'route' => 'admin.tour-categories.index', 'icon' => 'bi bi-diagram-3'],
        ['type' => 'link', 'label' => 'Tour du lịch', 'route' => 'admin.tours.index', 'icon' => 'bi bi-map'],
        ['type' => 'header', 'label' => 'ĐẶT TOUR'],
        ['type' => 'link', 'label' => 'Booking', 'route' => 'admin.bookings.index', 'icon' => 'bi bi-calendar-check'],
        ['type' => 'link', 'label' => 'Thanh toán', 'route' => 'admin.payments.index', 'icon' => 'bi bi-wallet2'],
        ['type' => 'header', 'label' => 'NỘI DUNG'],
        ['type' => 'link', 'label' => 'Trang tĩnh', 'route' => 'admin.pages.index', 'icon' => 'bi bi-file-earmark-richtext'],
        ['type' => 'link', 'label' => 'Bài viết', 'route' => 'admin.posts.index', 'icon' => 'bi bi-newspaper'],
        ['type' => 'link', 'label' => 'Slider', 'route' => 'admin.sliders.index', 'icon' => 'bi bi-images'],
        ['type' => 'link', 'label' => 'Menu website', 'route' => 'admin.menus.index', 'icon' => 'bi bi-menu-button-wide'],
        ['type' => 'header', 'label' => 'HỆ THỐNG'],
        ['type' => 'link', 'label' => 'Tài khoản admin', 'route' => 'admin.users.index', 'icon' => 'bi bi-shield-lock'],
        ['type' => 'link', 'label' => 'Vai trò & phân quyền', 'route' => 'admin.roles.index', 'icon' => 'bi bi-person-workspace'],
        ['type' => 'link', 'label' => 'Cài đặt', 'route' => 'admin.settings.general', 'icon' => 'bi bi-gear'],
    ],
];
