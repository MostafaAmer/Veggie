<?php
// app/Providers/NotificationServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Notifications\Channels\{
    DatabaseChannel,
    MailChannel,
    BroadcastChannel,
    SmsChannel,
    PushChannel,
    NotificationChannelInterface
};

class NotificationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(NotificationChannelInterface::class, DatabaseChannel::class);
        $this->app->bind(DatabaseChannel::class, DatabaseChannel::class);
        $this->app->bind(MailChannel::class, MailChannel::class);
        $this->app->bind(BroadcastChannel::class, BroadcastChannel::class);
        $this->app->bind(SmsChannel::class, SmsChannel::class);
        $this->app->bind(PushChannel::class, PushChannel::class);

        $this->app->when(\App\Services\NotificationDispatcher::class)
                  ->needs(NotificationChannelInterface::class)
                  ->give([
                      DatabaseChannel::class,
                      MailChannel::class,
                      BroadcastChannel::class,
                      SmsChannel::class,
                      PushChannel::class,
                  ]);
    }

    public function boot() {}
}