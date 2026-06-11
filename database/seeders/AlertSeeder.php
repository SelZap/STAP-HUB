<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\StapNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('alerts')->truncate();

        $alphaId = StapNode::where('node_name', 'Node Alpha')->value('node_id');
        $betaId  = StapNode::where('node_name', 'Node Beta')->value('node_id');

        $alerts = [
            // ── Unresolved ───────────────────────────────────────────
            [
                'node_id'      => $alphaId,
                'camera_id'    => null,
                'type'         => 'camera_offline',
                'severity'     => 'medium',
                'message'      => 'Alpha Cam 3 is unresponsive. USB connection may be loose.',
                'is_resolved'  => false,
                'triggered_at' => Carbon::now()->subHours(3),
                'resolved_at'  => null,
            ],
            [
                'node_id'      => $betaId,
                'camera_id'    => null,
                'type'         => 'high_congestion',
                'severity'     => 'critical',
                'message'      => 'Severe congestion detected on Elliptical Road eastbound. Vehicle count exceeds threshold.',
                'is_resolved'  => false,
                'triggered_at' => Carbon::now()->subMinutes(45),
                'resolved_at'  => null,
            ],
            [
                'node_id'      => $alphaId,
                'camera_id'    => null,
                'type'         => 'heavy_rain',
                'severity'     => 'low',
                'message'      => 'Heavy rainfall detected at Commonwealth Ave. node. Visibility may be reduced.',
                'is_resolved'  => false,
                'triggered_at' => Carbon::now()->subMinutes(20),
                'resolved_at'  => null,
            ],

            // ── Resolved ─────────────────────────────────────────────
            [
                'node_id'      => $betaId,
                'camera_id'    => null,
                'type'         => 'node_offline',
                'severity'     => 'critical',
                'message'      => 'Node Beta lost connection to STAP Hub. Heartbeat timeout exceeded.',
                'is_resolved'  => true,
                'triggered_at' => Carbon::now()->subDays(2)->subHours(4),
                'resolved_at'  => Carbon::now()->subDays(2)->subHours(3),
            ],
            [
                'node_id'      => $alphaId,
                'camera_id'    => null,
                'type'         => 'high_congestion',
                'severity'     => 'high',
                'message'      => 'Severe congestion on Commonwealth Ave. northbound during morning rush hour.',
                'is_resolved'  => true,
                'triggered_at' => Carbon::now()->subDays(1)->setHour(8)->setMinute(15),
                'resolved_at'  => Carbon::now()->subDays(1)->setHour(9)->setMinute(30),
            ],
            [
                'node_id'      => $betaId,
                'camera_id'    => null,
                'type'         => 'camera_offline',
                'severity'     => 'medium',
                'message'      => 'Beta Cam 2 stream interrupted. Auto-recovered after 10 minutes.',
                'is_resolved'  => true,
                'triggered_at' => Carbon::now()->subDays(3),
                'resolved_at'  => Carbon::now()->subDays(3)->addMinutes(10),
            ],
        ];

        foreach ($alerts as $alert) {
            Alert::create($alert);
        }

        $this->command->info('Alerts seeded successfully.');
    }
}