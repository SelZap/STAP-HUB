<?php

namespace Database\Seeders;

use App\Models\TrafficLight;
use App\Models\StapNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrafficLightSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('traffic_lights')->truncate();

        $alphaId = StapNode::where('node_name', 'Node Alpha')->value('node_id');
        $betaId  = StapNode::where('node_name', 'Node Beta')->value('node_id');

        $lights = [
            ['node_id' => $alphaId, 'location_label' => 'Alpha — Northbound Signal',   'current_state' => 'green',  'mode' => 'auto'],
            ['node_id' => $alphaId, 'location_label' => 'Alpha — Southbound Signal',   'current_state' => 'red',    'mode' => 'auto'],
            ['node_id' => $alphaId, 'location_label' => 'Alpha — Batasan Road Signal', 'current_state' => 'yellow', 'mode' => 'auto'],
            ['node_id' => $betaId,  'location_label' => 'Beta — Eastbound Signal',     'current_state' => 'green',  'mode' => 'auto'],
            ['node_id' => $betaId,  'location_label' => 'Beta — Westbound Signal',     'current_state' => 'red',    'mode' => 'auto'],
        ];

        foreach ($lights as $light) {
            TrafficLight::create($light);
        }
    }
}