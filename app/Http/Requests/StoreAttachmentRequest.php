<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AttachmentType;
use Illuminate\Validation\Rule;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasVerifiedEmail();
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:5120'],
            'type' => ['nullable', Rule::in(AttachmentType::getValues())],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'custom_properties' => ['nullable', 'json'],
        ];
    }
}