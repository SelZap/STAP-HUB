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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('cameras')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $alphaId = StapNode::where('node_name', 'Node Alpha')->value('node_id');
        $betaId  = StapNode::where('node_name', 'Node Beta')->value('node_id');

        Camera::create([
            'node_id'   => $alphaId,
            'usb_index' => 0,
            'label'     => 'Mayor Gil Fernando Ave — Northbound',
            'direction' => 'Northbound',
            'status'    => 'active',
        ]);

        Camera::create([
            'node_id'   => $alphaId,
            'usb_index' => 1,
            'label'     => 'Mayor Gil Fernando Ave — Southbound',
            'direction' => 'Southbound',
            'status'    => 'active',
        ]);

        Camera::create([
            'node_id'   => $betaId,
            'usb_index' => 0,
            'label'     => 'Sumulong Highway — Eastbound',
            'direction' => 'Eastbound',
            'status'    => 'active',
        ]);

        Camera::create([
            'node_id'   => $betaId,
            'usb_index' => 1,
            'label'     => 'Sumulong Highway — Westbound',
            'direction' => 'Westbound',
            'status'    => 'active',
        ]);
    }
}