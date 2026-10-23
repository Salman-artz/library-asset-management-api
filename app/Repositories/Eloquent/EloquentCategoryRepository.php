<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function getPaginated(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        return Category::query()
            ->withCount('collections')
            ->when($search, fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Category
    {
        return Category::query()->withCount('collections')->find($id);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh()->loadCount('collections');
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }
}
