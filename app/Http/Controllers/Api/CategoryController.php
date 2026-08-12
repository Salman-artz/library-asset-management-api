<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->withCount('collections')
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return ApiResponse::success(CategoryResource::collection($categories), 'Daftar kategori');
    }

    public function show(Category $category)
    {
        $category->loadCount('collections');

        return ApiResponse::success(new CategoryResource($category), 'Detail kategori');
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return ApiResponse::success(
            new CategoryResource($category),
            'Kategori berhasil dibuat',
            201
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return ApiResponse::success(
            new CategoryResource($category->fresh()->loadCount('collections')),
            'Kategori berhasil diperbarui'
        );
    }

    public function destroy(Category $category)
    {
        if ($category->collections()->exists()) {
            return ApiResponse::error(
                'Kategori tidak dapat dihapus karena masih memiliki koleksi terkait.',
                null,
                409
            );
        }

        $category->delete();

        return ApiResponse::success(null, 'Kategori berhasil dihapus');
    }
}

