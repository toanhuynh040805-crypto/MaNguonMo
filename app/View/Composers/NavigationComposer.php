<?php

namespace App\View\Composers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Supplies the header's quick category links. Cached because the layout is
 * rendered on every page; Category model events clear the cache on change.
 */
class NavigationComposer
{
    public const CACHE_KEY = 'openshop.nav-categories';

    /** Slug => icon, in display order. Slugs are stable (see HasUniqueSlug). */
    public const MAIN_CATEGORIES = [
        'dien-thoai' => 'smartphone',
        'laptop' => 'laptop',
        'may-tinh-bang' => 'tablet',
        'dong-ho-thong-minh' => 'watch',
        'am-thanh' => 'headphones',
        'phu-kien' => 'cable',
    ];

    public function compose(View $view): void
    {
        $categories = Cache::rememberForever(self::CACHE_KEY, fn () => Category::query()
            ->whereIn('slug', array_keys(self::MAIN_CATEGORIES))
            ->get(['id', 'name', 'slug'])
            ->sortBy(fn (Category $category) => array_search($category->slug, array_keys(self::MAIN_CATEGORIES), true))
            ->values());

        $view->with([
            'navCategories' => $categories,
            'navIcons' => self::MAIN_CATEGORIES,
        ]);
    }
}
