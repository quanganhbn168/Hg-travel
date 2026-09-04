<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['name', 'location', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function items(): HasMany { return $this->hasMany(MenuItem::class)->orderBy('position'); }
    public function topLevelItems(): HasMany { return $this->items()->whereNull('parent_id'); }
}
