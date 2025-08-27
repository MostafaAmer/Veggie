<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Enums\UserProvider;

class SocialLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider'    => ['required', 'string', Rule::enum(UserProvider::class)],
            'provider_id' => ['required', 'string', 'max:255'],
            'name'        => ['required', 'string', 'max:255'],
            'email'       => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users')
                    ->where(fn($q) => $q->whereNull('provider')),
            ],
            'avatar'      => ['sometimes', 'nullable', 'url', 'max:512'],
        ];
    }

    public function messages()
    {
        return [
            'provider.in'   => 'Only allowed registration type: :values',
            'email.unique'  => 'The email is already registered with another registration system.'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->checkBannedAccount()) {
                $validator->errors()->add('account', 'The account is currently disabled. Please contact support for assistance.');
            }
        });
    }

    protected function isBannedAccount(): bool
    {
        return User::where('provider', $this->input('provider'))
            ->where('provider_id', $this->input('provider_id'))
            ->where('is_active', false)
            ->exists();
    }
}