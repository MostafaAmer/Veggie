<?php
// app/Http/Controllers/Api/V1/CategoryController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\RebuildCategoryTreeRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProductResource;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $service) 
    {
        $this->authorizeResource(Category::class, 'category');
        $this->middleware('auth:sanctum')->except(['index', 'show', 'tree', 'featured']);
    }

    public function index(): AnonymousResourceCollection
    {
        $cats = $this->service->getAllCategoriesWithRelations();
        return CategoryResource::collection($cats);
    }

    public function tree(): AnonymousResourceCollection
    {
        $cats = $this->service->getTree();
        return CategoryResource::collection($cats);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $cat = $this->service->createCategory($request->validated());

        return CategoryResource::make($cat)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        $cat = $this->service->loadFullRelations($category);
        return CategoryResource::make($cat);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->service->updateCategory($category, $request->validated());

        return CategoryResource::make($category)
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->service->deleteCategory($category);
        return response()->json(null, 204);
    }

    public function featured(): AnonymousResourceCollection
    {
        $cats = $this->service->getFeaturedCategories();
        return CategoryResource::collection($cats);
    }

    public function updateTree(RebuildCategoryTreeRequest $request): JsonResponse
    {
        $this->service->rebuildTree($request->input('tree'));

        return response()->json([
            'message' => 'تم تحديث هيكل التصنيفات بنجاح',
        ]);
    }
}