<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeatherLog;
use Illuminate\Http\Request;

class WeatherLogController extends Controller
{
    /**
     * Receive a weather log entry posted by a STAP Node.
     */
    public function store(Request $request)
    {
        $request->validate([
            'temperature'   => 'nullable|numeric',
            'humidity'      => 'nullable|numeric|min:0|max:100',
            'rainfall_mm'   => 'nullable|numeric|min:0',
            'condition'     => 'nullable|string|max:100',
            'recorded_at'   => 'required|date',
        ]);

        $node = $request->attributes->get('stap_node');

        $log = WeatherLog::create([
            'node_id'       => $node->id,
            'temperature'   => $request->temperature,
            'humidity'      => $request->humidity,
            'rainfall_mm'   => $request->rainfall_mm,
            'condition'     => $request->condition,
            'recorded_at'   => $request->recorded_at,
        ]);

        return response()->json(['message' => 'Weather log recorded.', 'id' => $log->id], 201);
    }
}