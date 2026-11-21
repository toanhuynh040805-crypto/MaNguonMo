<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route middleware already requires an authenticated customer.
    }

    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^0[0-9]{9,10}$/'],
            'address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return ['phone.regex' => 'Vui lòng nhập số điện thoại Việt Nam hợp lệ.'];
    }
}
