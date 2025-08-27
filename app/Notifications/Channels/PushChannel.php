<?php

namespace App\Notifications\Channels;

use App\Notifications\ChannelPayload;
use Illuminate\Notifications\Notifiable;
use Kreait\Firebase\Contract\Messaging;              
use Kreait\Firebase\Messaging\CloudMessage;            
use Kreait\Firebase\Messaging\Notification as FcmNotif;

class PushChannel implements NotificationChannelInterface
{
    public function __construct(
        protected Messaging $messaging
    ) {}

    public function key(): string
    {
        return 'push';
    }

    public function send(Notifiable $notifiable, ChannelPayload $payload): void
    {
        $message = CloudMessage::withTarget('token', $notifiable->device_token)
            ->withNotification(FcmNotif::create(
                $payload->title,
                $payload->body
            ))
            ->withData($payload->data);

        $this->messaging->send($message);
    }
}