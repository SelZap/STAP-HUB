@extends('layouts.public')
@section('title', 'Live Camera Feed')
@section('page-title', 'Live Camera Feed')

@push('styles')
<style>
    /* ── Layout ─────────────────────────────────────────────── */
    .feed-layout {
        display: grid;
        grid-template-columns: 1fr 290px;
        gap: 1.25rem;
        align-items: start;
    }

    @media (max-width: 900px) {
        .feed-layout { grid-template-columns: 1fr; }
    }

    /* ── Camera Grid ─────────────────────────────────────────── */
    .feed-cam-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.875rem;
    }

    .feed-cam-grid-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.875rem;
    }

    .feed-cam-grid-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .feed-live-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem .75rem;
        border-radius: 999px;
        background: #0f172a;
        color: #fff;
        font-size: .78rem;
        font-weight: 700;
    }

    .feed-live-dot {
        width: .5rem;
        height: .5rem;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 4px rgba(34,197,94,.22);
        animation: pulse 1.6s infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(34,197,94,.22); }
        50%       { box-shadow: 0 0 0 8px rgba(34,197,94,.08); }
    }

    /* ── Camera Card ─────────────────────────────────────────── */
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

    /* MJPEG stream via <img> tag */
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

    /* ── Sidebar ─────────────────────────────────────────────── */
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

    .feed-intersection-name {
        font-size: .95rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
        margin-bottom: .25rem;
    }

    .feed-intersection-sub { font-size: .82rem; color: #64748b; }

    /* ── Status Panel ────────────────────────────────────────── */
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

    /* ── Camera Index ────────────────────────────────────────── */
    .feed-cam-index      { display: grid; gap: .55rem; }
    .feed-cam-index-row  { display: flex; align-items: center; gap: .65rem; font-size: .82rem; }
    .feed-cam-index-dot  { width: .5rem; height: .5rem; border-radius: 50%; flex-shrink: 0; }
    .feed-cam-index-dot.online  { background: #22c55e; }
    .feed-cam-index-dot.offline { background: #ef4444; }
    .feed-cam-index-label { flex: 1; color: #334155; font-weight: 500; }
    .feed-cam-index-tag   { font-size: .7rem; font-weight: 700; color: #64748b; }

    .feed-note {
        border-radius: .875rem;
        background: linear-gradient(135deg, #0f172a, #1e3a5f);
        padding: 1rem;
        color: rgba(255,255,255,.75);
        font-size: .8rem;
        line-height: 1.6;
    }

    /* ── Node IP config banner ───────────────────────────────── */
    .feed-ip-bar {
        background: #fef9ec;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 12px;
        color: #92400e;
        margin-bottom: 14px;
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
    <button onclick="applyNodeIp()">Apply</button>
    <span id="nodeIpStatus" style="font-size:11px;"></span>
</div>

<div class="feed-layout">

    {{-- Left: 2×2 Camera Grid --}}
    <div>
        <div class="feed-cam-grid-header">
            <span class="feed-cam-grid-title">Mayor Gil Fernando Ave / Sumulong Hwy</span>
            <div class="feed-live-badge">
                <span class="feed-live-dot"></span>
                <span>4 cameras</span>
            </div>
        </div>

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
                        <div class="feed-card-node">STAP Node — Mayor Gil Fernando Ave</div>
                    </div>
                    <span class="feed-card-status offline" id="status-{{ strtolower($cam['direction']) }}">Offline</span>
                </div>
            </div>
            @endforeach

        </div>
    </div>

    {{-- Right: Sidebar --}}
    <div class="feed-sidebar">

        {{-- Intersection Info --}}
        <div class="feed-info-card">
            <div class="feed-info-header">Intersection</div>
            <div class="feed-info-body">
                <div class="feed-intersection-name">Mayor Gil Fernando Ave<br>× Sumulong Highway</div>
                <div class="feed-intersection-sub" style="margin-top:.4rem;">Marikina City, Metro Manila</div>
                <div class="feed-intersection-sub" style="margin-top:.5rem;" id="lastUpdated">Waiting for node...</div>
            </div>
        </div>

        {{-- Live System Status --}}
        <div class="feed-info-card">
            <div class="feed-info-header">System Status</div>
            <div class="feed-info-body" id="statusPanel">
                <div style="color:#94a3b8;font-size:12px;text-align:center;padding:8px 0;">
                    Waiting for node...
                </div>
            </div>
        </div>

        {{-- Camera Index --}}
        <div class="feed-info-card">
            <div class="feed-info-header">Camera Index</div>
            <div class="feed-info-body">
                <div class="feed-cam-index">
                    @foreach (['NORTH', 'SOUTH', 'EAST', 'WEST'] as $dir)
                    <div class="feed-cam-index-row">
                        <span class="feed-cam-index-dot offline" id="dot-{{ strtolower($dir) }}"></span>
                        <span class="feed-cam-index-label">{{ $dir }}</span>
                        <span class="feed-cam-index-tag" id="tag-{{ strtolower($dir) }}">Offline</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="feed-note">
            Streams are sourced directly from STAP Node hardware at the intersection via local network.
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    // ── Node IP (saved in localStorage for convenience) ───────
    let NODE_IP = localStorage.getItem('stap_node_ip') || '192.168.1.100';
    document.getElementById('nodeIpInput').value = NODE_IP;

    const DIRECTIONS = ['north', 'south', 'east', 'west'];

    function applyNodeIp() {
        NODE_IP = document.getElementById('nodeIpInput').value.trim();
        localStorage.setItem('stap_node_ip', NODE_IP);
        document.getElementById('nodeIpStatus').textContent = '✅ Applied';
        setTimeout(() => document.getElementById('nodeIpStatus').textContent = '', 2000);
        loadStreams();
        startStatusPolling();
    }

    // ── Load MJPEG streams ────────────────────────────────────
    function loadStreams() {
        DIRECTIONS.forEach(dir => {
            const img     = document.getElementById('stream-' + dir);
            const offline = document.getElementById('offline-' + dir);
            const tag     = document.getElementById('tag-' + dir);
            const dot     = document.getElementById('dot-' + dir);
            const status  = document.getElementById('status-' + dir);

            const url = `http://${NODE_IP}:5000/video_feed/${dir}`;

            img.onload = () => {
                img.style.display    = 'block';
                offline.style.display = 'none';
                tag.textContent      = 'Online';
                dot.className        = 'feed-cam-index-dot online';
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
        const tag     = document.getElementById('tag-' + dir);
        const dot     = document.getElementById('dot-' + dir);
        const status  = document.getElementById('status-' + dir);

        img.style.display     = 'none';
        offline.style.display = 'flex';
        offline.querySelector('div').textContent = 'Stream unavailable';
        tag.textContent   = 'Offline';
        dot.className     = 'feed-cam-index-dot offline';
        status.textContent = 'Offline';
        status.className  = 'feed-card-status offline';

        // Retry after 5 seconds
        setTimeout(() => {
            img.src = `http://${NODE_IP}:5000/video_feed/${dir}?t=` + Date.now();
        }, 5000);
    }

    // ── Poll /status every 3 seconds ─────────────────────────
    function startStatusPolling() {
        fetchStatus();
        setInterval(fetchStatus, 3000);
    }

    async function fetchStatus() {
        try {
            const res  = await fetch(`http://${NODE_IP}:5000/status`, { signal: AbortSignal.timeout(2000) });
            const data = await res.json();
            renderStatusPanel(data);
            document.getElementById('lastUpdated').textContent = 'Updated ' + new Date().toLocaleTimeString();
        } catch (e) {
            document.getElementById('statusPanel').innerHTML =
                '<div style="color:#ef4444;font-size:12px;text-align:center;padding:8px 0;">⚠ Cannot reach STAP Node</div>';
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
                <span class="feed-status-label">Signal</span>
                <span class="feed-phase-badge ${phaseClass}">${data.phase_state ?? '—'}</span>
            </div>
            <div class="feed-status-row">
                <span class="feed-status-label">Remaining</span>
                <span class="feed-status-value">${data.remaining_secs ?? 0}s</span>
            </div>
            <div class="feed-status-row">
                <span class="feed-status-label">Mode</span>
                <span class="feed-status-value" style="text-transform:uppercase;">${data.mode ?? '—'}</span>
            </div>
            <div class="feed-status-row">
                <span class="feed-status-label">Rain</span>
                <span class="feed-status-value">${data.rain ? '🌧 Detected' : '☀ Clear'}</span>
            </div>
            <div style="margin-top:10px;margin-bottom:4px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Vehicle Counts</div>
            ${vehicleRows}
        `;
    }

    // ── Auto-load on page open if IP already saved ────────────
    document.addEventListener('DOMContentLoaded', () => {
        if (NODE_IP) {
            loadStreams();
            startStatusPolling();
        }
    });
</script>
@endpush