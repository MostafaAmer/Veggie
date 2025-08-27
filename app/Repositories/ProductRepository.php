<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    public function paginateFiltered(array $filters, int $perPage, array $relations = []): LengthAwarePaginator
    {
        $query = Product::query()
            ->approved()
            ->active()
            ->filter($filters);

        if ($relations) {
            $query->with($relations);
        }

        return $query->paginate($perPage);
    }

    public function findRelated(Product $product, int $limit): Collection
    {
        return Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->approved()
            ->active()
            ->inStock()
            ->with('mainImage')
            ->limit($limit)
            ->get();
    }

    public function bestSellers(int $limit): Collection
    {
       return Cache::remember('products.bestsellers', now()->addMinutes(10), function () use ($limit) {
            return Product::approved()
                ->active()
                ->orderByDesc('sold_count')
                ->with('mainImage')
                ->limit($limit)
                ->get();
        });
    }
}
