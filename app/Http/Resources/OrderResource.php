<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'reference_number' => $this->reference_number,
            'status'           => $this->status,
            'status_summary'   => [
                'code'       => $this->status,
                'label'      => $this->status_label,
                'can_cancel' => $this->can_cancel,
                'can_return' => $this->can_return,
            ],
            'payment'          => [
                'method'       => $this->payment_method,
                'method_label' => $this->payment_method_label,
                'status'       => $this->payment_status,
                'status_label' => $this->payment_status_label,
                'is_paid'      => $this->is_paid,
            ],
            'amounts'          => [
                'subtotal'     => $this->subtotal,
                'discount'     => $this->discount,
                'delivery_fee' => $this->delivery_fee,
                'tax'          => $this->tax,
                'total'        => $this->total,
            ],
            'customer'         => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],
            'shipping'         => $this->formatAddress(),
            'timestamps'       => [
                'created_at'         => $this->created_at->toIso8601String(),
                'confirmed_at'       => optional($this->confirmed_at)->toIso8601String(),
                'processed_at'       => optional($this->processed_at)->toIso8601String(),
                'shipped_at'         => optional($this->shipped_at)->toIso8601String(),
                'delivered_at'       => optional($this->delivered_at)->toIso8601String(),
                'estimated_delivery' => optional($this->estimated_delivery_time)->toIso8601String(),
                'cancelled_at'       => optional($this->cancelled_at)->toIso8601String(),
                'returned_at'        => optional($this->returned_at)->toIso8601String(),
                'refunded_at'        => optional($this->refunded_at)->toIso8601String(),
            ],
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history'   => OrderStatusLogResource::collection($this->whenLoaded('statusLogs')),
            'coupon'           => new CouponResource($this->whenLoaded('coupon')),
        ];
    }

    protected function formatAddress(): array
    {
        if ($this->address_id) {
            return [
                'type'       => 'saved_address',
                'address_id' => $this->address_id,
                'details'    => $this->address->only([
                    'recipient_name',
                    'street_address',
                    'city',
                    'country',
                    'postal_code',
                    'contact_phone',
                ]),
            ];
        }

        return [
            'type'    => 'custom_address',
            'details' => $this->address,
        ];
    }
}
