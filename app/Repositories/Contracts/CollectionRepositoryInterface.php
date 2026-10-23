<?php

namespace App\Repositories\Contracts;

use App\Models\LibraryCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CollectionRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function findById(int $id): ?LibraryCollection;

    public function create(array $data): LibraryCollection;

    public function update(LibraryCollection $collection, array $data): LibraryCollection;

    public function delete(LibraryCollection $collection): bool;
}
