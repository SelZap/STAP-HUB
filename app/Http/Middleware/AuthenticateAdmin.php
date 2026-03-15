<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthenticateAdmin
{
    /**
     * Validate the JWT token on admin-protected routes.
     *
     * Returns JSON 401 for API-style requests.
     * Redirects to admin login for browser requests.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $admin = JWTAuth::parseToken()->authenticate();

            if (! $admin) {
                return $this->unauthenticated($request, 'Admin not found.');
            }

        } catch (TokenExpiredException $e) {
            return $this->unauthenticated($request, 'Token has expired.');
        } catch (TokenInvalidException $e) {
            return $this->unauthenticated($request, 'Token is invalid.');
        } catch (JWTException $e) {
            return $this->unauthenticated($request, 'Token missing or malformed.');
        }

        return $next($request);
    }

    private function unauthenticated(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 401);
        }

        return redirect()->route('admin.login')->with('error', $message);
    }
}