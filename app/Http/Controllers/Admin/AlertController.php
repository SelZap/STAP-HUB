<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    /**
     * Show the admin alerts / system status view.
     */
    public function index()
    {
        return view('admin.alerts');
    }

    /**
     * List alerts with optional filters.
     *
     * Filters: resolved (bool), type, node_id, severity
     */
    public function list(Request $request)
    {
        $query = Alert::with('node:id,name')->latest('triggered_at');

        if ($request->has('resolved')) {
            $query->where('resolved', filter_var($request->resolved, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('node_id')) {
            $query->where('node_id', $request->node_id);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $alerts = $query->paginate(50);

        return response()->json($alerts);
    }

    /**
     * Get a single alert.
     */
    public function show($id)
    {
        $alert = Alert::with('node:id,name')->findOrFail($id);
        return response()->json($alert);
    }

    /**
     * Mark an alert as resolved.
     */
    public function resolve($id)
    {
        $alert = Alert::findOrFail($id);

        if ($alert->resolved) {
            return response()->json(['message' => 'Alert is already resolved.'], 422);
        }

        $alert->resolved    = true;
        $alert->resolved_at = now();
        $alert->save();

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'alert_resolved',
            'target_id' => $alert->id,
            'notes'     => "Alert resolved: {$alert->type}",
        ]);

        return response()->json(['message' => 'Alert resolved.', 'alert' => $alert]);
    }
}