<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FootageRequest;
use Illuminate\Http\Request;

class FootageRequestController extends Controller
{
    /**
     * Submit a footage request from the public modal form.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email'          => 'required|email|max:255',
            'contact_number' => 'required|string|max:30',
            'nature'         => 'required|in:academic,personal,legal,media,other',
            'footage_date'   => 'required|date',
            'time_from'      => 'required|date_format:H:i',
            'time_to'        => 'required|date_format:H:i|after:time_from',
            'details'        => 'nullable|string|max:1000',
        ]);

        $footageRequest = FootageRequest::create([
            'email'          => $request->email,
            'contact_number' => $request->contact_number,
            'nature'         => $request->nature,
            'footage_date'   => $request->footage_date,
            'time_from'      => $request->time_from,
            'time_to'        => $request->time_to,
            'details'        => $request->details,
            'status'         => 'pending',
        ]);

        return response()->json([
            'message'    => 'Your footage request has been submitted. We will contact you via email.',
            'request_id' => $footageRequest->id,
        ], 201);
    }
}