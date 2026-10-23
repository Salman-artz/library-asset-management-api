<?php

namespace App\Http\Resources;

use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Fine */
class FineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'borrowing' => new BorrowingResource($this->whenLoaded('borrowing')),
            'amount' => $this->amount,
            'days_late' => $this->days_late,
            'daily_rate' => $this->daily_rate,
            'status' => $this->status,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
