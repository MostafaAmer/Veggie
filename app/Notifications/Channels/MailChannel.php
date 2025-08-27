<?php

namespace App\Notifications\Channels;

use App\Notifications\ChannelPayload;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class MailChannel implements NotificationChannelInterface
{
    public function key(): string
    {
        return 'email';
    }

    public function send(Notifiable $notifiable, ChannelPayload $payload): void
    {
        Mail::send([], [], function($message) use($notifiable, $payload) {
            $message->to($notifiable->email)
                    ->subject($payload->title)
                    ->setBody(
                        "<p>{$payload->body}</p><p><a href=\"{$payload->link}\">عرض</a></p>",
                        'text/html'
                    );
        });
    }
}