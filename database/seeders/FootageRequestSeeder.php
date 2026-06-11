<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FootageRequestSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('footage_requests')->insert([
            [
                'camera_id'              => 1,
                'requester_name'         => 'Maria Santos',
                'requester_organization' => 'University of the Philippines',
                'requester_address'      => 'Diliman, Quezon City, Metro Manila',
                'requester_email'        => 'maria.santos@up.edu.ph',
                'requester_contact'      => '09171234567',
                'incident_date'          => $now->copy()->subDays(10)->toDateString(),
                'incident_time'          => '08:30 AM',
                'names_involved'         => null,
                'incident_description'   => 'Observed unusual traffic buildup near the intersection for academic traffic study.',
                'request_nature'         => 'academic',
                'footage_date'           => $now->copy()->subDays(10)->toDateString(),
                'footage_time_start'     => '08:00:00',
                'footage_time_end'       => '09:00:00',
                'status'                 => 'approved',
                'handled_by'             => 1,
                'created_at'             => $now->copy()->subDays(8)->toDateTimeString(),
                'updated_at'             => $now->copy()->subDays(5)->toDateTimeString(),
            ],
            [
                'camera_id'              => 2,
                'requester_name'         => 'Juan dela Cruz',
                'requester_organization' => null,
                'requester_address'      => '123 Mabini St., Marikina City',
                'requester_email'        => 'juan.delacruz@gmail.com',
                'requester_contact'      => '09289876543',
                'incident_date'          => $now->copy()->subDays(5)->toDateString(),
                'incident_time'          => '06:45 PM',
                'names_involved'         => 'Unknown driver of white van',
                'incident_description'   => 'Witnessed a near-miss accident involving a motorcycle and a van at the intersection.',
                'request_nature'         => 'personal',
                'footage_date'           => $now->copy()->subDays(5)->toDateString(),
                'footage_time_start'     => '18:30:00',
                'footage_time_end'       => '19:00:00',
                'status'                 => 'under_review',
                'handled_by'             => null,
                'created_at'             => $now->copy()->subDays(3)->toDateTimeString(),
                'updated_at'             => $now->copy()->subDays(2)->toDateTimeString(),
            ],
            [
                'camera_id'              => 3,
                'requester_name'         => 'Atty. Rosa Reyes',
                'requester_organization' => 'Reyes & Associates Law Office',
                'requester_address'      => '5F Eastwood City, Libis, Quezon City',
                'requester_email'        => 'rosa.reyes@reyeslaw.ph',
                'requester_contact'      => '09561112233',
                'incident_date'          => $now->copy()->subDays(14)->toDateString(),
                'incident_time'          => '11:00 AM',
                'names_involved'         => 'Pedro Manalo, delivery truck driver',
                'incident_description'   => 'Footage required as evidence for an ongoing vehicular accident case filed with the Quezon City RTC.',
                'request_nature'         => 'legal',
                'footage_date'           => $now->copy()->subDays(14)->toDateString(),
                'footage_time_start'     => '10:45:00',
                'footage_time_end'       => '11:30:00',
                'status'                 => 'pending',
                'handled_by'             => null,
                'created_at'             => $now->copy()->subDays(1)->toDateTimeString(),
                'updated_at'             => $now->copy()->subDays(1)->toDateTimeString(),
            ],
            [
                'camera_id'              => 4,
                'requester_name'         => 'Carlo Mendoza',
                'requester_organization' => 'GMA Network',
                'requester_address'      => 'EDSA, Diliman, Quezon City',
                'requester_email'        => 'carlo.mendoza@gmanews.tv',
                'requester_contact'      => '09178889900',
                'incident_date'          => $now->copy()->subDays(3)->toDateString(),
                'incident_time'          => '07:15 AM',
                'names_involved'         => null,
                'incident_description'   => 'Traffic incident footage for a news segment on road safety in Quezon City.',
                'request_nature'         => 'media',
                'footage_date'           => $now->copy()->subDays(3)->toDateString(),
                'footage_time_start'     => '07:00:00',
                'footage_time_end'       => '07:30:00',
                'status'                 => 'rejected',
                'handled_by'             => 1,
                'created_at'             => $now->copy()->subDays(2)->toDateTimeString(),
                'updated_at'             => $now->copy()->subDays(1)->toDateTimeString(),
            ],
        ]);
    }
}