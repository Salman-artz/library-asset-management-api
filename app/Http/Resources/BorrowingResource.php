<?php

namespace App\Http\Resources;

use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Borrowing */
class BorrowingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'collection' => new CollectionResource($this->whenLoaded('collection')),
            'borrowed_at' => $this->borrowed_at,
            'due_date' => $this->due_date,
            'returned_at' => $this->returned_at,
            'status' => $this->status,
            'fine' => new FineResource($this->whenLoaded('fine')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
