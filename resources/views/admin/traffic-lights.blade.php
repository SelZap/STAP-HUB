@extends('layouts.admin')
@section('title', 'Traffic Light Control')
@section('page-title', 'Traffic Light Control')

@push('styles')
<style>
    /* ── Base ─────────────────────────────────────────────────── */
    .tl-page { display: flex; flex-direction: column; gap: 1.25rem; }

    /* ── Node IP Bar ─────────────────────────────────────────── */
    .tl-ip-bar {
        background: #1a1a2e;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 12px;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 12px;
        color: rgba(255,255,255,.6);
    }

    .tl-ip-bar input {
        background: rgba(255,255,255,.07);
        border: 1.5px solid rgba(255,255,255,.15);
        border-radius: 7px;
        padding: 5px 12px;
        font-size: 12px;
        font-family: monospace;
        width: 160px;
        color: #fff;
        outline: none;
    }

    .tl-ip-bar input:focus { border-color: rgba(255,255,255,.4); }

    .tl-ip-bar button {
        background: rgba(255,255,255,.1);
        color: #fff;
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 7px;
        padding: 5px 14px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }

    .tl-ip-bar button:hover { background: rgba(255,255,255,.18); }

    .tl-node-indicator {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        color: rgba(255,255,255,.7);
    }

    .tl-node-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #475569;
        transition: background .3s;
    }

    .tl-node-dot.connected    { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.25); }
    .tl-node-dot.disconnected { background: #ef4444; }

    /* ── Main Layout ─────────────────────────────────────────── */
    .tl-main {
        display: grid;
        grid-template-columns: 1fr 1fr 320px;
        gap: 1.25rem;
        align-items: start;
    }

    @media (max-width: 1100px) { .tl-main { grid-template-columns: 1fr 320px; } }
    @media (max-width: 760px)  { .tl-main { grid-template-columns: 1fr; } }

    /* ── Card ────────────────────────────────────────────────── */
    .tl-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid rgba(15,23,42,.08);
        box-shadow: 0 4px 18px rgba(15,23,42,.07);
        overflow: hidden;
    }

    .tl-card-header {
        padding: .8rem 1.2rem;
        border-bottom: 1px solid rgba(15,23,42,.07);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .tl-card-title {
        font-size: .85rem;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .tl-card-body { padding: 1.2rem; }

    /* ── CONTROL BOX panel ───────────────────────────────────── */
    .tl-controlbox {
        background: #d1d5db;
        border-radius: 16px;
        border: 3px solid #9ca3af;
        padding: 1.5rem 1.25rem 1.25rem;
        box-shadow: inset 0 2px 6px rgba(0,0,0,.15), 0 8px 24px rgba(0,0,0,.12);
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .tl-controlbox-label {
        font-size: .65rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: #374151;
        margin-bottom: .5rem;
        text-align: center;
    }

    /* ── Mode Row ────────────────────────────────────────────── */
    .tl-mode-row {
        display: flex;
        align-items: center;
        gap: .75rem;
        justify-content: center;
    }

    /* Square MANUAL button */
    .tl-btn-manual {
        width: 64px; height: 64px;
        border-radius: 10px;
        background: linear-gradient(145deg, #374151, #1f2937);
        border: 3px solid #111827;
        box-shadow: 0 4px 0 #111, inset 0 1px 0 rgba(255,255,255,.1);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        color: rgba(255,255,255,.6);
        font-size: .6rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        transition: all .12s;
    }

    .tl-btn-manual:hover { transform: translateY(-1px); box-shadow: 0 5px 0 #111, inset 0 1px 0 rgba(255,255,255,.15); }
    .tl-btn-manual:active { transform: translateY(2px); box-shadow: 0 2px 0 #111; }
    .tl-btn-manual.active {
        background: linear-gradient(145deg, #1e3a5f, #1e40af);
        border-color: #1d4ed8;
        color: #fff;
        box-shadow: 0 0 0 3px rgba(59,130,246,.4), 0 4px 0 #1e3a5f;
    }

    /* Round colored mode buttons */
    .tl-btn-round {
        width: 56px; height: 56px;
        border-radius: 50%;
        border: 3px solid transparent;
        box-shadow: 0 4px 0 rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.25);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        font-size: .55rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: rgba(255,255,255,.85);
        transition: all .12s;
    }

    .tl-btn-round:hover { transform: translateY(-2px); filter: brightness(1.1); }
    .tl-btn-round:active { transform: translateY(2px); box-shadow: 0 1px 0 rgba(0,0,0,.35); }

    .tl-btn-stap {
        background: radial-gradient(circle at 35% 35%, #60a5fa, #2563eb);
        border-color: #1d4ed8;
    }

    .tl-btn-stap.active {
        box-shadow: 0 0 0 4px rgba(59,130,246,.45), 0 4px 0 #1e3a5f;
    }

    .tl-btn-hazard {
        background: radial-gradient(circle at 35% 35%, #fb923c, #ea580c);
        border-color: #c2410c;
    }

    .tl-btn-hazard.active {
        box-shadow: 0 0 0 4px rgba(249,115,22,.45), 0 4px 0 #7c2d12;
    }

    .tl-btn-emergency-mode {
        background: radial-gradient(circle at 35% 35%, #f87171, #dc2626);
        border-color: #b91c1c;
    }

    .tl-btn-emergency-mode.active {
        box-shadow: 0 0 0 4px rgba(239,68,68,.45), 0 4px 0 #7f1d1d;
        animation: emergencyPulse 1s infinite;
    }

    @keyframes emergencyPulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(239,68,68,.45), 0 4px 0 #7f1d1d; }
        50%       { box-shadow: 0 0 0 8px rgba(239,68,68,.2),  0 4px 0 #7f1d1d; }
    }

    /* ── Lane Buttons ────────────────────────────────────────── */
    .tl-lane-compass {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .65rem;
        max-width: 240px;
        margin: 0 auto;
    }

    .tl-lane-btn {
        aspect-ratio: 1;
        border-radius: 50%;
        background: radial-gradient(circle at 35% 35%, #4ade80, #16a34a);
        border: 3px solid #15803d;
        box-shadow: 0 4px 0 #14532d, inset 0 1px 0 rgba(255,255,255,.3);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        color: rgba(255,255,255,.9);
        font-size: .7rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
        transition: all .12s;
        min-width: 72px;
        min-height: 72px;
    }

    .tl-lane-btn:hover:not(:disabled) { transform: translateY(-2px); filter: brightness(1.1); }
    .tl-lane-btn:active:not(:disabled) { transform: translateY(2px); box-shadow: 0 1px 0 #14532d; }
    .tl-lane-btn:disabled { opacity: .35; cursor: not-allowed; filter: grayscale(.6); }

    .tl-lane-btn.active-green {
        background: radial-gradient(circle at 35% 35%, #86efac, #22c55e);
        box-shadow: 0 0 0 4px rgba(34,197,94,.4), 0 4px 0 #14532d;
    }

    .tl-lane-btn.active-red {
        background: radial-gradient(circle at 35% 35%, #f87171, #dc2626);
        border-color: #b91c1c;
        box-shadow: 0 0 0 4px rgba(239,68,68,.3), 0 4px 0 #7f1d1d;
    }

    .tl-lane-btn.active-yellow {
        background: radial-gradient(circle at 35% 35%, #fde047, #eab308);
        border-color: #a16207;
        box-shadow: 0 0 0 4px rgba(234,179,8,.3), 0 4px 0 #713f12;
        color: #1a0a00;
    }

    /* ── Divider ─────────────────────────────────────────────── */
    .tl-divider {
        height: 2px;
        background: linear-gradient(90deg, transparent, #9ca3af, transparent);
        border-radius: 2px;
    }

    /* ── Camera Grid ─────────────────────────────────────────── */
    .tl-cam-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .75rem;
    }

    .tl-cam-cell {
        border-radius: 10px;
        overflow: hidden;
        background: #0f172a;
        border: 1px solid rgba(255,255,255,.06);
        position: relative;
        aspect-ratio: 16/9;
    }

    .tl-cam-cell img.mjpeg {
        width: 100%; height: 100%;
        object-fit: cover;
        display: none;
    }

    .tl-cam-offline {
        position: absolute; inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        color: rgba(255,255,255,.25);
        font-size: .65rem;
        font-weight: 600;
        text-align: center;
    }

    .tl-cam-dir-tag {
        position: absolute;
        top: 5px; left: 5px;
        background: rgba(15,23,42,.75);
        color: rgba(255,255,255,.9);
        font-size: .6rem;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 5px;
        letter-spacing: .06em;
        backdrop-filter: blur(4px);
    }

    .tl-cam-status-dot {
        position: absolute;
        top: 6px; right: 6px;
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #ef4444;
    }

    .tl-cam-status-dot.online { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.2); }

    /* ── Sidebar ─────────────────────────────────────────────── */
    .tl-sidebar { display: flex; flex-direction: column; gap: 1rem; }

    .tl-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
    }

    .tl-status-row:last-child { border-bottom: none; }
    .tl-status-label { color: #64748b; font-weight: 600; }
    .tl-status-value { font-weight: 700; color: #0f172a; }

    .tl-phase-badge {
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .tl-phase-green  { background: #dcfce7; color: #166534; }
    .tl-phase-yellow { background: #fef3c7; color: #92400e; }
    .tl-phase-red    { background: #fee2e2; color: #991b1b; }

    /* ── Activity Log ────────────────────────────────────────── */
    .tl-log {
        max-height: 200px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: .35rem;
    }

    .tl-log-entry {
        font-size: .72rem;
        padding: .35rem .6rem;
        border-radius: 6px;
        background: #f8fafc;
        border-left: 3px solid #e2e8f0;
        color: #475569;
        line-height: 1.4;
    }

    .tl-log-entry.success { border-left-color: #22c55e; }
    .tl-log-entry.error   { border-left-color: #ef4444; }
    .tl-log-entry.info    { border-left-color: #3b82f6; }

    /* ── Toast ───────────────────────────────────────────────── */
    .tl-toast {
        position: fixed;
        bottom: 1.5rem; right: 1.5rem;
        background: #0f172a;
        color: #fff;
        padding: .75rem 1.25rem;
        border-radius: .75rem;
        font-size: .85rem;
        font-weight: 600;
        box-shadow: 0 8px 24px rgba(15,23,42,.3);
        z-index: 9999;
        transform: translateY(130%);
        transition: transform .25s;
        max-width: 280px;
    }

    .tl-toast.show      { transform: translateY(0); }
    .tl-toast.toast-err { background: #991b1b; }

    .tl-mode-hint {
        text-align: center;
        font-size: .7rem;
        font-weight: 700;
        color: #6b7280;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-top: -.25rem;
    }

    @keyframes livePulse {
        0%,100% { box-shadow: 0 0 0 3px rgba(34,197,94,.2); }
        50%      { box-shadow: 0 0 0 6px rgba(34,197,94,.06); }
    }
</style>
@endpush

@section('content')

<div class="tl-page">

    {{-- Node IP Bar --}}
    <div class="tl-ip-bar">
        <span>&#9881;&#65039; STAP Node IP:</span>
        <input type="text" id="nodeIpInput" value="192.168.1.100" placeholder="e.g. 192.168.1.50">
        <button onclick="applyNodeIp()">Apply</button>
        <span id="nodeIpMsg" style="font-size:11px;color:#22c55e;"></span>
        <div class="tl-node-indicator">
            <span class="tl-node-dot" id="nodeConnDot"></span>
            <span id="nodeConnLabel">Connecting...</span>
        </div>
    </div>

    <div class="tl-main">

        {{-- Col 1: Control Box --}}
        <div>
            <div class="tl-card">
                <div class="tl-card-header">
                    <span class="tl-card-title">Control Panel</span>
                    <span style="font-size:.72rem;color:#94a3b8;" id="modeHint">Select a mode</span>
                </div>
                <div class="tl-card-body">
                    <div class="tl-controlbox">

                        {{-- Mode Buttons --}}
                        <div>
                            <div class="tl-controlbox-label">System Mode</div>
                            <div class="tl-mode-row">

                                <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
                                    <button class="tl-btn-manual" id="btn-manual" onclick="setMode('manual')" title="Manual Override">
                                        <span style="font-size:1.2rem;">&#128281;</span>
                                        <span style="font-size:.55rem;font-weight:800;letter-spacing:.05em;">MANUAL</span>
                                    </button>
                                </div>

                                <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
                                    <button class="tl-btn-round tl-btn-stap active" id="btn-auto" onclick="setMode('auto')" title="STAP Auto Mode">
                                        <span style="font-size:1rem;">&#129302;</span>
                                        <span style="font-size:.55rem;font-weight:800;letter-spacing:.04em;">STAP</span>
                                    </button>
                                </div>

                                <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
                                    <button class="tl-btn-round tl-btn-hazard" id="btn-hazard" onclick="setMode('hazard')" title="Hazard Mode">
                                        <span style="font-size:1rem;">&#9888;&#65039;</span>
                                        <span style="font-size:.55rem;font-weight:800;letter-spacing:.04em;">HAZARD</span>
                                    </button>
                                </div>

                                <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
                                    <button class="tl-btn-round tl-btn-emergency-mode" id="btn-emergency-panel" onclick="showEmergencyPicker()" title="Emergency Override">
                                        <span style="font-size:1rem;">&#128680;</span>
                                        <span style="font-size:.55rem;font-weight:800;letter-spacing:.04em;">EMERG</span>
                                    </button>
                                </div>

                            </div>
                        </div>

                        <div class="tl-divider"></div>

                        {{-- Lane Buttons --}}
                        <div>
                            <div class="tl-controlbox-label">Lane Control</div>
                            <div class="tl-lane-compass">
                                <button class="tl-lane-btn" id="laneBtn-NORTH" data-lane="NORTH" onclick="cycleLaneLight(this.dataset.lane)" disabled>
                                    <span style="font-size:1rem;">&#8593;</span>
                                    <span style="font-size:.7rem;font-weight:900;">NORTH</span>
                                    <span style="font-size:.5rem;opacity:.8;" id="laneState-NORTH">RED</span>
                                </button>
                                <button class="tl-lane-btn" id="laneBtn-EAST" data-lane="EAST" onclick="cycleLaneLight(this.dataset.lane)" disabled>
                                    <span style="font-size:1rem;">&#8594;</span>
                                    <span style="font-size:.7rem;font-weight:900;">EAST</span>
                                    <span style="font-size:.5rem;opacity:.8;" id="laneState-EAST">RED</span>
                                </button>
                                <button class="tl-lane-btn" id="laneBtn-WEST" data-lane="WEST" onclick="cycleLaneLight(this.dataset.lane)" disabled>
                                    <span style="font-size:1rem;">&#8592;</span>
                                    <span style="font-size:.7rem;font-weight:900;">WEST</span>
                                    <span style="font-size:.5rem;opacity:.8;" id="laneState-WEST">RED</span>
                                </button>
                                <button class="tl-lane-btn" id="laneBtn-SOUTH" data-lane="SOUTH" onclick="cycleLaneLight(this.dataset.lane)" disabled>
                                    <span style="font-size:1rem;">&#8595;</span>
                                    <span style="font-size:.7rem;font-weight:900;">SOUTH</span>
                                    <span style="font-size:.5rem;opacity:.8;" id="laneState-SOUTH">RED</span>
                                </button>
                            </div>
                            <div class="tl-mode-hint" style="margin-top:.75rem;" id="laneHint">Enable Manual mode to control lanes</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Col 2: Camera Feeds --}}
        <div>
            <div class="tl-card" style="background:#0f172a;">
                <div class="tl-card-header" style="background:#0f172a;border-bottom-color:rgba(255,255,255,.07);">
                    <span class="tl-card-title" style="color:#fff;">Live Feeds</span>
                    <div style="display:flex;align-items:center;gap:.4rem;">
                        <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;animation:livePulse 1.6s infinite;"></span>
                        <span style="font-size:.72rem;color:rgba(255,255,255,.5);">4 cameras</span>
                    </div>
                </div>
                <div class="tl-card-body" style="padding:.875rem;">
                    <div class="tl-cam-grid">

                        <div class="tl-cam-cell">
                            <img class="mjpeg" id="stream-north" src="" alt="NORTH" onerror="handleStreamError(this)">
                            <div class="tl-cam-offline" id="offline-north">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                <span>Offline</span>
                            </div>
                            <span class="tl-cam-dir-tag">NORTH</span>
                            <span class="tl-cam-status-dot" id="camDot-north"></span>
                        </div>

                        <div class="tl-cam-cell">
                            <img class="mjpeg" id="stream-south" src="" alt="SOUTH" onerror="handleStreamError(this)">
                            <div class="tl-cam-offline" id="offline-south">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                <span>Offline</span>
                            </div>
                            <span class="tl-cam-dir-tag">SOUTH</span>
                            <span class="tl-cam-status-dot" id="camDot-south"></span>
                        </div>

                        <div class="tl-cam-cell">
                            <img class="mjpeg" id="stream-east" src="" alt="EAST" onerror="handleStreamError(this)">
                            <div class="tl-cam-offline" id="offline-east">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                <span>Offline</span>
                            </div>
                            <span class="tl-cam-dir-tag">EAST</span>
                            <span class="tl-cam-status-dot" id="camDot-east"></span>
                        </div>

                        <div class="tl-cam-cell">
                            <img class="mjpeg" id="stream-west" src="" alt="WEST" onerror="handleStreamError(this)">
                            <div class="tl-cam-offline" id="offline-west">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                <span>Offline</span>
                            </div>
                            <span class="tl-cam-dir-tag">WEST</span>
                            <span class="tl-cam-status-dot" id="camDot-west"></span>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Col 3: Sidebar --}}
        <div class="tl-sidebar">

            <div class="tl-card">
                <div class="tl-card-header">
                    <span class="tl-card-title">&#128225; Live Status</span>
                    <span style="font-size:.68rem;color:#94a3b8;" id="lastPoll">&#8212;</span>
                </div>
                <div class="tl-card-body" id="statusPanel">
                    <div style="color:#94a3b8;font-size:12px;text-align:center;padding:8px 0;">Waiting for node...</div>
                </div>
            </div>

            <div class="tl-card">
                <div class="tl-card-header">
                    <span class="tl-card-title">&#128203; Log</span>
                    <button onclick="clearLog()" style="font-size:.68rem;color:#94a3b8;background:none;border:none;cursor:pointer;">Clear</button>
                </div>
                <div class="tl-card-body" style="padding:.75rem;">
                    <div class="tl-log" id="activityLog">
                        <div class="tl-log-entry info">Panel loaded. Set Node IP to begin.</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Emergency Lane Picker Modal --}}
<div id="emergencyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9998;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:2rem;max-width:340px;width:90%;text-align:center;box-shadow:0 24px 48px rgba(0,0,0,.3);">
        <div style="font-size:2rem;margin-bottom:.5rem;">&#128680;</div>
        <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:.35rem;">Emergency Override</div>
        <div style="font-size:.82rem;color:#64748b;margin-bottom:1.25rem;">Select the lane that needs immediate green light access.</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-bottom:1rem;">
            <button data-lane="NORTH" onclick="triggerEmergency(this.dataset.lane)"
                style="padding:.75rem;background:#fff1f2;border:2px solid #fca5a5;border-radius:10px;color:#991b1b;font-size:.85rem;font-weight:800;cursor:pointer;">
                NORTH
            </button>
            <button data-lane="SOUTH" onclick="triggerEmergency(this.dataset.lane)"
                style="padding:.75rem;background:#fff1f2;border:2px solid #fca5a5;border-radius:10px;color:#991b1b;font-size:.85rem;font-weight:800;cursor:pointer;">
                SOUTH
            </button>
            <button data-lane="EAST" onclick="triggerEmergency(this.dataset.lane)"
                style="padding:.75rem;background:#fff1f2;border:2px solid #fca5a5;border-radius:10px;color:#991b1b;font-size:.85rem;font-weight:800;cursor:pointer;">
                EAST
            </button>
            <button data-lane="WEST" onclick="triggerEmergency(this.dataset.lane)"
                style="padding:.75rem;background:#fff1f2;border:2px solid #fca5a5;border-radius:10px;color:#991b1b;font-size:.85rem;font-weight:800;cursor:pointer;">
                WEST
            </button>
        </div>
        <button onclick="closeEmergencyModal()"
            style="width:100%;padding:.6rem;background:#f1f5f9;border:none;border-radius:8px;color:#64748b;font-size:.82rem;font-weight:600;cursor:pointer;">
            Cancel
        </button>
    </div>
</div>

{{-- Toast --}}
<div class="tl-toast" id="toast"></div>

@endsection

@push('scripts')
<script>
    let NODE_IP  = localStorage.getItem('stap_node_ip') || '192.168.1.100';
    let curMode  = 'auto';
    const LANES  = ['NORTH','SOUTH','EAST','WEST'];
    const DIRS   = ['north','south','east','west'];
    const laneStates = { NORTH: 'red', SOUTH: 'red', EAST: 'red', WEST: 'red' };

    document.getElementById('nodeIpInput').value = NODE_IP;

    function applyNodeIp() {
        NODE_IP = document.getElementById('nodeIpInput').value.trim();
        localStorage.setItem('stap_node_ip', NODE_IP);
        const msg = document.getElementById('nodeIpMsg');
        msg.textContent = 'Applied';
        setTimeout(() => msg.textContent = '', 2000);
        loadStreams();
        startStatusPolling();
        logActivity('Node IP set to ' + NODE_IP, 'info');
    }

    async function setMode(mode) {
        try {
            const res  = await postNode('/control/mode', { mode });
            const data = await res.json();
            if (data.success) {
                curMode = mode;
                updateModeUI(mode);
                logActivity('Mode switched to ' + mode.toUpperCase(), 'success');
                showToast('Mode: ' + mode.toUpperCase());
            } else {
                showToast(data.message || 'Failed', true);
                logActivity('Mode switch failed: ' + (data.message || ''), 'error');
            }
        } catch (e) {
            showToast('Node unreachable', true);
            logActivity('Cannot reach STAP Node', 'error');
        }
    }

    function updateModeUI(mode) {
        document.getElementById('btn-manual').classList.remove('active');
        document.getElementById('btn-auto').classList.remove('active');
        document.getElementById('btn-hazard').classList.remove('active');
        document.getElementById('btn-emergency-panel').classList.remove('active');

        if (mode === 'manual') document.getElementById('btn-manual').classList.add('active');
        if (mode === 'auto')   document.getElementById('btn-auto').classList.add('active');
        if (mode === 'hazard') document.getElementById('btn-hazard').classList.add('active');

        const isManual = mode === 'manual' || mode === 'hazard';
        LANES.forEach(function(lane) {
            document.getElementById('laneBtn-' + lane).disabled = !isManual;
        });

        document.getElementById('laneHint').textContent = isManual
            ? 'Tap a lane to toggle GREEN / RED'
            : 'Enable Manual mode to control lanes';

        document.getElementById('modeHint').textContent = 'Mode: ' + mode.toUpperCase();
    }

    function cycleLaneLight(lane) {
        var next = laneStates[lane] === 'green' ? 'red' : 'green';
        setLight(lane, next);
    }

    async function setLight(lane, state) {
        try {
            const res  = await postNode('/control/light', { lane: lane, state: state });
            const data = await res.json();
            if (data.success) {
                laneStates[lane] = state;
                updateLaneBtn(lane, state);
                logActivity(lane + ' set to ' + state.toUpperCase(), 'success');
                showToast(lane + ': ' + state.toUpperCase());
            } else {
                showToast(data.message || 'Failed', true);
                logActivity(lane + ' failed: ' + (data.message || ''), 'error');
            }
        } catch (e) {
            showToast('Node unreachable', true);
            logActivity('Cannot reach STAP Node', 'error');
        }
    }

    function updateLaneBtn(lane, state) {
        var btn = document.getElementById('laneBtn-' + lane);
        var lbl = document.getElementById('laneState-' + lane);
        btn.className = 'tl-lane-btn active-' + state;
        lbl.textContent = state.toUpperCase();
    }

    function showEmergencyPicker() {
        document.getElementById('emergencyModal').style.display = 'flex';
    }

    function closeEmergencyModal() {
        document.getElementById('emergencyModal').style.display = 'none';
    }

    async function triggerEmergency(lane) {
        closeEmergencyModal();
        try {
            const res  = await postNode('/control/emergency', { lane: lane });
            const data = await res.json();
            if (data.success) {
                curMode = 'auto';
                updateModeUI('auto');
                document.getElementById('btn-emergency-panel').classList.add('active');
                logActivity('EMERGENCY OVERRIDE: ' + lane + ' has priority', 'error');
                showToast('Emergency: ' + lane + ' priority lane');
            } else {
                showToast(data.message || 'Failed', true);
            }
        } catch (e) {
            showToast('Node unreachable', true);
            logActivity('Cannot reach STAP Node', 'error');
        }
    }

    function loadStreams() {
        DIRS.forEach(function(dir) {
            var img     = document.getElementById('stream-' + dir);
            var offline = document.getElementById('offline-' + dir);
            var dot     = document.getElementById('camDot-' + dir);

            img.onload = function() {
                img.style.display     = 'block';
                offline.style.display = 'none';
                dot.classList.add('online');
            };

            img.onerror = function() { handleStreamError(img); };
            img.src     = 'http://' + NODE_IP + ':5000/video_feed/' + dir;
        });
    }

    function handleStreamError(img) {
        var dir     = img.id.replace('stream-', '');
        var offline = document.getElementById('offline-' + dir);
        var dot     = document.getElementById('camDot-' + dir);

        img.style.display     = 'none';
        offline.style.display = 'flex';
        dot.classList.remove('online');

        setTimeout(function() {
            img.src = 'http://' + NODE_IP + ':5000/video_feed/' + dir + '?t=' + Date.now();
        }, 5000);
    }

    var pollInterval = null;

    function startStatusPolling() {
        if (pollInterval) clearInterval(pollInterval);
        fetchStatus();
        pollInterval = setInterval(fetchStatus, 3000);
    }

    async function fetchStatus() {
        try {
            const res  = await fetch('http://' + NODE_IP + ':5000/status', { signal: AbortSignal.timeout(2000) });
            const data = await res.json();

            setNodeConnected(true);
            renderStatusPanel(data);
            document.getElementById('lastPoll').textContent = new Date().toLocaleTimeString();

            if (data.mode && data.mode !== curMode) {
                curMode = data.mode;
                updateModeUI(data.mode);
            }
        } catch (e) {
            setNodeConnected(false);
            document.getElementById('statusPanel').innerHTML =
                '<div style="color:#ef4444;font-size:12px;text-align:center;padding:8px 0;">Cannot reach node</div>';
        }
    }

    function renderStatusPanel(data) {
        var phaseClass = data.phase_state === 'GREEN'  ? 'tl-phase-green'
                       : data.phase_state === 'YELLOW' ? 'tl-phase-yellow'
                       : 'tl-phase-red';

        var losColors = { A:'#166534', B:'#166534', C:'#92400e', D:'#92400e', E:'#991b1b', F:'#991b1b' };

        var rows = '';
        LANES.forEach(function(lane) {
            var count = (data.vehicle_counts && data.vehicle_counts[lane] !== undefined) ? data.vehicle_counts[lane] : 0;
            var los   = (data.los && data.los[lane]) ? data.los[lane] : '?';
            var color = losColors[los] || '#334155';
            rows += '<div class="tl-status-row">'
                  + '<span class="tl-status-label">' + lane + '</span>'
                  + '<span class="tl-status-value">' + count + 'v &nbsp;<span style="color:' + color + ';font-weight:800;">LOS ' + los + '</span></span>'
                  + '</div>';
        });

        document.getElementById('statusPanel').innerHTML =
            '<div class="tl-status-row"><span class="tl-status-label">Active Lane</span><span class="tl-status-value">' + (data.active_lane || '--') + '</span></div>'
          + '<div class="tl-status-row"><span class="tl-status-label">Signal</span><span class="tl-phase-badge ' + phaseClass + '">' + (data.phase_state || '--') + '</span></div>'
          + '<div class="tl-status-row"><span class="tl-status-label">Remaining</span><span class="tl-status-value">' + (data.remaining_secs || 0) + 's</span></div>'
          + '<div class="tl-status-row"><span class="tl-status-label">Mode</span><span class="tl-status-value" style="text-transform:uppercase;">' + (data.mode || '--') + '</span></div>'
          + '<div class="tl-status-row"><span class="tl-status-label">Rain</span><span class="tl-status-value">' + (data.rain ? 'Detected' : 'Clear') + '</span></div>'
          + '<div style="margin:8px 0 4px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Counts</div>'
          + rows;
    }

    function setNodeConnected(ok) {
        document.getElementById('nodeConnDot').className    = 'tl-node-dot ' + (ok ? 'connected' : 'disconnected');
        document.getElementById('nodeConnLabel').textContent = ok ? 'Node Connected' : 'Node Disconnected';
    }

    function postNode(endpoint, body) {
        return fetch('http://' + NODE_IP + ':5000' + endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
            signal: AbortSignal.timeout(3000)
        });
    }

    function logActivity(msg, type) {
        type = type || 'info';
        var log   = document.getElementById('activityLog');
        var entry = document.createElement('div');
        entry.className   = 'tl-log-entry ' + type;
        entry.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
        log.prepend(entry);
        while (log.children.length > 50) log.removeChild(log.lastChild);
    }

    function clearLog() {
        document.getElementById('activityLog').innerHTML = '<div class="tl-log-entry info">Log cleared.</div>';
    }

    var toastTimer = null;
    function showToast(msg, isErr) {
        var t = document.getElementById('toast');
        t.textContent = msg;
        t.className = 'tl-toast show' + (isErr ? ' toast-err' : '');
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(function() { t.classList.remove('show'); }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateModeUI('auto');
        if (NODE_IP) {
            loadStreams();
            startStatusPolling();
        }
    });
</script>
@endpush