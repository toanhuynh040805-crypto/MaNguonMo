<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request, CartService $cart): View|RedirectResponse
    {
        $raw = $cart->raw();
        if ($raw === []) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Giỏ hàng đang trống.']);
        }
        $items = $cart->items($raw);

        if ($items->count() !== count($raw) || $items->contains(fn ($item) => ! $item['product']->is_active || $item['quantity'] > $item['product']->stock)) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Giỏ hàng có sản phẩm hết hàng hoặc ngừng bán. Hãy kiểm tra lại.']);
        }

        $subtotal = (int) $items->sum('subtotal');
        $coupon = $cart->couponPreview($subtotal);

        return view('shop.checkout.create', [
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $coupon['coupon'],
            'discount' => $coupon['discount'],
            'couponError' => $coupon['error'],
            'total' => $subtotal - $coupon['discount'],
        ]);
    }

    public function store(CheckoutRequest $request, OrderService $orders, CartService $cart): RedirectResponse
    {
        $raw = $cart->raw();
        if ($raw === []) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Giỏ hàng đang trống.']);
        }

        $order = $orders->placeOrder($request->user(), $raw, $request->validated(), $cart->couponCode());

        $cart->clear();

        // Commit order + stock first; email failure cannot roll back checkout.
        $emailAccepted = $orders->notify($order, null, $request->user());

        return redirect()->route('orders.show', $order)->with(
            'success',
            'Đặt hàng thành công! Thanh toán khi nhận hàng (COD).' .
                ($emailAccepted ? ' Đã xử lý thông báo email.' : ' Chưa gửi được email; đơn hàng vẫn được lưu.')
        );
    }
}
