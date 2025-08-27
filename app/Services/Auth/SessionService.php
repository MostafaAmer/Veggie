<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SessionService
{
    public function createLoginSession(User $user, array $requestData): int
    {
        $sessionId = DB::table('user_sessions')->insertGetId([
            'user_id'        => $user->id,
            'ip_address'     => $requestData['ip'],
            'user_agent'     => $requestData['user_agent'],
            'payload'        => json_encode($requestData['headers'] ?? []),
            'last_activity'  => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
            'device_info'    => json_encode([
                'os'          => $this->getOsFromUserAgent($requestData['user_agent']),
                'browser'     => $this->getBrowserFromUserAgent($requestData['user_agent']),
                'device_type' => $this->getDeviceType($requestData['user_agent']),
            ]),
            'location'       => $this->getLocationFromIp($requestData['ip']),
            'is_mobile'      => $this->isMobile($requestData['user_agent']),
    ]);
    
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $requestData['ip']
        ]);
        return $sessionId;
    }

    public function logoutSession(?string $sessionId = null): void
    {
        if ($sessionId) {
            DB::table('user_sessions')
                ->where('id', $sessionId)
                ->update(['logged_out_at' => now()]);
        }
    }

    public function revokeOtherSessions(string $userId, string $currentSessionId): void
    {
        DB::table('user_sessions')
          ->where('user_id', $userId)
          ->where('id', '!=', $currentSessionId)
          ->update(['logged_out_at' => now()]);
    }
}