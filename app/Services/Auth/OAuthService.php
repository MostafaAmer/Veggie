<?php
declare(strict_types=1);

namespace App\Services;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class OAuthService
{
    private int $stateTtlMinutes = 10;

    /**
     * @param string $provider
     * @param array<string,string> $config
     */
    public function getRedirectUrl(string $provider, array $config): string
    {
        $state = Str::random(40);
        Cache::put("oauth_state:{$state}", true, now()->addMinutes($this->stateTtlMinutes));

        return Socialite::driver($provider)
            ->stateless()
            ->setConfig($config)
            ->with(['state' => $state])
            ->redirect()
            ->getTargetUrl();
    }
    
    public function validateProvider(string $provider): void
    {
        $allowed = config('services.oauth.providers', ['google', 'apple']);
        if (! in_array($provider, $allowed, true)) {
            throw new RuntimeException("Provider not supported: {$provider}");
        }
    }

    /**
     * @return array{provider_id:string,name:string,email:?string,avatar:?string}
     */
    public function handleProviderCallback(string $provider, string $state, string $code): array
    {
        if (! Cache::pull("oauth_state:{$state}")) {
            throw new RuntimeException('Invalid OAuth state');
        }

        $socialUser = Socialite::driver($provider)->stateless()->user();
        $email = $socialUser->getEmail();

        return [
            'provider_id' => $socialUser->getId(),
            'name'        => $socialUser->getName()
                             ?? $socialUser->getNickname()
                             ?? Str::before($email ?? '', '@')
                             ?? 'User',
            'email'       => $email,
            'avatar'      => $socialUser->getAvatar(),

        ];
    }
}