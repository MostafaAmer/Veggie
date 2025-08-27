<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogger;

class LogUserLogin
{
    public function __construct(private ActivityLogger $logger) {}

    public function handle(UserLoggedIn $event): void
    {
        Log::channel('auth')->info('User logged in', [
            'user_id'    => $event->user->id,
            'provider'   => $event->provider,
            'ip'         => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'time'       => $event->loginTime,
        ]);
    }
}