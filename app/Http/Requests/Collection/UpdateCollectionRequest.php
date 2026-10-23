<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $collectionId = $this->route('collection');

        return [
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('collections', 'code')->ignore($collectionId)],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'total_stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'available_stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'condition' => ['sometimes', 'required', 'string', Rule::in(['good', 'fair', 'damaged'])],
            'status' => ['sometimes', 'required', 'string', Rule::in(['available', 'borrowed', 'maintenance', 'lost'])],
        ];
    }
}
