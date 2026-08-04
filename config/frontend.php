<?php

return [
    'cache' => [
        'menus' => [
            'enabled' => (bool) env('FRONTEND_MENU_CACHE_ENABLED', true),
            'ttl' => (int) env('FRONTEND_MENU_CACHE_TTL', 3600),
        ],
    ],
];
