<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'status'       => $this->status,
            'status_label' => $this->status_label,
            'notes'        => $this->notes,
            'changed_by'   => $this->whenLoaded('user', fn() => $this->user->only(['id','name','email'])),
            'changed_at'   => $this->changed_at->toIso8601String(),
        ];
    }
}
