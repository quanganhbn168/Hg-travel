<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SiteAsset extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const COLLECTIONS = ['logo', 'favicon', 'seo_image', 'homepage_hero', 'about_image'];

    protected $fillable = ['key'];

    public static function current(): self
    {
        return self::firstOrCreate(['key' => 'site']);
    }

    public function registerMediaCollections(): void
    {
        foreach (self::COLLECTIONS as $collection) {
            $this->addMediaCollection($collection)->singleFile()->useDisk('public_media');
        }

        $this->addMediaCollection('library')->useDisk('public_media');
    }
}
