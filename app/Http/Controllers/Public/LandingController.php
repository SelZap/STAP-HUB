<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\TrafficSnapshot;
use App\Models\WeatherLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LandingController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // --------------------------------------------------------
        // 1. Live Vehicle Count
        // Get the latest snapshot per camera, grouped by node/location
        // --------------------------------------------------------
        $latestSnapshots = TrafficSnapshot::with('camera.node')
            ->whereIn('snapshot_id', function ($query) {
                $query->select(DB::raw('MAX(snapshot_id)'))
                      ->from('traffic_snapshots')
                      ->groupBy('camera_id');
            })
            ->get()
            ->groupBy(fn ($s) => $s->camera->node->location_label ?? 'Unknown');

        // Build per-location summary: total vehicle count + LOS
        $liveVehicleData = $latestSnapshots->map(function ($snapshots, $location) {
            $totalVehicles = $snapshots->sum('vehicle_count');
            // Use the highest (worst) LOS among cameras in this location
            $losOrder  = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6];
            $worstLos  = $snapshots->sortByDesc(fn ($s) => $losOrder[$s->congestion_level] ?? 0)
                                   ->first()->congestion_level ?? 'A';
            return [
                'location'       => $location,
                'vehicle_count'  => $totalVehicles,
                'los'            => $worstLos,
                'los_label'      => TrafficSnapshot::$losLabels[$worstLos] ?? '',
            ];
        })->values();

        // --------------------------------------------------------
        // 2. Traffic History
        // Hourly LOS per location for today (grouped by hour)
        // --------------------------------------------------------
        $hourlySnapshots = TrafficSnapshot::with('camera.node')
            ->whereDate('captured_at', $today)
            ->get()
            ->groupBy(function ($s) {
                return $s->camera->node->location_label ?? 'Unknown';
            });

        $trafficHistory = [];
        $losOrder = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6];

        foreach ($hourlySnapshots as $location => $snapshots) {
            $byHour = $snapshots->groupBy(fn ($s) => Carbon::parse($s->captured_at)->format('g:i A'));
            foreach ($byHour as $hour => $hourSnaps) {
                $worstLos = $hourSnaps->sortByDesc(fn ($s) => $losOrder[$s->congestion_level] ?? 0)
                                      ->first()->congestion_level ?? 'A';
                $trafficHistory[$hour][$location] = $worstLos;
            }
        }

        // Sort hours chronologically
        uksort($trafficHistory, function ($a, $b) {
            return Carbon::createFromFormat('g:i A', $a) <=> Carbon::createFromFormat('g:i A', $b);
        });

        // Get unique location labels
        $locations = $liveVehicleData->pluck('location')->toArray();

        // --------------------------------------------------------
        // 3. Rain & Weather Log
        // Today's weather logs per hour, per node
        // --------------------------------------------------------
        $weatherLogs = WeatherLog::with('node')
            ->whereDate('recorded_at', $today)
            ->orderBy('recorded_at')
            ->get()
            ->groupBy(fn ($w) => Carbon::parse($w->recorded_at)->format('g:i A'));

        // Map rain_intensity to display info
        $rainMap = [
            'none'     => ['label' => 'No Rain',       'color' => '#D0D6E8', 'pct' => 5  ],
            'light'    => ['label' => 'Minimal Rain',  'color' => '#29B357', 'pct' => 35 ],
            'moderate' => ['label' => 'Moderate Rain', 'color' => '#F4B942', 'pct' => 65 ],
            'heavy'    => ['label' => 'Strong Rain',   'color' => '#E03040', 'pct' => 95 ],
        ];

        $weatherData = $weatherLogs->map(function ($logs, $time) use ($rainMap) {
            // Use the worst rain intensity at that hour
            $order       = ['none' => 0, 'light' => 1, 'moderate' => 2, 'heavy' => 3];
            $worstRain   = $logs->sortByDesc(fn ($w) => $order[$w->rain_intensity] ?? 0)
                                 ->first()->rain_intensity ?? 'none';
            return [
                'time'  => $time,
                'rain'  => $worstRain,
                'meta'  => $rainMap[$worstRain],
            ];
        })->values();

        // --------------------------------------------------------
        // 4. Chart data — vehicle count trend (last 7 days)
        // --------------------------------------------------------
        $trendData = TrafficSnapshot::select(
                DB::raw('DATE(captured_at) as date'),
                DB::raw('SUM(vehicle_count) as total')
            )
            ->where('captured_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'  => Carbon::parse($row->date)->format('M d'),
                'total' => (int) $row->total,
            ]);

        return view('public.dashboard', compact(
            'liveVehicleData',
            'trafficHistory',
            'locations',
            'weatherData',
            'rainMap',
            'trendData',
            'today',
        ));
    }
}