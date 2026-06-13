@extends('layouts.admin')

@section('title', 'Traffic Light Control')
@section('page-title', 'Traffic Light Control')

@push('styles')
<style>
/* ================================================================
   STAP HUB — Traffic Light Control Page
   ESP32 firmware v7 compatible.
   Direct IP control: sends HTTP to ESP32 at configured IP.
================================================================ */

/* ── Layout: single column, full width ───────────────────────── */
.tl-root {
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-width: 900px;
    margin: 0 auto;
}

/* ── Shared card ──────────────────────────────────────────────── */
.tl-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.tl-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid var(--border);
}
.tl-card-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--navy);
    letter-spacing: .1px;
}
.tl-card-body { padding: 24px; }

/* ── IP Connect card ──────────────────────────────────────────── */
.tl-ip-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.tl-ip-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text-muted);
    white-space: nowrap;
}
.tl-ip-input {
    flex: 1;
    min-width: 160px;
    padding: 11px 14px;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 15px;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-weight: 600;
    color: var(--navy);
    background: var(--bg-input);
    transition: border-color .18s;
    outline: none;
}
.tl-ip-input:focus { border-color: #29B357; }
.tl-ip-input.invalid { border-color: #E03040; }
.tl-ip-input::placeholder { color: var(--text-muted); font-weight: 500; }
.tl-ip-connect-btn {
    padding: 11px 22px;
    border-radius: 8px;
    border: 2px solid transparent;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all .18s ease;
    background: #29B357;
    color: #fff;
    white-space: nowrap;
}
.tl-ip-connect-btn:hover { background: #1d9047; transform: translateY(-1px); }
.tl-ip-connect-btn:disabled { opacity: .45; cursor: not-allowed; transform: none; }
.tl-ip-connect-btn.disconnecting {
    background: rgba(224,48,64,.12);
    border-color: rgba(224,48,64,.4);
    color: #9a1020;
}
.tl-ip-connect-btn.disconnecting:hover { background: rgba(224,48,64,.22); }

.tl-conn-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 0 0;
    color: var(--text-muted);
}
.tl-conn-dot {
    width: 9px; height: 9px;
    border-radius: 50%;
    background: var(--border);
    flex-shrink: 0;
    transition: background .3s;
}
.tl-conn-dot.online  { background: #29B357; animation: pulse-dot 1.8s ease-in-out infinite; }
.tl-conn-dot.offline { background: #E03040; }
.tl-conn-dot.pinging { background: #F4B942; animation: pulse-dot 1s ease-in-out infinite; }
@keyframes pulse-dot {
    0%,100% { box-shadow: 0 0 0 0 rgba(41,179,87,.6); }
    50%      { box-shadow: 0 0 0 5px rgba(41,179,87,0); }
}

/* ── Mode badge ───────────────────────────────────────────────── */
.tl-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 700;
    padding: 5px 13px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.tl-badge-auto      { background: rgba(41,179,87,.14); color: #1a7a3a; }
.tl-badge-manual    { background: rgba(244,185,66,.18); color: #7a5000; }
.tl-badge-emergency { background: rgba(224,48,64,.14); color: #9a1020; }

/* ── Mode buttons (larger) ────────────────────────────────────── */
.tl-mode-row {
    display: grid;
    grid-template-columns: 1fr 1fr;   /* Auto | Manual only — Emergency is inside Manual */
    gap: 14px;
    margin-bottom: 0;
}
.tl-mode-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 28px 16px;
    border-radius: 12px;
    border: 2px solid var(--border);
    background: var(--bg-input);
    cursor: pointer;
    font-family: inherit;
    transition: all .18s ease;
    min-height: 130px;
}
.tl-mode-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.tl-mode-btn:disabled { opacity: .38; cursor: not-allowed; transform: none; }

.tl-mode-icon { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; }
.tl-mode-icon svg { width: 32px; height: 32px; }
.tl-mode-name {
    font-size: 16px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--navy);
}
.tl-mode-desc {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
    text-align: center;
    line-height: 1.4;
}

/* Active states */
.tl-mode-btn.sel-auto    { border-color: #29B357; background: rgba(41,179,87,.08); }
.tl-mode-btn.sel-auto .tl-mode-name { color: #1a7a3a; }
.tl-mode-btn.sel-auto .tl-mode-icon svg { stroke: #1a7a3a; }

.tl-mode-btn.sel-manual  { border-color: #F4B942; background: rgba(244,185,66,.10); }
.tl-mode-btn.sel-manual .tl-mode-name { color: #7a5000; }
.tl-mode-btn.sel-manual .tl-mode-icon svg { stroke: #7a5000; }

/* ── Manual-only panel (lanes + emergency) ────────────────────── */
.tl-manual-panel {
    display: none;   /* hidden until manual mode */
    flex-direction: column;
    gap: 20px;
    margin-top: 20px;
}
.tl-manual-panel.visible { display: flex; }

/* ── Emergency section inside Manual panel ────────────────────── */
.tl-emg-btn-wrap {
    width: 100%;
}
.tl-emg-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 20px 24px;
    border-radius: 12px;
    border: 2px solid rgba(224,48,64,.35);
    background: rgba(224,48,64,.08);
    cursor: pointer;
    font-family: inherit;
    font-size: 17px;
    font-weight: 800;
    color: #9a1020;
    letter-spacing: .4px;
    transition: all .18s ease;
}
.tl-emg-btn:hover { background: rgba(224,48,64,.15); border-color: #E03040; transform: translateY(-1px); }
.tl-emg-btn.active {
    background: #E03040;
    border-color: #b01020;
    color: #fff;
    animation: emg-card-pulse 1.3s ease-in-out infinite;
}
.tl-emg-btn.active:hover { background: #b01020; }
.tl-emg-btn svg { width: 22px; height: 22px; flex-shrink: 0; }
@keyframes emg-card-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(224,48,64,.4); }
    50%      { box-shadow: 0 0 0 10px rgba(224,48,64,0); }
}

/* ── Emergency active banner ──────────────────────────────────── */
.tl-emg-banner {
    display: none;
    align-items: center;
    gap: 12px;
    padding: 16px 18px;
    background: rgba(224,48,64,.09);
    border: 2px solid rgba(224,48,64,.3);
    border-radius: 10px;
}
.tl-emg-banner.on { display: flex; }
.tl-emg-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #E03040;
    flex-shrink: 0;
    animation: emg-dot 0.8s ease-in-out infinite;
}
@keyframes emg-dot {
    0%,100% { transform: scale(1); opacity: 1; }
    50%      { transform: scale(1.5); opacity: .5; }
}
.tl-emg-banner-title { font-size: 15px; font-weight: 800; color: #9a1020; line-height: 1; }
.tl-emg-banner-sub   { font-size: 12px; font-weight: 500; color: #9a1020; opacity: .8; margin-top: 4px; }

/* ── Section label inside manual panel ───────────────────────── */
.tl-section-sub {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: var(--text-muted);
    margin-bottom: 12px;
}

/* ── Lane cards ───────────────────────────────────────────────── */
.tl-lanes { display: flex; flex-direction: column; gap: 13px; }

.tl-lane {
    border: 2px solid var(--border);
    border-radius: 12px;
    padding: 18px 20px;
    background: var(--bg-input);
    transition: border-color .2s, background .2s;
}
.tl-lane.is-green  { border-color: #29B357; background: rgba(41,179,87,.06); }
.tl-lane.is-yellow { border-color: #F4B942; background: rgba(244,185,66,.07); }
.tl-lane.is-red-emg { border-color: rgba(224,48,64,.45); background: rgba(224,48,64,.05); }

.tl-lane-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}
.tl-lane-name {
    font-size: 17px;
    font-weight: 800;
    color: var(--navy);
    line-height: 1;
}
.tl-lane-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 4px;
    font-weight: 500;
}

/* Signal light display */
.tl-signal {
    display: flex;
    align-items: center;
    gap: 7px;
    background: #111820;
    border-radius: 30px;
    padding: 9px 18px;
    flex-shrink: 0;
}
.tl-sig-dot {
    width: 17px; height: 17px;
    border-radius: 50%;
    opacity: .16;
    transition: opacity .3s, box-shadow .3s;
}
.tl-sig-dot.r { background: #E03040; }
.tl-sig-dot.y { background: #F4B942; }
.tl-sig-dot.g { background: #29B357; }
.tl-sig-dot.lit-r { opacity: 1; box-shadow: 0 0 12px 3px rgba(224,48,64,.65); }
.tl-sig-dot.lit-y { opacity: 1; box-shadow: 0 0 12px 3px rgba(244,185,66,.65); animation: blink-y .7s ease-in-out infinite; }
.tl-sig-dot.lit-g { opacity: 1; box-shadow: 0 0 12px 3px rgba(41,179,87,.65); }
@keyframes blink-y {
    0%,100% { opacity: 1; }
    50%      { opacity: .22; }
}

/* Lane action button */
.tl-lane-btn {
    width: 100%;
    padding: 15px 18px;
    border-radius: 9px;
    border: 2px solid transparent;
    font-family: inherit;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: .3px;
    cursor: pointer;
    transition: all .18s ease;
    background: rgba(41,179,87,.11);
    border-color: rgba(41,179,87,.28);
    color: #1a7a3a;
}
.tl-lane-btn:hover:not(:disabled):not(.btn-active):not(.btn-transitioning) {
    background: rgba(41,179,87,.20);
    border-color: #29B357;
    transform: translateY(-1px);
}
.tl-lane-btn.btn-active {
    background: #29B357;
    border-color: #1d9047;
    color: #fff;
    box-shadow: 0 4px 18px rgba(41,179,87,.38);
    cursor: default;
}
.tl-lane-btn.btn-transitioning {
    background: rgba(244,185,66,.14);
    border-color: #F4B942;
    color: #7a5000;
    animation: btn-blink-y .7s ease-in-out infinite;
    pointer-events: none;
    cursor: not-allowed;
}
@keyframes btn-blink-y {
    0%,100% { background: rgba(244,185,66,.22); border-color: #F4B942; }
    50%      { background: rgba(244,185,66,.05); border-color: rgba(244,185,66,.35); }
}
.tl-lane-btn:disabled:not(.btn-active):not(.btn-transitioning) {
    opacity: .38;
    cursor: not-allowed;
    transform: none;
    background: rgba(27,39,68,.07);
    border-color: var(--border);
    color: var(--text-muted);
}
.tl-lane-btn-sub {
    font-size: 12px;
    text-align: center;
    color: var(--text-muted);
    min-height: 18px;
    margin-top: 8px;
    font-weight: 600;
    letter-spacing: .2px;
}

/* ── Activity log ─────────────────────────────────────────────── */
.tl-log { max-height: 220px; overflow-y: auto; }
.tl-log::-webkit-scrollbar { width: 4px; }
.tl-log::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.tl-log-entry {
    display: flex;
    align-items: baseline;
    gap: 12px;
    padding: 9px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}
.tl-log-entry:last-child { border-bottom: none; }
.tl-log-time { font-size: 11px; color: var(--text-muted); flex-shrink: 0; font-family: monospace; }
.tl-log-msg  { flex: 1; color: var(--text-secondary); font-weight: 500; }
.tl-log-msg.err  { color: #9a1020; }
.tl-log-msg.warn { color: #7a5000; }
.tl-log-msg.ok   { color: #1a7a3a; }
.tl-log-empty { font-size: 13px; color: var(--text-muted); padding: 12px 0; text-align: center; }
</style>
@endpush

@section('content')

<div class="tl-root">

    {{-- ── 1. ESP32 IP Connection ──────────────────────────────────── --}}
    <div class="tl-card">
        <div class="tl-card-head">
            <span class="tl-card-title">ESP32 Connection</span>
            <span class="tl-badge tl-badge-auto" id="connBadge" style="display:none;">Connected</span>
        </div>
        <div class="tl-card-body">
            <div class="tl-ip-row">
                <span class="tl-ip-label">ESP32 IP Address</span>
                <input
                    type="text"
                    id="esp32IpInput"
                    class="tl-ip-input"
                    placeholder="192.168.1.100"
                    maxlength="15"
                    spellcheck="false"
                    autocomplete="off"
                    onkeydown="if(event.key==='Enter') toggleConnect()"
                />
                <button class="tl-ip-connect-btn" id="connectBtn" onclick="toggleConnect()">
                    Connect
                </button>
            </div>
            <div class="tl-conn-status">
                <span class="tl-conn-dot" id="connDot"></span>
                <span id="connStatusText">Not connected — enter the ESP32 IP address to start.</span>
            </div>
        </div>
    </div>

    {{-- ── 2. Operating Mode ────────────────────────────────────────── --}}
    <div class="tl-card">
        <div class="tl-card-head">
            <span class="tl-card-title">Operating Mode</span>
            <span class="tl-badge tl-badge-auto" id="modeBadge">Auto</span>
        </div>
        <div class="tl-card-body">

            <div class="tl-mode-row">

                <button class="tl-mode-btn sel-auto" id="btn-mode-auto" onclick="selectMode('auto')" disabled>
                    <span class="tl-mode-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                    </span>
                    <span class="tl-mode-name">Auto</span>
                    <span class="tl-mode-desc">AI controls the cycle automatically</span>
                </button>

                <button class="tl-mode-btn" id="btn-mode-manual" onclick="selectMode('manual')" disabled>
                    <span class="tl-mode-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0"/>
                            <path d="M14 10V4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v2"/>
                            <path d="M10 10.5V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"/>
                            <path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>
                        </svg>
                    </span>
                    <span class="tl-mode-name">Manual</span>
                    <span class="tl-mode-desc">Select which lane gets the green light</span>
                </button>

            </div>

            {{-- ── Manual-only panel: lanes + emergency ─────────────── --}}
            <div class="tl-manual-panel" id="manualPanel">

                {{-- Emergency button --}}
                <div>
                    <div class="tl-section-sub">Emergency Control</div>
                    <div class="tl-emg-btn-wrap">
                        <button class="tl-emg-btn" id="btn-emergency" onclick="toggleEmergency()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            All Lanes Red — Emergency Stop
                        </button>
                    </div>
                    <div class="tl-emg-banner" id="emgBanner" style="margin-top:12px;">
                        <span class="tl-emg-dot"></span>
                        <div>
                            <div class="tl-emg-banner-title">Emergency Mode Active</div>
                            <div class="tl-emg-banner-sub">All lanes are RED. Click the button again to resume Manual control.</div>
                        </div>
                    </div>
                </div>

                {{-- Lane buttons --}}
                <div>
                    <div class="tl-section-sub">Lane Control</div>
                    <div class="tl-lanes" id="laneList">
                        <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px 0;">Loading lanes…</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── 3. Activity Log ──────────────────────────────────────────── --}}
    <div class="tl-card">
        <div class="tl-card-head">
            <span class="tl-card-title">Activity Log</span>
            <button onclick="clearLog()" style="font-size:11px;color:var(--text-muted);background:none;border:none;cursor:pointer;font-weight:700;">Clear</button>
        </div>
        <div class="tl-card-body" style="padding:12px 24px;">
            <div class="tl-log" id="activityLog">
                <div class="tl-log-empty">No actions yet this session.</div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
/* ================================================================
   STAP Hub — Traffic Light Control
   Direct ESP32 IP control + Laravel API fallback.

   ESP32 HTTP endpoints (firmware v7):
     POST http://<ip>/control/mode   { mode: 'auto'|'manual'|'hazard' }
     POST http://<ip>/control/light  { lane: 'NORTH'|'SOUTH'|'EAST'|'WEST', state: 'green'|'yellow'|'red' }
     GET  http://<ip>/status         → { mode, lanes: [{lane, state}] }

   Yellow transition: 3 s (YELLOW_TIME_MS), mirrors ESP32 firmware.
================================================================ */

const YELLOW_TIME_MS = 3000;
const ESP32_TIMEOUT_MS = 5000;

// ── State ──────────────────────────────────────────────────────
let esp32Ip             = localStorage.getItem('esp32Ip') ?? '';
let isConnected         = false;
let currentMode         = 'auto';     // 'auto' | 'manual' | 'emergency'
let lights              = [];
let activeLaneId        = null;
let transitioningToId   = null;
let prevActiveLaneId    = null;
let transitionTimer     = null;
let countdownInterval   = null;
let countdown           = 0;
let pingInterval        = null;
const sessionLog        = [];

// ── Boot ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (esp32Ip) {
        document.getElementById('esp32IpInput').value = esp32Ip;
    }
    loadLights(); // load lane names from Laravel DB regardless
    setInterval(loadLights, 30000);
});

// ── Utility: auth headers ──────────────────────────────────────
function authHeaders() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return {
        'X-CSRF-TOKEN':    meta ? meta.getAttribute('content') : '',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept':          'application/json',
    };
}

// ── IP connection ──────────────────────────────────────────────
function isValidIp(val) {
    return /^(\d{1,3}\.){3}\d{1,3}(:\d+)?$/.test(val.trim());
}

async function toggleConnect() {
    if (isConnected) {
        disconnect();
        return;
    }

    const raw = document.getElementById('esp32IpInput').value.trim();
    const input = document.getElementById('esp32IpInput');
    if (!raw) { input.focus(); return; }

    if (!isValidIp(raw)) {
        input.classList.add('invalid');
        setTimeout(() => input.classList.remove('invalid'), 1500);
        log('Invalid IP address format', 'err');
        return;
    }

    esp32Ip = raw;
    localStorage.setItem('esp32Ip', esp32Ip);
    setConnStatus('pinging', 'Connecting to ' + esp32Ip + '…');

    try {
        const ok = await pingEsp32();
        if (ok) {
            onConnected();
        } else {
            setConnStatus('offline', 'Could not reach ESP32 at ' + esp32Ip);
            log('Connection failed — check IP and network', 'err');
        }
    } catch(e) {
        setConnStatus('offline', 'Connection error: ' + e.message);
        log('Connection error: ' + e.message, 'err');
    }
}

async function pingEsp32() {
    try {
        // Try GET /status — if reachable we get JSON back
        const res = await fetchEsp32('/status', 'GET');
        if (res.ok) {
            // Sync mode from ESP32 status
            const data = await res.json().catch(() => ({}));
            if (data.mode) {
                currentMode = data.mode === 'hazard' ? 'emergency' : data.mode;
            }
            return true;
        }
        return false;
    } catch(e) {
        return false;
    }
}

function onConnected() {
    isConnected = true;
    setConnStatus('online', 'Connected to ESP32 at ' + esp32Ip);
    log('Connected to ESP32 at ' + esp32Ip, 'ok');

    document.getElementById('connectBtn').textContent = 'Disconnect';
    document.getElementById('connectBtn').classList.add('disconnecting');
    document.getElementById('esp32IpInput').disabled = true;

    const badge = document.getElementById('connBadge');
    badge.textContent = 'Connected';
    badge.className   = 'tl-badge tl-badge-auto';
    badge.style.display = 'inline-flex';

    // Enable mode buttons
    document.getElementById('btn-mode-auto').disabled   = false;
    document.getElementById('btn-mode-manual').disabled = false;

    refreshModeUI();
    renderLanes();

    // Periodic keep-alive ping
    pingInterval = setInterval(async () => {
        const alive = await pingEsp32();
        if (!alive && isConnected) {
            disconnect();
            log('ESP32 connection lost', 'err');
        }
    }, 15000);
}

function disconnect() {
    isConnected = false;
    clearInterval(pingInterval);
    pingInterval = null;

    // Cancel any transition
    if (transitionTimer) {
        clearTimeout(transitionTimer);
        clearInterval(countdownInterval);
        transitionTimer = transitioningToId = prevActiveLaneId = null;
        countdownInterval = null; countdown = 0;
    }

    setConnStatus('offline', 'Disconnected.');
    log('Disconnected from ESP32', 'warn');

    document.getElementById('connectBtn').textContent = 'Connect';
    document.getElementById('connectBtn').classList.remove('disconnecting');
    document.getElementById('esp32IpInput').disabled = false;

    const badge = document.getElementById('connBadge');
    badge.style.display = 'none';

    document.getElementById('btn-mode-auto').disabled   = true;
    document.getElementById('btn-mode-manual').disabled = true;

    // Snap back to auto, hide manual panel
    currentMode = 'auto';
    refreshModeUI();
    renderLanes();
}

function setConnStatus(state, text) {
    const dot  = document.getElementById('connDot');
    const span = document.getElementById('connStatusText');
    dot.className  = 'tl-conn-dot ' + state;
    span.textContent = text;
}

// ── Low-level ESP32 fetch ──────────────────────────────────────
async function fetchEsp32(path, method = 'POST', body = null) {
    const url = 'http://' + esp32Ip + path;
    const opts = {
        method,
        headers: { 'Content-Type': 'application/json' },
        signal: AbortSignal.timeout(ESP32_TIMEOUT_MS),
    };
    if (body) opts.body = JSON.stringify(body);
    return fetch(url, opts);
}

// ── Send mode to ESP32 ─────────────────────────────────────────
async function esp32SetMode(mode) {
    // mode: 'auto' | 'manual' | 'hazard'
    if (!isConnected) return false;
    try {
        const res = await fetchEsp32('/control/mode', 'POST', { mode });
        if (!res.ok) {
            log('ESP32 mode error: HTTP ' + res.status, 'err');
            return false;
        }
        return true;
    } catch(e) {
        log('ESP32 mode send failed: ' + e.message, 'err');
        return false;
    }
}

// ── Send light state to ESP32 ──────────────────────────────────
async function esp32SetLight(lane, state) {
    // lane: 'NORTH'|'SOUTH'|'EAST'|'WEST', state: 'green'|'yellow'|'red'
    if (!isConnected) {
        log('Not connected — command not sent', 'err');
        return false;
    }
    try {
        const res = await fetchEsp32('/control/light', 'POST', { lane, state });
        if (!res.ok) {
            log('ESP32 light error: HTTP ' + res.status, 'err');
            return false;
        }
        return true;
    } catch(e) {
        log('ESP32 light send failed: ' + e.message, 'err');
        return false;
    }
}

// ── Load lights from Laravel DB (lane labels + node info) ──────
async function loadLights() {
    try {
        const res  = await fetch('/admin/api/traffic-lights', { headers: authHeaders() });
        const data = await res.json();
        lights     = data.data ?? data;
        renderLanes();
    } catch(e) { console.error('loadLights:', e); }
}

const lightId   = l => l.light_id ?? l.id;
const laneLabel = light => {
    if (!light) return null;
    const loc = (light.location_label ?? '').toUpperCase();
    if (loc.includes('NORTH')) return 'NORTH';
    if (loc.includes('SOUTH')) return 'SOUTH';
    if (loc.includes('EAST'))  return 'EAST';
    if (loc.includes('WEST'))  return 'WEST';
    const dir = (light.direction ?? '').toUpperCase();
    return ['NORTH','SOUTH','EAST','WEST'].includes(dir) ? dir : null;
};

// ── Mode selection ─────────────────────────────────────────────
async function selectMode(mode) {
    if (!isConnected) return;

    // Cancel any pending transition
    if (transitionTimer) {
        clearTimeout(transitionTimer);
        clearInterval(countdownInterval);
        transitionTimer = transitioningToId = prevActiveLaneId = null;
        countdownInterval = null; countdown = 0;
    }

    const prev = currentMode;
    currentMode = mode;

    if (mode === 'auto') {
        activeLaneId = null;
        // If coming out of emergency, make sure ESP32 knows
        document.getElementById('emgBanner').classList.remove('on');
        document.getElementById('btn-emergency').classList.remove('active');
    }

    refreshModeUI();
    renderLanes();

    const esp32Mode = mode === 'emergency' ? 'hazard' : mode;
    const ok = await esp32SetMode(esp32Mode);

    if (ok) {
        const labels = {
            auto:      'Auto mode — AI in control',
            manual:    'Manual mode activated',
            emergency: 'Emergency mode — all lanes RED',
        };
        log(labels[mode] ?? mode, mode === 'emergency' ? 'err' : 'ok');
    }

    // Also sync to Laravel backend (optional fallback)
    fetch('/admin/api/node/mode', {
        method:  'POST',
        headers: { ...authHeaders(), 'Content-Type': 'application/json' },
        body:    JSON.stringify({ mode: esp32Mode }),
    }).catch(() => {});
}

// ── Emergency toggle (within manual mode) ─────────────────────
async function toggleEmergency() {
    if (!isConnected || currentMode === 'auto') return;

    if (currentMode === 'emergency') {
        // Resume manual
        currentMode = 'manual';
        document.getElementById('emgBanner').classList.remove('on');
        document.getElementById('btn-emergency').classList.remove('active');
        activeLaneId = null;
        refreshModeUI();
        renderLanes();
        const ok = await esp32SetMode('manual');
        if (ok) log('Emergency cleared — Manual resumed', 'warn');
    } else {
        // Activate emergency
        if (transitionTimer) {
            clearTimeout(transitionTimer);
            clearInterval(countdownInterval);
            transitionTimer = transitioningToId = prevActiveLaneId = null;
            countdownInterval = null; countdown = 0;
        }
        currentMode = 'emergency';
        activeLaneId = null;
        document.getElementById('emgBanner').classList.add('on');
        document.getElementById('btn-emergency').classList.add('active');
        refreshModeUI();
        renderLanes();
        const ok = await esp32SetMode('hazard');
        if (ok) log('EMERGENCY STOP — all lanes RED', 'err');
    }
}

// ── Refresh mode UI (buttons + badge) ─────────────────────────
function refreshModeUI() {
    const badge      = document.getElementById('modeBadge');
    const manPanel   = document.getElementById('manualPanel');
    const btnAuto    = document.getElementById('btn-mode-auto');
    const btnManual  = document.getElementById('btn-mode-manual');

    // Clear active classes
    btnAuto.className   = 'tl-mode-btn' + (currentMode === 'auto'   ? ' sel-auto'   : '');
    btnManual.className = 'tl-mode-btn' + (currentMode === 'manual' || currentMode === 'emergency' ? ' sel-manual' : '');

    // Badge
    if (currentMode === 'auto') {
        badge.textContent = 'Auto';
        badge.className   = 'tl-badge tl-badge-auto';
    } else if (currentMode === 'manual') {
        badge.textContent = 'Manual';
        badge.className   = 'tl-badge tl-badge-manual';
    } else {
        badge.textContent = 'Emergency';
        badge.className   = 'tl-badge tl-badge-emergency';
    }

    // Manual panel visible only in manual / emergency
    const isManualOrEmg = currentMode === 'manual' || currentMode === 'emergency';
    manPanel.classList.toggle('visible', isManualOrEmg);
}

// ── Render lane buttons (only used in manual/emergency) ────────
function renderLanes() {
    if (!lights.length) {
        document.getElementById('laneList').innerHTML =
            '<div style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px 0;">No traffic lights registered.</div>';
        return;
    }

    const isEmg  = currentMode === 'emergency';

    document.getElementById('laneList').innerHTML = lights.map(l => {
        const lid       = lightId(l);
        const isActive  = activeLaneId === lid;
        const isTo      = transitioningToId === lid;
        const isFrom    = prevActiveLaneId === lid;
        const isTransit = (isTo || isFrom) && !!transitioningToId;

        const sigState = isEmg ? 'r' : isTransit ? 'y' : isActive ? 'g' : 'r';

        const rowCls = isActive ? 'is-green' : isTransit ? 'is-yellow' : isEmg ? 'is-red-emg' : '';
        const btnCls = isActive ? 'btn-active' : isTransit ? 'btn-transitioning' : '';
        const disabled = isEmg || (!!transitioningToId && !isTransit) || isActive ? 'disabled' : '';

        const btnText = isTo && isTransit ? `Switching… (${countdown}s)`
            : isFrom && isTransit ? 'Turning Yellow…'
            : isActive ? '✓ Active — Green'
            : 'Set to Green';

        return `
        <div class="tl-lane ${rowCls}" id="lane-row-${lid}">
            <div class="tl-lane-top">
                <div>
                    <div class="tl-lane-name">${l.location_label ?? 'Light ' + lid}</div>
                    <div class="tl-lane-sub">Node: ${l.node?.node_name ?? l.node?.name ?? '—'}</div>
                </div>
                <div class="tl-signal">
                    <div class="tl-sig-dot r ${sigState === 'r' ? 'lit-r' : ''}"></div>
                    <div class="tl-sig-dot y ${sigState === 'y' ? 'lit-y' : ''}"></div>
                    <div class="tl-sig-dot g ${sigState === 'g' ? 'lit-g' : ''}"></div>
                </div>
            </div>
            <button class="tl-lane-btn ${btnCls}" id="lane-btn-${lid}"
                onclick="requestGreen(${lid})" ${disabled}>
                ${btnText}
            </button>
            <div class="tl-lane-btn-sub" id="lane-sub-${lid}"></div>
        </div>`;
    }).join('');
}

// ── Request green lane (3-second yellow transition) ────────────
async function requestGreen(lid) {
    if (currentMode !== 'manual') return;
    if (transitioningToId) return;
    if (activeLaneId === lid) return;
    if (!isConnected) { log('Not connected to ESP32', 'err'); return; }

    const prev            = activeLaneId;
    prevActiveLaneId      = prev;
    transitioningToId     = lid;
    countdown             = Math.ceil(YELLOW_TIME_MS / 1000);

    renderLanes();

    // Set previous lane to yellow on ESP32
    if (prev !== null) {
        const prevLight = lights.find(l => lightId(l) === prev);
        const prevDir   = laneLabel(prevLight);
        if (prevDir) {
            const ok = await esp32SetLight(prevDir, 'yellow');
            if (ok) log(`Yellow: ${prevLight?.location_label ?? prevDir}`);
        }
    }

    // Countdown
    countdownInterval = setInterval(() => {
        countdown = Math.max(0, countdown - 1);
        const el = document.getElementById('lane-btn-' + lid);
        if (el) el.textContent = `Switching… (${countdown}s)`;
    }, 1000);

    // After yellow period → send green
    transitionTimer = setTimeout(async () => {
        clearInterval(countdownInterval);
        countdownInterval = null;
        transitionTimer   = null;

        const newLight = lights.find(l => lightId(l) === lid);
        const newDir   = laneLabel(newLight);

        if (newDir) {
            const ok = await esp32SetLight(newDir, 'green');
            if (ok) log(`Green: ${newLight?.location_label ?? newDir}`, 'ok');
        }

        activeLaneId      = lid;
        transitioningToId = null;
        prevActiveLaneId  = null;
        countdown         = 0;
        renderLanes();
    }, YELLOW_TIME_MS);
}

// ── Activity log ───────────────────────────────────────────────
function log(msg, type) {
    const time = new Date().toLocaleTimeString();
    sessionLog.unshift({ msg, type: type ?? 'info', time });
    if (sessionLog.length > 30) sessionLog.pop();
    renderLog();
}
function clearLog() { sessionLog.length = 0; renderLog(); }
function renderLog() {
    const el = document.getElementById('activityLog');
    if (!sessionLog.length) {
        el.innerHTML = '<div class="tl-log-empty">No actions yet this session.</div>';
        return;
    }
    el.innerHTML = sessionLog.map(e =>
        `<div class="tl-log-entry">
            <span class="tl-log-time">${e.time}</span>
            <span class="tl-log-msg ${e.type}">${e.msg}</span>
        </div>`
    ).join('');
}
</script>
@endpush