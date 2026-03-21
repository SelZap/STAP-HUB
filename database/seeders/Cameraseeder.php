<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\StapNode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CameraSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cameras')->truncate();

        $alphaId = StapNode::where('node_name', 'Node Alpha')->value('node_id');
        $betaId  = StapNode::where('node_name', 'Node Beta')->value('node_id');

        // ── Node Alpha ────────────────────────────────────────────────
        Camera::create([
            'node_id'   => $alphaId,
            'usb_index' => 0,
            'label'     => 'Alpha Cam 1',
            'direction' => 'Northbound',
            'status'    => 'active',
        ]);

        Camera::create([
            'node_id'   => $alphaId,
            'usb_index' => 1,
            'label'     => 'Alpha Cam 2',
            'direction' => 'Southbound',
            'status'    => 'active',
        ]);

        Camera::create([
            'node_id'   => $alphaId,
            'usb_index' => 2,
            'label'     => 'Alpha Cam 3',
            'direction' => 'Intersection — Batasan Road',
            'status'    => 'inactive',
        ]);

        // ── Node Beta ─────────────────────────────────────────────────
        Camera::create([
            'node_id'   => $betaId,
            'usb_index' => 0,
            'label'     => 'Beta Cam 1',
            'direction' => 'Eastbound',
            'status'    => 'active',
        ]);

        Camera::create([
            'node_id'   => $betaId,
            'usb_index' => 1,
            'label'     => 'Beta Cam 2',
            'direction' => 'Westbound',
            'status'    => 'active',
        ]);
    }
}