<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TrafficSnapshot;
use App\Models\Camera;
use Illuminate\Http\Request;

class TrafficHistoryController extends Controller
{
    /**
     * Show the public traffic data archive page.
     */
    public function index()
    {
        return view('traffic-data-archive');
    }

    /**
     * Return paginated traffic snapshot records.
     *
     * Filters: camera_id, date_from, date_to
     */
    public function list(Request $request)
    {
        $query = TrafficSnapshot::with('camera:id,label')
            ->select('id', 'camera_id', 'image_url', 'vehicle_count', 'congestion_level', 'captured_at')
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

        return response()->json($query->paginate(40));
    }

    /**
     * Return available cameras for the filter dropdown.
     */
    public function cameras()
    {
        return response()->json(
            Camera::where('is_active', true)->select('id', 'label')->get()
        );
    }
}