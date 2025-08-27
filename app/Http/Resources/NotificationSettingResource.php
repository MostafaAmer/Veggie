<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationSettingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'type'    => $this->type,
            'channel' => $this->channel,
            'enabled' => $this->enabled,
        ];
    }
}