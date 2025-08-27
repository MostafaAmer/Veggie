<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionStatusLogResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'transaction_id'  => $this->transaction_id,
            'changed_by'      => [
                'id'   => $this->changed_by,
                'name' => $this->whenLoaded('changedBy', fn() => $this->changedBy->name),
            ],
            'status'          => $this->status,
            'status_label'    => $this->status_label,
            'action'          => $this->action,
            'details'         => $this->details ?? [],
            'changed_at'      => $this->changed_at->toIso8601String(),
            'created_at'      => $this->created_at->toIso8601String(),
        ];
    }
}
