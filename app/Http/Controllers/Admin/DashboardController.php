<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StapNode;
use App\Models\Camera;
use App\Models\TrafficSnapshot;
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
    $losOrder = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6];
    $locationOrder = [
      'Mayor Gil Fernando Ave North',
      'Mayor Gil Fernando Ave South',
      'Sumulong Hwy East',
      'Sumulong Hwy West',
    ];

    $cameraDirectionLabel = function (?string $direction, ?string $fallback = null): ?string {
      $direction = strtolower(trim((string) $direction));

      return match ($direction) {
        'northbound', 'north' => 'Mayor Gil Fernando Ave North',
        'southbound', 'south' => 'Mayor Gil Fernando Ave South',
        'eastbound', 'east' => 'Sumulong Hwy East',
        'westbound', 'west' => 'Sumulong Hwy West',
        default => null,
      };
    };

    $latestSnapshots = TrafficSnapshot::with('camera')
      ->whereIn('snapshot_id', function ($query) {
        $query->selectRaw('MAX(snapshot_id)')
          ->from('traffic_snapshots')
          ->groupBy('camera_id');
      })
      ->get();

    $liveVehicleData = $latestSnapshots
      ->groupBy(function ($snapshot) use ($cameraDirectionLabel) {
        $location = $cameraDirectionLabel(
          $snapshot->camera->direction ?? null,
          $snapshot->camera->label ?? 'Unknown'
        );

        return $location;
      })
      ->reject(function ($snapshots, $location) {
        return blank($location);
      })
      ->map(function ($snapshots, $location) use ($losOrder) {
        $worstLos = $snapshots
          ->sortByDesc(fn($snapshot) => $losOrder[$snapshot->congestion_level] ?? 0)
          ->first()
          ->congestion_level ?? 'A';

        return [
          'location' => $location,
          'vehicle_count' => (int) $snapshots->sum('vehicle_count'),
          'los' => $worstLos,
        ];
      })
      ->sortBy(function ($item) use ($locationOrder) {
        $position = array_search($item['location'], $locationOrder, true);
        return $position === false ? 99 : $position;
      })
      ->values();

    $data = [
      'live_vehicle_data' => $liveVehicleData,
      'nodes' => StapNode::select(
        'node_id',
        'node_name',
        'status',
        'last_heartbeat'
      )->get()->map(function ($node) {
        return [
          'id' => $node->node_id,
          'name' => $node->node_name,
          'status' => $node->status,
          'mode' => 'auto',
          'last_ping_at' => $node->last_heartbeat,
        ];
      }),
      'camera_count' => \App\Models\Camera::count(),
      'active_alerts' => \App\Models\Alert::where('is_resolved', false)->count(),
      'recent_activity' => \App\Models\AdminActivityLog::with('admin:admin_id,admin_name')
        ->latest('performed_at')
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
      'auto' => 'auto_mode_on',
      'manual' => 'manual_mode_on',
      'hazard' => 'hazard_mode_on',
    ];

    AdminActivityLog::create([
      'admin_id' => Auth::guard('admin')->id(),
      'action' => $actionMap[$request->mode],
      'target_id' => $node->id,
      'notes' => "Mode changed from {$previousMode} to {$request->mode}",
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
      'admin_id' => Auth::guard('admin')->id(),
      'action' => 'node_restarted',
      'target_id' => $node->id,
      'notes' => "Restart triggered for node: {$node->name}",
    ]);

    return response()->json(['message' => "Restart signal sent to node {$node->name}."]);
  }
}
