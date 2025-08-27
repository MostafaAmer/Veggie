<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'product'       => [
                'id'      => $this->product_id,
                'name'    => $this->product_name,
                'image'   => $this->product_image,
                'slug'    => optional($this->product)->slug,
                'variant' => $this->variant ? [
                    'id'   => $this->variant_id,
                    'name' => $this->variant->name,
                    'sku'  => $this->variant->sku,
                ] : null,
            ],
            'quantity'      => $this->quantity,
            'unit_price'    => $this->formatted_price,
            'line_total'    => $this->formatted_total,
            'price'         => [
                'original'     => $this->formatted_original_price,
                'discount'     => $this->formatted_discount,
                'refund_amount'=> $this->formatted_refund_amount,
            ],
            'status'         => [
                'code'       => $this->status,
                'label'      => $this->status_label,
                'can_return' => $this->canReturn(),
            ],
            'attributes'     => $this->attributes,
            'custom_options' => $this->custom_options,
            'notes'          => $this->notes,
            'created_at'     => $this->created_at->toIso8601String(),
        ];
    }
}
