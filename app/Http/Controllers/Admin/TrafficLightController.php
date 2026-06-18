<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrafficLight;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class TrafficLightController extends Controller
{
    /**
     * Show the admin traffic light control view.
     */
    public function index()
    {
        return view('admin.traffic-lights');
    }

    /**
     * List all traffic lights and their current state.
     */
    public function list()
    {
        $lights = TrafficLight::with('node:id,node_name,status')->get();
        return response()->json($lights);
    }

    /**
     * Get a single traffic light's state.
     */
    public function show($id)
    {
        $light = TrafficLight::with('node:id,node_name,status')->findOrFail($id);
        return response()->json($light);
    }

    /**
     * Update a traffic light state via the admin route.
     */
    public function updateState(Request $request, $id)
    {
        return $this->setState($request, $id);
    }

    /**
     * Update a traffic light mode via the admin route.
     */
    public function updateMode(Request $request, $id)
    {
        $request->validate([
            'mode' => 'required|in:auto,manual,hazard',
        ]);

        $light = TrafficLight::with('node')->findOrFail($id);
        $previousMode = $light->mode;

        $light->mode = $request->mode;
        $light->save();

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'light_mode_changed',
            'target_id' => $light->light_id,
            'notes'     => "Light {$light->light_id} mode changed from {$previousMode} to {$request->mode}",
        ]);

        return response()->json([
            'message' => "Traffic light mode set to {$request->mode}.",
            'light'   => $light,
        ]);
    }

    /**
     * Proxy the node status endpoint through Laravel so HTTPS pages can read it.
     */
    public function proxyStatus(Request $request)
    {
        $baseUrl = $this->nodeBaseUrl($request);

        $response = Http::timeout(5)->get($baseUrl . '/status');
        $contentType = $response->header('Content-Type') ?: 'application/json';

        return response($response->body(), $response->status(), [
            'Content-Type' => $contentType,
        ]);
    }

    /**
     * Proxy control commands to the Flask node.
     */
    public function proxyControl(Request $request, string $endpoint)
    {
        $request->validate([
            'payload' => 'nullable|array',
        ]);

        if (! in_array($endpoint, ['mode', 'light'], true)) {
            abort(404);
        }

        $baseUrl = $this->nodeBaseUrl($request);
        $response = Http::timeout(10)->post($baseUrl . '/control/' . $endpoint, $request->input('payload', []));
        $contentType = $response->header('Content-Type') ?: 'application/json';

        return response($response->body(), $response->status(), [
            'Content-Type' => $contentType,
        ]);
    }

    /**
     * Proxy the MJPEG stream through Laravel.
     */
    public function proxyFeed(Request $request, string $lane)
    {
        $baseUrl = $this->nodeBaseUrl($request);
        $lane = strtolower($lane);

        $client = new Client([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'stream' => true,
            'http_errors' => false,
            'timeout' => 0,
            'read_timeout' => 0,
        ]);

        $upstream = $client->get('video_feed/' . $lane);
        $stream = $upstream->getBody();
        $contentType = $upstream->getHeaderLine('Content-Type') ?: 'multipart/x-mixed-replace; boundary=frame';

        return response()->stream(function () use ($stream) {
            ignore_user_abort(true);
            set_time_limit(0);

            while (! $stream->eof()) {
                echo $stream->read(8192);
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                flush();
            }
        }, $upstream->getStatusCode(), [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function nodeBaseUrl(Request $request): string
    {
        $nodeIp = $request->query('node_ip');

        if (! is_string($nodeIp) || ! filter_var($nodeIp, FILTER_VALIDATE_IP)) {
            abort(422, 'Valid node_ip is required.');
        }

        return 'http://' . $nodeIp . ':5000';
    }

    /**
     * Manually set a traffic light state.
     * Only allowed when the node is in manual or hazard mode.
     */
    public function setState(Request $request, $id)
    {
        $request->validate([
            'state'          => 'required|in:red,yellow,green',
            'duration_sec'   => 'nullable|integer|min:1',
        ]);

        $light = TrafficLight::with('node')->findOrFail($id);

        if (! in_array($light->mode, ['manual', 'hazard'])) {
            return response()->json(['message' => 'Node must be in manual or hazard mode to override lights.'], 422);
        }

        $previousState = $light->current_state;
        $light->current_state = $request->state;
        $light->save();

        AdminActivityLog::create([
            'admin_id'  => Auth::guard('admin')->id(),
            'action'    => 'light_state_changed',
            'target_id' => $light->light_id,
            'notes'     => "Light {$light->light_id} changed from {$previousState} to {$request->state}",
        ]);

        // TODO: Push state change to Node via WebSocket/broadcast

        return response()->json([
            'message' => "Traffic light set to {$request->state}.",
            'light'   => $light,
        ]);
    }
}