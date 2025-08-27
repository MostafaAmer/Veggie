<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'code'         => $this->code,
            'amount' => [
                'raw'       => $this->value,
                'formatted' => $this->formatted_value,
            ],
            'type' => [
                'code'  => $this->type->value,
                'label' => $this->type->label(),
            ],
            'scope' => [
                'code'       => $this->scope->value,
                'label'      => $this->scope->label(),

                'categories' => CategoryResource::collection(
                    $this->whenLoaded('categories')
                ),
                'products'   => ProductResource::collection(
                    $this->whenLoaded('products')
                ),
            ],
            'conditions' => [
                'min_order_value' => [
                    'raw'       => $this->min_order_value,
                    'formatted' => $this->min_order_value > 0
                        ? number_format($this->min_order_value, 2)
                          . ' ' . config('app.currency')
                        : null,
                ],
                'max_discount_amount' => [
                    'raw'       => $this->max_discount_amount,
                    'formatted' => $this->max_discount_amount
                        ? number_format($this->max_discount_amount, 2)
                          . ' ' . config('app.currency')
                        : null,
                ],
            ],
            'validity' => [
                'from'     => optional($this->valid_from)->toIso8601String(),
                'to'       => optional($this->valid_to)->toIso8601String(),
                'period'   => $this->validity_period,
                'is_valid' => $this->is_valid,
            ],
            'usage' => [
                'max_uses'           => $this->max_uses,
                'used_count'         => $this->used_count,
                'remaining_uses'     => $this->remaining_uses,
                'max_uses_per_user'  => $this->max_uses_per_user,
                'is_reusable'        => $this->is_reusable,
            ],
            'timestamps' => [
                'created_at' => $this->created_at->toIso8601String(),
                'updated_at' => $this->updated_at->toIso8601String(),
            ],
        ];
    }
}
