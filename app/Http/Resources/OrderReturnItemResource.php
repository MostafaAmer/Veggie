<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderReturnItemResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $orderItem = $this->orderItem;
        $product   = $orderItem->product;
        return [
            'id'            => $this->id,
            'product'   => [
                'id'    => $product->id,
                'name'  => $product->name,
                'image' => $product->getFirstMediaUrl('images') ?? null,
            ],
            'quantity'      => $this->quantity,
            'condition'     => [
                    'code'  => $this->condition,
                    'label' => $this->condition_label
            ],
            'restocked'     => (bool) $this->restocked,
            'pricing'       => [
                'unit_price' => $orderItem->price,
                'subtotal'   => $orderItem->price * $this->quantity,
            ],
            'created_at'    => $this->created_at->toIso8601String()
        ];
    }
}