<?php

return [
    'max_pixels' => 40000000,
    'pending_hours' => 48,
    'gallery_batch_limit' => 12,
    'webp_quality' => 90,
    'thumbnail_width' => 320,
    'queue' => 'media',
    // Explicitly recognized previous hosts, never rewrite arbitrary external URLs.
    'legacy_hosts' => array_filter(explode(',', env('MEDIA_LEGACY_HOSTS', 'dulich1.test,hgtrip.vn,www.hgtrip.vn'))),
];
