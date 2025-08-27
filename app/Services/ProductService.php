<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Repositories\ProductRepository;
use App\Services\Contracts\ImageServiceInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\Contracts\TagServiceInterface;

class ProductService
{
    public function __construct(
        protected ProductRepository     $repo,
        protected ImageServiceInterface $images,
        protected TagServiceInterface   $tags

    ) {}

    public function list(array $filters): LengthAwarePaginator
    {
        $perPage   = (int) ($filters['per_page'] ?? 15);
        $relations = ['category', 'mainImage', 'tags'];

        return $this->repo->paginateFiltered($filters, $perPage, $relations);
    }

    public function create(array $data, int $userId): Product
    {
        return DB::transaction(function () use ($data, $userId) {
            $product = Product::create([
                ...$data,
                'created_by' => $userId,
            ]);

            $this->tags->syncTags($product, $data['tags'] ?? []);
            $this->images->syncImages($product, $data['images'] ?? []);

            return $product->load('category', 'mainImage', 'tags');
        });
    }

    public function update(Product $product, array $data): Product
    {
        DB::transaction(function () use ($product, $data) {
            $product->update($data);

            if (isset($data['tags'])) {
                $this->tags->syncTags($product, $data['tags']);
            }

            if (isset($data['images'])) {
                $this->images->syncImages($product, $data['images']);
            }
        });

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $this->images->deleteAll($product);
            $product->delete();
        });
    }

    public function related(Product $product, int $limit = 4)
    {
        return $this->repo->findRelated($product, $limit);
    }

    public function bestSellers(int $limit = 10)
    {
        return $this->repo->bestSellers($limit);
    }

    public function updateInventory(Product $product, array $data): void
    {
        DB::transaction(function () use ($product, $data) {
            $product->update([
                'stock'              => $data['stock'],
                'min_order_quantity' => $data['min_order_quantity'] ?? 1,
                'max_order_quantity' => $data['max_order_quantity'] ?? null,
            ]);

            $product->inventoryLogs()->create([
                'quantity' => $data['stock'],
                'type'     => 'adjustment',
                'notes'    => $data['notes'] ?? 'Manual adjustment',
            ]);
        });
    }

    /** @return void */
    protected function syncImages(Product $product, array $uploads): void
    {
        if (empty($uploads)) {
            return;
        }

        $product->productImages()->delete();

        foreach ($uploads as $file) {
            ['path' => $path, 'thumbnail' => $thumb] = $this->images->store('products', $file);

            $attachment = $product->productImages()->create([
                'path'           => $path,
                'thumbnail_path' => $thumb,
                'type'           => 'product_image',
                'original_name'  => $file->getClientOriginalName(),
                'mime_type'      => $file->getMimeType(),
                'size'           => $file->getSize(),
            ]);

            if (!$product->main_image_id) {
                $product->update(['main_image_id' => $attachment->id]);
            }
        }
    }

    /** @return void */
    protected function syncTags(Product $product, array $tags): void
    {
        $ids = array_map(fn(string $name) => Tag::firstOrCreate(['name' => $name])->id, $tags);
        $product->tags()->sync($ids);
    }
}