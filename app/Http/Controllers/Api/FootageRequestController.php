<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FootageRequest;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FootageRequestController extends Controller
{
    protected CloudinaryService $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * Receive footage upload from a STAP Node for an approved footage request.
     * POST /api/footage-requests/{footage_request_id}/upload
     */
    public function upload(Request $request, int $footageRequestId)
    {
        $footageRequest = FootageRequest::findOrFail($footageRequestId);

        // Only accept uploads for approved requests
        if ($footageRequest->status !== 'approved') {
            return response()->json([
                'message' => 'Footage can only be uploaded for approved requests.',
            ], 403);
        }

        $validated = $request->validate([
            'image' => 'nullable|string', // base64
            'video' => 'nullable|string', // base64
        ]);

        if (empty($validated['image']) && empty($validated['video'])) {
            return response()->json([
                'message' => 'At least one of image or video must be provided.',
            ], 422);
        }

        $imageUrl = null;
        $videoUrl = null;

        if (!empty($validated['image'])) {
            $imageUrl = $this->cloudinary->uploadImage(
                $validated['image'],
                'stap-hub/footage-requests'
            );
            if (!$imageUrl) {
                Log::warning('Footage image upload failed for footage_request_id: ' . $footageRequestId);
            }
        }

        if (!empty($validated['video'])) {
            $videoUrl = $this->cloudinary->uploadVideo(
                $validated['video'],
                'stap-hub/footage-requests'
            );
            if (!$videoUrl) {
                Log::warning('Footage video upload failed for footage_request_id: ' . $footageRequestId);
            }
        }

        $footageRequest->update([
            'image_url' => $imageUrl ?? $footageRequest->image_url,
            'video_url' => $videoUrl ?? $footageRequest->video_url,
        ]);

        return response()->json([
            'message'         => 'Footage uploaded successfully.',
            'footage_request' => $footageRequest,
        ], 200);
    }
}