<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\TrafficSnapshot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrafficArchiveSeeder extends Seeder
{
    private array $sampleImages = [
        'https://res.cloudinary.com/demo/image/upload/v1312461204/sample.jpg',
        'https://res.cloudinary.com/demo/image/upload/v1371281596/sample_eggplant.jpg',
        'https://res.cloudinary.com/demo/image/upload/v1371281596/sample_elvish.jpg',
        'https://res.cloudinary.com/demo/image/upload/v1371281596/sample_sea_turtle.jpg',
        'https://res.cloudinary.com/demo/image/upload/v1371281596/sample.jpg',
    ];

    private function getVehicleCount(int $hour): int
    {
        if (($hour >= 7 && $hour <= 9) || ($hour >= 17 && $hour <= 20)) {
            return rand(60, 120);
        }
        if ($hour >= 10 && $hour <= 16) {
            return rand(30, 70);
        }
        return rand(2, 25);
    }

    private function getCongestionFromCount(int $count): string
    {
        if ($count >= 90) return 'severe';
        if ($count >= 60) return 'heavy';
        if ($count >= 30) return 'moderate';
        return 'free_flow';
    }

    public function run(): void
    {
        DB::table('traffic_snapshots')->truncate();

        $cameraIds = Camera::where('status', 'active')->pluck('camera_id');

        if ($cameraIds->isEmpty()) {
            $this->command->warn('No active cameras found. Run CameraSeeder first.');
            return;
        }

        $snapshots = [];
        $now       = Carbon::now();

        for ($daysAgo = 7; $daysAgo >= 0; $daysAgo--) {
            for ($hour = 0; $hour < 24; $hour++) {
                for ($minute = 0; $minute < 60; $minute += 15) {
                    $capturedAt = $now->copy()
                        ->subDays($daysAgo)
                        ->setHour($hour)
                        ->setMinute($minute)
                        ->setSecond(0);

                    foreach ($cameraIds as $cameraId) {
                        $total      = $this->getVehicleCount($hour);
                        $congestion = $this->getCongestionFromCount($total);

                        $snapshots[] = [
                            'camera_id'          => $cameraId,
                            'vehicle_count'      => $total,
                            'cars'               => (int)($total * 0.55),
                            'motorcycles'        => (int)($total * 0.25),
                            'trucks'             => (int)($total * 0.10),
                            'buses'              => (int)($total * 0.07),
                            'emergency_vehicles' => rand(0, 1),
                            'congestion_level'   => $congestion,
                            'image_url'          => $this->sampleImages[array_rand($this->sampleImages)],
                            'video_url'          => null,
                            'captured_at'        => $capturedAt->toDateTimeString(),
                        ];

                        if (count($snapshots) >= 500) {
                            TrafficSnapshot::insert($snapshots);
                            $snapshots = [];
                        }
                    }
                }
            }
        }

        if (!empty($snapshots)) {
            TrafficSnapshot::insert($snapshots);
        }

        $this->command->info('Traffic archive seeded successfully.');
    }
}