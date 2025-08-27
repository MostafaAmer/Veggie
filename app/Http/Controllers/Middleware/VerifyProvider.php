<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use App\Services\ProviderService;

class VerifyProvider
{
    public function __construct(
        private ProviderService $providerService
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $provider = $this->getProviderFromRequest($request);

        if (!$this->providerService->isSupported($provider)) {
            return $this->errorResponse(
                'Service provider not supported',
                $this->providerService->getSupportedProvidersMessage(),
                Response::HTTP_NOT_FOUND
            );
        }

        if (!$this->providerService->isActive($provider)) {
            return $this->errorResponse(
                'Service provider is not activated',
                "The provider '{$provider}' is currently inactive. Please try again later.",
                Response::HTTP_FORBIDDEN
            );
        }

        $request->merge([
            'verified_provider'     => $provider,
            'provider_config'       => $this->providerService->getConfig($provider)
        ]);

        return $next($request);
    }

    protected function getProviderFromRequest(Request $request): string
    {
        $provider = $request->route('provider') ?? 
                   $request->input('provider');

        if (empty($provider)) {
            abort(Response::HTTP_BAD_REQUEST, 'Provider is required');
        }

        return strtolower($provider);
    }

    protected function errorResponse(string $error, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error'                 => $error,
            'message'               => $message,
            'supported_providers'   => $this->providerService->getSupportedProviders(),
            'active_providers'      => $this->providerService->getActiveProviders()
        ], $status);
    }
}