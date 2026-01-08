<?php

namespace Database\Seeders;

use App\Models\TrafficData;
use Illuminate\Database\Seeder;

class TrafficDataSeeder extends Seeder
{
    public function run(): void
    {
        $roads = ['Main Street', 'Oak Avenue', 'Pine Road', 'Elm Street', 'Maple Drive'];
        $weathers = ['Sunny', 'Rainy', 'Cloudy', 'Partially Cloudy', 'Foggy'];
        $levels = ['light', 'medium', 'heavy'];

        for ($i = 0; $i < 100; $i++) {
            TrafficData::create([
                'date' => now()->subDays(rand(0, 30)),
                'time' => sprintf('%02d:%02d:00', rand(6, 22), rand(0, 59)),
                'road' => $roads[array_rand($roads)],
                'level_of_service' => $levels[array_rand($levels)],
                'weather' => $weathers[array_rand($weathers)],
            ]);
        }
    }
}