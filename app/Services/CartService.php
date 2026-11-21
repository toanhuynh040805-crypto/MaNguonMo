<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/** Reads the session cart ([product_id => quantity]) and the applied coupon code. */
class CartService
{
    public const SESSION_CART = 'cart';
    public const SESSION_COUPON = 'coupon';

    public function raw(): array
    {
        return Session::get(self::SESSION_CART, []);
    }

    /** @return Collection<int, array{product: Product, quantity: int, subtotal: int}> */
    public function items(?array $cart = null): Collection
    {
        $cart ??= $this->raw();
        $products = Product::with('category')->whereIn('id', array_keys($cart))->get()->keyBy('id');

        return collect($cart)->map(fn ($quantity, $id) => $products->has((int) $id)
            ? ['product' => $products->get((int) $id), 'quantity' => (int) $quantity,
                'subtotal' => $products->get((int) $id)->price * (int) $quantity]
            : null)->filter()->values();
    }

    public function subtotal(): int
    {
        return (int) $this->items()->sum('subtotal');
    }

    public function couponCode(): ?string
    {
        return Session::get(self::SESSION_COUPON);
    }

    /**
     * Applied coupon preview for the cart/checkout pages. A coupon that stopped
     * being valid (expired, cart now too small...) is removed from the session.
     *
     * @return array{coupon: ?Coupon, discount: int, error: ?string}
     */
    public function couponPreview(int $subtotal): array
    {
        $code = $this->couponCode();
        if ($code === null) {
            return ['coupon' => null, 'discount' => 0, 'error' => null];
        }

        $coupon = Coupon::findByCode($code);
        $error = $coupon === null ? 'Mã giảm giá không tồn tại.' : $coupon->unavailableReason($subtotal);
        if ($error !== null) {
            Session::forget(self::SESSION_COUPON);
            return ['coupon' => null, 'discount' => 0, 'error' => "Đã gỡ mã {$code}: {$error}"];
        }

        return ['coupon' => $coupon, 'discount' => $coupon->discountFor($subtotal), 'error' => null];
    }

    public function clear(): void
    {
        Session::forget([self::SESSION_CART, self::SESSION_COUPON]);
    }
}
