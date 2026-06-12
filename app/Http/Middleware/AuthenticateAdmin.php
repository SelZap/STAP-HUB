<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthenticateAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Try to get the token from the Authorization header first,
        //    then fall back to the admin_token cookie.
        $token = null;

        // Parse Bearer token from Authorization header manually so we
        // don't trigger a JWTException just because no token is present.
        $authHeader = $request->header('Authorization', '');
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = trim(substr($authHeader, 7));
        }

        // Fallback: cookie (set by the login response)
        if (empty($token)) {
            $token = $request->cookie('admin_token');
        }

        if (empty($token)) {
            return $this->unauthenticated($request, 'No authentication token provided.');
        }

        try {
            $admin = JWTAuth::setToken($token)->authenticate();

            if (! $admin) {
                return $this->unauthenticated($request, 'Admin not found.');
            }

            // Bind the resolved admin to Laravel's auth guard
            Auth::guard('admin')->setUser($admin);

        } catch (TokenExpiredException $e) {
            return $this->unauthenticated($request, 'Token has expired.');
        } catch (TokenInvalidException $e) {
            return $this->unauthenticated($request, 'Token is invalid.');
        } catch (JWTException $e) {
            return $this->unauthenticated($request, 'Token is malformed or missing.');
        }

        return $next($request);
    }

    private function unauthenticated(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->is('admin/api/*') || $request->wantsJson()) {
            return response()->json(['message' => $message], 401);
        }

        return redirect()->route('public.dashboard')->with('error', $message);
    }
}