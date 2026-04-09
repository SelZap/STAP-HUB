<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\FootageRequest;
use Illuminate\Http\Request;

class FootageRequestController extends Controller
{
    /**
     * Show the public footage / data request page.
     */
    public function index()
    {
        return view('public.data-request');
    }

    /**
     * Return active cameras for the request form dropdown.
     */
    public function cameras()
    {
        $cameras = Camera::where('status', 'active')
            ->with('node:node_id,location_label')
            ->get(['camera_id', 'node_id', 'label', 'direction'])
            ->map(fn ($cam) => [
                'camera_id' => $cam->camera_id,
                'label'     => $cam->label,
                'direction' => $cam->direction,
                'location'  => $cam->node->location_label ?? '',
            ]);

        return response()->json($cameras);
    }

    /**
     * Submit a footage request from the public form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'camera_id'              => 'required|integer|exists:cameras,camera_id',
            'requester_name'         => 'required|string|max:150',
            'requester_organization' => 'nullable|string|max:150',
            'requester_address'      => 'nullable|string|max:500',
            'requester_email'        => 'required|email|max:150',
            'requester_contact'      => 'required|string|max:50',
            'request_nature'         => 'required|in:academic,personal,legal,media,other',
            'footage_date'           => 'required|date|before_or_equal:today',
            'footage_time_start'     => 'required|date_format:H:i',
            'footage_time_end'       => 'required|date_format:H:i|after:footage_time_start',
            'incident_date'          => 'nullable|date',
            'incident_time'          => 'nullable|string|max:50',
            'names_involved'         => 'nullable|string|max:500',
            'incident_description'   => 'nullable|string|max:2000',
        ]);

        $footageRequest = FootageRequest::create([
            'camera_id'              => $validated['camera_id'],
            'requester_name'         => $validated['requester_name'],
            'requester_organization' => $validated['requester_organization'] ?? null,
            'requester_address'      => $validated['requester_address'] ?? null,
            'requester_email'        => $validated['requester_email'],
            'requester_contact'      => $validated['requester_contact'],
            'request_nature'         => $validated['request_nature'],
            'footage_date'           => $validated['footage_date'],
            'footage_time_start'     => $validated['footage_time_start'] . ':00',
            'footage_time_end'       => $validated['footage_time_end'] . ':00',
            'incident_date'          => $validated['incident_date'] ?? null,
            'incident_time'          => $validated['incident_time'] ?? null,
            'names_involved'         => $validated['names_involved'] ?? null,
            'incident_description'   => $validated['incident_description'] ?? null,
            'status'                 => 'pending',
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Your footage request has been submitted. We will contact you via email.',
            'request_id' => $footageRequest->request_id,
        ], 201);
    }
}