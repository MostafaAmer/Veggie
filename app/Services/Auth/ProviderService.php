<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Config;

class ProviderService
{
    private array $providers = [
        'google' => [
            'active'      => true,
            'config_keys' => ['client_id', 'client_secret', 'redirect'],
            'name'        => 'Google',
        ],
        'apple' => [
            'active'      => true,
            'config_keys' => ['client_id', 'client_secret', 'redirect'],
            'name'        => 'Apple',
        ],
    ];

    public function isSupported(string $provider): bool
    {
        return array_key_exists(strtolower($provider), $this->providers);
    }

    public function isActive(string $provider): bool
    {
        $key = strtolower($provider);
        return $this->isSupported($key) && $this->providers[$key]['active'];
    }

    /**
     * @return string[]
     */
    public function getSupportedProviders(): array
    {
        return array_map(fn(array $p) => $p['name'], $this->providers);
    }

    /**
     * @return string[]
     */
    public function getActiveProviders(): array
    {
        return array_map(
            fn(string $key) => $this->providers[$key]['name'],
            array_keys(array_filter($this->providers, fn(array $p) => $p['active']))
        );
    }

    public function getSupportedProvidersMessage(): string
    {
        return "Supported Providers: " . implode(', ', $this->getSupportedProviders());
    }

    /**
     * @return array<string,string>
     */
    public function getConfig(string $provider): array
    {
        $key = strtolower($provider);
        if (! $this->isSupported($key)) {
            return [];
        }

        return collect($this->providers[$key]['config_keys'])
            ->mapWithKeys(fn(string $k) => [
                $k => Config::get("services.{$key}.{$k}", ''),
            ])
            ->toArray();
    }

    public function activateProvider(string $provider): void
    {
        $key = strtolower($provider);
        if ($this->isSupported($key)) {
            $this->providers[$key]['active'] = true;
        }
    }

    public function deactivateProvider(string $provider): void
    {
        $key = strtolower($provider);
        if ($this->isSupported($key)) {
            $this->providers[$key]['active'] = false;
        }
    }
}