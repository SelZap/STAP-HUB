<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Receive an alert triggered by a STAP Node.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'         => 'required|string|max:100',
            'severity'     => 'required|in:info,warning,critical',
            'message'      => 'required|string',
            'triggered_at' => 'required|date',
        ]);

        $node = $request->attributes->get('stap_node');

        $alert = Alert::create([
            'node_id'      => $node->id,
            'type'         => $request->type,
            'severity'     => $request->severity,
            'message'      => $request->message,
            'triggered_at' => $request->triggered_at,
            'resolved'     => false,
        ]);

        // TODO: event(new AlertTriggered($alert)); — push to admin dashboard in real-time

        return response()->json(['message' => 'Alert recorded.', 'id' => $alert->id], 201);
    }
}