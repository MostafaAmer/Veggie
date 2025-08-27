<?php

namespace App\Notifications;

class ChannelPayload
{
    public function __construct(
        public string $type,
        public string $title,
        public string $body,
        public array  $data = [],
        public ?string $link = null,
        public ?string $icon = null,
        public ?string $priority = 'normal'
    ) {}
}