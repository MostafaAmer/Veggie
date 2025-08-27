<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog; 

class ActivityLogger
{
    /**
     * @param  string|null  $userId
     * @param  string       $event
     * @param  array        $properties
     * @return void
     */
    public function log(?string $userId, string $event, array $properties = []): void
    {
        ActivityLog::create([
            'user_id'    => $userId,
            'event'      => $event,
            'properties' => json_encode($properties, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}