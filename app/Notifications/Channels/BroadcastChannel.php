<?php

namespace App\Notifications\Channels;

use App\Notifications\ChannelPayload;
use Illuminate\Notifications\Notifiable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;

class BroadcastChannel implements NotificationChannelInterface
{
    public function key(): string
    {
        return 'broadcast';
    }

    public function send(Notifiable $notifiable, ChannelPayload $payload): void
    {
        $channelName = "private-user.{$notifiable->id}";
        broadcast(new BroadcastNotificationCreated(
            new PrivateChannel($channelName),
            $payload
        ))->toOthers();
    }
}