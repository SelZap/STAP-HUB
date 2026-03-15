<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CameraController extends Controller
{
    /**
     * Show the admin camera feeds & diagnostics view.
     */
    public function index()
    {
        return view('admin.cameras');
    }

    /**
     * List all cameras with status info.
     */
    public function list()
    {
        $cameras = Camera::with('node:id,name,status')->get();
        return response()->json($cameras);
    }

    /**
     * Get a single camera's details.
     */
    public function show($id)
    {
        $camera = Camera::with('node:id,name')->findOrFail($id);
        return response()->json($camera);
    }

    /**
     * Enable a camera.
     */
    public function enable($id)
    {
        $camera = Camera::findOrFail($id);
        $camera->is_active = true;
        $camera->save();

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'camera_enabled',
            'target_id' => $camera->id,
            'notes'     => "Camera enabled: {$camera->label}",
        ]);

        return response()->json(['message' => "Camera {$camera->label} enabled."]);
    }

    /**
     * Disable a camera.
     */
    public function disable($id)
    {
        $camera = Camera::findOrFail($id);
        $camera->is_active = false;
        $camera->save();

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'camera_disabled',
            'target_id' => $camera->id,
            'notes'     => "Camera disabled: {$camera->label}",
        ]);

        return response()->json(['message' => "Camera {$camera->label} disabled."]);
    }

    /**
     * Update camera metadata (label, location description, etc.).
     */
    public function update(Request $request, $id)
    {
        $camera = Camera::findOrFail($id);

        $request->validate([
            'label'    => 'sometimes|string|max:255',
            'position' => 'sometimes|string|max:255',
        ]);

        $camera->fill($request->only('label', 'position'))->save();

        return response()->json(['message' => 'Camera updated.', 'camera' => $camera]);
    }
}