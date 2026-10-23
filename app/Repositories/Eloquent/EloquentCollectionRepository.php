<?php

namespace App\Repositories\Eloquent;

use App\Models\LibraryCollection;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCollectionRepository implements CollectionRepositoryInterface
{
    public function getPaginated(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return LibraryCollection::query()
            ->with('category')
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($filters['category_id'] ?? null, fn ($q, $catId) => $q->where('category_id', $catId))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['condition'] ?? null, fn ($q, $condition) => $q->where('condition', $condition))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?LibraryCollection
    {
        return LibraryCollection::query()->with('category')->find($id);
    }

    public function create(array $data): LibraryCollection
    {
        $collection = LibraryCollection::create($data);
        $collection->load('category');

        return $collection;
    }

    public function update(LibraryCollection $collection, array $data): LibraryCollection
    {
        $collection->update($data);
        $collection->load('category');

        return $collection->fresh();
    }

    public function delete(LibraryCollection $collection): bool
    {
        return (bool) $collection->delete();
    }
}
