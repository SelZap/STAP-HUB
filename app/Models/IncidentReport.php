<?php

namespace App\Http\Controllers;

use App\Models\IncidentReport;
use App\Models\AdminActivityLog;
use App\Mail\IncidentReportReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class IncidentReportController extends Controller
{
    // PUBLIC — Show form
    public function create()
    {
        return view('public.incident-report');
    }

    // PUBLIC — Handle submission
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'incident_date'           => 'required|date|before_or_equal:today',
            'incident_time'           => 'required|date_format:H:i',
            'environmental_condition' => 'required|in:clear,cloudy,rainy,foggy,night',
            'location_description'    => 'required|string|max:500',
            'vehicle_type'            => 'nullable|array',
            'vehicle_type.*'          => 'in:car,truck,motorcycle,bus,mini_bus,tricycle,jeepney,ambulance,fire_truck,emergency_vehicle',
            'vehicle_count'           => 'nullable|integer|min:1|max:255',
            'people_hurt'             => 'required|boolean',
            'injured_count'           => 'nullable|integer|min:1|max:255|required_if:people_hurt,1',
            'description'             => 'required|string|min:20',
            'reporting_party_name'    => 'required|string|max:255',
            'reporter_email'          => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $report = IncidentReport::create([
            'incident_date'           => $request->incident_date,
            'incident_time'           => $request->incident_time,
            'environmental_condition' => $request->environmental_condition,
            'location_description'    => $request->location_description,
            'vehicle_type'            => $request->vehicle_type ? implode(',', $request->vehicle_type) : null,
            'vehicle_count'           => $request->vehicle_count,
            'people_hurt'             => $request->people_hurt,
            'injured_count'           => $request->people_hurt ? $request->injured_count : null,
            'description'             => $request->description,
            'reporting_party_name'    => $request->reporting_party_name,
            'reporter_email'          => $request->reporter_email,
            'status'                  => 'pending',
        ]);

        Mail::to($report->reporter_email)->send(new IncidentReportReceived($report));

        return response()->json([
            'success' => true,
            'message' => 'Your incident report has been submitted successfully. Please check your email for confirmation.',
        ]);
    }

    // ADMIN — List all reports
    public function index()
    {
        $reports = IncidentReport::with('reviewer')
            ->orderByRaw("FIELD(status, 'pending', 'reviewed')")
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingCount = IncidentReport::pending()->count();

        return view('admin.incident-reports', compact('reports', 'pendingCount'));
    }

    // ADMIN — Mark as reviewed
    public function markReviewed(Request $request, $id)
    {
        $report = IncidentReport::findOrFail($id);

        if ($report->status === 'reviewed') {
            return response()->json([
                'success' => false,
                'message' => 'Report is already marked as reviewed.',
            ], 409);
        }

        $admin = auth('admin')->user();

        $report->update([
            'status'      => 'reviewed',
            'reviewed_by' => $admin->admin_id,
            'reviewed_at' => now(),
        ]);

        AdminActivityLog::create([
            'admin_id'     => $admin->admin_id,
            'target_type'  => 'incident_report',
            'target_id'    => $report->incident_id,
            'target_label' => 'Incident Report #' . $report->incident_id,
            'details'      => 'Reviewed incident report submitted by ' . $report->reporting_party_name,
        ]);

        return response()->json([
            'success'      => true,
            'message'      => 'Report marked as reviewed.',
            'pendingCount' => IncidentReport::pending()->count(),
        ]);
    }

    // ADMIN — Pending count for dashboard banner
    public function pendingCount()
    {
        return response()->json([
            'pendingCount' => IncidentReport::pending()->count(),
        ]);
    }
}