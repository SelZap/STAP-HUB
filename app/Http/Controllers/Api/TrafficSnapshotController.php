<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrafficSnapshot;
use App\Models\Camera;
use Illuminate\Http\Request;

class TrafficSnapshotController extends Controller
{
    /**
     * Receive a traffic snapshot posted by a STAP Node.
     *
     * Expects JSON payload with camera_id, vehicle_count,
     * congestion_level, image_url (Cloudinary URL), and captured_at.
     */
    public function store(Request $request)
    {
        $request->validate([
            'camera_id'        => 'required|exists:cameras,id',
            'vehicle_count'    => 'required|integer|min:0',
            'congestion_level' => 'required|in:low,moderate,high,severe',
            'image_url'        => 'nullable|url',
            'video_url'        => 'nullable|url',
            'captured_at'      => 'required|date',
            'vehicle_breakdown' => 'nullable|array',  // e.g. { "car": 10, "truck": 2, ... }
        ]);

        // Verify camera belongs to authenticated node
        $node   = $request->attributes->get('stap_node');
        $camera = Camera::where('id', $request->camera_id)
                        ->where('node_id', $node->id)
                        ->firstOrFail();

        $snapshot = TrafficSnapshot::create([
            'camera_id'         => $camera->id,
            'vehicle_count'     => $request->vehicle_count,
            'congestion_level'  => $request->congestion_level,
            'image_url'         => $request->image_url,
            'video_url'         => $request->video_url,
            'captured_at'       => $request->captured_at,
            'vehicle_breakdown' => $request->vehicle_breakdown,
        ]);

        // Broadcast to dashboard in real-time
        // TODO: event(new TrafficSnapshotReceived($snapshot));

        return response()->json(['message' => 'Snapshot recorded.', 'id' => $snapshot->id], 201);
    }
}