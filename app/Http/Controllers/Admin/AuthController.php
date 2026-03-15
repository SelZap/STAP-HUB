<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Show the admin login page.
     */
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle admin login and issue JWT.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token.'], 500);
        }

        $admin = Auth::guard('admin')->user();

        AdminActivityLog::create([
            'admin_id' => $admin->id,
            'action'   => 'admin_login',
        ]);

        return response()->json([
            'token' => $token,
            'admin' => [
                'id'           => $admin->id,
                'name'         => $admin->name,
                'email'        => $admin->email,
                'is_superuser' => $admin->is_superuser,
            ],
        ]);
    }

    /**
     * Refresh the JWT token.
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json(['token' => $newToken]);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token refresh failed.'], 401);
        }
    }

    /**
     * Logout the admin and invalidate JWT.
     */
    public function logout(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            // Token already invalid — proceed
        }

        if ($admin) {
            AdminActivityLog::create([
                'admin_id' => $admin->id,
                'action'   => 'admin_logout',
            ]);
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }
}