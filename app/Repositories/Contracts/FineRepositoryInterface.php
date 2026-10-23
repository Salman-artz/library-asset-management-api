<?php

namespace App\Repositories\Contracts;

use App\Models\Fine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FineRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Fine;

    public function markAsPaid(Fine $fine): Fine;
}
