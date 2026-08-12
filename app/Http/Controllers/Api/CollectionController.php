<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\StoreCollectionRequest;
use App\Http\Requests\Collection\UpdateCollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\LibraryCollection;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $collections = LibraryCollection::query()
            ->with('category')
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            })
            ->when($request->category_id, fn ($q, $catId) => $q->where('category_id', $catId))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->condition, fn ($q, $condition) => $q->where('condition', $condition))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 12);

        return ApiResponse::success(CollectionResource::collection($collections), 'Daftar koleksi');
    }

    public function show(LibraryCollection $collection)
    {
        $collection->load('category');

        return ApiResponse::success(new CollectionResource($collection), 'Detail koleksi');
    }

    public function store(StoreCollectionRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')
                ->store('collections/covers', 'public');
        }

        // Set default available_stock = total_stock for new collections
        $data['available_stock'] = $data['total_stock'];

        $collection = LibraryCollection::create($data);
        $collection->load('category');

        return ApiResponse::success(
            new CollectionResource($collection),
            'Koleksi berhasil ditambahkan',
            201
        );
    }

    public function update(UpdateCollectionRequest $request, LibraryCollection $collection)
    {
        $data = $request->validated();

        if ($request->hasFile('cover_image')) {
            // Delete old cover if exists
            if ($collection->cover_image_path) {
                Storage::disk('public')->delete($collection->cover_image_path);
            }

            $data['cover_image_path'] = $request->file('cover_image')
                ->store('collections/covers', 'public');
        }

        $collection->update($data);
        $collection->load('category');

        return ApiResponse::success(
            new CollectionResource($collection->fresh()),
            'Koleksi berhasil diperbarui'
        );
    }

    public function destroy(LibraryCollection $collection)
    {
        if ($collection->borrowings()->whereIn('status', ['borrowed', 'overdue'])->exists()) {
            return ApiResponse::error(
                'Koleksi tidak dapat dihapus karena sedang dipinjam.',
                null,
                409
            );
        }

        // Delete cover image if exists
        if ($collection->cover_image_path) {
            Storage::disk('public')->delete($collection->cover_image_path);
        }

        $collection->delete();

        return ApiResponse::success(null, 'Koleksi berhasil dihapus');
    }
}

