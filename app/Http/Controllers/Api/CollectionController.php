<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\StoreCollectionRequest;
use App\Http\Requests\Collection\UpdateCollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\LibraryCollection;
use App\Services\CollectionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function __construct(
        private readonly CollectionService $collectionService
    ) {}

    public function index(Request $request)
    {
        $collections = $this->collectionService->getPaginated(
            $request->only(['search', 'category_id', 'status', 'condition']),
            (int) ($request->per_page ?? 12)
        );

        return ApiResponse::success(CollectionResource::collection($collections), 'Daftar koleksi');
    }

    public function show(LibraryCollection $collection)
    {
        $data = $this->collectionService->getById($collection->id);

        return ApiResponse::success(new CollectionResource($data), 'Detail koleksi');
    }

    public function store(StoreCollectionRequest $request)
    {
        $collection = $this->collectionService->create(
            $request->validated(),
            $request->file('cover_image')
        );

        return ApiResponse::success(
            new CollectionResource($collection),
            'Koleksi berhasil ditambahkan',
            201
        );
    }

    public function update(UpdateCollectionRequest $request, LibraryCollection $collection)
    {
        $updated = $this->collectionService->update(
            $collection,
            $request->validated(),
            $request->file('cover_image')
        );

        return ApiResponse::success(
            new CollectionResource($updated),
            'Koleksi berhasil diperbarui'
        );
    }

    public function destroy(LibraryCollection $collection)
    {
        try {
            $this->collectionService->delete($collection);

            return ApiResponse::success(null, 'Koleksi berhasil dihapus');
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 409);
        }
    }
}
