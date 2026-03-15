<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrafficLight;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrafficLightController extends Controller
{
    /**
     * Show the admin traffic light control view.
     */
    public function index()
    {
        return view('admin.traffic-lights');
    }

    /**
     * List all traffic lights and their current state.
     */
    public function list()
    {
        $lights = TrafficLight::with('node:id,name,mode')->get();
        return response()->json($lights);
    }

    /**
     * Get a single traffic light's state.
     */
    public function show($id)
    {
        $light = TrafficLight::with('node:id,name')->findOrFail($id);
        return response()->json($light);
    }

    /**
     * Manually set a traffic light state.
     * Only allowed when the node is in manual or hazard mode.
     */
    public function setState(Request $request, $id)
    {
        $request->validate([
            'state'          => 'required|in:red,yellow,green',
            'duration_sec'   => 'nullable|integer|min:1',
        ]);

        $light = TrafficLight::with('node')->findOrFail($id);

        if (! in_array($light->node->mode, ['manual', 'hazard'])) {
            return response()->json(['message' => 'Node must be in manual or hazard mode to override lights.'], 422);
        }

        $previousState = $light->current_state;
        $light->current_state = $request->state;
        $light->save();

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'light_state_changed',
            'target_id' => $light->id,
            'notes'     => "Light {$light->id} changed from {$previousState} to {$request->state}",
        ]);

        // TODO: Push state change to Node via WebSocket/broadcast

        return response()->json([
            'message' => "Traffic light set to {$request->state}.",
            'light'   => $light,
        ]);
    }
}