<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Authorization ("review" ability in ProductPolicy) is applied on the route. */
class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [], ['rating' => 'số sao', 'comment' => 'nhận xét']);

        // One review per customer and product: submitting again edits it.
        $review = $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data,
        );

        return redirect()->to(route('products.show', $product->slug) . '#danh-gia')
            ->with('success', $review->wasRecentlyCreated ? 'Cảm ơn bạn đã đánh giá sản phẩm!' : 'Đã cập nhật đánh giá của bạn.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $product->reviews()->where('user_id', $request->user()->id)->delete();

        return redirect()->to(route('products.show', $product->slug) . '#danh-gia')
            ->with('success', 'Đã xóa đánh giá của bạn.');
    }
}
