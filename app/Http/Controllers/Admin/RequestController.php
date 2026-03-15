<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FootageRequest;
use App\Models\RequestMessage;
use App\Models\AdminActivityLog;
use App\Mail\FootageRequestMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RequestController extends Controller
{
    /**
     * Show the admin footage requests page.
     */
    public function index()
    {
        return view('admin.requests');
    }

    /**
     * List all footage requests with optional filters.
     *
     * Filters: status, nature
     */
    public function list(Request $request)
    {
        $query = FootageRequest::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('nature')) {
            $query->where('nature', $request->nature);
        }

        $requests = $query->paginate(30);

        return response()->json($requests);
    }

    /**
     * Show a single footage request with its message thread.
     */
    public function show($id)
    {
        $footageRequest = FootageRequest::with('messages')->findOrFail($id);
        return response()->json($footageRequest);
    }

    /**
     * Mark a request as reviewed.
     */
    public function markReviewed($id)
    {
        $footageRequest = FootageRequest::findOrFail($id);
        $footageRequest->status = 'reviewed';
        $footageRequest->save();

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'request_reviewed',
            'target_id' => $footageRequest->id,
        ]);

        return response()->json(['message' => 'Request marked as reviewed.']);
    }

    /**
     * Send a requirements message to the requester.
     * Stores the message and sends it via Laravel Mail.
     */
    public function sendRequirements(Request $request, $id)
    {
        $request->validate([
            'message_body' => 'required|string',
        ]);

        $footageRequest = FootageRequest::findOrFail($id);

        $message = RequestMessage::create([
            'footage_request_id' => $footageRequest->id,
            'sender'             => 'admin',
            'body'               => $request->message_body,
        ]);

        $footageRequest->status = 'requirements_sent';
        $footageRequest->save();

        // Send email to requester
        Mail::to($footageRequest->email)->send(
            new FootageRequestMessage($footageRequest, $message)
        );

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'request_requirements_sent',
            'target_id' => $footageRequest->id,
        ]);

        return response()->json(['message' => 'Requirements sent to requester.', 'msg' => $message]);
    }

    /**
     * Approve a footage request.
     */
    public function approve($id)
    {
        $footageRequest = FootageRequest::findOrFail($id);
        $footageRequest->status = 'approved';
        $footageRequest->save();

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'request_approved',
            'target_id' => $footageRequest->id,
        ]);

        return response()->json(['message' => 'Request approved.']);
    }

    /**
     * Reject a footage request.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $footageRequest = FootageRequest::findOrFail($id);
        $footageRequest->status = 'rejected';
        $footageRequest->save();

        if ($request->filled('reason')) {
            RequestMessage::create([
                'footage_request_id' => $footageRequest->id,
                'sender'             => 'admin',
                'body'               => $request->reason,
            ]);
        }

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'request_rejected',
            'target_id' => $footageRequest->id,
        ]);

        return response()->json(['message' => 'Request rejected.']);
    }
}