<?php

namespace App\Http\Controllers;

use App\Models\TrafficArchive;
use Illuminate\Http\Request;

class TrafficArchiveController extends Controller
{
    public function index()
    {
        return view('traffic-data-archive');
    }

    public function getData(Request $request)
    {
        $query = TrafficArchive::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('archive_id', 'like', "%{$search}%")
                  ->orWhere('date', 'like', "%{$search}%")
                  ->orWhere('time', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('los') && $request->los !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('gil_fernando_los', $request->los)
                  ->orWhere('sumulong_los', $request->los);
            });
        }

        $archives = $query->orderBy('date', 'desc')
                         ->orderBy('time', 'desc')
                         ->paginate(10);

        return response()->json($archives);
    }

    public function download($id)
    {
        $archive = TrafficArchive::where('archive_id', $id)->firstOrFail();
        return response()->json($archive);
    }
}