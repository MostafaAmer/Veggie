<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Product;

interface TagServiceInterface
{
    /**
     * Synchronize the given product’s tags.
     * 
     * This will ensure each tag name in the array exists in the database
     * (creating it if necessary), and then attach the product to those tags.
     *
     * @param  Product   $product
     * @param  string[]  $tagNames
     * @return void
     */
    public function syncTags(Product $product, array $tagNames): void;
}