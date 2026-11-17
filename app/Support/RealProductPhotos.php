<?php

namespace App\Support;

class RealProductPhotos
{
    public static function all(): array
    {
        static $items = null;
        if ($items === null) {
            $path = resource_path('data/real_product_photos.json');
            $items = is_file($path) ? json_decode(file_get_contents($path), true) : [];
        }
        return $items;
    }

    public static function forSlug(string $slug): ?array
    {
        foreach (self::all() as $photo) {
            if ($photo['slug'] === $slug) {
                return $photo;
            }
        }
        return null;
    }

    public static function localUrl(string $slug): ?string
    {
        if (! preg_match('/\\A[a-z0-9]+(?:-[a-z0-9]+)*\\z/D', $slug)) {
            return null;
        }

        // Only a seeded, attributed photo may be selected by product slug.
        if (self::forSlug($slug) === null) {
            return null;
        }

        $relative = 'images/real-products/' . $slug . '.webp';
        return is_file(public_path($relative)) ? asset($relative) : null;
    }
}
