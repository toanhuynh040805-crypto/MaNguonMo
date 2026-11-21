<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /** Verified-purchase reviews: the customer must have a completed order with this product. */
    public function review(User $user, Product $product): bool
    {
        return ! $user->isAdmin() && $user->hasPurchased($product);
    }
}
