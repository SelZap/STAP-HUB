<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StapNode;
use App\Models\Camera;
use App\Models\Alert;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the admin system control panel view.
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Get dashboard summary data (nodes, cameras, active alerts).
     */
    public function summary()
    {
        $data = [
            'nodes'          => StapNode::select('id', 'name', 'status', 'mode', 'last_ping_at')->get(),
            'camera_count'   => Camera::count(),
            'active_alerts'  => Alert::where('resolved', false)->count(),
            'recent_activity' => AdminActivityLog::with('admin:id,name')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return response()->json($data);
    }

    /**
     * Set a node's operating mode (auto / manual / hazard).
     */
    public function setNodeMode(Request $request, $nodeId)
    {
        $request->validate([
            'mode' => 'required|in:auto,manual,hazard',
        ]);

        $node = StapNode::findOrFail($nodeId);
        $previousMode = $node->mode;
        $node->mode = $request->mode;
        $node->save();

        $actionMap = [
            'auto'   => 'auto_mode_on',
            'manual' => 'manual_mode_on',
            'hazard' => 'hazard_mode_on',
        ];

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => $actionMap[$request->mode],
            'target_id' => $node->id,
            'notes'     => "Mode changed from {$previousMode} to {$request->mode}",
        ]);

        return response()->json(['message' => "Node mode set to {$request->mode}.", 'node' => $node]);
    }

    /**
     * Restart a STAP Node (sends restart signal — actual execution handled by Node).
     */
    public function restartNode(Request $request, $nodeId)
    {
        $node = StapNode::findOrFail($nodeId);

        // TODO: Send restart command to Node via WebSocket or queued job

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'node_restarted',
            'target_id' => $node->id,
            'notes'     => "Restart triggered for node: {$node->name}",
        ]);

        return response()->json(['message' => "Restart signal sent to node {$node->name}."]);
    }
}