<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FootageRequest;
use App\Models\AdminActivityLog;
use App\Mail\FootageRequestMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RequestController extends Controller
{
    public function index()
    {
        return view('admin.requests');
    }

    public function list(Request $request)
    {
        $query = FootageRequest::with('handler:admin_id,admin_name')->latest();

        if ($request->filled('tab')) {
            match($request->tab) {
                'new'      => $query->where('status', 'pending'),
                'ongoing'  => $query->whereIn('status', ['approved', 'requirements_sent', 'reviewed']),
                'rejected' => $query->where('status', 'rejected'),
                default    => null,
            };
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(30));
    }

    public function show(int|string $request_id)
    {
        // 'messages' relationship removed — foreign key mismatch on request_messages table
        $footageRequest = FootageRequest::with('handler:admin_id,admin_name')
            ->where('request_id', $request_id)
            ->firstOrFail();

        return response()->json($footageRequest);
    }

    public function updateStatus(Request $request, int|string $id)
    {
        $request->validate(['status' => 'required|in:pending,reviewed,requirements_sent,approved,rejected']);

        $footageRequest = FootageRequest::where('request_id', $id)->firstOrFail();
        $footageRequest->status = $request->status;

        if (in_array($request->status, ['approved', 'rejected'])) {
            $footageRequest->handled_by = Auth::guard('admin')->user()->admin_id;
        }

        $footageRequest->save();

        AdminActivityLog::create([
            'admin_id'     => Auth::guard('admin')->user()->admin_id,
            'target_type'  => 'footage_request',
            'target_id'    => $footageRequest->request_id,
            'target_label' => 'Footage Request #' . $footageRequest->request_id,
            'details'      => 'Status updated to ' . $request->status,
        ]);

        return response()->json([
            'message' => 'Status updated.',
            'request' => $footageRequest->load('handler:admin_id,admin_name'),
        ]);
    }

    public function sendEmail(Request $request, int|string $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $footageRequest = FootageRequest::where('request_id', $id)->firstOrFail();

        if (! $footageRequest->requester_email) {
            return response()->json(['success' => false, 'message' => 'This request has no email address on file.'], 422);
        }

        Mail::raw($request->body, function ($m) use ($footageRequest, $request) {
            $m->to($footageRequest->requester_email)->subject($request->subject);
        });

        AdminActivityLog::create([
            'admin_id'     => Auth::guard('admin')->user()->admin_id,
            'target_type'  => 'footage_request',
            'target_id'    => $footageRequest->request_id,
            'target_label' => 'Footage Request #' . $footageRequest->request_id,
            'details'      => 'Email sent to ' . $footageRequest->requester_email,
        ]);

        return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
    }

    public function sendRequirements(Request $request, int|string $id)
    {
        $request->validate(['message_body' => 'required|string']);

        $footageRequest = FootageRequest::where('request_id', $id)->firstOrFail();

        $footageRequest->status = 'requirements_sent';
        $footageRequest->save();

        AdminActivityLog::create([
            'admin_id'     => Auth::guard('admin')->user()->admin_id,
            'target_type'  => 'footage_request',
            'target_id'    => $footageRequest->request_id,
            'target_label' => 'Footage Request #' . $footageRequest->request_id,
            'details'      => 'Requirements sent to requester.',
        ]);

        return response()->json(['message' => 'Requirements sent to requester.']);
    }

    public function markReviewed(int|string $id)
    {
        $footageRequest = FootageRequest::where('request_id', $id)->firstOrFail();
        $footageRequest->status = 'reviewed';
        $footageRequest->save();

        AdminActivityLog::create([
            'admin_id'     => Auth::guard('admin')->user()->admin_id,
            'target_type'  => 'footage_request',
            'target_id'    => $footageRequest->request_id,
            'target_label' => 'Footage Request #' . $footageRequest->request_id,
            'details'      => 'Request marked as reviewed.',
        ]);

        return response()->json(['message' => 'Request marked as reviewed.']);
    }
}