<?php

namespace App\Http\Middleware;

use App\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Check either the bearer token or the query string (Sanctum)
        $token = $request->bearerToken() ?: $request->input('api_token');
        if (!empty($token)) {
            /** @var \Laravel\Sanctum\PersonalAccessToken $token */
            $token = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($token) { // Ensure the token exists
                if ($token->expires_at === null || $token->expires_at > Carbon::now()) { // Ensure the token hasn't expired'
                    /** @var User $user */
                    $user = $token->tokenable;
                    Auth::onceUsingId($user->id);
                    return $next($request);
                }
            }
        }

        // Check basic auth
        $response = Auth::onceBasic();
        if ($response === null) {
            return $next($request);
        }
        return $response; // 401 returned by Auth::onceBasic()
    }
}
