<?php

namespace App\Notifications\Channels;

use App\Notifications\ChannelPayload;
use Illuminate\Notifications\Notifiable;
use Twilio\Rest\Client;

class SmsChannel implements NotificationChannelInterface
{
    protected Client $twilio;

    public function __construct()
    {
        $this->twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
    }

    public function key(): string
    {
        return 'sms';
    }

    public function send(Notifiable $notifiable, ChannelPayload $payload): void
    {
        $this->twilio->messages->create($notifiable->phone, [
            'from' => config('services.twilio.from'),
            'body' => "{$payload->title}\n{$payload->body}\n{$payload->link}"
        ]);
    }
}