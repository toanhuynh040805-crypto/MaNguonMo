<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'stock', 'image_path', 'is_active',
    ];

    protected function casts(): array
    {
        return ['price' => 'integer', 'stock' => 'integer', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    /** Products visible in the storefront. */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** Adds reviews_avg_rating and reviews_count for star displays. */
    public function scopeWithRating(Builder $query): void
    {
        $query->withAvg('reviews', 'rating')->withCount('reviews');
    }

    public function getImageUrlAttribute(): ?string
    {
        // Uploaded photos live in storage/app/public/products and take priority
        // when the file still exists. A missing upload falls back to local artwork
        // rather than leaving the page with a broken image.
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }

        // Seeded demo photos are committed in public/images/real-products.
        // Never depend on Wikimedia being online to display product pictures.
        return \App\Support\RealProductPhotos::localUrl((string) $this->slug);
    }
}
