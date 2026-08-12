<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\LibraryCollection */
class CollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'cover_image_path' => $this->cover_image_path,
            'cover_image_url' => $this->cover_image_path
                ? asset('storage/' . $this->cover_image_path)
                : null,
            'total_stock' => $this->total_stock,
            'available_stock' => $this->available_stock,
            'condition' => $this->condition,
            'status' => $this->status,
            'borrowings_count' => $this->when(isset($this->borrowings_count), $this->borrowings_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

