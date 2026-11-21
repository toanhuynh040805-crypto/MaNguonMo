<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

/** Customer-facing order access. Admin pages are guarded by the role middleware. */
class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
