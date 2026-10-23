<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getPaginated(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->categoryRepository->getPaginated($search, $perPage);
    }

    public function getById(int $id): Category
    {
        $category = $this->categoryRepository->findById($id);

        if (! $category) {
            throw new \RuntimeException('Kategori tidak ditemukan.');
        }

        return $category;
    }

    public function create(array $data): Category
    {
        return $this->categoryRepository->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        return $this->categoryRepository->update($category, $data);
    }

    public function delete(Category $category): void
    {
        if ($category->collections()->exists()) {
            throw new \DomainException('Kategori tidak dapat dihapus karena masih memiliki koleksi terkait.');
        }

        $this->categoryRepository->delete($category);
    }
}
