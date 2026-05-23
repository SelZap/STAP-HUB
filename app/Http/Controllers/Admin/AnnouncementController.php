<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function active()
    {
        $announcements = Announcement::active()
            ->with('incidentReport')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($a) => $this->withUrl($a));

        return response()->json($announcements);
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->list();
        }

        return view('admin.announcements');
    }

    public function list()
    {
        $announcements = Announcement::with(['creator', 'incidentReport'])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(fn($a) => $this->withUrl($a));

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'type'               => 'required|in:general,incident,weather,maintenance,emergency',
            'content'            => 'required|string',
            'expires_at'         => 'nullable|string',
            'is_active'          => 'nullable',
            'incident_report_id' => 'nullable|exists:incident_reports,incident_id',
            'attachment'         => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,pdf|max:20480',
        ]);

        $expiresAt = null;
        if ($request->filled('expires_at')) {
            try {
                // Parse as Asia/Manila (PH local time) then convert to UTC for storage
                $expiresAt = Carbon::parse($request->expires_at, 'Asia/Manila')->utc();
            } catch (\Exception $e) {
                $expiresAt = null;
            }
        }

        $attachmentPath = null;
        $attachmentName = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file           = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('announcements', 'public');
        }

        $announcement = Announcement::create([
            'created_by'         => $admin->admin_id,
            'title'              => $validated['title'],
            'type'               => $validated['type'],
            'content'            => $validated['content'],
            'expires_at'         => $expiresAt,
            'is_active'          => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
            'incident_report_id' => $request->input('incident_report_id') ?: null,
            'attachment_path'    => $attachmentPath,
            'attachment_name'    => $attachmentName,
        ]);

        return response()->json(
            $this->withUrl($announcement->load(['creator', 'incidentReport'])),
            201
        );
    }

    public function toggle(int|string $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update(['is_active' => ! $announcement->is_active]);

        return response()->json(
            $this->withUrl($announcement->load(['creator', 'incidentReport']))
        );
    }

    public function destroy(int|string $id)
    {
        $announcement = Announcement::findOrFail($id);

        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted.']);
    }

    private function withUrl($a)
    {
        $a->attachment_url = $a->attachment_path
            ? asset('storage/' . $a->attachment_path)
            : null;

        return $a;
    }
}