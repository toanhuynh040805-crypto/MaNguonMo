<?php

namespace App\Rules;

use App\Models\Coupon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Validates that a coupon code exists and can be applied to the given subtotal. */
class ValidCoupon implements ValidationRule
{
    public function __construct(private int $subtotal)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $coupon = Coupon::findByCode(is_string($value) ? $value : null);

        if ($coupon === null) {
            $fail('Mã giảm giá không tồn tại.');
            return;
        }

        if ($reason = $coupon->unavailableReason($this->subtotal)) {
            $fail($reason);
        }
    }
}
