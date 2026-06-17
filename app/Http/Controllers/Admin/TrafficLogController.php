<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrafficSnapshot;
use App\Models\Camera;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrafficLogController extends Controller
{
  /**
   * Show the admin traffic logs view.
   */
  public function index()
  {
    return view('admin.traffic-logs');
  }

  /**
   * Paginated list of traffic snapshots with optional filters.
   *
   * Filters: camera_id, node_id, date_from, date_to, vehicle_type
   */
  public function list(Request $request)
  {
    // Eager-load camera metadata relations cleanly
    $query = TrafficSnapshot::with([
      'camera' => function ($q) {
        $q->select(['id', 'node_id', 'label', 'direction']);
      },
      'camera.node' => function ($q) {
        $q->select(['id', 'name']);
      }
    ])->orderBy('captured_at', 'desc');

    // Camera Dropdown Filter tracking
    if ($request->filled('camera_id')) {
      $query->where('camera_id', '=', $request->input('camera_id'));
    }

    // Date Range Constraints
    if ($request->filled('date_from')) {
      $query->whereDate('captured_at', '>=', $request->input('date_from'));
    }

    if ($request->filled('date_to')) {
      $query->whereDate('captured_at', '<=', $request->input('date_to'));
    }

    $snapshots = $query->paginate(50);

    return response()->json($snapshots);
  }

  /**
   * Show a single traffic snapshot record.
   */
  public function show($id)
  {
    $snapshot = TrafficSnapshot::with('camera.node')->findOrFail($id);
    return response()->json($snapshot);
  }

  /**
   * Parse and import the custom traffic logs CSV structure.
   */
  public function importCSV(Request $request)
  {
    $request->validate([
      'traffic_summary' => 'required|file|mimes:csv,txt',
    ]);

    $file = $request->file('traffic_summary');
    $handle = fopen($file->getRealPath(), 'r');

    // Default snapshot timestamp
    $currentTimestamp = now();
    $processingRows = false;
    $importedCount = 0;

    DB::beginTransaction();
    try {
      while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        // Skip empty rows
        if (empty($row) || !isset($row[0])) {
          continue;
        }

        $firstCell = trim($row[0]);

        // 1. Capture system startup initialization timestamp if available
        if (str_contains($firstCell, 'Session Start Initialization Time')) {
          $currentTimestamp = Carbon::parse(trim($row[1]));
          continue;
        }

        // 2. Identify Snapshot sections & extract timestamps: [2026-06-17 00:59:08]
        if (str_contains($firstCell, 'INTERVAL RECORDING SNAPSHOT')) {
          preg_match('/\[(.*?)\]/', $firstCell, $matches);
          if (isset($matches[1])) {
            $currentTimestamp = Carbon::parse($matches[1]);
          }
          $processingRows = false;
          continue;
        }

        // 3. Update timestamp if parsing the termination segment matrix
        if (str_contains($firstCell, 'Session Termination Completed Clock')) {
          $currentTimestamp = Carbon::parse(trim($row[1]));
          $processingRows = false;
          continue;
        }

        // 4. Track dynamic data rows immediately following headers
        if ($firstCell === 'Lane Approach' || $firstCell === 'Approach Lane Name') {
          $processingRows = true;
          continue;
        }

        // 5. Explicitly break when hitting metrics summaries to prevent double-counting total logs
        if (
          str_contains($firstCell, 'Intersection Cumulative Unique Vehicles Sum') ||
          str_contains($firstCell, 'FINAL INTERSECTION REPORT SUMMARY MATRIX') ||
          $firstCell === 'TOTAL INTERSECTION CORRIDOR'
        ) {
          $processingRows = false;
          continue;
        }

        // 6. Process individual directional values
        if ($processingRows && in_array($firstCell, ['NORTH', 'SOUTH', 'EAST', 'WEST'])) {

          // Match approach data directly to camera instances
          $camera = Camera::firstOrCreate(
            ['direction' => $firstCell],
            ['label' => $firstCell . ' Approach Cam']
          );

          // Consolidate separate vehicle classifications into the keys parsed by your frontend table
          $carsTotal = (int) ($row[3] ?? 0) + (int) ($row[7] ?? 0) + (int) ($row[11] ?? 0); // sedan + pickup + van
          $jeepneyTotal = (int) ($row[5] ?? 0) + (int) ($row[8] ?? 0);                        // modern + traditional
          $tricycleTotal = (int) ($row[4] ?? 0) + (int) ($row[9] ?? 0);                        // e-trike + tricycle
          $trucksTotal = (int) ($row[10] ?? 0);
          $motorcycles = (int) ($row[6] ?? 0);
          $ambulance = (int) ($row[1] ?? 0);

          // Extract Density % and calculate Level of Service (LOS)
          $densityString = $row[13] ?? '0%';
          $densityValue = (float) str_replace('%', '', $densityString);
          $calculatedLOS = $this->determineLevelOfService($densityValue);

          // Save log directly via TrafficSnapshot (matching your existing schema model)
          TrafficSnapshot::create([
            'camera_id' => $camera->id,
            'cars' => $carsTotal,
            'trucks' => $trucksTotal,
            'motorcycles' => $motorcycles,
            'jeepney' => $jeepneyTotal,
            'ambulance' => $ambulance,
            'tricycle' => $tricycleTotal,
            'congestion' => $calculatedLOS,
            'captured_at' => $currentTimestamp, // Maps safely to your model's timestamp logic
          ]);

          $importedCount++;
        }
      }

      fclose($handle);
      DB::commit();

      return response()->json([
        'success' => true,
        'message' => "Successfully imported {$importedCount} approach snapshot entries."
      ], 200);

    } catch (\Exception $e) {
      DB::rollBack();
      if (is_resource($handle)) {
        fclose($handle);
      }
      Log::error('CSV Traffic Import Failed: ' . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'An error occurred while parsing the CSV data: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Helper mapping road density percentages directly to Highway Capacity Manual LOS structures.
   */
  private function determineLevelOfService($density)
  {
    if ($density <= 15)
      return 'A';
    if ($density <= 30)
      return 'B';
    if ($density <= 50)
      return 'C';
    if ($density <= 70)
      return 'D';
    if ($density <= 85)
      return 'E';
    return 'F';
  }
}
