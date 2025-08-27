<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\NotificationDispatcher;
use App\Notifications\ChannelPayload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected NotificationDispatcher $dispatcher) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $user  = $order->user;

        $payload = new ChannelPayload(
            type: 'order_updates',
            title: 'طلب جديد قيد المعالجة',
            body: "طلب #{$order->id} تم إنشاؤه بنجاح لديك",
            data: ['order_id' => $order->id],
            link: "/orders/{$order->id}",
            icon: 'shopping-cart',
            priority: 'normal'
        );

        $this->dispatcher->dispatch($user, $payload);
    }
}