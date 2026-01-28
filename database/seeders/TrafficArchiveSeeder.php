<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrafficArchive;
use Carbon\Carbon;

class TrafficArchiveSeeder extends Seeder
{
    public function run(): void
    {
        $archives = [
            ['archive_id' => 'AR-24612474-53', 'date' => '2024-03-25', 'time' => '18:45:00', 'gil_fernando_los' => 'C', 'sumulong_los' => 'B', 'status' => 'Pending'],
            ['archive_id' => 'AR-24536474-45', 'date' => '2024-03-25', 'time' => '12:30:00', 'gil_fernando_los' => 'A', 'sumulong_los' => 'A', 'status' => 'Completed'],
            ['archive_id' => 'AR-26466374-44', 'date' => '2024-03-24', 'time' => '15:20:00', 'gil_fernando_los' => 'D', 'sumulong_los' => 'C', 'status' => 'Completed'],
            ['archive_id' => 'AR-24655532-11', 'date' => '2024-03-23', 'time' => '10:55:00', 'gil_fernando_los' => 'B', 'sumulong_los' => 'B', 'status' => 'Completed'],
            ['archive_id' => 'AR-64642415-23', 'date' => '2024-03-23', 'time' => '04:30:00', 'gil_fernando_los' => 'A', 'sumulong_los' => 'A', 'status' => 'Completed'],
            ['archive_id' => 'AR-64641474-51', 'date' => '2024-03-22', 'time' => '17:15:00', 'gil_fernando_los' => 'E', 'sumulong_los' => 'D', 'status' => 'Completed'],
            ['archive_id' => 'AR-24242474-63', 'date' => '2024-03-22', 'time' => '11:40:00', 'gil_fernando_los' => 'C', 'sumulong_los' => 'C', 'status' => 'Completed'],
            ['archive_id' => 'AR-24612424-12', 'date' => '2024-03-21', 'time' => '14:05:00', 'gil_fernando_los' => 'B', 'sumulong_los' => 'A', 'status' => 'Completed'],
            ['archive_id' => 'AR-24615374-53', 'date' => '2024-03-21', 'time' => '09:20:00', 'gil_fernando_los' => 'D', 'sumulong_los' => 'C', 'status' => 'Completed'],
            ['archive_id' => 'AR-24451474-32', 'date' => '2024-03-21', 'time' => '09:15:00', 'gil_fernando_los' => 'A', 'sumulong_los' => 'B', 'status' => 'Completed'],
            ['archive_id' => 'AR-24612475-54', 'date' => '2024-03-20', 'time' => '16:30:00', 'gil_fernando_los' => 'F', 'sumulong_los' => 'E', 'status' => 'Completed'],
            ['archive_id' => 'AR-24536475-46', 'date' => '2024-03-20', 'time' => '08:45:00', 'gil_fernando_los' => 'B', 'sumulong_los' => 'A', 'status' => 'Completed'],
            ['archive_id' => 'AR-26466375-45', 'date' => '2024-03-19', 'time' => '13:10:00', 'gil_fernando_los' => 'C', 'sumulong_los' => 'D', 'status' => 'Completed'],
            ['archive_id' => 'AR-24655533-12', 'date' => '2024-03-19', 'time' => '19:25:00', 'gil_fernando_los' => 'E', 'sumulong_los' => 'D', 'status' => 'Completed'],
            ['archive_id' => 'AR-64642416-24', 'date' => '2024-03-18', 'time' => '07:50:00', 'gil_fernando_los' => 'A', 'sumulong_los' => 'A', 'status' => 'Completed'],
        ];

        foreach ($archives as $archive) {
            TrafficArchive::create($archive);
        }
    }
}