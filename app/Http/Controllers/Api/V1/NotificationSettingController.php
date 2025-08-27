<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNotificationSettingRequest;
use App\Http\Resources\NotificationSettingResource;
use App\Models\NotificationSetting;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $settings = NotificationSetting::where('user_id', auth()->id())->get();

        return NotificationSettingResource::collection($settings);
    }

    public function update(UpdateNotificationSettingRequest $request)
    {
        $setting = NotificationSetting::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'type'    => $request->type,
                'channel' => $request->channel,
            ],
            ['enabled' => $request->enabled]
        );

        return new NotificationSettingResource($setting);
    }
}