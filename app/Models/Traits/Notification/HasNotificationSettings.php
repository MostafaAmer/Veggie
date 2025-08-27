<?php

namespace App\Traits;

use App\Models\NotificationSetting;

trait HasNotificationSettings
{
    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class);
    }

    public function canNotify(string $type, string $channel): bool
    {
        $setting = $this->notificationSettings
            ->firstWhere('type', $type);

        return $setting ? $setting->allows($channel) : true;
    }
}