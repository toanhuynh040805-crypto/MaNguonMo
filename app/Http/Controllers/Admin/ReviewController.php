<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $reviews = Review::with(['user:id,name,email', 'product:id,name,slug'])
            ->when($filters['rating'] ?? null, fn (Builder $q, $rating) => $q->where('rating', $rating))
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where(fn (Builder $sub) => $sub
                ->where('comment', 'like', '%' . $term . '%')
                ->orWhereHas('product', fn (Builder $p) => $p->where('name', 'like', '%' . $term . '%'))))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();
        return back()->with('success', 'Đã xóa đánh giá.');
    }
}
