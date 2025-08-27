<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'product'    => [
                'id'    => $this->product_id,
                'name'  => $this->product->name,
                'price' => (float) $this->price,
            ],
            'quantity'   => $this->quantity,
            'subtotal'   => (float) ($this->price * $this->quantity),
            'metadata'   => $this->metadata,
        ];
    }
}