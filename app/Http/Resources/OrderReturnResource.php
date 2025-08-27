<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\RefundMethod;
use App\Enums\OrderReturnStatus;
use App\Http\Resources\OrderReturnItemResource;

class OrderReturnResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'order_id'      => $this->order_id,
            'status'        => [
                    'code'  => $this->status,
                    'label' =>OrderReturnStatus::from($this->status)->label(),
            ],
            'reason'        => $this->reason,
            'refund'        => [
                'method'            => $this->refund_method,
                'method_label'      => RefundMethod::from($this->refund_method)->label(),
                'amount'            => $this->refund_amount,
                'bank_details' => $this->when(
                    $this->refund_method === RefundMethod::BankTransfer->value,
                    fn() => [
                        'account_number' => $this->bank_details['account_number'] ?? null,
                        'bank_name'      => $this->bank_details['bank_name'] ?? null,
                        'iban'           => $this->bank_details['iban'] ?? null,
                    ]
                ),
            ],
            'items' => OrderReturnItemResource::collection($this->whenLoaded('items')),
            'approved_by' => $this->whenLoaded('approvedBy', fn() => [
                'id'   => $this->approvedBy->id,
                'name' => $this->approvedBy->name,
            ]),

            'rejected_by' => $this->whenLoaded('rejectedBy', fn() => [
                'id'   => $this->rejectedBy->id,
                'name' => $this->rejectedBy->name,
            ]),

            'refunded_by' => $this->whenLoaded('refundedBy', fn() => [
                'id'   => $this->refundedBy->id,
                'name' => $this->refundedBy->name,
            ]),
            'timestamps' => [
                'created_at'  => $this->created_at?->toIso8601String(),
                'updated_at'  => $this->updated_at?->toIso8601String(),
                'approved_at' => $this->approved_at?->toIso8601String(),
                'rejected_at' => $this->rejected_at?->toIso8601String(),
                'refunded_at' => $this->refunded_at?->toIso8601String(),
            ],
        ];
    }
}