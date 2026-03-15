<?php

namespace App\Http\Middleware;

use App\Models\StapNode;
use Closure;
use Illuminate\Http\Request;

class AuthenticateStapNode
{
    /**
     * Validate the STAP Node API key sent in the request header.
     *
     * Nodes must include: Authorization: Bearer <api_key>
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->bearerToken();

        if (! $apiKey) {
            return response()->json(['message' => 'API key missing.'], 401);
        }

        $node = StapNode::where('api_key', $apiKey)->first();

        if (! $node) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        if ($node->status === 'disabled') {
            return response()->json(['message' => 'This node has been disabled.'], 403);
        }

        // Attach node to request for use in controllers
        $request->attributes->set('stap_node', $node);

        return $next($request);
    }
}