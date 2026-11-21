<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()->wishlist()
            ->with('category')->withRating()
            ->orderByPivot('created_at', 'desc')
            ->paginate(12);

        return view('shop.wishlist.index', [
            'products' => $products,
            'wishlistIds' => $products->pluck('id')->all(),
        ]);
    }

    /** Adds or removes the product; works with plain forms and the AJAX heart button. */
    public function toggle(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $changes = $request->user()->wishlist()->toggle($product->id);
        $saved = $changes['attached'] !== [];
        $message = $saved ? 'Đã lưu vào danh sách yêu thích.' : 'Đã bỏ khỏi danh sách yêu thích.';

        if ($request->expectsJson()) {
            return response()->json([
                'saved' => $saved,
                'message' => $message,
                'wishlist_count' => $request->user()->wishlist()->count(),
            ]);
        }

        return back()->with('success', $message);
    }
}
