@extends('layouts.admin')

@section('title', 'Traffic Light Control')
@section('page-title', 'Traffic Light Control')

@push('styles')
<style>
/* ================================================================
   STAP HUB — Traffic Light Control Page
   Mirrors ESP32 firmware v7 transition logic exactly:
     MANUAL green switch → YELLOW on current lane (3s) → GREEN new lane
     HAZARD  → MODE:HAZARD + HAZARD:x4 → all red, no active lane
     AUTO    → MODE:AUTO, Python takes over
================================================================ */

/* ── Layout ───────────────────────────────────────────────────── */
.tl-root {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 1140px) { .tl-root { grid-template-columns: 1fr; } }

/* ── Feed ─────────────────────────────────────────────────────── */
.tl-feed {
    position: relative;
    background: #0d1220;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 16/9;
    min-height: 340px;
    box-shadow: 0 8px 32px rgba(0,0,0,.32);
}
.tl-feed iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: none;
}
.tl-feed-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: rgba(255,255,255,.2);
}
.tl-feed-placeholder svg { width: 52px; height: 52px; }
.tl-feed-placeholder span { font-size: 13px; font-weight: 600; letter-spacing: .5px; }
.tl-feed-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    align-items: center;
    gap: 7px;
    background: rgba(8,14,28,.72);
    backdrop-filter: blur(6px);
    border-radius: 8px;
    padding: 6px 13px;
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    letter-spacing: .3px;
    pointer-events: none;
}
.tl-feed-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #29B357;
    animation: pulse-dot 1.8s ease-in-out infinite;
}
@keyframes pulse-dot {
    0%,100% { box-shadow: 0 0 0 0 rgba(41,179,87,.6); }
    50%      { box-shadow: 0 0 0 5px rgba(41,179,87,0); }
}

/* ── Right column ─────────────────────────────────────────────── */
.tl-right { display: flex; flex-direction: column; gap: 16px; }

/* ── Shared card ──────────────────────────────────────────────── */
.tl-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.tl-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}
.tl-card-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--navy);
    letter-spacing: .1px;
}
.tl-card-body { padding: 20px; }

/* ── Mode badge ───────────────────────────────────────────────── */
.tl-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 11px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.tl-badge-auto      { background: rgba(41,179,87,.14); color: #1a7a3a; }
.tl-badge-manual    { background: rgba(244,185,66,.18); color: #7a5000; }
.tl-badge-emergency { background: rgba(224,48,64,.14); color: #9a1020; }

/* ── Mode buttons ─────────────────────────────────────────────── */
.tl-mode-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
    margin-bottom: 18px;
}
.tl-mode-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 18px 10px;
    border-radius: 10px;
    border: 2px solid var(--border);
    background: var(--bg-input);
    cursor: pointer;
    font-family: inherit;
    transition: all .18s ease;
    min-height: 96px;
}
.tl-mode-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.tl-mode-btn:disabled { opacity: .45; cursor: not-allowed; transform: none; }

.tl-mode-btn .tl-mode-icon {
    /* SVG container */
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.tl-mode-btn .tl-mode-icon svg { width: 26px; height: 26px; }
.tl-mode-btn .tl-mode-name {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--navy);
}
.tl-mode-btn .tl-mode-desc {
    font-size: 11px;
    font-weight: 500;
    color: var(--text-muted);
    text-align: center;
    line-height: 1.35;
}

/* Active states */
.tl-mode-btn.sel-auto {
    border-color: #29B357;
    background: rgba(41,179,87,.08);
}
.tl-mode-btn.sel-auto .tl-mode-name { color: #1a7a3a; }
.tl-mode-btn.sel-auto .tl-mode-icon svg { stroke: #1a7a3a; }

.tl-mode-btn.sel-manual {
    border-color: #F4B942;
    background: rgba(244,185,66,.10);
}
.tl-mode-btn.sel-manual .tl-mode-name { color: #7a5000; }
.tl-mode-btn.sel-manual .tl-mode-icon svg { stroke: #7a5000; }

.tl-mode-btn.sel-emergency {
    border-color: #E03040;
    background: rgba(224,48,64,.10);
    animation: emg-card-pulse 1.3s ease-in-out infinite;
}
.tl-mode-btn.sel-emergency .tl-mode-name { color: #9a1020; }
.tl-mode-btn.sel-emergency .tl-mode-icon svg { stroke: #9a1020; }
@keyframes emg-card-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(224,48,64,.3); }
    50%      { box-shadow: 0 0 0 7px rgba(224,48,64,0); }
}

/* ── Emergency banner ─────────────────────────────────────────── */
.tl-emg-banner {
    display: none;
    align-items: center;
    gap: 11px;
    padding: 14px 16px;
    background: rgba(224,48,64,.09);
    border: 2px solid rgba(224,48,64,.3);
    border-radius: 10px;
    margin-bottom: 18px;
}
.tl-emg-banner.on { display: flex; }
.tl-emg-dot {
    width: 11px; height: 11px;
    border-radius: 50%;
    background: #E03040;
    flex-shrink: 0;
    animation: emg-dot 0.8s ease-in-out infinite;
}
@keyframes emg-dot {
    0%,100% { transform: scale(1); opacity: 1; }
    50%      { transform: scale(1.5); opacity: .5; }
}
.tl-emg-banner-text { flex: 1; min-width: 0; }
.tl-emg-banner-title { font-size: 14px; font-weight: 800; color: #9a1020; line-height: 1; }
.tl-emg-banner-sub   { font-size: 12px; font-weight: 500; color: #9a1020; opacity: .8; margin-top: 4px; }

/* ── Lane cards ───────────────────────────────────────────────── */
.tl-divider {
    border: none; border-top: 1px solid var(--border); margin: 4px 0 18px;
}
.tl-lane-hint {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 14px;
    font-weight: 500;
}
.tl-lanes { display: flex; flex-direction: column; gap: 11px; }

.tl-lane {
    border: 2px solid var(--border);
    border-radius: 10px;
    padding: 14px 16px;
    background: var(--bg-input);
    transition: border-color .2s, background .2s;
}
.tl-lane.is-green {
    border-color: #29B357;
    background: rgba(41,179,87,.06);
}
.tl-lane.is-yellow {
    border-color: #F4B942;
    background: rgba(244,185,66,.07);
}
.tl-lane.is-red-emg {
    border-color: rgba(224,48,64,.45);
    background: rgba(224,48,64,.05);
}

/* Lane top row */
.tl-lane-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
}

.tl-lane-name {
    font-size: 15px;
    font-weight: 800;
    color: var(--navy);
    line-height: 1;
}
.tl-lane-sub {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 3px;
    font-weight: 500;
}

/* Signal light display */
.tl-signal {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #111820;
    border-radius: 30px;
    padding: 7px 16px;
    flex-shrink: 0;
}
.tl-sig-dot {
    width: 15px; height: 15px;
    border-radius: 50%;
    opacity: .16;
    transition: opacity .3s, box-shadow .3s;
}
.tl-sig-dot.r { background: #E03040; }
.tl-sig-dot.y { background: #F4B942; }
.tl-sig-dot.g { background: #29B357; }

.tl-sig-dot.lit-r { opacity: 1; box-shadow: 0 0 11px 2px rgba(224,48,64,.65); }
.tl-sig-dot.lit-y { opacity: 1; box-shadow: 0 0 11px 2px rgba(244,185,66,.65); animation: blink-y .7s ease-in-out infinite; }
.tl-sig-dot.lit-g { opacity: 1; box-shadow: 0 0 11px 2px rgba(41,179,87,.65); }
@keyframes blink-y {
    0%,100% { opacity: 1; }
    50%      { opacity: .22; }
}

/* Lane action button */
.tl-lane-btn {
    width: 100%;
    padding: 13px 16px;
    border-radius: 8px;
    border: 2px solid transparent;
    font-family: inherit;
    font-size: 14px;
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
    box-shadow: 0 4px 16px rgba(41,179,87,.38);
    cursor: default;
}
.tl-lane-btn.btn-transitioning {
    /* Yellow blink — mirrors ESP32 MAN_TRANSITION */
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
    font-size: 11px;
    text-align: center;
    color: var(--text-muted);
    min-height: 17px;
    margin-top: 6px;
    font-weight: 600;
    letter-spacing: .2px;
}

/* ── Activity log (below lane cards on right column, full-width) ─ */
.tl-log { max-height: 200px; overflow-y: auto; }
.tl-log::-webkit-scrollbar { width: 4px; }
.tl-log::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.tl-log-entry {
    display: flex;
    align-items: baseline;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}
.tl-log-entry:last-child { border-bottom: none; }
.tl-log-time { font-size: 11px; color: var(--text-muted); flex-shrink: 0; }
.tl-log-msg  { flex: 1; color: var(--text-secondary); font-weight: 500; }
.tl-log-msg.err  { color: #9a1020; }
.tl-log-msg.warn { color: #7a5000; }
.tl-log-empty { font-size: 13px; color: var(--text-muted); padding: 10px 0; text-align: center; }
</style>
@endpush

@section('content')

<div class="tl-root">

    {{-- ── LEFT: Live feeds ────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Primary feed (first active camera) --}}
        <div>
            <div class="tl-section-label" style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:10px;">
                Intersection Live View
            </div>
            <div class="tl-feed" id="primaryFeed">
                <div class="tl-feed-placeholder" id="feedPlaceholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <rect x="2" y="7" width="15" height="10" rx="2"/>
                        <path d="M17 9l5-3v12l-5-3"/>
                    </svg>
                    <span>Connecting to camera feed…</span>
                </div>
                <div class="tl-feed-badge">
                    <span class="tl-feed-dot"></span>
                    Mayor Gil Fernando Ave — LIVE
                </div>
            </div>
        </div>

        {{-- Secondary 2×2 camera grid --}}
        <div>
            <div class="tl-section-label" style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:10px;">
                All Camera Views
            </div>
            <div id="cameraGrid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                @for($i = 0; $i < 4; $i++)
                <div style="background:#0d1220;border-radius:8px;aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:11px;color:rgba(255,255,255,.18);font-weight:600;">Loading…</span>
                </div>
                @endfor
            </div>
        </div>

        {{-- Node status strip --}}
        <div id="nodeStrip" style="display:flex;flex-direction:column;gap:8px;"></div>

    </div>

    {{-- ── RIGHT: Control panel ─────────────────────────────────────── --}}
    <div class="tl-right">

        {{-- Mode selector --}}
        <div class="tl-card">
            <div class="tl-card-head">
                <span class="tl-card-title">Operating Mode</span>
                <span class="tl-badge tl-badge-auto" id="modeBadge">Auto</span>
            </div>
            <div class="tl-card-body">

                <div class="tl-mode-row">

                    <button class="tl-mode-btn sel-auto" id="btn-mode-auto" onclick="selectMode('auto')">
                        <span class="tl-mode-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="5 3 19 12 5 21 5 3"/>
                            </svg>
                        </span>
                        <span class="tl-mode-name">Auto</span>
                        <span class="tl-mode-desc">AI controls the cycle</span>
                    </button>

                    <button class="tl-mode-btn" id="btn-mode-manual" onclick="selectMode('manual')">
                        <span class="tl-mode-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0"/>
                                <path d="M14 10V4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v2"/>
                                <path d="M10 10.5V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"/>
                                <path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>
                            </svg>
                        </span>
                        <span class="tl-mode-name">Manual</span>
                        <span class="tl-mode-desc">Select which lane gets green</span>
                    </button>

                    <button class="tl-mode-btn" id="btn-mode-emergency" onclick="selectMode('emergency')">
                        <span class="tl-mode-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </span>
                        <span class="tl-mode-name">Emergency</span>
                        <span class="tl-mode-desc">All lanes stop immediately</span>
                    </button>

                </div>

                {{-- Emergency active banner --}}
                <div class="tl-emg-banner" id="emgBanner">
                    <span class="tl-emg-dot"></span>
                    <div class="tl-emg-banner-text">
                        <div class="tl-emg-banner-title">Emergency Mode Active</div>
                        <div class="tl-emg-banner-sub">All lanes are RED. Switch to Auto or Manual to resume.</div>
                    </div>
                </div>

                <hr class="tl-divider">

                {{-- Lane controls --}}
                <div class="tl-lane-hint" id="laneHint">Enable Manual mode to control individual lanes.</div>
                <div class="tl-lanes" id="laneList">
                    <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px 0;">Loading lanes…</div>
                </div>

            </div>
        </div>

        {{-- Activity log --}}
        <div class="tl-card">
            <div class="tl-card-head">
                <span class="tl-card-title">Activity Log</span>
                <button onclick="clearLog()" style="font-size:11px;color:var(--text-muted);background:none;border:none;cursor:pointer;font-weight:600;">Clear</button>
            </div>
            <div class="tl-card-body" style="padding:12px 20px;">
                <div class="tl-log" id="activityLog">
                    <div class="tl-log-empty">No actions yet this session.</div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
/* ================================================================
   STAP Hub — Traffic Light Control
   Mirrors ESP32 firmware v7 transition behaviour precisely.

   ESP32 MANUAL transition sequence (handleManual / MAN_TRANSITION):
     1. Press "Set Green" on target lane
     2. Previously-green lane → YELLOW signal (3s countdown) [setTransitionLights]
     3. After 3s → target lane gets GREEN [setNorthGo/setSouthGo/etc.]

   Web UI replicates this:
     1. User clicks target lane button
     2. UI immediately sends MANUAL_LIGHT:PREVLANE,YELLOW to Laravel API
        (which proxies to the Node's /control/light endpoint)
     3. Previous lane button blinks yellow; target lane button blinks yellow
     4. After YELLOW_TIME seconds: sends MANUAL_LIGHT:NEWLANE,GREEN
     5. UI updates to show new green lane

   EMERGENCY (MODE:HAZARD):
     Sends MODE:HAZARD (no specific lane).
     All lanes snap to red on the ESP32 via its HAZARD branch.
     No yellow transition — instant all-red.

   AUTO (MODE:AUTO):
     Returns control to Python/AI. No lane selection on web UI.
================================================================ */

const YELLOW_TIME_MS = 3000; // Must match ESP32 YELLOW_TIME = 3s

// ── State ──────────────────────────────────────────────────────
let currentMode         = 'auto';     // 'auto' | 'manual' | 'emergency'
let lights              = [];         // fetched from /admin/api/traffic-lights
let activeLaneId        = null;       // light_id currently GREEN
let transitioningToId   = null;       // light_id we are transitioning TO
let prevActiveLaneId    = null;       // light_id that is currently turning YELLOW
let transitionTimer     = null;
let countdownInterval   = null;
let countdown           = 0;
const sessionLog        = [];

// ── Boot ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadCameras();
    loadLights();
    setInterval(loadLights, 25000); // background refresh
});

// ── Camera feed ────────────────────────────────────────────────
async function loadCameras() {
    try {
        const res  = await fetch('/admin/api/cameras', { headers: authHeaders() });
        const data = await res.json();
        const cams = data.data ?? data;

        // Primary feed: first camera with a stream URL
        const primary = cams.find(c => c.stream_url);
        if (primary) {
            const placeholder = document.getElementById('feedPlaceholder');
            const iframe      = document.createElement('iframe');
            iframe.src        = primary.stream_url;
            iframe.title      = primary.label ?? 'Live Feed';
            iframe.referrerPolicy = 'no-referrer';
            document.getElementById('primaryFeed').insertBefore(iframe, placeholder);
            placeholder.style.display = 'none';
        }

        // 2×2 grid
        const grid = document.getElementById('cameraGrid');
        const slots = [...cams.slice(0, 4)];
        while (slots.length < 4) slots.push(null);

        grid.innerHTML = slots.map(cam => {
            if (!cam) return `
                <div style="background:#0d1220;border-radius:8px;aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:11px;color:rgba(255,255,255,.15);font-weight:600;">No camera</span>
                </div>`;
            const online = cam.status === 'active' || cam.status === 'online';
            const media  = cam.stream_url
                ? `<iframe src="${cam.stream_url}" style="width:100%;height:100%;border:none;position:absolute;inset:0;" referrerpolicy="no-referrer"></iframe>`
                : `<span style="font-size:10px;color:rgba(255,255,255,.18);font-weight:600;padding:8px;text-align:center;line-height:1.4;">No stream<br>${cam.label ?? ''}</span>`;
            return `
                <div style="background:#0d1220;border-radius:8px;aspect-ratio:16/9;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                    ${media}
                    <div style="position:absolute;bottom:5px;left:7px;font-size:10px;font-weight:700;color:rgba(255,255,255,.8);text-shadow:0 1px 4px rgba(0,0,0,.6);pointer-events:none;">
                        ${cam.label ?? 'Camera'}
                    </div>
                    <div style="position:absolute;top:6px;right:6px;width:7px;height:7px;border-radius:50%;background:${online ? '#29B357' : '#E03040'};box-shadow:0 0 6px 1px ${online ? 'rgba(41,179,87,.6)' : 'rgba(224,48,64,.5)'};"></div>
                </div>`;
        }).join('');

    } catch(e) { /* camera load fail — placeholder stays */ }
}

// ── Load lights & node summary ─────────────────────────────────
async function loadLights() {
    try {
        const res  = await fetch('/admin/api/traffic-lights', { headers: authHeaders() });
        const data = await res.json();
        lights     = data.data ?? data;

        // Sync mode from node if not in the middle of a user-initiated switch
        if (!transitionTimer) {
            const nodeMode = lights[0]?.node?.mode ?? 'auto';
            currentMode = nodeMode === 'hazard' ? 'emergency' : nodeMode;
            refreshModeUI();
        }

        // Infer green lane from DB state (manual only)
        if (!transitionTimer && currentMode === 'manual' && !activeLaneId) {
            const green = lights.find(l => l.current_state === 'green');
            if (green) activeLaneId = lightId(green);
        }

        renderLanes();
        renderNodeStrip();
    } catch(e) { console.error('loadLights:', e); }
}

const lightId = l => l.light_id ?? l.id;

// ── Render node strip ──────────────────────────────────────────
function renderNodeStrip() {
    const nodes = {};
    lights.forEach(l => {
        const nid  = l.node?.node_id ?? l.node?.id ?? l.node_id;
        if (!nid) return;
        const name = l.node?.node_name ?? l.node?.name ?? 'Node';
        const mode = l.node?.mode ?? '—';
        if (!nodes[nid]) nodes[nid] = { name, mode, count: 0 };
        nodes[nid].count++;
    });

    document.getElementById('nodeStrip').innerHTML = Object.values(nodes).map(n => {
        const mc  = n.mode === 'auto' ? 'tl-badge-auto' : n.mode === 'manual' ? 'tl-badge-manual' : 'tl-badge-emergency';
        return `<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--navy);">${n.name}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">${n.count} signal(s) managed</div>
            </div>
            <span class="tl-badge ${mc}">${n.mode.toUpperCase()}</span>
        </div>`;
    }).join('') || '';
}

// ── Render lane buttons ────────────────────────────────────────
function renderLanes() {
    const hint    = document.getElementById('laneHint');
    const isMan   = currentMode === 'manual';
    const isEmg   = currentMode === 'emergency';

    hint.textContent = isMan
        ? 'Select a lane to give it the green light. Yellow transition is 3 seconds.'
        : isEmg
            ? 'Disengage Emergency mode to control individual lanes.'
            : 'Lanes are controlled automatically by the AI. Enable Manual to override.';

    if (!lights.length) {
        document.getElementById('laneList').innerHTML =
            '<div style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px 0;">No traffic lights registered.</div>';
        return;
    }

    document.getElementById('laneList').innerHTML = lights.map(l => {
        const lid        = lightId(l);
        const isActive   = activeLaneId === lid && isMan;
        const isTo       = transitioningToId === lid;
        const isFrom     = prevActiveLaneId === lid;
        const isTransit  = (isTo || isFrom) && !!transitioningToId;

        // Displayed signal state
        let sigState = isEmg ? 'r'
            : isTransit ? 'y'
            : isActive  ? 'g'
            : 'r';

        // Row highlight class
        const rowCls = isActive  ? 'is-green'
            : isTransit ? 'is-yellow'
            : isEmg     ? 'is-red-emg'
            : '';

        // Button state
        const btnCls    = isActive  ? 'btn-active'
            : isTransit ? 'btn-transitioning'
            : '';
        const disabled  = (!isMan || isEmg || (!!transitioningToId && !isTransit)) ? 'disabled' : '';
        const isAlreadyActive = isActive && isMan;

        const btnText = isTo && isTransit ? `Switching… (${countdown}s)`
            : isFrom && isTransit ? 'Turning Yellow…'
            : isActive ? 'Active — Green'
            : 'Set to Green';

        return `
        <div class="tl-lane ${rowCls}" id="lane-row-${lid}">
            <div class="tl-lane-top">
                <div class="tl-lane-info">
                    <div class="tl-lane-name">${l.location_label ?? 'Light ' + lid}</div>
                    <div class="tl-lane-sub">Node: ${l.node?.node_name ?? l.node?.name ?? '—'}</div>
                </div>
                <div class="tl-signal" id="signal-${lid}">
                    <div class="tl-sig-dot r ${sigState === 'r' ? 'lit-r' : ''}"></div>
                    <div class="tl-sig-dot y ${sigState === 'y' ? 'lit-y' : ''}"></div>
                    <div class="tl-sig-dot g ${sigState === 'g' ? 'lit-g' : ''}"></div>
                </div>
            </div>
            <button class="tl-lane-btn ${btnCls}" id="lane-btn-${lid}"
                onclick="requestGreen(${lid})"
                ${disabled || isAlreadyActive ? 'disabled' : ''}>
                ${btnText}
            </button>
            <div class="tl-lane-btn-sub" id="lane-sub-${lid}"></div>
        </div>`;
    }).join('');
}

// ── Request green (with 3-second yellow transition) ────────────
function requestGreen(lid) {
    if (currentMode !== 'manual') return;
    if (transitioningToId)        return;
    if (activeLaneId === lid)     return; // already green

    const prev = activeLaneId; // may be null if no lane was green yet
    prevActiveLaneId  = prev;
    transitioningToId = lid;
    countdown         = Math.ceil(YELLOW_TIME_MS / 1000);

    // Re-render immediately to show yellow state
    renderLanes();

    // If a lane was previously green, tell the ESP32 to set it yellow
    if (prev !== null) {
        const prevLight = lights.find(l => lightId(l) === prev);
        const prevLabel = laneLabel(prevLight);
        if (prevLabel) {
            apiSetLight(prevLabel, 'yellow').then(() => {
                log(`Yellow: ${prevLight?.location_label ?? prevLabel}`);
            });
        }
    }

    // Live countdown in button label
    countdownInterval = setInterval(() => {
        countdown = Math.max(0, countdown - 1);
        const btnEl = document.getElementById(`lane-btn-${lid}`);
        if (btnEl) btnEl.textContent = `Switching… (${countdown}s)`;
    }, 1000);

    // After YELLOW_TIME_MS → send green command
    transitionTimer = setTimeout(async () => {
        clearInterval(countdownInterval);
        countdownInterval = null;
        transitionTimer   = null;

        const newLight  = lights.find(l => lightId(l) === lid);
        const newLabel  = laneLabel(newLight);

        if (newLabel) {
            try {
                await apiSetLight(newLabel, 'green');
                log(`Green set: ${newLight?.location_label ?? newLabel}`);
            } catch(e) {
                log('Error setting green — check node connection', 'err');
            }
        }

        activeLaneId      = lid;
        transitioningToId = null;
        prevActiveLaneId  = null;
        countdown         = 0;
        renderLanes();
    }, YELLOW_TIME_MS);
}

// ── Mode selection ─────────────────────────────────────────────
async function selectMode(mode) {
    // Cancel any pending transition
    if (transitionTimer) {
        clearTimeout(transitionTimer);
        clearInterval(countdownInterval);
        transitionTimer   = null;
        countdownInterval = null;
        transitioningToId = null;
        prevActiveLaneId  = null;
        countdown         = 0;
    }

    currentMode = mode;

    if (mode === 'emergency') {
        activeLaneId = null; // all red — no active lane
    }

    refreshModeUI();
    renderLanes();

    // Send to backend
    const laravelMode = mode === 'emergency' ? 'hazard' : mode;
    try {
        const res  = await fetch('/admin/api/node/mode', {
            method:  'POST',
            headers: { ...authHeaders(), 'Content-Type': 'application/json' },
            body:    JSON.stringify({ mode: laravelMode }),
        });
        const data = await res.json();
        if (!res.ok) {
            log('Mode error: ' + (data.message ?? 'Failed'), 'err');
        } else {
            const labels = { auto: 'Auto mode — AI in control', manual: 'Manual mode activated', emergency: 'Emergency mode — all lanes RED' };
            log(labels[mode] ?? mode);
        }
    } catch(e) {
        log('Network error setting mode', 'err');
    }
}

// ── Refresh mode UI (buttons + badge + banner) ─────────────────
function refreshModeUI() {
    const badge     = document.getElementById('modeBadge');
    const banner    = document.getElementById('emgBanner');
    const btns      = { auto: 'btn-mode-auto', manual: 'btn-mode-manual', emergency: 'btn-mode-emergency' };

    Object.keys(btns).forEach(m => {
        const el = document.getElementById(btns[m]);
        el.className = 'tl-mode-btn' + (currentMode === m ? ' sel-' + m : '');
    });

    if (currentMode === 'auto') {
        badge.textContent = 'Auto';
        badge.className   = 'tl-badge tl-badge-auto';
        banner.classList.remove('on');
    } else if (currentMode === 'manual') {
        badge.textContent = 'Manual';
        badge.className   = 'tl-badge tl-badge-manual';
        banner.classList.remove('on');
    } else {
        badge.textContent = 'Emergency';
        badge.className   = 'tl-badge tl-badge-emergency';
        banner.classList.add('on');
    }
}

// ── API calls ──────────────────────────────────────────────────
// Derive the LANE label (NORTH/SOUTH/EAST/WEST) from location_label or direction
function laneLabel(light) {
    if (!light) return null;
    const loc = (light.location_label ?? '').toUpperCase();
    if (loc.includes('NORTH')) return 'NORTH';
    if (loc.includes('SOUTH')) return 'SOUTH';
    if (loc.includes('EAST'))  return 'EAST';
    if (loc.includes('WEST'))  return 'WEST';
    // Fallback: direction field
    const dir = (light.direction ?? '').toUpperCase();
    if (['NORTH','SOUTH','EAST','WEST'].includes(dir)) return dir;
    return null;
}

async function apiSetLight(lane, state) {
    const res = await fetch('/admin/traffic-lights/' + lights.find(l => laneLabel(l) === lane)?.light_id + '/state', {
        method:  'POST',
        headers: { ...authHeaders(), 'Content-Type': 'application/json' },
        body:    JSON.stringify({ state, lane }),
    });
    if (!res.ok) throw new Error(await res.text());
    return res.json();
}

// ── Activity log ───────────────────────────────────────────────
function log(msg, type) {
    const now  = new Date().toLocaleTimeString();
    sessionLog.unshift({ msg, type: type ?? 'info', time: now });
    if (sessionLog.length > 20) sessionLog.pop();
    renderLog();
}

function clearLog() {
    sessionLog.length = 0;
    renderLog();
}

function renderLog() {
    const el = document.getElementById('activityLog');
    if (!sessionLog.length) {
        el.innerHTML = '<div class="tl-log-empty">No actions yet this session.</div>';
        return;
    }
    el.innerHTML = sessionLog.map(e =>
        `<div class="tl-log-entry">
            <span class="tl-log-time">${e.time}</span>
            <span class="tl-log-msg ${e.type === 'err' ? 'err' : e.type === 'warn' ? 'warn' : ''}">${e.msg}</span>
        </div>`
    ).join('');
}
</script>
@endpush