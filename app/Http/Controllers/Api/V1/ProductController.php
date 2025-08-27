<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\Contracts\ProductServiceInterface;

class ProductController extends Controller
{
    public function __construct(
        protected ProductServiceInterface $products
    ) {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginated = $this->products->list(request()->all());
        return ProductResource::collection($paginated);
    }

    public function store(StoreProductRequest $req): JsonResponse
    {
        $product = $this->products->create(
            $req->validated(),
            $req->user()->getKey()
        );

        return ProductResource::make($product)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
       return new ProductResource(
            $product->load(['category','productImages','tags'])
        );
    }

    public function update(UpdateProductRequest $req, Product $product): ProductResource
    {
        $updated = $this->products->update($product, $req->validated());
        return new ProductResource($updated);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->products->deleteProduct($product);
        
        return response()->json([
            'message' => 'تم حذف المنتج بنجاح'
        ]);
    }

     public function related(Product $product): AnonymousResourceCollection
    {
        $related = $this->products->related($product);
        return ProductResource::collection($related);
    }

    public function bestSellers(): AnonymousResourceCollection
    {
        $best = $this->products->bestSellers();
        return ProductResource::collection($best);
    }

    public function updateInventory(Product $product): JsonResponse
    {
        $data = request()->validate([
            'stock'              => 'required|integer|min:0',
            'min_order_quantity' => 'sometimes|integer|min:1',
            'max_order_quantity' => 'nullable|integer|gt:min_order_quantity',
            'notes'              => 'nullable|string|max:255',
        ]);

        $this->products->updateInventory($product, $data);

        return response()->json(['message' => 'تم تحديث المخزون بنجاح']);
    }
}