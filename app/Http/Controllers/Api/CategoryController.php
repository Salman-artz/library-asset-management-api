<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function index(Request $request)
    {
        $categories = $this->categoryService->getPaginated(
            $request->search,
            (int) ($request->per_page ?? 20)
        );

        return ApiResponse::success(CategoryResource::collection($categories), 'Daftar kategori');
    }

    public function show(Category $category)
    {
        $data = $this->categoryService->getById($category->id);

        return ApiResponse::success(new CategoryResource($data), 'Detail kategori');
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->create($request->validated());

        return ApiResponse::success(
            new CategoryResource($category),
            'Kategori berhasil dibuat',
            201
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $updated = $this->categoryService->update($category, $request->validated());

        return ApiResponse::success(
            new CategoryResource($updated),
            'Kategori berhasil diperbarui'
        );
    }

    public function destroy(Category $category)
    {
        try {
            $this->categoryService->delete($category);

            return ApiResponse::success(null, 'Kategori berhasil dihapus');
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 409);
        }
    }
}
