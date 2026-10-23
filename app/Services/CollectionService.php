<?php

namespace App\Services;

use App\Models\LibraryCollection;
use App\Repositories\Contracts\CollectionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CollectionService
{
    public function __construct(
        private readonly CollectionRepositoryInterface $collectionRepository
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->collectionRepository->getPaginated($filters, $perPage);
    }

    public function getById(int $id): LibraryCollection
    {
        $collection = $this->collectionRepository->findById($id);

        if (! $collection) {
            throw new \RuntimeException('Koleksi tidak ditemukan.');
        }

        return $collection;
    }

    public function create(array $data, ?UploadedFile $coverImage = null): LibraryCollection
    {
        if ($coverImage) {
            $data['cover_image_path'] = $coverImage->store('collections/covers', 'public');
        }

        // Default available stock equals total stock on creation
        $data['available_stock'] = $data['total_stock'];

        return $this->collectionRepository->create($data);
    }

    public function update(LibraryCollection $collection, array $data, ?UploadedFile $coverImage = null): LibraryCollection
    {
        if ($coverImage) {
            if ($collection->cover_image_path) {
                Storage::disk('public')->delete($collection->cover_image_path);
            }
            $data['cover_image_path'] = $coverImage->store('collections/covers', 'public');
        }

        return $this->collectionRepository->update($collection, $data);
    }

    public function delete(LibraryCollection $collection): void
    {
        if ($collection->borrowings()->whereIn('status', ['borrowed', 'overdue'])->exists()) {
            throw new \DomainException('Koleksi tidak dapat dihapus karena sedang dipinjam.');
        }

        if ($collection->cover_image_path) {
            Storage::disk('public')->delete($collection->cover_image_path);
        }

        $this->collectionRepository->delete($collection);
    }
}
