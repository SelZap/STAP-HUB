<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\StapNode;

class LiveFeedController extends Controller
{
    /**
     * Show the public live camera feed page.
     */
    public function index()
    {
        return view('live-feed');
    }

    /**
     * Return active cameras for the live feed view.
     */
    public function cameras()
    {
        $cameras = Camera::where('is_active', true)
            ->with('node:id,name,status')
            ->select('id', 'node_id', 'label', 'position', 'stream_url')
            ->get();

        return response()->json($cameras);
    }
}