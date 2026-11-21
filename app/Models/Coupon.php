<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Coupon extends Model
{
    public const TYPES = [
        'percent' => 'Giảm theo %',
        'fixed' => 'Giảm số tiền',
    ];

    protected $fillable = [
        'code', 'description', 'type', 'value', 'max_discount', 'min_order_amount',
        'usage_limit', 'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer', 'max_discount' => 'integer', 'min_order_amount' => 'integer',
            'usage_limit' => 'integer', 'used_count' => 'integer',
            'starts_at' => 'datetime', 'expires_at' => 'datetime', 'is_active' => 'boolean',
        ];
    }

    /** Codes are case-insensitive for customers: always store them upper-case. */
    protected function code(): Attribute
    {
        return Attribute::set(fn (?string $value) => Str::upper(trim((string) $value)));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public static function findByCode(?string $code): ?self
    {
        $code = Str::upper(trim((string) $code));
        return $code === '' ? null : static::where('code', $code)->first();
    }

    /** Null when usable for this order subtotal, otherwise a message for the customer. */
    public function unavailableReason(int $subtotal): ?string
    {
        return match (true) {
            ! $this->is_active => 'Mã giảm giá đã ngừng áp dụng.',
            $this->starts_at !== null && $this->starts_at->isFuture() => 'Mã giảm giá chưa đến thời gian áp dụng.',
            $this->expires_at !== null && $this->expires_at->isPast() => 'Mã giảm giá đã hết hạn.',
            $this->usage_limit !== null && $this->used_count >= $this->usage_limit => 'Mã giảm giá đã hết lượt sử dụng.',
            $subtotal < $this->min_order_amount => 'Đơn hàng cần tối thiểu ' . number_format($this->min_order_amount, 0, ',', '.') . '₫ để dùng mã này.',
            default => null,
        };
    }

    /** Discount in VND, never more than the subtotal. */
    public function discountFor(int $subtotal): int
    {
        $discount = $this->type === 'percent'
            ? intdiv($subtotal * min($this->value, 100), 100)
            : $this->value;

        if ($this->type === 'percent' && $this->max_discount !== null) {
            $discount = min($discount, $this->max_discount);
        }

        return max(0, min($discount, $subtotal));
    }

    /** e.g. "Giảm 10% (tối đa 500.000₫) · đơn từ 1.000.000₫" */
    public function getSummaryAttribute(): string
    {
        $text = $this->type === 'percent'
            ? 'Giảm ' . $this->value . '%' . ($this->max_discount ? ' (tối đa ' . number_format($this->max_discount, 0, ',', '.') . '₫)' : '')
            : 'Giảm ' . number_format($this->value, 0, ',', '.') . '₫';

        return $this->min_order_amount > 0
            ? $text . ' · đơn từ ' . number_format($this->min_order_amount, 0, ',', '.') . '₫'
            : $text;
    }
}
