<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'sort' => ['nullable', 'in:latest,price_asc,price_desc,rating'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:999999999', 'gte:min_price'],
        ], ['max_price.gte' => 'Giá đến phải lớn hơn hoặc bằng giá từ.']);

        $query = Product::query()->with('category')->active()->withRating()
            ->when($data['q'] ?? null, fn (Builder $q, string $term) => $q->where(fn (Builder $sub) => $sub
                ->where('name', 'like', '%' . $term . '%')
                ->orWhere('description', 'like', '%' . $term . '%')))
            ->when($data['category'] ?? null, fn (Builder $q, $category) => $q->where('category_id', $category))
            ->when(isset($data['min_price']), fn (Builder $q) => $q->where('price', '>=', $data['min_price']))
            ->when(isset($data['max_price']), fn (Builder $q) => $q->where('price', '<=', $data['max_price']));

        match ($data['sort'] ?? 'latest') {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'rating' => $query->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count')->orderByDesc('id'),
            default => $query->latest(),
        };

        return view('shop.products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::withCount(['products as active_products_count' => fn ($q) => $q->where('is_active', true)])
                ->orderBy('name')->get(),
            'wishlistIds' => $this->wishlistIds($request),
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load('category')->loadAvg('reviews', 'rating')->loadCount('reviews');
        $related = Product::where('category_id', $product->category_id)
            ->active()->withRating()->whereKeyNot($product->id)->limit(4)->get();

        $user = $request->user();

        return view('shop.products.show', [
            'product' => $product,
            'related' => $related,
            'reviews' => $product->reviews()->with('user:id,name')->latest()->paginate(5, pageName: 'danh-gia'),
            'myReview' => $user ? $product->reviews()->where('user_id', $user->id)->first() : null,
            'canReview' => $user?->can('review', $product) ?? false,
            'wishlistIds' => $this->wishlistIds($request),
        ]);
    }

    /** IDs of the customer's saved products, for the heart buttons on cards. */
    private function wishlistIds(Request $request): array
    {
        $user = $request->user();
        return $user && ! $user->isAdmin() ? $user->wishlist()->pluck('products.id')->all() : [];
    }
}
