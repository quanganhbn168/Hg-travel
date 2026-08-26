<?php

return [
    'admin' => [
        'tour_import' => [
            'zip_visible' => (bool) env('ADMIN_TOUR_ZIP_IMPORT_VISIBLE', false),
        ],
    ],
    'cache' => [
        'menus' => [
            'enabled' => (bool) env('FRONTEND_MENU_CACHE_ENABLED', true),
            'ttl' => (int) env('FRONTEND_MENU_CACHE_TTL', 3600),
        ],
    ],
];
