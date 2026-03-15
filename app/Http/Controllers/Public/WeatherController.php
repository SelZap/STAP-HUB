<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\WeatherLog;
use App\Models\StapNode;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * Show the public rain & weather page.
     */
    public function index()
    {
        return view('weather');
    }

    /**
     * Return latest weather log per node.
     */
    public function latest()
    {
        $nodes = StapNode::with(['weatherLogs' => function ($q) {
            $q->latest()->limit(1);
        }])->get();

        return response()->json($nodes);
    }

    /**
     * Return weather history for a given node.
     *
     * Filters: node_id (required), date_from, date_to
     */
    public function history(Request $request)
    {
        $request->validate([
            'node_id' => 'required|exists:stap_nodes,id',
        ]);

        $query = WeatherLog::where('node_id', $request->node_id)->latest('recorded_at');

        if ($request->filled('date_from')) {
            $query->whereDate('recorded_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('recorded_at', '<=', $request->date_to);
        }

        return response()->json($query->paginate(50));
    }
}