<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Carbon;
use Illuminate\Queue\SerializesModels;


class UserLoggedIn
{
    use Dispatchable, SerializesModels;

    public User $user;
    public string $provider;
    public string $ipAddress;
    public string $userAgent;
    public Carbon $loginTime;
    public ?string $deviceId;

     public function __construct(
        User $user, 
        string $provider,
        string $ipAddress = null,
        string $userAgent = null,
        string $deviceId = null
    ) {
        $this->user       = $user;
        $this->provider   = $provider;
        $this->ipAddress  = $ipAddress  ?? request()->ip();
        $this->userAgent  = $userAgent  ?? request()->userAgent();
        $this->loginTime  = now();
        $this->deviceId   = $deviceId   ?? $this->generateDeviceId();
    }

    protected function generateDeviceId(): string
    {
        return hash(
            'sha256',
            $this->user->id
            . $this->ipAddress
            . $this->userAgent
            . $this->loginTime->timestamp
        );
    }

    public function toArray(): array
    {
        return [
            'user_id'     => $this->user->id,
            'provider'    => $this->provider,
            'ip_address'  => $this->ipAddress,
            'user_agent'  => $this->userAgent,
            'login_time'  => $this->loginTime->toIso8601String(),
            'device_id'   => $this->deviceId,
        ];
    }
}