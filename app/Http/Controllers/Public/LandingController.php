<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\StapNode;
use App\Models\Alert;

class LandingController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function index()
    {
        $activeNodes  = StapNode::where('status', 'online')->count();
        $activeAlerts = Alert::where('resolved', false)->count();

        return view('landing', compact('activeNodes', 'activeAlerts'));
    }
}