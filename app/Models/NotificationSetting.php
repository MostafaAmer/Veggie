<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'channel',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function allows(int $userId, string $type, string $channel): bool
    {
        return static::where([
            ['user_id', $userId],
            ['type',    $type],
            ['channel', $channel],
        ])->value('enabled') ?? true;
    }
}