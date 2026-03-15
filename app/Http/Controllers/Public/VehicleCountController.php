<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TrafficSnapshot;
use App\Models\Camera;
use Illuminate\Http\Request;

class VehicleCountController extends Controller
{
    /**
     * Show the public vehicle count page.
     */
    public function index()
    {
        return view('vehicle-count');
    }

    /**
     * Return vehicle count summary data for charts.
     *
     * Groups by hour for the last 24 hours across all active cameras,
     * or filtered by camera_id.
     */
    public function summary(Request $request)
    {
        $query = TrafficSnapshot::selectRaw(
                'camera_id,
                 DATE_FORMAT(captured_at, "%Y-%m-%d %H:00:00") as hour,
                 SUM(vehicle_count) as total_vehicles'
            )
            ->where('captured_at', '>=', now()->subHours(24))
            ->groupBy('camera_id', 'hour')
            ->orderBy('hour');

        if ($request->filled('camera_id')) {
            $query->where('camera_id', $request->camera_id);
        }

        return response()->json($query->get());
    }

    /**
     * Return current live vehicle counts per active camera.
     */
    public function live()
    {
        $latest = TrafficSnapshot::with('camera:id,label')
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('traffic_snapshots')
                    ->groupBy('camera_id');
            })
            ->get(['id', 'camera_id', 'vehicle_count', 'congestion_level', 'captured_at']);

        return response()->json($latest);
    }
}