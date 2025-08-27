<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notifiable;
use App\Notifications\ChannelPayload;

interface NotificationChannelInterface
{
    /**
     * @param Notifiable           $notifiable
     * @param ChannelPayload       $payload
     * @return void
     */
    public function send(Notifiable $notifiable, ChannelPayload $payload): void;

    /**
     * @return string
     */
    public function key(): string;
}