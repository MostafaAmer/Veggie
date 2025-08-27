<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// Route::middleware('auth:sanctum')->group(function(){
//     Route::get('notifications',                [NotificationController::class, 'index']);
//     Route::get('notifications/{id}',           [NotificationController::class, 'show']);
//     Route::post('notifications/{id}/read',     [NotificationController::class, 'markAsRead']);
//     Route::post('notifications/read-all',      [NotificationController::class, 'markAllAsRead']);

//     Route::get('notification-settings',        [NotificationSettingController::class, 'index']);
//     Route::put('notification-settings',        [NotificationSettingController::class, 'update']);
// });
