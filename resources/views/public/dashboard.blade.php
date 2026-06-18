@extends('layouts.public')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<style>
    /* ── Layout Override for Live Streams & Sidebar ── */
    .feed-layout {
        display: grid;
        grid-template-columns: 1fr 290px;
        gap: 1.25rem;
        align-items: start;
        margin-top: 1rem;
    }

    @media (max-width: 900px) {
        .feed-layout { grid-template-columns: 1fr; }
    }

    /* Camera Grid System */
    .feed-cam-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.875rem;
    }

    .feed-card {
        border-radius: .875rem;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(15,23,42,.08);
        box-shadow: 0 6px 20px rgba(15,23,42,.07);
        transition: box-shadow .2s;
    }

    .feed-card:hover { box-shadow: 0 12px 32px rgba(15,23,42,.13); }

    .feed-card-media {
        aspect-ratio: 16 / 9;
        background: #1e293b;
        position: relative;
        overflow: hidden;
        display: grid;
        place-items: center;
    }

    .feed-card-media img.mjpeg {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .feed-card-dir {
        position: absolute;
        top: .55rem;
        left: .55rem;
        background: rgba(15,23,42,.72);
        color: #fff;
        font-size: .7rem;
        font-weight: 700;
        padding: .25rem .6rem;
        border-radius: 6px;
        letter-spacing: 0.04em;
        backdrop-filter: blur(4px);
    }

    .feed-card-offline {
        color: rgba(255,255,255,.5);
        font-size: .85rem;
        text-align: center;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .5rem;
    }

    .feed-card-body {
        padding: .75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .feed-card-name { font-size: .9rem; font-weight: 700; color: #0f172a; }
    .feed-card-node { font-size: .78rem; color: #64748b; margin-top: 1px; }

    .feed-card-status {
        font-size: .7rem;
        font-weight: 700;
        padding: .2rem .6rem;
        border-radius: 20px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .feed-card-status.online  { background: #dcfce7; color: #166534; }
    .feed-card-status.offline { background: #fee2e2; color: #991b1b; }

    /* Polled Sidebar Panels */
    .feed-sidebar {
        display: grid;
        gap: 1rem;
        position: sticky;
        top: 1.25rem;
    }

    .feed-info-card {
        border-radius: .875rem;
        background: #fff;
        border: 1px solid rgba(15,23,42,.08);
        box-shadow: 0 4px 14px rgba(15,23,42,.06);
        overflow: hidden;
    }

    .feed-info-header {
        padding: .875rem 1rem .6rem;
        font-size: .75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        border-bottom: 1px solid rgba(15,23,42,.06);
    }

    .feed-info-body { padding: .875rem 1rem; }

    .feed-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
    }

    .feed-status-row:last-child { border-bottom: none; }
    .feed-status-label { color: #64748b; font-weight: 600; }
    .feed-status-value { font-weight: 700; color: #0f172a; }

    .feed-phase-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .feed-phase-green  { background: #dcfce7; color: #166534; }
    .feed-phase-yellow { background: #fef3c7; color: #92400e; }
    .feed-phase-red    { background: #fee2e2; color: #991b1b; }

    /* Node IP Configuration Banner */
    .feed-ip-bar {
        background: #fef9ec;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 12px;
        color: #92400e;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .feed-ip-bar input {
        border: 1.5px solid #fbbf24;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 12px;
        font-family: monospace;
        width: 160px;
        outline: none;
    }

    .feed-ip-bar button {
        background: #0f172a;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }
</style>
@endpush

@section('content')

{{-- Node IP Config Bar --}}
<div class="feed-ip-bar">
    <span>⚙️ STAP Node IP:</span>
    <input type="text" id="nodeIpInput" value="192.168.1.100" placeholder="e.g. 192.168.1.50">
    <button onclick="applyNodeIp()">Apply Settings</button>
    <span id="nodeIpStatus" style="font-size:11px;"></span>
</div>

{{-- 1. Live Vehicle Count Cards (From Database Summary) --}}
<section class="dash-section" style="margin-bottom: 2rem;">
    <div class="dash-section-header">
        <div class="stap-live-badge"><span class="stap-pulse"></span> LIVE DATABASE</div>
        <h2 class="dash-section-title">Latest Captured Vehicle Counts</h2>
    </div>
    <div class="dash-los-grid">
        @php $vehicleItems = $liveVehicleData ?? []; @endphp
        @forelse ($vehicleItems as $data)
            <div class="dash-los-card">
                <span class="dash-los-number"
                      data-count="{{ $data['vehicle_count'] ?? 0 }}"
                      data-original="{{ $data['vehicle_count'] ?? 0 }}"
                      id="count-{{ $loop->index }}">
                    {{ number_format($data['vehicle_count'] ?? 0) }}
                </span>
                <div class="dash-los-badge los-{{ $data['los'] ?? 'A' }}"
                     data-original-los="{{ $data['los'] ?? 'A' }}"
                     id="los-badge-{{ $loop->index }}">
                    {{ $data['los'] ?? '—' }}
                </div>
                <div class="dash-los-location">{{ $data['location'] ?? '' }}</div>
            </div>
        @empty
            <div class="stap-empty">No live database entries available.</div>
        @endforelse
    </div>
</section>

{{-- 2. Live Camera Feed Panel & Real-Time Hardware Status --}}
<section class="dash-section" style="margin-bottom: 2rem;">
    <h2 class="dash-section-title" style="margin-bottom: 1rem;">Real-Time System Intersections</h2>
    
    <div class="feed-layout">
        {{-- Left Grid: 4 Camera Streams --}}
        <div>
            <div class="feed-cam-grid">
                @foreach ([
                    ['direction' => 'NORTH', 'label' => 'Mayor Gil Fernando Ave — Northbound'],
                    ['direction' => 'SOUTH', 'label' => 'Mayor Gil Fernando Ave — Southbound'],
                    ['direction' => 'EAST',  'label' => 'Sumulong Highway — Eastbound'],
                    ['direction' => 'WEST',  'label' => 'Sumulong Highway — Westbound'],
                ] as $cam)
                <div class="feed-card">
                    <div class="feed-card-media">
                        <img
                            class="mjpeg"
                            id="stream-{{ strtolower($cam['direction']) }}"
                            src=""
                            alt="{{ $cam['label'] }}"
                            onerror="handleStreamError(this)"
                            style="display:none;"
                        >
                        <div class="feed-card-offline" id="offline-{{ strtolower($cam['direction']) }}">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.4;"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                            <div>Set Node IP above to load stream</div>
                        </div>
                        <span class="feed-card-dir">{{ $cam['direction'] }}</span>
                    </div>
                    <div class="feed-card-body">
                        <div>
                            <div class="feed-card-name">{{ $cam['label'] }}</div>
                            <div class="feed-card-node">STAP Node — Hardware Stream</div>
                        </div>
                        <span class="feed-card-status offline" id="status-{{ strtolower($cam['direction']) }}">Offline</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Right Side: Edge Device Live Telemetry --}}
        <div class="feed-sidebar">
            <div class="feed-info-card">
                <div class="feed-info-header">Active Microcontroller State</div>
                <div class="feed-info-body" id="statusPanel">
                    <div style="color:#94a3b8;font-size:12px;text-align:center;padding:8px 0;">
                        Waiting for device connection...
                    </div>
                </div>
            </div>
            
            <div class="feed-info-card">
                <div class="feed-info-header">Last Synchronized</div>
                <div class="feed-info-body">
                    <div class="feed-intersection-name" style="font-size:0.85rem;" id="lastUpdated">Offline</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 3. Rain & Weather Log Section --}}
<section class="dash-section">
    <h2 class="dash-section-title">Rain &amp; Weather Log</h2>
    <div class="dash-weather-grid">

        <div class="stap-card">
            <div class="stap-card-header">
                <span class="stap-card-title">Today's Rain Log</span>
                <span class="stap-card-link">From STAP Nodes</span>
            </div>
            <div class="stap-card-body">
                @if (!empty($weatherData) && $weatherData->count() > 0)
                    <div class="dash-rain-list">
                        @foreach ($weatherData as $entry)
                            @php
                                $meta  = $entry['meta'] ?? [];
                                $pct   = $meta['pct']   ?? 0;
                                $color = $meta['color'] ?? '#ccc';
                                $label = $meta['label'] ?? '';
                                $time  = $entry['time'] ?? '';
                            @endphp
                            <div class="dash-rain-row">
                                <span class="dash-rain-time">{{ $time }}</span>
                                <div class="stap-bar-track dash-rain-bar">
                                    <div class="stap-bar-fill"
                                         data-width="{{ $pct }}"
                                         style="background: {{ $color }}; width: 0%">
                                    </div>
                                </div>
                                <span class="dash-rain-label" style="color: {{ $color }}">
                                    {{ $label }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="stap-empty">No weather data recorded today.</div>
                @endif
            </div>
        </div>

        <div class="stap-card">
            <div class="stap-card-header">
                <span class="stap-card-title">Weekly Weather Forecast</span>
                <span class="stap-card-link">Open-Meteo · Mayor Gil Fernando Ave</span>
            </div>
            <div class="stap-card-body">
                <div id="weather-forecast-wrap">
                    <div class="dash-forecast-loading">
                        <div class="stap-spinner" style="border-color: rgba(27,39,68,.2); border-top-color: var(--navy);"></div>
                        <span>Loading forecast...</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

@endsection

@push('scripts')
<script>
    // ── Node IP Configuration (localStorage keeps persistent config across updates) ──
    let NODE_IP = localStorage.getItem('stap_node_ip') || '192.168.1.100';
    document.getElementById('nodeIpInput').value = NODE_IP;

    const DIRECTIONS = ['north', 'south', 'east', 'west'];

    function applyNodeIp() {
        NODE_IP = document.getElementById('nodeIpInput').value.trim();
        localStorage.setItem('stap_node_ip', NODE_IP);
        document.getElementById('nodeIpStatus').textContent = '✅ Applied';
        setTimeout(() => document.getElementById('nodeIpStatus').textContent = '', 2000);
        loadStreams();
    }

    // ── Stream Fetcher (MJPEG directly from local endpoint) ──
    function loadStreams() {
        DIRECTIONS.forEach(dir => {
            const img     = document.getElementById('stream-' + dir);
            const offline = document.getElementById('offline-' + dir);
            const status  = document.getElementById('status-' + dir);

            const url = `http://${NODE_IP}:5000/video_feed/${dir}`;

            img.onload = () => {
                img.style.display    = 'block';
                offline.style.display = 'none';
                status.textContent   = 'Online';
                status.className     = 'feed-card-status online';
            };

            img.onerror = () => handleStreamError(img);
            img.src     = url;
        });
    }

    function handleStreamError(img) {
        const dir     = img.id.replace('stream-', '');
        const offline = document.getElementById('offline-' + dir);
        const status  = document.getElementById('status-' + dir);

        img.style.display     = 'none';
        offline.style.display = 'flex';
        offline.querySelector('div').textContent = 'Stream unavailable';
        status.textContent = 'Offline';
        status.className  = 'feed-card-status offline';

        setTimeout(() => {
            img.src = `http://${NODE_IP}:5000/video_feed/${dir}?t=` + Date.now();
        }, 5000);
    }

    // ── Long Polling Loop for Real-time Hardware Analytics ──
    function startStatusPolling() {
        fetchStatus();
        setInterval(fetchStatus, 3000);
    }

    // ── Vehicle Count Card Helpers ──
    let _countsAreZeroed = false;

    function setVehicleCountsOffline() {
        if (_countsAreZeroed) return;
        _countsAreZeroed = true;
        document.querySelectorAll('[id^="count-"]').forEach(el => {
            el.textContent = '0';
            el.style.opacity = '0.45';
        });
        document.querySelectorAll('[id^="los-badge-"]').forEach(el => {
            el.textContent = '—';
            el.className = 'dash-los-badge';
            el.style.opacity = '0.45';
        });
        // Show a subtle offline notice on the section badge
        const badge = document.querySelector('.stap-live-badge');
        if (badge && !document.getElementById('db-offline-note')) {
            const note = document.createElement('span');
            note.id = 'db-offline-note';
            note.style.cssText = 'font-size:11px;color:#ef4444;font-weight:600;margin-left:8px;';
            note.textContent = '— Node offline, counts reset to 0';
            badge.parentNode.insertBefore(note, badge.nextSibling);
        }
    }

    function setVehicleCountsOnline() {
        if (!_countsAreZeroed) return;
        _countsAreZeroed = false;
        document.querySelectorAll('[id^="count-"]').forEach(el => {
            el.textContent = Number(el.dataset.original).toLocaleString();
            el.style.opacity = '';
        });
        document.querySelectorAll('[id^="los-badge-"]').forEach(el => {
            const los = el.dataset.originalLos || 'A';
            el.textContent = los;
            el.className = `dash-los-badge los-${los}`;
            el.style.opacity = '';
        });
        const note = document.getElementById('db-offline-note');
        if (note) note.remove();
    }

    async function fetchStatus() {
        try {
            const res  = await fetch(`http://${NODE_IP}:5000/status`, { signal: AbortSignal.timeout(2000) });
            const data = await res.json();
            renderStatusPanel(data);
            document.getElementById('lastUpdated').textContent = 'Updated ' + new Date().toLocaleTimeString();
            setVehicleCountsOnline();
        } catch (e) {
            document.getElementById('statusPanel').innerHTML =
                '<div style="color:#ef4444;font-size:12px;text-align:center;padding:8px 0;">⚠ Cannot reach STAP Edge Device</div>';
            document.getElementById('lastUpdated').textContent = 'Offline';
            setVehicleCountsOffline();
        }
    }

    function renderStatusPanel(data) {
        const phaseClass = data.phase_state === 'GREEN' ? 'feed-phase-green'
                         : data.phase_state === 'YELLOW' ? 'feed-phase-yellow'
                         : 'feed-phase-red';

        const losColors = { A:'#166534', B:'#166534', C:'#92400e', D:'#92400e', E:'#991b1b', F:'#991b1b' };

        let vehicleRows = '';
        for (const lane of ['NORTH','SOUTH','EAST','WEST']) {
            const count = data.vehicle_counts?.[lane] ?? 0;
            const los   = data.los?.[lane] ?? '—';
            const color = losColors[los] || '#334155';
            vehicleRows += `
                <div class="feed-status-row">
                    <span class="feed-status-label">${lane}</span>
                    <span class="feed-status-value">
                        ${count} vehicles &nbsp;
                        <span style="color:${color};font-weight:800;">LOS ${los}</span>
                    </span>
                </div>`;
        }

        document.getElementById('statusPanel').innerHTML = `
            <div class="feed-status-row">
                <span class="feed-status-label">Active Lane</span>
                <span class="feed-status-value">${data.active_lane ?? '—'}</span>
            </div>
            <div class="feed-status-row">
                <span class="feed-status-label">Signal State</span>
                <span class="feed-phase-badge ${phaseClass}">${data.phase_state ?? '—'}</span>
            </div>
            <div class="feed-status-row">
                <span class="feed-status-label">Time Remaining</span>
                <span class="feed-status-value">${data.remaining_secs ?? 0}s</span>
            </div>
            <div class="feed-status-row">
                <span class="feed-status-label">Control Mode</span>
                <span class="feed-status-value" style="text-transform:uppercase;">${data.mode ?? '—'}</span>
            </div>
            <div class="feed-status-row">
                <span class="feed-status-label">Rain Status</span>
                <span class="feed-status-value">${data.rain ? '🌧 Detected' : '☀ Clear'}</span>
            </div>
            <div style="margin-top:10px;margin-bottom:4px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Live Lane Processing</div>
            ${vehicleRows}
        `;
    }

    // ── Boot Initialization ──
    document.addEventListener('DOMContentLoaded', () => {
        if (NODE_IP) {
            loadStreams();
            startStatusPolling();
        }
    });
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
