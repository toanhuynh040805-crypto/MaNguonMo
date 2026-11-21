<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use App\View\Composers\NavigationComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasUniqueSlug;

    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted(): void
    {
        // The header menu is cached; refresh it whenever a category changes.
        $forget = fn () => Cache::forget(NavigationComposer::CACHE_KEY);
        static::saved($forget);
        static::deleted($forget);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
