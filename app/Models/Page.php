<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasSlug, SoftDeletes;

    protected $fillable = ['template', 'name', 'slug', 'sub_title', 'content', 'seo_title', 'seo_description', 'seo_keywords', 'is_active', 'sort_order', 'published_at'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'published_at' => 'datetime'];
    }
}
