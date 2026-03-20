<?php

namespace Database\Seeders;

use App\Models\FootageRequest;
use App\Models\RequestMessage;
use App\Models\Camera;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FootageRequestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('request_messages')->truncate();
        DB::table('footage_requests')->truncate();

        $cam1Id      = Camera::where('label', 'Alpha Cam 1')->value('camera_id');
        $cam2Id      = Camera::where('label', 'Alpha Cam 2')->value('camera_id');
        $cam3Id      = Camera::where('label', 'Beta Cam 1')->value('camera_id');
        $superuserId = Admin::where('email', 'admin@staphub.local')->value('admin_id');

        // ── Pending ──────────────────────────────────────────────────
        FootageRequest::create([
            'camera_id'          => $cam1Id,
            'requester_email'    => 'juandelacruz@email.com',
            'requester_contact'  => '09171234567',
            'request_nature'     => 'legal',
            'footage_date'       => Carbon::now()->subDays(3)->toDateString(),
            'footage_time_start' => '08:00:00',
            'footage_time_end'   => '09:30:00',
            'status'             => 'pending',
            'handled_by'         => null,
        ]);

        // ── Under Review ─────────────────────────────────────────────
        FootageRequest::create([
            'camera_id'          => $cam2Id,
            'requester_email'    => 'researcher@university.edu.ph',
            'requester_contact'  => '09281234567',
            'request_nature'     => 'academic',
            'footage_date'       => Carbon::now()->subDays(5)->toDateString(),
            'footage_time_start' => '17:00:00',
            'footage_time_end'   => '19:00:00',
            'status'             => 'under_review',
            'handled_by'         => $superuserId,
        ]);

        // ── Approved ─────────────────────────────────────────────────
        $approved = FootageRequest::create([
            'camera_id'          => $cam3Id,
            'requester_email'    => 'reporter@newsph.com',
            'requester_contact'  => '09191234567',
            'request_nature'     => 'media',
            'footage_date'       => Carbon::now()->subDays(10)->toDateString(),
            'footage_time_start' => '07:30:00',
            'footage_time_end'   => '08:30:00',
            'status'             => 'approved',
            'handled_by'         => $superuserId,
        ]);

        RequestMessage::create([
            'request_id'       => $approved->request_id,
            'sender_type'      => 'admin',
            'admin_id'         => $superuserId,
            'message'          => 'Your footage request has been approved. A secure download link will be sent to your email within 48 hours.',
            'requirement_list' => null,
        ]);

        // ── Rejected ─────────────────────────────────────────────────
        $rejected = FootageRequest::create([
            'camera_id'          => $cam1Id,
            'requester_email'    => 'unknown@email.com',
            'requester_contact'  => '09001234567',
            'request_nature'     => 'other',
            'footage_date'       => Carbon::now()->subDays(14)->toDateString(),
            'footage_time_start' => '00:00:00',
            'footage_time_end'   => '23:59:00',
            'status'             => 'rejected',
            'handled_by'         => $superuserId,
        ]);

        RequestMessage::create([
            'request_id'       => $rejected->request_id,
            'sender_type'      => 'admin',
            'admin_id'         => $superuserId,
            'message'          => 'We are unable to approve this request. Bulk footage requests covering an entire day cannot be accommodated. Please submit a specific time range (maximum 2 hours) with a valid reason.',
            'requirement_list' => null,
        ]);

        $this->command->info('Footage requests seeded successfully.');
    }
}