<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,

            'order'         => [
                'id'        => $this->order_id,
                'reference' => $this->whenLoaded('order', fn() => $this->order->reference_number),
                'link'      => route('api.v1.orders.show', $this->order_id),
            ],

            'user'          => [
                'id'    => $this->user_id,
                'name'  => $this->whenLoaded('user', fn() => $this->user->name),
                'email' => $this->whenLoaded('user', fn() => $this->user->email),
                'link'  => route('api.v1.users.show', $this->user_id),
            ],

            'amount'        => [
                'value'     => (float) $this->amount,
                'formatted' => $this->formatted_amount,
                'currency'  => $this->currency,
            ],

            'type'          => [
                'code'  => $this->type,
                'label' => $this->type_label,
            ],

            'status'        => [
                'code'    => $this->status,
                'label'   => $this->status_label,
                'history' => TransactionStatusLogResource::collection(
                    $this->whenLoaded('statusLogs')
                ),
            ],

            'payment'       => [
                'method'          => $this->payment_method,
                'method_label'    => $this->payment_method_label,
                'gateway'         => $this->gateway,
                'gateway_txn_id'  => $this->gateway_transaction_id,
                'response'        => $this->gateway_response ?? [],
            ],

            'processed_by'  => $this->whenLoaded('processedBy', function () {
                return [
                    'id'   => $this->processedBy->id,
                    'name' => $this->processedBy->name,
                ];
            }),

            'processed_at'  => optional($this->processed_at)->toIso8601String(),
            'notes'         => $this->notes,
            'metadata'      => $this->metadata ?? [],

            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),

            'links'         => [
                'self'   => route('api.v1.transactions.show', $this->id),
                'order'  => route('api.v1.orders.show', $this->order_id),
                'user'   => route('api.v1.users.show', $this->user_id),
            ],
        ];
    }
}