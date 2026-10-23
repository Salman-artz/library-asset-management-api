<?php

namespace App\Repositories\Eloquent;

use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\User;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentBorrowingRepository implements BorrowingRepositoryInterface
{
    public function getBorrowings(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Borrowing::query()->with(['user', 'collection.category', 'fine']);

        if ($user->role === 'member') {
            $query->where('user_id', $user->id);
        } elseif (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ?Borrowing
    {
        return Borrowing::query()->with(['user', 'collection.category', 'fine'])->find($id);
    }

    public function create(array $data): Borrowing
    {
        return Borrowing::create($data);
    }

    public function countActiveByUserId(int $userId): int
    {
        return Borrowing::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->count();
    }

    public function hasUnpaidFines(int $userId): bool
    {
        return Fine::query()
            ->whereHas('borrowing', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 'unpaid')
            ->exists();
    }
}
