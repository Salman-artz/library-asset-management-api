<?php

namespace App\Repositories\Eloquent;

use App\Models\Fine;
use App\Repositories\Contracts\FineRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class EloquentFineRepository implements FineRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Fine::query()->with('borrowing.user', 'borrowing.collection');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id'])) {
            $query->whereHas('borrowing', fn ($q) => $q->where('user_id', $filters['user_id']));
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ?Fine
    {
        return Fine::query()->with('borrowing.user', 'borrowing.collection')->find($id);
    }

    public function markAsPaid(Fine $fine): Fine
    {
        $fine->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        return $fine->fresh()->load('borrowing.user', 'borrowing.collection');
    }
}
