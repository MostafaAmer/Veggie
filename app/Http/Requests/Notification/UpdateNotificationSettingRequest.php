<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type'    => ['required','string'],
            'channel' => ['required','string','in:database,email,broadcast,sms,push'],
            'enabled' => ['required','boolean'],
        ];
    }
}