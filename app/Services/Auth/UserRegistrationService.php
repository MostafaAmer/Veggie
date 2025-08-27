<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserRegistrationService
{
    public function registerSocialUser(string $provider, string $providerId, array $data): User
    {
        $this->validateEmailUniqueness($data['email'] ?? null);
        
        return User::create([
            'id'                    => Str::uuid(),
            'name'                  => $data['name'] ?? $this->generateName($data['email'] ?? null),
            'email'                 => $data['email'] ?? null,
            'provider'              => $provider,
            'provider_id'           => $providerId,
            'avatar'                => $data['avatar'] ?? null,
            'password'              => Hash::make(Str::random(32)),
            'email_verified_at'     => now(),
            'is_verified'           => true,
            'is_active'             => true
        ]);
    }

    protected function validateEmailUniqueness(?string $email): void
    {
        if ($email && User::where('email', $email)
                ->whereNull('provider_id')
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'email' => ['The email is already registered with another registration system.']
            ]);
        }
    }


    protected function generateName(?string $email): string
    {
        return $email ? Str::before($email, '@') : 'User_' . Str::random(8);
    }
}