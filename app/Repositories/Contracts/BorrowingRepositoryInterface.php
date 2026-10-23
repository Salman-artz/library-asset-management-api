<?php

namespace App\Repositories\Contracts;

use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BorrowingRepositoryInterface
{
    public function getBorrowings(User $user, array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Borrowing;

    public function create(array $data): Borrowing;

    public function countActiveByUserId(int $userId): int;

    public function hasUnpaidFines(int $userId): bool;
}
