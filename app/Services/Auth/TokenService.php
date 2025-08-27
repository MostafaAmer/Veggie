<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\PersonalAccessToken;
use App\Repositories\TokenRepository;


class TokenService
{
    public function __construct(
        private TokenRepository $repository
    ) {}

    public function createAuthToken(User $user): string
    {
        $this->repository->revoke(
            $user->tokens()->pluck('id')->toArray()
        );

        $abilities  = Config::get('auth.token_abilities', ['*']);
        $expiration = now()->addDays(Config::get('auth.token_expiration_days', 7));

        $newToken = $user->createToken('auth-token', $abilities, $expiration);

         return $newToken->plainTextToken;
    }
    
    public function revokeUserTokens(User $user, ?string $tokenId = null): void
    {
        $query = $user->tokens();
        if ($tokenId) {
            $query->where('id', $tokenId);
        }

        $ids = $query->pluck('id')->toArray();
        $this->repository->revoke($ids);
    }
}