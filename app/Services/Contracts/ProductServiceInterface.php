<?php
declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface ProductServiceInterface
{
    public function getFilteredProducts(array $filters): LengthAwarePaginator;
    public function createProduct(array $data, int $userId): Product;
    public function updateProduct(Product $product, array $data): void;
    public function deleteProduct(Product $product): void;
    public function updateInventory(Product $product, array $data): void;
    public function getRelatedProducts(Product $product, int $limit = 4): \Illuminate\Support\Collection;
    public function getBestSellers(int $limit = 10): \Illuminate\Support\Collection;
}
