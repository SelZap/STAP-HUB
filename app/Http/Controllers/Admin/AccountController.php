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
    // List all admin accounts (superuser only)
    public function index()
    {
        if (request()->expectsJson()) {
            $admins = Admin::select('admin_id', 'admin_name', 'email', 'is_superuser', 'created_at')
                ->orderBy('admin_id')
                ->get();
            return response()->json($admins);
        }
        return view('admin.accounts');
    }

    // Create a new admin account (superuser only)
    public function store(Request $request)
    {
        $this->authorizeSuperuser();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Admin::create([
            'admin_name'    => $request->name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'is_superuser'  => false,
        ]);

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->user()->admin_id,
            'action'    => 'admin_account_created',
            'target_id' => $admin->admin_id,
            'notes'     => "Created account for {$admin->email}",
        ]);

        return response()->json(['message' => 'Admin account created.', 'admin' => $admin], 201);
    }

    // Show single admin account (superuser only)
    public function show($id)
    {
        $this->authorizeSuperuser();
        return response()->json(Admin::findOrFail($id));
    }

    // Update admin account (superuser = any; regular admin = own only)
    public function update(Request $request, $id)
    {
        $current = Auth::guard('admin')->user();

        if (!$current->is_superuser && $current->admin_id != $id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $admin = Admin::findOrFail($id);

        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => "sometimes|email|unique:admins,email,{$admin->admin_id},admin_id",
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if ($request->filled('name'))     $admin->admin_name    = $request->name;
        if ($request->filled('email'))    $admin->email         = $request->email;
        if ($request->filled('password')) $admin->password_hash = Hash::make($request->password);

        $admin->save();

        $action = ($current->admin_id === $admin->admin_id) ? 'own_account_edited' : 'admin_account_edited';

        AdminActivityLog::create([
            'admin_id'  => $current->admin_id,
            'action'    => $action,
            'target_id' => $admin->admin_id,
        ]);

        return response()->json(['message' => 'Account updated.', 'admin' => $admin]);
    }

    // Delete admin account (superuser only)
    public function destroy($id)
    {
        $this->authorizeSuperuser();

        $current = Auth::guard('admin')->user();

        if ($current->admin_id == $id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $admin = Admin::findOrFail($id);
        $email = $admin->email;
        $admin->delete();

        AdminActivityLog::create([
            'admin_id'  => $current->admin_id,
            'action'    => 'admin_account_deleted',
            'target_id' => $id,
            'notes'     => "Deleted account: {$email}",
        ]);

        return response()->json(['message' => 'Admin account deleted.']);
    }

    private function authorizeSuperuser()
    {
        if (!Auth::guard('admin')->user()->is_superuser) {
            abort(403, 'Superuser access required.');
        }
    }
}