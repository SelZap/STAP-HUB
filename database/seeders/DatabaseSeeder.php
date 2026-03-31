<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks so truncate works cleanly across all seeders
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call([
            AdminSeeder::class,
            StapNodeSeeder::class,
            CameraSeeder::class,
            TrafficLightSeeder::class,
            TrafficSnapshotSeeder::class,
            WeatherLogSeeder::class,
            AlertSeeder::class,
            FootageRequestSeeder::class,
        ]);

        // Re-enable FK checks after all seeders finish
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}