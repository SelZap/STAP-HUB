<?php

namespace Database\Seeders;

use App\Models\StapNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StapNodeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stap_nodes')->truncate();

        StapNode::create([
            'node_name'      => 'Node Alpha',
            'location_label' => 'Commonwealth Ave. cor. Batasan Road, Quezon City',
            'api_key'        => 'node_alpha_' . Str::random(32),
            'status'         => 'online',
            'last_heartbeat' => now()->subMinutes(2),
        ]);

        StapNode::create([
            'node_name'      => 'Node Beta',
            'location_label' => 'Elliptical Road cor. Quezon Ave., Quezon City',
            'api_key'        => 'node_beta_' . Str::random(32),
            'status'         => 'online',
            'last_heartbeat' => now()->subMinutes(5),
        ]);
    }
}