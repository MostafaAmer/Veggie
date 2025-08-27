<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Enums\UserStatus;
use App\Events\UserLoggedIn;
use Illuminate\Support\Facades\DB;
use Illuminate\Cache\RateLimiter;
use App\Services\Auth\UserRegistrationService;
use App\Services\Auth\TokenService;
use App\Services\Auth\SessionService;
use App\Services\ActivityLogger;
use App\Repositories\TokenRepository;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class AuthService
{
    public function __construct(
        private UserRegistrationService $registrationService,
        private SessionService          $sessionService,
        private TokenService            $tokenService,
        private ActivityLogger          $activityLogger,
        private TokenRepository         $tokenRepository,
        private RateLimiter             $rateLimiter
    ) {}

    /**
     * @param string $provider
     * @param string $providerId
     * @param array<string,mixed> $userData
     * @param array{ip:string,user_agent:string,headers?:array} $requestData
     *
     * @return array{
     *     user:User,
     *     token:string,
     *     expires_in:int,
     *     session_id:int
     * }
     */
    public function handleSocialLogin(
        string $provider,
        string $providerId,
        array  $userData,
        array  $requestData
    ): array {
        $this->validateProvider($provider);
        $this->checkForSuspiciousActivity($requestData['ip']);

        $result = DB::transaction(fn() => $this->processLogin(
            $provider,
            $providerId,
            $userData,
            $requestData
        ));

        event(new UserLoggedIn(
            $result['user'],
            $provider,
            $requestData['ip'],
            $requestData['user_agent'] ?? '',
        ));

        return [
            'user'       => $result['user'],
            'token'      => $result['token'],
            'expires_in' => config('auth.token_expiration_days', 7) * 86400,
            'session_id' => $result['sessionId'],
        ];
    }

    protected function processLogin(
        string $provider,
        string $providerId,
        array  $userData,
        array  $requestData
    ): array {
        $user = User::firstWhere([
            ['provider', '=', $provider],
            ['provider_id', '=', $providerId],
        ]) ?? tap(
            $this->registrationService->registerSocialUser($provider, $providerId, $userData),
            fn(User $u) => $u->assignRole('user')
        );

        if ($user->status !== UserStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'account' => ['الحساب غير مفعل أو معطل حالياً'],
            ]);
        }

        $ids = $user->tokens()->pluck('id')->toArray();
        $this->tokenRepository->revoke($ids);

        $token     = $this->tokenService->createAuthToken($user);
        $sessionId = $this->sessionService->createLoginSession($user, $requestData);

        return compact('user', 'token', 'sessionId');
    }

    protected function checkForSuspiciousActivity(string $ip): void
    {
        $key = "login_attempts:{$ip}";
        if ($this->rateLimiter->tooManyAttempts($key, 5)) {
            throw new TooManyRequestsHttpException(
                1800,
                'Maximum login attempts exceeded, try again later.'
            );
        }
        $this->rateLimiter->hit($key, 1800);
    }

    protected function validateProvider(string $provider): void
    {
        $allowed = config('services.oauth.providers', ['google', 'apple']);

        if (! in_array($provider, $allowed, true)) {
            throw ValidationException::withMessages([
                'provider' => ["This type of recording is not allowed: {$provider}"],
            ]);
        }
    }

    public function logoutUser(User $user, ?string $sessionId = null): void
    {
        $this->tokenService->revokeUserTokens($user, $sessionId);
        $this->sessionService->logoutSession($sessionId);
    }
}