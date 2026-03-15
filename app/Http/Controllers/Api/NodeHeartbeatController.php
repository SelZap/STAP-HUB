<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NodeHeartbeatController extends Controller
{
    /**
     * Receive a heartbeat ping from a STAP Node.
     * Updates the node's last_ping_at and status.
     */
    public function ping(Request $request)
    {
        $node = $request->attributes->get('stap_node');

        $node->last_ping_at = now();
        $node->status       = 'online';
        $node->save();

        return response()->json(['message' => 'Heartbeat received.', 'server_time' => now()->toISOString()]);
    }
}