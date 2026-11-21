<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:40']]);

        $coupons = Coupon::withCount('orders')
            ->withSum(['orders as discount_total' => fn (Builder $q) => $q->where('status', '!=', 'cancelled')], 'discount_amount')
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where('code', 'like', '%' . $term . '%'))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.form', ['coupon' => new Coupon(['type' => 'percent', 'is_active' => true])]);
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        Coupon::create($request->couponData());
        return redirect()->route('admin.coupons.index')->with('success', 'Đã tạo mã giảm giá.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($request->couponData());
        return redirect()->route('admin.coupons.index')->with('success', 'Đã cập nhật mã giảm giá.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        // Orders keep coupon_code and discount_amount, so history survives deletion.
        if ($coupon->used_count > 0) {
            return back()->with('error', 'Mã đã được sử dụng. Hãy tắt mã thay vì xóa để giữ thống kê.');
        }
        $coupon->delete();
        return back()->with('success', 'Đã xóa mã giảm giá.');
    }
}
