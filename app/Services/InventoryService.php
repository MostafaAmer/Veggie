<?php
namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductNotFoundException;
use InvalidArgumentException;

class InventoryService
{
    public const ACTION_DECREASE = 'decrease';
    public const ACTION_INCREASE = 'increase';

    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @param string                                           $action
     *
     * @throws InvalidArgumentException
     * @throws ProductNotFoundException
     * @throws InsufficientStockException
     */
    public function adjust(array $items, string $action): void
    {
        if (! in_array($action, self::getSupportedActions(), true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid action "%s". Supported actions: %s',
                    $action,
                    implode(', ', self::getSupportedActions())
                )
            );
        }

        DB::transaction(function () use ($items, $action): void 
        {
            $ids = array_column($items, 'product_id');
            $products = Product::whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantity  = (int) $item['quantity'];

                /** @var Product|null $product */
                $product = $products->get($productId);
                if (! $product) {
                    throw new ProductNotFoundException("Product with ID {$productId} not found.");
                }

                if ($action === self::ACTION_DECREASE && $product->stock < $quantity) {
                    throw new InsufficientStockException(
                        "Insufficient stock for product ID {$productId}."
                    );
                }

                if ($action === self::ACTION_DECREASE) {
                    $product->decrement('stock', $quantity);
                } else {
                    $product->increment('stock', $quantity);
                }
            }
        });
    }

    /**
     * @return string[]
     */
    public static function getSupportedActions(): array
    {
        return [
            self::ACTION_DECREASE,
            self::ACTION_INCREASE,
        ];
    }

}