<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrafficSnapshotSeeder extends Seeder
{
    private function getLos(int $total): string
    {
        return match (true) {
            $total <= 30  => 'A',
            $total <= 60  => 'B',
            $total <= 100 => 'C',
            $total <= 150 => 'D',
            $total <= 200 => 'E',
            default       => 'F',
        };
    }

    public function run(): void
    {
        $cameraIds = [1, 2, 3, 4, 5];
        $snapshots = [];
        $baseDate  = Carbon::now()->subDays(6)->startOfDay();

        for ($day = 0; $day < 7; $day++) {
            $date = $baseDate->copy()->addDays($day);

            foreach ($cameraIds as $cameraId) {
                for ($hour = 6; $hour <= 22; $hour++) {
                    foreach ([0, 30] as $min) {
                        $isRush = ($hour >= 6 && $hour <= 9) || ($hour >= 17 && $hour <= 20);

                        if ($isRush) {
                            $cars        = rand(40, 90);
                            $trucks      = rand(5, 15);
                            $motorcycles = rand(20, 50);
                            $miniBus     = rand(3, 10);
                            $ambulance   = rand(0, 1);
                            $fireTruck   = rand(0, 1);
                            $tricycle    = rand(5, 20);
                            $jeepney     = rand(10, 30);
                        } else {
                            $cars        = rand(5, 30);
                            $trucks      = rand(0, 5);
                            $motorcycles = rand(3, 20);
                            $miniBus     = rand(0, 4);
                            $ambulance   = 0;
                            $fireTruck   = 0;
                            $tricycle    = rand(1, 8);
                            $jeepney     = rand(2, 10);
                        }

                        $total = $cars + $trucks + $motorcycles + $miniBus
                            + $ambulance + $fireTruck + $tricycle + $jeepney;

                        $snapshots[] = [
                            'camera_id'        => $cameraId,
                            'vehicle_count'    => $total,
                            'cars'             => $cars,
                            'trucks'           => $trucks,
                            'motorcycles'      => $motorcycles,
                            'mini_bus'         => $miniBus,
                            'ambulance'        => $ambulance,
                            'fire_truck'       => $fireTruck,
                            'tricycle'         => $tricycle,
                            'jeepney'          => $jeepney,
                            'congestion_level' => $this->getLos($total),
                            'image_url'        => null,
                            'video_url'        => null,
                            'captured_at'      => $date->copy()->setTime($hour, $min)->toDateTimeString(),
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($snapshots, 500) as $chunk) {
            DB::table('traffic_snapshots')->insert($chunk);
        }
    }
}