<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * List all admin accounts. (Superuser only)
     */
    public function index()
    {
        $admins = Admin::select('id', 'name', 'email', 'is_superuser', 'created_at')->get();
        return response()->json($admins);
    }

    /**
     * Create a new admin account. (Superuser only)
     */
    public function store(Request $request)
    {
        $this->authorizeSuperuser();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Admin::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'is_superuser' => false,
        ]);

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'admin_account_created',
            'target_id' => $admin->id,
            'notes'     => "Created account for {$admin->email}",
        ]);

        return response()->json(['message' => 'Admin account created.', 'admin' => $admin], 201);
    }

    /**
     * Show a single admin account. (Superuser only)
     */
    public function show($id)
    {
        $this->authorizeSuperuser();

        $admin = Admin::findOrFail($id);
        return response()->json($admin);
    }

    /**
     * Update an admin account.
     * Superusers can update any account.
     * Regular admins can only update their own account.
     */
    public function update(Request $request, $id)
    {
        $current = Auth::guard('admin')->user();

        if (! $current->is_superuser && $current->id != $id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $admin = Admin::findOrFail($id);

        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => "sometimes|email|unique:admins,email,{$admin->id}",
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($request->filled('name'))     $admin->name  = $request->name;
        if ($request->filled('email'))    $admin->email = $request->email;
        if ($request->filled('password')) $admin->password = Hash::make($request->password);

        $admin->save();

        $action = ($current->id === $admin->id) ? 'own_account_edited' : 'admin_account_edited';

        AdminActivityLog::create([
            'admin_id'  => $current->id,
            'action'    => $action,
            'target_id' => $admin->id,
        ]);

        return response()->json(['message' => 'Account updated.', 'admin' => $admin]);
    }

    /**
     * Delete an admin account. (Superuser only)
     */
    public function destroy($id)
    {
        $this->authorizeSuperuser();

        $current = Auth::guard('admin')->user();

        if ($current->id == $id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $admin = Admin::findOrFail($id);
        $email = $admin->email;
        $admin->delete();

        AdminActivityLog::create([
            'admin_id'  => $current->id,
            'action'    => 'admin_account_deleted',
            'target_id' => $id,
            'notes'     => "Deleted account: {$email}",
        ]);

        return response()->json(['message' => 'Admin account deleted.']);
    }

    /**
     * Helper: Abort if current admin is not a superuser.
     */
    private function authorizeSuperuser()
    {
        if (! Auth::guard('admin')->user()->is_superuser) {
            abort(403, 'Superuser access required.');
        }
    }
}