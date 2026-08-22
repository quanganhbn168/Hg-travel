<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'service_category_id', 'name', 'slug', 'icon', 'description', 'intro', 'benefits',
        'cover_image', 'seo_title', 'seo_description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['benefits' => 'array', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

}
