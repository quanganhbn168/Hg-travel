<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourCategory extends Model
{
    use HasFactory, HasSlug, SoftDeletes;
    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'cover_image', 'seo_title', 'seo_description', 'sort_order', 'is_active', 'is_home'];
    protected function casts(): array { return ['is_active' => 'boolean', 'is_home' => 'boolean']; }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }
    public function tours(): HasMany { return $this->hasMany(Tour::class); }
}
