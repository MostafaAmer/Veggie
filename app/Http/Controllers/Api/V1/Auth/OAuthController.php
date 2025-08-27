<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OAuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OAuthController extends Controller
{
    public function __construct(
        private OAuthService $oauthService
    ) {
        $this->middleware('verify.provider');
        $this->middleware('validate.oauth.state')->only(['handleProviderCallback']);
    }

    public function redirectToProvider(Request $request, string $provider): JsonResponse
    {
        try {
            $config      = $request->get('provider_config', []);
            $redirectUrl = $this->oauthService->getRedirectUrl(
                $provider,
                $config
            );
            
            return response()->json([
                'redirect_url'  => $redirectUrl,
                'expires_in'    => 300,
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            \Log::error('OAuth redirect failed', [
                'provider'      => $provider,
                'error'         => $e->getMessage()
            ]);
            
            return response()->json([
                'error'     => 'Failed to initiate OAuth',
                'message'   => 'Cannot redirect to provider'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function handleProviderCallback(Request $request, string $provider): JsonResponse
    {
        try {
            $this->oauthService->validateProvider($provider);
            
            $userData = $this->oauthService->handleProviderCallback(
                $provider,
                $request->input('state'),
                $request->input('code')
            );
            
            return response()->json([
                'provider'  => $provider,
                'user_data' => [
                    'provider_id' => $userData['provider_id'],
                    'name'        => $userData['name'],
                    'email'       => $userData['email'],
                    'avatar'      => $userData['avatar'],
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            \Log::error('OAuth callback failed', [
                'provider'  => $provider,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error'     => 'Authentication failed',
                'message'   => 'Failed to authenticate with ' . $provider
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}