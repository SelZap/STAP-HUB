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
            'admin_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('admin_name', 'password');

        try {
            if (! $token = Auth::guard('admin')->attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token.'], 500);
        }

        $admin = Auth::guard('admin')->user();

        AdminActivityLog::create([
            'admin_id' => $admin->admin_id,
            'action'   => 'admin_login',
        ]);

        return response()->json([
            'token' => $token,
            'admin' => [
                'id'           => $admin->admin_id,
                'name'         => $admin->name,
                'admin_name'   => $admin->name,
                'email'        => $admin->email,
                'is_superuser' => $admin->is_superuser,
            ],
        ])->cookie('admin_token', $token, 60) ; 
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
            Auth::guard('admin')->logout();
        } catch (JWTException $e) {
            // Token already invalid — proceed
        }

        if ($admin) {
            AdminActivityLog::create([
                'admin_id' => $admin->admin_id,
                'action'   => 'admin_logout',
            ]);
        }

        return response()->json(['message' => 'Logged out successfully.'])
          ->withoutCookie('admin_token');
    }
}
