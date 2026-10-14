<?php

namespace App\Domains\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FurnitureCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'order',
    ];

    /** @return HasMany<FurnitureItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(FurnitureItem::class, 'category_id')->where('is_active', true);
    }

    /** @return HasMany<FurnitureItem, $this> */
    public function allItems(): HasMany
    {
        return $this->hasMany(FurnitureItem::class, 'category_id');
    }
}
