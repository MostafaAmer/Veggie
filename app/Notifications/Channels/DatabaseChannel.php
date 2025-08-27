<?php

namespace App\Notifications\Channels;

use App\Notifications\ChannelPayload;
use App\Models\Notification;
use Illuminate\Notifications\Notifiable;

class DatabaseChannel implements NotificationChannelInterface
{
    public function key(): string
    {
        return 'database';
    }

    public function send(Notifiable $notifiable, ChannelPayload $payload): void
    {
        $notifiable->notifications()->create([
            'type' => $payload->type,
            'data' => array_merge([
                'title'    => $payload->title,
                'body'     => $payload->body,
                'link'     => $payload->link,
                'icon'     => $payload->icon,
                'priority' => $payload->priority,
            ], $payload->data),
        ]);
    }
}