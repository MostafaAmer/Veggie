<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\OrderItem;

class ReturnQuantityRule implements Rule
{
    private int $orderId;
    private ?string $messageKey = null;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function passes($attribute, $value): bool
    {
        [$_, $index,] = explode('.', $attribute);
        $itemId       = request()->input("items.$index.order_item_id");

        $orderItem = OrderItem::where('order_id', $this->orderId)
                              ->find($itemId);

        if (! $orderItem) {
            $this->messageKey = 'items.*.order_item_id.exists';
            return false;
        }

        if ($value > $orderItem->quantity) {
            $this->messageKey = 'RETURN_QUANTITY_EXCEEDS';
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->messageKey === 'RETURN_QUANTITY_EXCEEDS'
            ? 'الكمية المطلوبة أكبر من المشتراة'
            : __('validation.exists');
    }
}