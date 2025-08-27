<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'order'   => [
                'id'   => $this->order_id,
                'link' => route('api.v1.orders.show', $this->order_id),
            ],
            'amount'            => [
                'value'     => $this->amount,
                'formatted' => $this->formatted_amount,
                'currency'  => $this->currency,
            ],
            'refund' => [
                'refunded'           => $this->refunded_amount,
                'formatted_refunded' => number_format($this->refunded_amount, 2) . ' ' . $this->currency,
                'is_refundable'      => $this->isRefundable(),
                'refundable_amount'  => $this->amount - $this->refunded_amount,
            ],
            'method'            => [
                'code'  => $this->method->value,
                'label' => $this->method_label,
            ],
            'gateway' => $this->when($this->gateway, [
                'driver'           => $this->gateway,
                'idempotency_key'  => $this->idempotency_key,
                'transaction_id'   => $this->transaction_id,
                'details'          => $this->when($request->user()->can('manage_orders'), $this->gateway_response),
            ]),
            'status'            => [
                'code'  => $this->status->value,
                'label' => $this->status_label,
            ],
            'paid_at'    => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'links' => [
                'self'   => route('api.v1.payments.show', $this->id),
                'refund' => $this->when($this->isRefundable(), route('api.v1.payments.refund', $this->id)),
            ],
            'error' => $this->when($this->status->value === 'failed', [
                'code'    => $this->error_code,
                'details' => $this->error_data,
            ]),
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
