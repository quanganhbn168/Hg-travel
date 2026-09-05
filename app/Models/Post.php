<?php

namespace App\Models;

use App\Traits\HasManagedImages;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasManagedImages;
    use HasSlug, SoftDeletes;

    protected $fillable = ['post_category_id', 'created_by', 'name', 'slug', 'summary', 'content', 'cover_image', 'seo_title', 'seo_description', 'seo_keywords', 'is_featured', 'is_active', 'view_count', 'published_at'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_active' => 'boolean', 'published_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }
}
