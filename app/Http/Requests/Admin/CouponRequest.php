<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => Str::upper(trim((string) $this->input('code')))]);
    }

    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return [
            'code' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('coupons', 'code')->ignore($coupon instanceof Coupon ? $coupon->id : null)],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Coupon::TYPES))],
            'value' => ['required', 'integer', 'min:1',
                $this->input('type') === 'percent' ? 'max:100' : 'max:999999999'],
            'max_discount' => ['nullable', 'integer', 'min:1000', 'max:999999999'],
            'min_order_amount' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'mã', 'type' => 'loại giảm', 'value' => 'mức giảm', 'max_discount' => 'giảm tối đa',
            'min_order_amount' => 'đơn tối thiểu', 'usage_limit' => 'số lượt dùng',
            'starts_at' => 'ngày bắt đầu', 'expires_at' => 'ngày hết hạn',
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Mã chỉ gồm chữ không dấu, số, gạch ngang hoặc gạch dưới.',
            'value.max' => $this->input('type') === 'percent' ? 'Mức giảm theo % tối đa là 100.' : 'Mức giảm quá lớn.',
        ];
    }

    public function couponData(): array
    {
        $data = $this->validated();
        $data['min_order_amount'] ??= 0;
        // A cap only makes sense for percentage coupons.
        if ($data['type'] !== 'percent') {
            $data['max_discount'] = null;
        }

        return $data;
    }
}
