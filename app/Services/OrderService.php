<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderNeedsAttention;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Single place for every order write that touches inventory.
 * Customers and Admins share the same locking, audit and restock rules.
 */
class OrderService
{
    public function __construct(private OrderEmailService $emails)
    {
    }

    /**
     * Creates a pending COD order from a session cart ([product_id => quantity]).
     * Products are locked so concurrent checkouts cannot oversell stock.
     *
     * @throws ValidationException when the cart no longer matches the inventory
     */
    public function placeOrder(User $customer, array $cart, array $shipping, ?string $couponCode = null): Order
    {
        return DB::transaction(function () use ($customer, $cart, $shipping, $couponCode): Order {
            $products = Product::whereIn('id', array_keys($cart))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($products->count() !== count($cart)) {
                throw ValidationException::withMessages(['cart' => 'Có sản phẩm không còn tồn tại.']);
            }

            $total = 0;
            foreach ($cart as $id => $quantity) {
                $product = $products->get((int) $id);
                if (! $product || ! $product->is_active || ! is_int($quantity) || $quantity < 1 || $quantity > 99 || $product->stock < $quantity) {
                    throw ValidationException::withMessages(['cart' => 'Giỏ hàng đã thay đổi hoặc tồn kho không đủ.']);
                }
                $total += $product->price * $quantity;
            }

            // Re-check the coupon under lock so the usage limit cannot be exceeded
            // by concurrent checkouts.
            $coupon = null;
            $discount = 0;
            if (filled($couponCode)) {
                $coupon = Coupon::where('code', Str::upper(trim($couponCode)))->lockForUpdate()->first();
                $reason = $coupon === null ? 'Mã giảm giá không tồn tại.' : $coupon->unavailableReason($total);
                if ($reason !== null) {
                    throw ValidationException::withMessages(['coupon' => 'Mã giảm giá không còn áp dụng được: ' . $reason]);
                }
                $discount = $coupon->discountFor($total);
                $coupon->increment('used_count');
            }

            $order = Order::create([
                'user_id' => $customer->id,
                'recipient_name' => $shipping['recipient_name'],
                'phone' => $shipping['phone'],
                'address' => $shipping['address'],
                'note' => $shipping['note'] ?? null,
                'status' => 'pending',
                'subtotal_amount' => $total,
                'discount_amount' => $discount,
                'total_amount' => $total - $discount,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
            ]);

            $order->statusHistories()->create([
                'actor_id' => $customer->id,
                'from_status' => null,
                'to_status' => 'pending',
            ]);

            foreach ($cart as $id => $quantity) {
                $product = $products->get((int) $id);
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $product->price * $quantity,
                ]);
                $product->decrement('stock', $quantity);
            }

            return $order;
        }, 3);
    }

    /**
     * Moves an order along Order::TRANSITIONS, writing one audit record and
     * restoring stock exactly once on cancellation.
     *
     * @param  list<string>|null  $allowedFrom  extra restriction on the current status
     *                                          (customers may only cancel pending orders)
     * @return string|null the previous status, or null when the transition was refused
     */
    public function transition(Order $order, string $to, User $actor, ?array $allowedFrom = null): ?string
    {
        return DB::transaction(function () use ($order, $to, $actor, $allowedFrom): ?string {
            // Re-read under lock: the in-memory model may be stale.
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $from = $locked->status;

            if (! in_array($to, Order::TRANSITIONS[$from] ?? [], true)
                || ($allowedFrom !== null && ! in_array($from, $allowedFrom, true))) {
                return null;
            }

            $locked->update([
                'status' => $to,
                'completed_at' => $to === 'completed' ? now() : $locked->completed_at,
            ]);
            $locked->statusHistories()->create([
                'actor_id' => $actor->id,
                'from_status' => $from,
                'to_status' => $to,
            ]);

            if ($to === 'cancelled') {
                foreach ($locked->items as $item) {
                    if ($item->product_id !== null) {
                        Product::whereKey($item->product_id)->increment('stock', $item->quantity);
                    }
                }
                // Give the coupon usage back so another order can use it.
                if ($locked->coupon_id !== null) {
                    Coupon::whereKey($locked->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
                }
            }

            return $from;
        }, 3);
    }

    /**
     * Notifies after the transaction committed: email (queued) and an in-site
     * notification for the customer; Admins hear about new orders and
     * cancellations made by customers.
     *
     * @return bool whether the email was accepted (queued or sent)
     */
    public function notify(Order $order, ?string $previousStatus = null, ?User $actor = null): bool
    {
        $order = $order->fresh(['user', 'items']);

        $order->user?->notify(new OrderStatusChanged($order, $previousStatus));

        $customerAction = $previousStatus === null
            || ($order->status === 'cancelled' && $actor !== null && ! $actor->isAdmin());
        if ($customerAction) {
            Notification::send(User::where('role', 'admin')->get(), new OrderNeedsAttention($order, $previousStatus));
        }

        return $this->emails->send($order, $previousStatus);
    }
}
