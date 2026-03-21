<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrafficSnapshot;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrafficSnapshotController extends Controller
{
    protected CloudinaryService $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * Receive and store a traffic snapshot from a STAP Node.
     * POST /api/snapshots
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'camera_id'           => 'required|integer|exists:cameras,camera_id',
            'cars'                => 'required|integer|min:0',
            'trucks'              => 'required|integer|min:0',
            'motorcycles'         => 'required|integer|min:0',
            'buses'               => 'required|integer|min:0',
            'emergency_vehicles'  => 'required|integer|min:0',
            'congestion'          => 'required|in:free_flow,moderate,heavy,severe',
            'snapshot_time'       => 'required|date',
            'image'               => 'nullable|string',  // base64 image from STAP Node
            'video'               => 'nullable|string',  // base64 video from STAP Node
        ]);

        $imageUrl = null;
        $videoUrl = null;

        if (!empty($validated['image'])) {
            $imageUrl = $this->cloudinary->uploadImage($validated['image']);
            if (!$imageUrl) {
                Log::warning('Snapshot image upload to Cloudinary failed for camera_id: ' . $validated['camera_id']);
            }
        }

        if (!empty($validated['video'])) {
            $videoUrl = $this->cloudinary->uploadVideo($validated['video']);
            if (!$videoUrl) {
                Log::warning('Snapshot video upload to Cloudinary failed for camera_id: ' . $validated['camera_id']);
            }
        }

        $snapshot = TrafficSnapshot::create([
            'camera_id'          => $validated['camera_id'],
            'cars'               => $validated['cars'],
            'trucks'             => $validated['trucks'],
            'motorcycles'        => $validated['motorcycles'],
            'buses'              => $validated['buses'],
            'emergency_vehicles' => $validated['emergency_vehicles'],
            'congestion'         => $validated['congestion'],
            'snapshot_time'      => $validated['snapshot_time'],
            'image_url'          => $imageUrl,
            'video_url'          => $videoUrl,
        ]);

        return response()->json([
            'message'  => 'Snapshot received successfully.',
            'snapshot' => $snapshot,
        ], 201);
    }
}