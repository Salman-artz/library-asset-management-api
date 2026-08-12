<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:collections,code'],
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'total_stock' => ['required', 'integer', 'min:0'],
            'condition' => ['required', 'string', Rule::in(['good', 'fair', 'damaged'])],
            'status' => ['required', 'string', Rule::in(['available', 'borrowed', 'maintenance', 'lost'])],
        ];
    }
}

