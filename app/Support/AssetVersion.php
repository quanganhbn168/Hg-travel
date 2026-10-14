<?php

namespace App\Support;

class AssetVersion
{
    /**
     * Asset URL with a cache-busting `?v=<mtime>` of the file in /public.
     * A missing file yields the plain URL instead of failing the page.
     */
    public static function url(string $path, ?string $versionFrom = null): string
    {
        $file = public_path($versionFrom ?? $path);
        $version = is_file($file) ? filemtime($file) : false;

        return asset($path).($version ? '?v='.$version : '');
    }
}
