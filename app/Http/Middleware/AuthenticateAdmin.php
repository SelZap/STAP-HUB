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
        try {
            $token = JWTAuth::getToken() ?: $request->cookie('admin_token');

            if (! $token) {
                return $this->unauthenticated($request, 'Admin not found.');
            }

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
            return $this->unauthenticated($request, 'Token missing or malformed.');
        }

        return $next($request);
    }

    private function unauthenticated(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 401);
        }

        return redirect()->route('public.dashboard')->with('error', $message);
    }
}