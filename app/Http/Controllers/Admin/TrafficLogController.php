<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrafficSnapshot;
use Illuminate\Http\Request;

class TrafficLogController extends Controller
{
    /**
     * Show the admin traffic logs view.
     */
    public function index()
    {
        return view('admin.traffic-logs');
    }

    /**
     * Paginated list of traffic snapshots with optional filters.
     *
     * Filters: camera_id, node_id, date_from, date_to, vehicle_type
     */
    public function list(Request $request)
    {
        $query = TrafficSnapshot::with('camera:id,label', 'camera.node:id,name')
            ->latest('captured_at');

        if ($request->filled('camera_id')) {
            $query->where('camera_id', $request->camera_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('captured_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('captured_at', '<=', $request->date_to);
        }

        $snapshots = $query->paginate(50);

        return response()->json($snapshots);
    }

    /**
     * Show a single traffic snapshot record.
     */
    public function show($id)
    {
        $snapshot = TrafficSnapshot::with('camera.node')->findOrFail($id);
        return response()->json($snapshot);
    }
}