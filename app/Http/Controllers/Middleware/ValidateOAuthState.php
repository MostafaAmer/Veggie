<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateOAuthState
{
    public function handle(Request $request, Closure $next): mixed
    {
        $state          = $request->input('state');
        $sessionState   = $request->session()->pull('oauth_state');

        if (empty($state) ||
            empty($sessionState) ||
            ! hash_equals($sessionState, $state)
        ) {
            return response()->json([
                'error' => 'Invalid OAuth state',
                'message' => 'Authentication state mismatch'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}