<?php

namespace Database\Seeders;

use App\Models\StapNode;
use App\Models\WeatherLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeatherLogSeeder extends Seeder
{
    private function getRainIntensity(int $hour): string
    {
        $isAfternoon = $hour >= 12 && $hour <= 17;
        $roll        = rand(1, 10);

        if ($isAfternoon) {
            if ($roll <= 2) return 'heavy';
            if ($roll <= 4) return 'moderate';
            if ($roll <= 6) return 'light';
        } else {
            if ($roll <= 1) return 'heavy';
            if ($roll <= 2) return 'moderate';
            if ($roll <= 3) return 'light';
        }

        return 'none';
    }

    public function run(): void
    {
        DB::table('weather_logs')->truncate();

        $nodeIds = StapNode::pluck('node_id');

        if ($nodeIds->isEmpty()) {
            $this->command->warn('No nodes found. Run StapNodeSeeder first.');
            return;
        }

        $logs = [];
        $now  = Carbon::now();

        for ($daysAgo = 7; $daysAgo >= 0; $daysAgo--) {
            for ($hour = 0; $hour < 24; $hour++) {
                for ($minute = 0; $minute < 60; $minute += 30) {
                    $recordedAt = $now->copy()
                        ->subDays($daysAgo)
                        ->setHour($hour)
                        ->setMinute($minute)
                        ->setSecond(0);

                    foreach ($nodeIds as $nodeId) {
                        $logs[] = [
                            'node_id'       => $nodeId,
                            'rain_intensity' => $this->getRainIntensity($hour),
                            'recorded_at'   => $recordedAt->toDateTimeString(),
                        ];

                        if (count($logs) >= 500) {
                            WeatherLog::insert($logs);
                            $logs = [];
                        }
                    }
                }
            }
        }

        if (!empty($logs)) {
            WeatherLog::insert($logs);
        }

        $this->command->info('Weather logs seeded successfully.');
    }
}