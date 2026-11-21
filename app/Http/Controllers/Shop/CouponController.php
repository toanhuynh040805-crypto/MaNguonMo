<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Rules\ValidCoupon;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request, CartService $cart): RedirectResponse
    {
        $subtotal = $cart->subtotal();
        if ($subtotal === 0) {
            return back()->withErrors(['coupon' => 'Giỏ hàng đang trống.']);
        }

        $data = $request->validate([
            'coupon' => ['required', 'string', 'max:40', new ValidCoupon($subtotal)],
        ], [], ['coupon' => 'mã giảm giá']);

        $coupon = Coupon::findByCode($data['coupon']);
        $request->session()->put(CartService::SESSION_COUPON, $coupon->code);

        return back()->with('success', "Đã áp dụng mã {$coupon->code}: giảm "
            . number_format($coupon->discountFor($subtotal), 0, ',', '.') . '₫.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $request->session()->forget(CartService::SESSION_COUPON);
        return back()->with('success', 'Đã gỡ mã giảm giá.');
    }
}
