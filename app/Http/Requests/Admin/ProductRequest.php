<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        // Accept "Ten San Pham" style input in the slug box; store it normalized.
        if (filled($this->input('slug'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('slug'))]);
        }
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $ignore = $product instanceof Product ? $product->id : null;

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150', Rule::unique('products', 'name')->ignore($ignore)],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('products', 'slug')->ignore($ignore)],
            'description' => ['nullable', 'string', 'max:3000'],
            'price' => ['required', 'integer', 'min:1000', 'max:999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (blank($this->input('slug')) && Str::slug((string) $this->input('name')) === '' && ! $this->route('product')) {
                $validator->errors()->add('name', 'Tên sản phẩm cần có chữ hoặc số để tạo đường dẫn.');
            }
        }];
    }

    public function attributes(): array
    {
        return ['name' => 'tên sản phẩm', 'slug' => 'đường dẫn', 'category_id' => 'danh mục',
            'price' => 'đơn giá', 'stock' => 'tồn kho', 'image' => 'ảnh sản phẩm'];
    }

    public function messages(): array
    {
        return ['name.unique' => 'Tên sản phẩm đã tồn tại.', 'slug.unique' => 'Đường dẫn đã được sản phẩm khác sử dụng.'];
    }

    /** Data for Product::fill(). A blank slug keeps the current one (or is generated on create). */
    public function productData(): array
    {
        $data = $this->safe()->except(['image', 'remove_image', 'slug']);
        if (filled($this->validated('slug'))) {
            $data['slug'] = $this->validated('slug');
        }

        return $data;
    }
}
