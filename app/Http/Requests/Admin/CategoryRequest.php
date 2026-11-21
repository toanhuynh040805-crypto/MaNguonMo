<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (filled($this->input('slug'))) {
            $this->merge(['slug' => Str::slug((string) $this->input('slug'))]);
        }
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $ignore = $category instanceof Category ? $category->id : null;

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('categories', 'name')->ignore($ignore)],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash:ascii', Rule::unique('categories', 'slug')->ignore($ignore)],
            'description' => ['nullable', 'string', 'max:1500'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (blank($this->input('slug')) && Str::slug((string) $this->input('name')) === '' && ! $this->route('category')) {
                $validator->errors()->add('name', 'Tên danh mục cần có chữ hoặc số để tạo đường dẫn.');
            }
        }];
    }

    public function messages(): array
    {
        return ['name.unique' => 'Tên danh mục đã tồn tại.', 'slug.unique' => 'Đường dẫn đã được danh mục khác sử dụng.'];
    }

    public function categoryData(): array
    {
        $data = $this->safe()->except(['slug']);
        if (filled($this->validated('slug'))) {
            $data['slug'] = $this->validated('slug');
        }

        return $data;
    }
}
