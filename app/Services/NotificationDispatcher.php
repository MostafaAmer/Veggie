<?php

namespace App\Services;

use App\Notifications\ChannelPayload;
use App\Notifications\Channels\NotificationChannelInterface;
use Illuminate\Notifications\Notifiable;
use Traversable;

class NotificationDispatcher
{
    /** @var NotificationChannelInterface[] */

    protected array $channels;

    /**
     * @param array<NotificationChannelInterface>|Traversable<NotificationChannelInterface> $channels
     */
    public function __construct(array|Traversable $channels)
    {
        $this->channels = is_array($channels) ? $channels : iterator_to_array($channels);
    }

    /**
     * @param Notifiable      $notifiable
     * @param ChannelPayload  $payload
     */
    public function dispatch(Notifiable $notifiable, ChannelPayload $payload): void
    {
        foreach ($this->channels as $channel) {
            if ($notifiable->canNotify($payload->type, $channel->key())) {
                $channel->send($notifiable, $payload);
            }
        }
    }
}