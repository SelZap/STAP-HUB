@extends('layouts.admin')

@section('title', 'Traffic Lights')
@section('page-title', 'Traffic Light Control')

@push('styles')
<style>
    .tlc-layout {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 16px;
        align-items: start;
    }
    @media (max-width: 1100px) {
        .tlc-layout { grid-template-columns: 1fr; }
    }

    /* ── Node IP bar ───────────────────────────────────── */
    .tlc-nodebar {
        background: var(--navy);
        border-radius: var(--radius-md);
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        color: #fff;
    }
    .tlc-nodebar input {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: #fff;
        padding: 7px 12px;
        border-radius: 6px;
        font-size: 12px;
        width: 150px;
    }
    .tlc-nodebar input::placeholder { color: rgba(255,255,255,0.4); }
    .tlc-nodebar button {
        background: #fff; color: var(--navy); border: none;
        padding: 7px 16px; border-radius: 6px; font-size: 12px; font-weight: 700;
        cursor: pointer;
    }
    .tlc-conn-badge {
        margin-left: auto; font-size: 11px; font-weight: 700;
        padding: 4px 10px; border-radius: 20px; display: flex; align-items: center; gap: 6px;
    }
    .tlc-conn-dot { width: 7px; height: 7px; border-radius: 50%; }
    .tlc-conn-badge.online  { background: rgba(41,179,87,.18); color: #5be08a; }
    .tlc-conn-badge.online .tlc-conn-dot  { background: #29B357; }
    .tlc-conn-badge.offline { background: rgba(224,48,64,.18); color: #ff8f99; }
    .tlc-conn-badge.offline .tlc-conn-dot { background: #E03040; }

    /* ── Control panel (compact) ──────────────────────── */
    .tlc-panel {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px;
        box-shadow: var(--shadow-sm);
    }
    .tlc-panel-title { font-size: 12px; font-weight: 800; color: var(--navy); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
    .tlc-panel-sub   { font-size: 11px; color: var(--text-muted); margin-bottom: 14px; }

    .tlc-mode-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 14px;
    }

    .tlc-btn {
        border: none;
        border-radius: 10px;
        padding: 12px 8px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        background: #1c2742;
        color: rgba(255,255,255,0.55);
        transition: all 0.15s ease;
        border: 2px solid transparent;
        position: relative;
    }
    .tlc-btn svg { width: 18px; height: 18px; }
    .tlc-btn:hover:not(:disabled) { color: #fff; background: #243460; }
    .tlc-btn:disabled { opacity: 0.4; cursor: not-allowed; }

    /* glow per mode when active */
    .tlc-btn.mode-ai.active     { background: #1d3a2c; color: #5be08a; border-color: #29B357; box-shadow: 0 0 16px rgba(41,179,87,.45); }
    .tlc-btn.mode-manual.active { background: #2a2f4a; color: #93a9ff; border-color: #5b73e8; box-shadow: 0 0 16px rgba(91,115,232,.45); }
    .tlc-btn.mode-hazard.active { background: #3a2d12; color: #ffce5b; border-color: #F4B942; box-shadow: 0 0 16px rgba(244,185,66,.45); animation: tlc-pulse-amber 1.2s infinite; }
    .tlc-btn.mode-emergency.active { background: #3a1414; color: #ff7a7a; border-color: #E03040; box-shadow: 0 0 16px rgba(224,48,64,.5); animation: tlc-pulse-red 1s infinite; }

    @keyframes tlc-pulse-amber { 0%,100% { box-shadow: 0 0 14px rgba(244,185,66,.4); } 50% { box-shadow: 0 0 24px rgba(244,185,66,.75); } }
    @keyframes tlc-pulse-red   { 0%,100% { box-shadow: 0 0 14px rgba(224,48,64,.45); } 50% { box-shadow: 0 0 26px rgba(224,48,64,.85); } }

    /* Lane D-pad */
    .tlc-dpad-label { font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.6px; margin: 14px 0 8px; }
    .tlc-dpad {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        grid-template-rows: 1fr 1fr 1fr;
        gap: 6px;
        width: 100%;
        max-width: 220px;
        margin: 0 auto;
    }
    .tlc-dpad-btn {
        border: 2px solid #2a3354;
        background: #1c2742;
        color: rgba(255,255,255,0.55);
        border-radius: 8px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.4px;
        cursor: pointer;
        padding: 10px 4px;
        transition: all 0.15s ease;
    }
    .tlc-dpad-btn:hover:not(:disabled) { color: #fff; background: #243460; }
    .tlc-dpad-btn:disabled { opacity: 0.35; cursor: not-allowed; }
    .tlc-dpad-btn.lane-active {
        background: #1d3a2c; color: #5be08a; border-color: #29B357;
        box-shadow: 0 0 14px rgba(41,179,87,.45);
    }
    .tlc-dpad-n { grid-column: 2; grid-row: 1; }
    .tlc-dpad-w { grid-column: 1; grid-row: 2; }
    .tlc-dpad-center { grid-column: 2; grid-row: 2; display:flex; align-items:center; justify-content:center; color: var(--text-muted); font-size: 9px; font-weight: 700; }
    .tlc-dpad-e { grid-column: 3; grid-row: 2; }
    .tlc-dpad-s { grid-column: 2; grid-row: 3; }

    /* ── Inline live status (below control panel) ─────── */
    .tlc-live-status {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--border);
    }
    .tlc-live-status-title {
        font-size: 10px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 8px;
    }
    .tlc-lane-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px solid var(--border);
        font-size: 11px;
        gap: 6px;
    }
    .tlc-lane-row:last-child { border-bottom: none; }
    .tlc-lane-name {
        font-weight: 800;
        font-size: 10px;
        letter-spacing: 0.5px;
        color: var(--text-secondary);
        width: 44px;
        flex-shrink: 0;
    }
    .tlc-lane-name.emg { color: #ff7a7a; }

    /* Mini traffic light: three dots in a column */
    .tlc-mini-light {
        display: flex;
        flex-direction: column;
        gap: 2px;
        align-items: center;
        background: #111824;
        border-radius: 4px;
        padding: 3px 4px;
        border: 1px solid #2a3354;
        flex-shrink: 0;
    }
    .tlc-mini-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #1e2840;
    }
    .tlc-mini-dot.on-red    { background: #E03040; box-shadow: 0 0 4px rgba(224,48,64,.7); }
    .tlc-mini-dot.on-yellow { background: #F4B942; box-shadow: 0 0 4px rgba(244,185,66,.7); }
    .tlc-mini-dot.on-green  { background: #29B357; box-shadow: 0 0 4px rgba(41,179,87,.7); }

    .tlc-lane-meta {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1px;
        min-width: 0;
    }
    .tlc-lane-queue {
        font-size: 10px;
        color: var(--text-secondary);
    }
    .tlc-lane-los {
        font-size: 10px;
        color: var(--text-muted);
    }

    .tlc-phase-strip {
        margin-top: 8px;
        background: #111824;
        border-radius: 6px;
        padding: 6px 9px;
        font-size: 10px;
        color: var(--text-muted);
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .tlc-phase-strip .val { color: var(--navy); font-weight: 800; font-size: 11px; }
    .tlc-phase-strip.emg  .val { color: #ff7a7a; }

    /* ── Live feeds (large) ────────────────────────────── */
    .tlc-feeds-card { background: var(--navy); border-radius: var(--radius-md); padding: 14px; }
    .tlc-feeds-header { display: flex; align-items: center; justify-content: space-between; padding: 0 4px 12px; color: #fff; }
    .tlc-feeds-title { font-size: 12px; font-weight: 800; letter-spacing: 0.5px; }
    .tlc-feeds-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    @media (max-width: 700px) { .tlc-feeds-grid { grid-template-columns: 1fr; } }

    .tlc-feed-cell {
        background: #0d1526; border-radius: 10px; overflow: hidden;
        aspect-ratio: 16/10; position: relative; border: 1px solid rgba(255,255,255,0.08);
    }
    .tlc-feed-cell img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .tlc-feed-label {
        position: absolute; top: 8px; left: 10px; font-size: 11px; font-weight: 800;
        color: #fff; text-shadow: 0 1px 4px rgba(0,0,0,0.7); letter-spacing: 0.5px;
    }
    .tlc-feed-dot {
        position: absolute; top: 10px; right: 10px; width: 8px; height: 8px; border-radius: 50%;
    }
    .tlc-feed-dot.live    { background: #29B357; box-shadow: 0 0 6px #29B357; }
    .tlc-feed-dot.offline { background: #E03040; }
    .tlc-feed-offline {
        position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center;
        justify-content: center; gap: 6px; color: rgba(255,255,255,0.3); font-size: 12px;
    }
    .tlc-feed-state-chip {
        position: absolute; bottom: 8px; left: 10px; font-size: 10px; font-weight: 800;
        padding: 3px 9px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.4px;
    }

    /* ── Log ───────────────────────────────────────────── */
    .tlc-log-entry { font-size: 11px; padding: 6px 0; border-bottom: 1px solid var(--border); color: var(--text-secondary); }
    .tlc-log-entry:last-child { border-bottom: none; }
    .tlc-log-entry.err { color: var(--red-text); }
</style>
@endpush

@section('content')

{{-- Node IP control bar --}}
<div class="tlc-nodebar">
    <span style="font-size:12px;font-weight:700;">⚙ STAP Node IP:</span>
    <input type="text" id="nodeIpInput" placeholder="e.g. 192.168.1.42">
    <button onclick="applyNodeIp()">Apply</button>
    <div class="tlc-conn-badge offline" id="connBadge">
        <span class="tlc-conn-dot"></span>
        <span id="connBadgeText">Node Disconnected</span>
    </div>
</div>

<div class="tlc-layout">

    {{-- LEFT: Compact control panel --}}
    <div class="tlc-panel">
        <div class="tlc-panel-title">Control Panel</div>
        <div class="tlc-panel-sub">System mode &amp; lane override</div>

        <div class="tlc-mode-grid">
            <button class="tlc-btn mode-ai" id="btnModeAuto" onclick="setMode('auto')" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/><path d="M12 6v6l4 2"/></svg>
                AI Mode
            </button>
            <button class="tlc-btn mode-manual" id="btnModeManual" onclick="setMode('manual')" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M9 18h6"/></svg>
                Manual
            </button>
            <button class="tlc-btn mode-hazard" id="btnModeHazard" onclick="setMode('hazard')" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Hazard
            </button>
            {{-- Emergency: toggle. Click once = lockdown (all red), click again = back to manual --}}
            <button class="tlc-btn mode-emergency" id="btnModeEmergency" onclick="toggleEmergency()" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Emergency
            </button>
        </div>

        <div class="tlc-dpad-label">Lane Override (Manual Mode)</div>
        <div class="tlc-dpad">
            <button class="tlc-dpad-btn tlc-dpad-n" id="laneBtnNorth" onclick="setLane('NORTH')" disabled>NORTH</button>
            <button class="tlc-dpad-btn tlc-dpad-w" id="laneBtnWest"  onclick="setLane('WEST')"  disabled>WEST</button>
            <div class="tlc-dpad-center">LANE</div>
            <button class="tlc-dpad-btn tlc-dpad-e" id="laneBtnEast"  onclick="setLane('EAST')"  disabled>EAST</button>
            <button class="tlc-dpad-btn tlc-dpad-s" id="laneBtnSouth" onclick="setLane('SOUTH')" disabled>SOUTH</button>
        </div>

        {{-- ── INLINE LIVE STATUS (below dpad, inside the panel) ── --}}
        <div class="tlc-live-status">
            <div class="tlc-live-status-title">📡 Live Status</div>

            {{-- Per-lane rows with mini traffic light --}}
            @foreach (['NORTH','SOUTH','EAST','WEST'] as $lane)
            <div class="tlc-lane-row" id="laneRow{{ $lane }}">
                <span class="tlc-lane-name" id="laneLabel{{ $lane }}">{{ $lane }}</span>
                <div class="tlc-mini-light" id="miniLight{{ $lane }}">
                    <div class="tlc-mini-dot" id="miniRed{{ $lane }}"></div>
                    <div class="tlc-mini-dot" id="miniYellow{{ $lane }}"></div>
                    <div class="tlc-mini-dot" id="miniGreen{{ $lane }}"></div>
                </div>
                <div class="tlc-lane-meta">
                    <span class="tlc-lane-queue">Queue: <strong id="miniQueue{{ $lane }}">—</strong></span>
                    <span class="tlc-lane-los">LOS: <strong id="miniLos{{ $lane }}">—</strong></span>
                </div>
            </div>
            @endforeach

            {{-- Phase / timing strip --}}
            <div class="tlc-phase-strip" id="phaseStrip">
                <div>Active: <span class="val" id="statActiveLane">—</span></div>
                <div>Phase: <span class="val" id="statPhase">—</span> · <span class="val" id="statRemaining">—</span>s left</div>
                <div>Mode: <span class="val" id="statMode">—</span> · Rain: <span class="val" id="statRain">—</span></div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Live feeds (large) --}}
    <div class="tlc-feeds-card">
        <div class="tlc-feeds-header">
            <span class="tlc-feeds-title">LIVE FEEDS</span>
            <span style="font-size:11px;color:rgba(255,255,255,0.6);" id="feedsCamCount">4 cameras</span>
        </div>
        <div class="tlc-feeds-grid">
            @foreach (['NORTH','SOUTH','EAST','WEST'] as $lane)
            <div class="tlc-feed-cell">
                <img id="feedImg{{ $lane }}" src="" alt="{{ $lane }}" style="display:none;">
                <div class="tlc-feed-offline" id="feedOffline{{ $lane }}">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    <span>Offline</span>
                </div>
                <span class="tlc-feed-dot offline" id="feedDot{{ $lane }}"></span>
                <span class="tlc-feed-label">{{ $lane }}</span>
                <span class="tlc-feed-state-chip" id="feedChip{{ $lane }}" style="display:none;"></span>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- Bottom: Log only (live status moved into the panel above) --}}
<div style="margin-top:16px;">
    <div class="stap-card">
        <div class="stap-card-header">
            <span class="stap-card-title">📋 LOG</span>
            <span class="stap-card-link" onclick="clearLog()" style="cursor:pointer;">Clear</span>
        </div>
        <div class="stap-card-body" id="logBody" style="max-height:220px;overflow-y:auto;">
            <div class="tlc-log-entry">Panel loaded. Set Node IP to begin.</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ============================================================
   STAP Hub — Traffic Light Control
   ============================================================
   The Hub sends commands to the Flask STAP Node.
   The Flask node forwards them to the ESP32 over serial.

   MODE BUTTONS mirror the ESP32 physical control box exactly:
     AUTO     → AI-managed phasing
     MANUAL   → remote lane selection via D-pad (or physical box buttons)
     HAZARD   → all four lanes flash yellow (ESP32 handles blink timing)
     EMERGENCY→ TOGGLE: ON = all-red lockdown, OFF = back to MANUAL

   The /status poll tells us: mode ('auto'|'manual'), active_lane,
   phase_state, remaining_secs, vehicle_counts, los, lane_statuses.
   It does NOT expose hazard/emergency sub-states — those are tracked
   client-side based on what the Hub itself commanded, matching the
   ESP32 physical button behaviour exactly.

   Mini traffic lights in the live status panel derive from:
     - In AUTO mode:  the active lane is GREEN, all others RED
                      (YELLOW shown during phase_state === 'YELLOW')
     - In MANUAL mode: we track which lane the Hub set to GREEN last.
                       If HAZARD is active: all dots = YELLOW.
                       If EMERGENCY lockdown: all dots = RED.
   ============================================================ */

let nodeBaseUrl   = sessionStorage.getItem('stap_node_ip') ? `http://${sessionStorage.getItem('stap_node_ip')}:5000` : null;
let nodeConnected = false;

// Mirrors the ESP32 physical control box states
let currentMode      = null;   // 'auto' | 'manual' | 'hazard' | 'emergency'
let currentLane      = null;   // 'NORTH' | 'SOUTH' | 'EAST' | 'WEST'  (last green lane in manual)
let emergencyActive  = false;  // tracks the toggle state for the emergency button

const LANES = ['NORTH', 'SOUTH', 'EAST', 'WEST'];

// ── Logging ─────────────────────────────────────────────────
function logLine(msg, isErr = false) {
    const body = document.getElementById('logBody');
    const time = new Date().toLocaleTimeString();
    const div  = document.createElement('div');
    div.className = 'tlc-log-entry' + (isErr ? ' err' : '');
    div.textContent = `[${time}] ${msg}`;
    body.prepend(div);
    while (body.children.length > 50) body.removeChild(body.lastChild);
}
function clearLog() { document.getElementById('logBody').innerHTML = ''; }

// ── Node IP ─────────────────────────────────────────────────
function applyNodeIp() {
    const ip = document.getElementById('nodeIpInput').value.trim();
    if (!ip) return;
    sessionStorage.setItem('stap_node_ip', ip);
    nodeBaseUrl = `http://${ip}:5000`;
    logLine(`Node IP set to ${ip}. Connecting…`);
    wireFeeds();
    pollStatus();
}
(function initIp() {
    const saved = sessionStorage.getItem('stap_node_ip');
    if (saved) document.getElementById('nodeIpInput').value = saved;
})();

// ── Connection badge + button enable/disable ─────────────────
function setConnBadge(online) {
    nodeConnected = online;
    const badge = document.getElementById('connBadge');
    const text  = document.getElementById('connBadgeText');
    badge.className = 'tlc-conn-badge ' + (online ? 'online' : 'offline');
    text.textContent = online ? 'Node Connected' : 'Node Disconnected';
    ['btnModeAuto','btnModeManual','btnModeHazard','btnModeEmergency'].forEach(id => {
        document.getElementById(id).disabled = !online;
    });
    updateLaneButtonsEnabled();
}

// Lane D-pad only usable in manual mode, and not during hazard/emergency lockdown
function updateLaneButtonsEnabled() {
    const enabled = nodeConnected && currentMode === 'manual';
    LANES.forEach(lane => {
        document.getElementById('laneBtn' + capitalize(lane)).disabled = !enabled;
    });
}
function capitalize(s) { return s.charAt(0) + s.slice(1).toLowerCase(); }

// ── Fetch wrapper ────────────────────────────────────────────
async function callControl(path, body) {
    if (!nodeBaseUrl) { logLine('No Node IP set.', true); return null; }
    try {
        const res  = await fetch(nodeBaseUrl + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body || {}),
        });
        const data = await res.json();
        if (!res.ok || data.success === false) {
            logLine(data.message || `Request to ${path} failed.`, true);
            return null;
        }
        return data;
    } catch (e) {
        logLine(`Connection failed: ${e.message}`, true);
        setConnBadge(false);
        return null;
    }
}

// ── MODE BUTTONS ─────────────────────────────────────────────
// Mirrors the ESP32 physical control box behaviour:
//   AUTO     → AI phasing takes over
//   MANUAL   → remote lane selection; all lights go red until a lane is picked
//   HAZARD   → all four lanes flash yellow (ESP32 blinks); lane d-pad locked
//   EMERGENCY→ toggle — ON: all-red lockdown, OFF: release back to MANUAL

async function setMode(mode) {
    // Hazard and Emergency put the ESP32 into MANUAL mode internally.
    // Flask's /control/mode accepts: 'auto' | 'manual' | 'hazard'.
    // Emergency lockdown is implemented as MANUAL + a MANUAL_LIGHT red command
    // to all four lanes (the ESP32 physical box does the same via MAN_EMERGENCY state).
    const flaskMode = (mode === 'emergency') ? 'manual' : mode;

    const data = await callControl('/control/mode', { mode: flaskMode });
    if (!data) return;

    if (mode === 'emergency') {
        // After entering manual mode, command all four lanes to RED — matching
        // the ESP32's MAN_EMERGENCY state (physical emergency button press).
        const promises = LANES.map(lane =>
            callControl('/control/light', { lane, state: 'red' })
        );
        await Promise.all(promises);
        emergencyActive = true;
        currentLane     = null;
        logLine('Emergency lockdown activated — all lanes RED.');
    } else {
        emergencyActive = false;
        if (mode === 'manual') currentLane = null;
        logLine(`Mode set to ${mode.toUpperCase()}.`);
    }

    currentMode = mode;
    refreshModeButtons();
    refreshLaneButtons();
    updateLaneButtonsEnabled();
}

// Emergency button is a toggle (matches the physical box behaviour: press once =
// lockdown, press again = releases back to MANUAL with all-red cleared).
async function toggleEmergency() {
    if (emergencyActive) {
        // Release emergency → go back to plain MANUAL (all red, awaiting lane pick)
        const data = await callControl('/control/mode', { mode: 'manual' });
        if (!data) return;
        emergencyActive = false;
        currentMode     = 'manual';
        currentLane     = null;
        logLine('Emergency lockdown released — returned to Manual mode.');
        refreshModeButtons();
        refreshLaneButtons();
        updateLaneButtonsEnabled();
    } else {
        await setMode('emergency');
    }
}

// ── LANE D-PAD ───────────────────────────────────────────────
async function setLane(lane) {
    if (currentMode !== 'manual') {
        logLine('Switch to Manual mode before setting a lane.', true);
        return;
    }
    const data = await callControl('/control/light', { lane, state: 'green' });
    if (!data) return;
    logLine(`Lane ${lane} set to GREEN (manual).`);
    currentLane = lane;
    refreshLaneButtons();
    // Immediately update the mini traffic lights so there's no wait for next poll
    renderMiniLights({ mode: 'manual', active_lane: lane, phase_state: 'GREEN' }, {}, {});
}

// ── REFRESH HELPERS ──────────────────────────────────────────
function refreshModeButtons() {
    document.getElementById('btnModeAuto').classList.toggle('active',      currentMode === 'auto');
    document.getElementById('btnModeManual').classList.toggle('active',    currentMode === 'manual');
    document.getElementById('btnModeHazard').classList.toggle('active',    currentMode === 'hazard');
    document.getElementById('btnModeEmergency').classList.toggle('active', currentMode === 'emergency');
    document.getElementById('statMode').textContent = currentMode ? currentMode.toUpperCase() : '—';
}

function refreshLaneButtons() {
    LANES.forEach(lane => {
        document.getElementById('laneBtn' + capitalize(lane))
            .classList.toggle('lane-active', currentLane === lane);
    });
}

// ── STATUS POLLING ───────────────────────────────────────────
async function pollStatus() {
    if (!nodeBaseUrl) return;
    try {
        const res = await fetch(nodeBaseUrl + '/status', { method: 'GET' });
        if (!res.ok) throw new Error('Bad response');
        const data = await res.json();

        if (!nodeConnected) logLine('Connected to STAP Node.');
        setConnBadge(true);

        // /status returns 'auto' or 'manual'. The Hub's client-side currentMode
        // is the source of truth for hazard/emergency sub-states, because the
        // ESP32 physical sub-states (MAN_HAZARD, MAN_EMERGENCY) are not exposed
        // through /status. We only sync to 'auto' from the server — if it became
        // auto from the physical box, we want to reflect that.
        if (data.mode === 'auto' && currentMode !== 'auto') {
            currentMode     = 'auto';
            emergencyActive = false;
            currentLane     = null;
            refreshModeButtons();
            refreshLaneButtons();
            updateLaneButtonsEnabled();
        } else if (data.mode === 'manual' && currentMode === null) {
            // First connect — server is already in manual, adopt it
            currentMode = 'manual';
            refreshModeButtons();
            updateLaneButtonsEnabled();
        }

        renderLiveStatus(data);
        renderMiniLights(data, data.vehicle_counts || {}, data.los || {});
        renderFeedChips(data);

    } catch (e) {
        if (nodeConnected) logLine('Lost connection to STAP Node.', true);
        setConnBadge(false);
        renderMiniLightsOffline();
    }
}

// ── MINI TRAFFIC LIGHTS ──────────────────────────────────────
// The mini lights in the live status panel show the actual signalling state:
//   HAZARD mode       → all four lanes show YELLOW
//   EMERGENCY lockdown→ all four lanes show RED
//   MANUAL, lane set  → that lane shows GREEN, others RED
//   MANUAL, no lane   → all RED
//   AUTO              → active lane uses phase_state colour; others RED
function renderMiniLights(data, counts, los) {
    LANES.forEach(lane => {
        let lamp = 'RED'; // default

        if (currentMode === 'hazard') {
            lamp = 'YELLOW';
        } else if (currentMode === 'emergency' && emergencyActive) {
            lamp = 'RED';
        } else if (currentMode === 'manual') {
            lamp = (currentLane === lane) ? 'GREEN' : 'RED';
        } else {
            // AUTO or unknown — use server phase state
            const activeLane = data.active_lane;
            const phaseState = data.phase_state;
            if (lane === activeLane) {
                if (phaseState === 'GREEN')   lamp = 'GREEN';
                else if (phaseState === 'YELLOW') lamp = 'YELLOW';
                else lamp = 'RED';
            } else {
                lamp = 'RED';
            }
        }

        // Update the three mini dots
        const rEl = document.getElementById('miniRed'    + lane);
        const yEl = document.getElementById('miniYellow' + lane);
        const gEl = document.getElementById('miniGreen'  + lane);
        rEl.className = 'tlc-mini-dot' + (lamp === 'RED'    ? ' on-red'    : '');
        yEl.className = 'tlc-mini-dot' + (lamp === 'YELLOW' ? ' on-yellow' : '');
        gEl.className = 'tlc-mini-dot' + (lamp === 'GREEN'  ? ' on-green'  : '');

        // Queue + LOS
        document.getElementById('miniQueue' + lane).textContent = counts[lane] ?? '—';
        document.getElementById('miniLos'   + lane).textContent = los[lane]    ?? '—';

        // Emergency label colour
        const statuses = data.lane_statuses || {};
        const label = document.getElementById('laneLabel' + lane);
        label.className = 'tlc-lane-name' + (statuses[lane] === 'EMERGENCY' ? ' emg' : '');
    });

    // Phase strip
    const strip = document.getElementById('phaseStrip');
    const hasEmg = Object.values(data.lane_statuses || {}).some(s => s === 'EMERGENCY');
    strip.className = 'tlc-phase-strip' + (hasEmg ? ' emg' : '');

    document.getElementById('statActiveLane').textContent = data.active_lane   ?? '—';
    document.getElementById('statPhase').textContent      = data.phase_state   ?? '—';
    document.getElementById('statRemaining').textContent  = data.remaining_secs ?? '—';
    document.getElementById('statRain').textContent       = data.rain ? 'Yes' : 'No';
}

function renderMiniLightsOffline() {
    LANES.forEach(lane => {
        ['miniRed','miniYellow','miniGreen'].forEach(prefix => {
            document.getElementById(prefix + lane).className = 'tlc-mini-dot';
        });
        document.getElementById('miniQueue' + lane).textContent = '—';
        document.getElementById('miniLos'   + lane).textContent = '—';
    });
    document.getElementById('statActiveLane').textContent = '—';
    document.getElementById('statPhase').textContent      = '—';
    document.getElementById('statRemaining').textContent  = '—';
    document.getElementById('statRain').textContent       = '—';
    document.getElementById('statMode').textContent       = '—';
}

// ── LIVE STATUS (kept for internal use, no longer rendered in a card) ────────
function renderLiveStatus(data) {
    // statMode is updated here as a complement to refreshModeButtons
    const modeLabel = currentMode ? currentMode.toUpperCase() : (data.mode ? data.mode.toUpperCase() : '—');
    document.getElementById('statMode').textContent = modeLabel;
}

// ── FEED CHIPS ───────────────────────────────────────────────
function renderFeedChips(data) {
    const statuses = data.lane_statuses || {};
    LANES.forEach(lane => {
        const chip = document.getElementById('feedChip' + lane);
        const s = statuses[lane];
        if (s === 'EMERGENCY') {
            chip.textContent = 'EMERGENCY';
            chip.style.background = 'var(--red)'; chip.style.color = '#fff';
            chip.style.display = 'inline-block';
        } else if (s === 'VEHICLE') {
            chip.textContent = 'VEHICLE';
            chip.style.background = 'var(--amber)'; chip.style.color = '#3a2d12';
            chip.style.display = 'inline-block';
        } else {
            chip.style.display = 'none';
        }
    });
}

// ── MJPEG FEED WIRING ────────────────────────────────────────
function wireFeeds() {
    LANES.forEach(lane => {
        const img     = document.getElementById('feedImg'     + lane);
        const offline = document.getElementById('feedOffline' + lane);
        const dot     = document.getElementById('feedDot'     + lane);

        if (!nodeBaseUrl) {
            img.style.display = 'none';
            offline.style.display = 'flex';
            dot.className = 'tlc-feed-dot offline';
            return;
        }

        img.src = `${nodeBaseUrl}/video_feed/${lane.toLowerCase()}`;
        img.onload  = () => { img.style.display = 'block'; offline.style.display = 'none'; dot.className = 'tlc-feed-dot live'; };
        img.onerror = () => { img.style.display = 'none'; offline.style.display = 'flex'; dot.className = 'tlc-feed-dot offline'; };
    });
}

// ── BOOT ─────────────────────────────────────────────────────
if (nodeBaseUrl) {
    wireFeeds();
    pollStatus();
}
setInterval(() => { if (nodeBaseUrl) pollStatus(); }, 2000);
</script>
@endpush