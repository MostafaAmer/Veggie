<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (
            $user &&
            ! $user->hasVerifiedEmail() &&
            ! $user->is_social_login
            ) {
                return response()->json([
                    'message'   => 'Email not verified',
                    'verified'  => false
                ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}