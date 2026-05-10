<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\FootageRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FootageRequestController extends Controller
{
    public function index()
    {
        $cameras = Camera::where('status', 'active')
            ->orderBy('camera_id')
            ->get(['camera_id', 'label', 'direction']);

        return view('public.data-request', compact('cameras'));
    }

    public function cameras()
    {
        $cameras = Camera::where('status', 'active')
            ->with('node:node_id,location_label')
            ->get(['camera_id', 'node_id', 'label', 'direction'])
            ->map(fn($cam) => [
                'camera_id' => $cam->camera_id,
                'label'     => $cam->label,
                'direction' => $cam->direction,
                'location'  => $cam->node->location_label ?? '',
            ]);

        return response()->json($cameras);
    }

    public function store(Request $request)
    {
        $isMulti = $request->input('multi_date') === '1';

        // ── Base rules ─────────────────────────────────────
        $rules = [
            'requester_name'         => 'required|string|max:150',
            'requester_organization' => 'nullable|string|max:150',
            'requester_address'      => 'nullable|string|max:500',
            'requester_email'        => 'required|email|max:150',
            'requester_contact'      => 'required|string|max:50',
            'request_nature'         => 'required|in:academic,personal,legal,media,other',
            'footage_time_start'     => 'required|date_format:H:i',
            'footage_time_end'       => 'required|date_format:H:i|after:footage_time_start',
            'incident_date'          => 'nullable|date',
            'incident_time'          => 'nullable|string|max:50',
            'names_involved'         => 'nullable|string|max:500',
            'incident_description'   => 'nullable|string|max:2000',
        ];

        // ── Camera: accept a real camera_id or "all" ───────
        $validCameraIds = Camera::where('status', 'active')->pluck('camera_id')->toArray();
        $rules['camera_id'] = [
            'required',
            Rule::in(array_merge(array_map('strval', $validCameraIds), ['all'])),
        ];

        // ── Date: single vs range ──────────────────────────
        if ($isMulti) {
            $rules['footage_date_start'] = 'required|date|before_or_equal:today';
            $rules['footage_date_end']   = 'required|date|after_or_equal:footage_date_start|before_or_equal:today';
        } else {
            $rules['footage_date'] = 'required|date|before_or_equal:today';
        }

        // ── "Other" nature requires a reason ───────────────
        if ($request->input('request_nature') === 'other') {
            $rules['other_reason'] = 'required|string|max:500';
        }

        $validated = $request->validate($rules);

        // ── Resolve camera_id: "all" → null ───────────────
        $cameraId = $validated['camera_id'] === 'all'
            ? null
            : (int) $validated['camera_id'];

        // ── Resolve dates ──────────────────────────────────
        $footageDate      = $isMulti ? null : $validated['footage_date'];
        $footageDateStart = $isMulti ? $validated['footage_date_start'] : null;
        $footageDateEnd   = $isMulti ? $validated['footage_date_end']   : null;

        $footageRequest = FootageRequest::create([
            'camera_id'              => $cameraId,
            'requester_name'         => $validated['requester_name'],
            'requester_organization' => $validated['requester_organization'] ?? null,
            'requester_address'      => $validated['requester_address'] ?? null,
            'requester_email'        => $validated['requester_email'],
            'requester_contact'      => $validated['requester_contact'],
            'request_nature'         => $validated['request_nature'],
            'other_reason'           => $validated['other_reason'] ?? null,
            'footage_date'           => $footageDate,
            'footage_date_start'     => $footageDateStart,
            'footage_date_end'       => $footageDateEnd,
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