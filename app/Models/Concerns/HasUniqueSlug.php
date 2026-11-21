<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Generates a slug from "name" once, when the record is created.
 *
 * The slug is a permanent identifier: product URLs, the bundled demo photos
 * (public/images/real-products/{slug}.webp) and the storefront navigation all
 * depend on it, so renaming a record must never silently change it.
 */
trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::creating(function ($model): void {
            if (blank($model->slug)) {
                $model->slug = $model->uniqueSlugFrom((string) $model->name);
            }
        });
    }

    public function uniqueSlugFrom(string $source): string
    {
        $base = Str::slug($source) ?: Str::lower(class_basename($this));
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
