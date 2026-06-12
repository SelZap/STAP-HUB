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
    /**
     * Map camera direction to the display label shown on the public dashboard.
     */
    private function cameraDirectionLabel(string $direction): string
    {
        return match (strtolower(trim($direction))) {
            'northbound' => 'Mayor Gil Fernando Ave North',
            'southbound' => 'Mayor Gil Fernando Ave South',
            'eastbound'  => 'Sumulong Hwy East',
            'westbound'  => 'Sumulong Hwy West',
            default      => $direction,
        };
    }

    public function index()
    {
        $today = Carbon::today();

        // --------------------------------------------------------
        // 1. Live Vehicle Count
        // Get the latest snapshot per camera, group by camera direction
        // --------------------------------------------------------
        $latestSnapshots = TrafficSnapshot::with('camera')
            ->whereIn('snapshot_id', function ($query) {
                $query->select(DB::raw('MAX(snapshot_id)'))
                      ->from('traffic_snapshots')
                      ->groupBy('camera_id');
            })
            ->get()
            ->groupBy(function ($s) {
                $direction = $s->camera->direction ?? null;
                if ($direction) {
                    return $this->cameraDirectionLabel($direction);
                }
                // Fallback: use camera label
                return $s->camera->label ?? 'Unknown';
            });

        // Defined display order for the four directions
        $orderedKeys = [
            'Mayor Gil Fernando Ave North',
            'Mayor Gil Fernando Ave South',
            'Sumulong Hwy East',
            'Sumulong Hwy West',
        ];

        // Build per-location summary: total vehicle count + LOS
        $liveVehicleData = $latestSnapshots->map(function ($snapshots, $location) {
            $totalVehicles = $snapshots->sum('vehicle_count');
            $losOrder  = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6];
            $worstLos  = $snapshots->sortByDesc(fn ($s) => $losOrder[$s->congestion_level] ?? 0)
                                   ->first()->congestion_level ?? 'A';
            return [
                'location'       => $location,
                'vehicle_count'  => $totalVehicles,
                'los'            => $worstLos,
                'los_label'      => TrafficSnapshot::$losLabels[$worstLos] ?? '',
            ];
        });

        // Sort by the defined display order, unknown directions go last
        $liveVehicleData = $liveVehicleData->sortBy(function ($item) use ($orderedKeys) {
            $pos = array_search($item['location'], $orderedKeys);
            return $pos === false ? 99 : $pos;
        })->values();

        // --------------------------------------------------------
        // 2. Traffic History
        // Hourly LOS per camera direction for today
        // --------------------------------------------------------
        $hourlySnapshots = TrafficSnapshot::with('camera')
            ->whereDate('captured_at', $today)
            ->get()
            ->groupBy(function ($s) {
                $direction = $s->camera->direction ?? null;
                if ($direction) {
                    return $this->cameraDirectionLabel($direction);
                }
                return $s->camera->label ?? 'Unknown';
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

        // Use ordered location list for table columns
        $locations = $liveVehicleData->pluck('location')->toArray();
        // Ensure all four are present even if no data yet
        foreach ($orderedKeys as $key) {
            if (! in_array($key, $locations)) {
                $locations[] = $key;
            }
        }

        // --------------------------------------------------------
        // 3. Rain & Weather Log
        // --------------------------------------------------------
        $weatherLogs = WeatherLog::with('node')
            ->whereDate('recorded_at', $today)
            ->orderBy('recorded_at')
            ->get()
            ->groupBy(fn ($w) => Carbon::parse($w->recorded_at)->format('g:i A'));

        $rainMap = [
            'none'     => ['label' => 'No Rain',       'color' => '#D0D6E8', 'pct' => 5  ],
            'light'    => ['label' => 'Minimal Rain',  'color' => '#29B357', 'pct' => 35 ],
            'moderate' => ['label' => 'Moderate Rain', 'color' => '#F4B942', 'pct' => 65 ],
            'heavy'    => ['label' => 'Strong Rain',   'color' => '#E03040', 'pct' => 95 ],
        ];

        $weatherData = $weatherLogs->map(function ($logs, $time) use ($rainMap) {
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