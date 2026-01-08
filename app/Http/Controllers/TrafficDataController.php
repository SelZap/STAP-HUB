<?php

namespace App\Http\Controllers;

use App\Models\TrafficData;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TrafficDataController extends Controller
{
    /**
     * Show the traffic data archive page
     */
    public function index(Request $request)
    {
        $query = TrafficData::query();

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Filter by road
        if ($request->filled('road')) {
            $query->where('road', $request->road);
        }

        // Filter by level of service
        if ($request->filled('level_of_service')) {
            $query->where('level_of_service', $request->level_of_service);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $trafficData = $query->paginate(20);

        // Get unique roads for filter dropdown
        $roads = TrafficData::distinct()->pluck('road')->sort();

        // Get chart data (last 7 days)
        $chartData = $this->getChartData();

        return view('traffic.archive', [
            'trafficData' => $trafficData,
            'roads' => $roads,
            'chartData' => $chartData,
            'filters' => $request->only(['start_date', 'end_date', 'road', 'level_of_service']),
        ]);
    }

    /**
     * Generate chart data for last 7 days
     */
    private function getChartData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $counts = [
                'light' => TrafficData::whereDate('date', $date)->where('level_of_service', 'light')->count(),
                'medium' => TrafficData::whereDate('date', $date)->where('level_of_service', 'medium')->count(),
                'heavy' => TrafficData::whereDate('date', $date)->where('level_of_service', 'heavy')->count(),
            ];
            $data[] = [
                'date' => \Carbon\Carbon::parse($date)->format('M d'),
                'light' => $counts['light'],
                'medium' => $counts['medium'],
                'heavy' => $counts['heavy'],
                'total' => array_sum($counts),
            ];
        }
        return $data;
    }

    /**
     * Export traffic data as CSV
     */
    public function exportCSV(Request $request)
    {
        $query = TrafficData::query();

        // Apply same filters
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }
        if ($request->filled('road')) {
            $query->where('road', $request->road);
        }
        if ($request->filled('level_of_service')) {
            $query->where('level_of_service', $request->level_of_service);
        }

        $data = $query->get();

        $filename = 'traffic-data-' . now()->format('Y-m-d-His') . '.csv';

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Write header
            fputcsv($handle, ['Date', 'Time', 'Road', 'Level of Service', 'Weather']);
            
            // Write data rows
            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->date->format('M d, Y'),
                    $row->time,
                    $row->road,
                    ucfirst($row->level_of_service),
                    $row->weather,
                ]);
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export traffic data as PDF
     */
    public function exportPDF(Request $request)
    {
        $query = TrafficData::query();

        // Apply same filters
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }
        if ($request->filled('road')) {
            $query->where('road', $request->road);
        }
        if ($request->filled('level_of_service')) {
            $query->where('level_of_service', $request->level_of_service);
        }

        $data = $query->get();
        $chartData = $this->getChartData();

        $pdf = Pdf::loadView('traffic.archive-pdf', [
            'trafficData' => $data,
            'chartData' => $chartData,
            'generatedAt' => now(),
        ]);

        return $pdf->download('traffic-data-' . now()->format('Y-m-d-His') . '.pdf');
    }
}