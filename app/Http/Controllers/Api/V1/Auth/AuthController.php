<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialLoginRequest;
use App\Http\Resources\AuthResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
        $this->middleware('auth:sanctum')->only('logout');
        $this->middleware('throttle:10,1')->only(['socialLogin']);
    }

    public function socialLogin(SocialLoginRequest $request): JsonResponse
    {
        $key = 'social_login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'message'     => 'Too many login attempts',
                'retry_after' => RateLimiter::availableIn($key),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, 60);

        try {
            $data = $this->authService->handleSocialLogin(
                $request->input('provider'),
                $request->input('provider_id'),
                $request->only(['name', 'email', 'avatar']),
                $request
            );
            return response()->json([
                    'message'     => 'تم تسجيل الدخول بنجاح',
                    'user'        => new AuthResource($data['user']),
                    'token'       => $data['token'],
                    'token_type'  => 'Bearer',
                    'expires_in'  => $data['expires_in'],
                    'session_id'  => $data['session_id'],
                ], Response::HTTP_OK);

        } catch (\Exception $e) {
            \Log::error('Social login failed', [
                'provider' => $request->input('provider'),
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'message' => 'فشل تسجيل الدخول',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $sessionId = (string) $request->input('session_id');
        try {      
            $this->authService->logoutUser(
                $request->user(),
                $sessionId
            );
            return response()->json([
                'message' => 'تم تسجيل الخروج بنجاح'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::error('Logout failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'فشل تسجيل الخروج'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}