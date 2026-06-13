@extends('layouts.admin')
@section('title', 'Traffic Light Control')
@section('page-title', 'Traffic Light Control')

@push('styles')
<style>
    /* ── Layout ─────────────────────────────────────────────── */
    .ctrl-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.25rem;
        align-items: start;
    }

    @media (max-width: 960px) {
        .ctrl-layout { grid-template-columns: 1fr; }
    }

    /* ── Card Base ───────────────────────────────────────────── */
    .ctrl-card {
        background: #fff;
        border-radius: .875rem;
        border: 1px solid rgba(15,23,42,.08);
        box-shadow: 0 4px 14px rgba(15,23,42,.06);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .ctrl-card-header {
        padding: .875rem 1.25rem;
        border-bottom: 1px solid rgba(15,23,42,.07);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }

    .ctrl-card-title {
        font-size: .92rem;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .ctrl-card-body { padding: 1.25rem; }

    /* ── Node IP Bar ─────────────────────────────────────────── */
    .ctrl-ip-bar {
        background: #fef9ec;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 12px;
        color: #92400e;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ctrl-ip-bar input {
        border: 1.5px solid #fbbf24;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 12px;
        font-family: monospace;
        width: 160px;
        outline: none;
    }

    .ctrl-ip-bar button {
        background: #0f172a;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    /* ── Mode Buttons ────────────────────────────────────────── */
    .ctrl-mode-row {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .ctrl-mode-btn {
        flex: 1;
        min-width: 120px;
        padding: .875rem 1rem;
        border-radius: .75rem;
        border: 2px solid transparent;
        font-size: .88rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .18s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .35rem;
        background: #f8fafc;
        color: #64748b;
    }

    .ctrl-mode-btn .ctrl-mode-icon { font-size: 1.5rem; }

    .ctrl-mode-btn:hover { border-color: #cbd5e1; background: #f1f5f9; }

    .ctrl-mode-btn.active-auto    { background: #dcfce7; color: #166534; border-color: #22c55e; }
    .ctrl-mode-btn.active-manual  { background: #dbeafe; color: #1e40af; border-color: #3b82f6; }
    .ctrl-mode-btn.active-hazard  { background: #fef3c7; color: #92400e; border-color: #f59e0b; }

    /* ── Lane Grid ───────────────────────────────────────────── */
    .ctrl-lane-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .875rem;
    }

    @media (max-width: 600px) {
        .ctrl-lane-grid { grid-template-columns: 1fr; }
    }

    .ctrl-lane-card {
        border-radius: .75rem;
        border: 2px solid #e2e8f0;
        overflow: hidden;
        transition: border-color .2s;
    }

    .ctrl-lane-card.active-lane { border-color: #22c55e; }

    .ctrl-lane-header {
        padding: .6rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .ctrl-lane-name { font-size: .85rem; font-weight: 800; color: #0f172a; }

    .ctrl-lane-badge {
        font-size: .68rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
        text-transform: uppercase;
    }

    .ctrl-lane-badge.green  { background: #dcfce7; color: #166534; }
    .ctrl-lane-badge.yellow { background: #fef3c7; color: #92400e; }
    .ctrl-lane-badge.red    { background: #fee2e2; color: #991b1b; }

    .ctrl-lane-body { padding: .75rem; }

    /* ── Light Buttons ───────────────────────────────────────── */
    .ctrl-light-row {
        display: flex;
        gap: .5rem;
        margin-bottom: .6rem;
    }

    .ctrl-light-btn {
        flex: 1;
        padding: .55rem .25rem;
        border: 2px solid transparent;
        border-radius: .6rem;
        font-size: .75rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .2rem;
    }

    .ctrl-light-btn:disabled { opacity: .35; cursor: not-allowed; }

    .ctrl-light-btn.btn-red    { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .ctrl-light-btn.btn-yellow { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
    .ctrl-light-btn.btn-green  { background: #dcfce7; color: #166534; border-color: #86efac; }

    .ctrl-light-btn.btn-red:hover:not(:disabled)    { background: #fca5a5; }
    .ctrl-light-btn.btn-yellow:hover:not(:disabled) { background: #fcd34d; }
    .ctrl-light-btn.btn-green:hover:not(:disabled)  { background: #86efac; }

    .ctrl-light-btn.active { box-shadow: 0 0 0 3px rgba(15,23,42,.18); transform: scale(1.04); }

    .ctrl-light-dot {
        width: .85rem;
        height: .85rem;
        border-radius: 50%;
    }

    .ctrl-light-dot.red    { background: #ef4444; box-shadow: 0 0 6px #ef4444; }
    .ctrl-light-dot.yellow { background: #f59e0b; box-shadow: 0 0 6px #f59e0b; }
    .ctrl-light-dot.green  { background: #22c55e; box-shadow: 0 0 6px #22c55e; }
    .ctrl-light-dot.off    { background: #e2e8f0; }

    /* ── Emergency Button ────────────────────────────────────── */
    .ctrl-emergency-btn {
        width: 100%;
        padding: .55rem;
        background: #fff1f2;
        border: 2px solid #fca5a5;
        border-radius: .6rem;
        color: #991b1b;
        font-size: .75rem;
        font-weight: 800;
        cursor: pointer;
        transition: all .15s;
        letter-spacing: .02em;
    }

    .ctrl-emergency-btn:hover:not(:disabled) { background: #fca5a5; }
    .ctrl-emergency-btn:disabled { opacity: .35; cursor: not-allowed; }

    /* ── Sidebar Status ──────────────────────────────────────── */
    .ctrl-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 7px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
    }

    .ctrl-status-row:last-child { border-bottom: none; }
    .ctrl-status-label { color: #64748b; font-weight: 600; }
    .ctrl-status-value { font-weight: 700; color: #0f172a; }

    .ctrl-phase-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .ctrl-phase-green  { background: #dcfce7; color: #166534; }
    .ctrl-phase-yellow { background: #fef3c7; color: #92400e; }
    .ctrl-phase-red    { background: #fee2e2; color: #991b1b; }

    /* ── Activity Log ────────────────────────────────────────── */
    .ctrl-log {
        max-height: 220px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: .4rem;
    }

    .ctrl-log-entry {
        font-size: .75rem;
        padding: .4rem .6rem;
        border-radius: 6px;
        background: #f8fafc;
        border-left: 3px solid #e2e8f0;
        color: #475569;
        line-height: 1.4;
    }

    .ctrl-log-entry.success { border-left-color: #22c55e; }
    .ctrl-log-entry.error   { border-left-color: #ef4444; }
    .ctrl-log-entry.info    { border-left-color: #3b82f6; }

    /* ── Node Connection Indicator ───────────────────────────── */
    .ctrl-node-indicator {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .78rem;
        font-weight: 700;
    }

    .ctrl-node-dot {
        width: .55rem;
        height: .55rem;
        border-radius: 50%;
        background: #94a3b8;
    }

    .ctrl-node-dot.connected    { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.2); }
    .ctrl-node-dot.disconnected { background: #ef4444; }

    /* ── Toast ───────────────────────────────────────────────── */
    .ctrl-toast {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        background: #0f172a;
        color: #fff;
        padding: .75rem 1.25rem;
        border-radius: .75rem;
        font-size: .85rem;
        font-weight: 600;
        box-shadow: 0 8px 24px rgba(15,23,42,.25);
        z-index: 9999;
        transform: translateY(120%);
        transition: transform .25s;
        max-width: 300px;
    }

    .ctrl-toast.show { transform: translateY(0); }
    .ctrl-toast.toast-error { background: #991b1b; }
</style>
@endpush

@section('content')

{{-- Node IP Bar --}}
<div class="ctrl-ip-bar">
    <span>⚙️ STAP Node IP:</span>
    <input type="text" id="nodeIpInput" value="192.168.1.100" placeholder="e.g. 192.168.1.50">
    <button onclick="applyNodeIp()">Apply</button>
    <div class="ctrl-node-indicator" style="margin-left:auto;">
        <span class="ctrl-node-dot" id="nodeConnDot"></span>
        <span id="nodeConnLabel">Connecting...</span>
    </div>
</div>

<div class="ctrl-layout">

    {{-- Left: Controls --}}
    <div>

        {{-- Mode Control --}}
        <div class="ctrl-card">
            <div class="ctrl-card-header">
                <span class="ctrl-card-title">🚦 System Mode</span>
                <span style="font-size:.75rem;color:#64748b;" id="modeHint">Select a mode to begin</span>
            </div>
            <div class="ctrl-card-body">
                <div class="ctrl-mode-row">
                    <button class="ctrl-mode-btn" id="btn-auto" onclick="setMode('auto')">
                        <span class="ctrl-mode-icon">🤖</span>
                        <span>Auto</span>
                        <span style="font-size:.7rem;font-weight:500;opacity:.7;">AI Controlled</span>
                    </button>
                    <button class="ctrl-mode-btn" id="btn-manual" onclick="setMode('manual')">
                        <span class="ctrl-mode-icon">🕹️</span>
                        <span>Manual</span>
                        <span style="font-size:.7rem;font-weight:500;opacity:.7;">Admin Override</span>
                    </button>
                    <button class="ctrl-mode-btn" id="btn-hazard" onclick="setMode('hazard')">
                        <span class="ctrl-mode-icon">⚠️</span>
                        <span>Hazard</span>
                        <span style="font-size:.7rem;font-weight:500;opacity:.7;">All Yellow Flash</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Lane Light Control --}}
        <div class="ctrl-card">
            <div class="ctrl-card-header">
                <span class="ctrl-card-title">🔦 Lane Light Control</span>
                <span style="font-size:.75rem;color:#94a3b8;" id="laneHint">Switch to Manual or Hazard mode first</span>
            </div>
            <div class="ctrl-card-body">
                <div class="ctrl-lane-grid" id="laneGrid">

                    @foreach (['NORTH', 'SOUTH', 'EAST', 'WEST'] as $lane)
                    <div class="ctrl-lane-card" id="laneCard-{{ $lane }}">
                        <div class="ctrl-lane-header">
                            <span class="ctrl-lane-name">{{ $lane }}</span>
                            <span class="ctrl-lane-badge red" id="laneBadge-{{ $lane }}">RED</span>
                        </div>
                        <div class="ctrl-lane-body">
                            <div class="ctrl-light-row">
                                <button class="ctrl-light-btn btn-red"    id="btn-{{ $lane }}-red"    onclick="setLight('{{ $lane }}','red')"    disabled>
                                    <span class="ctrl-light-dot off" id="dot-{{ $lane }}-red"></span>RED
                                </button>
                                <button class="ctrl-light-btn btn-yellow" id="btn-{{ $lane }}-yellow" onclick="setLight('{{ $lane }}','yellow')" disabled>
                                    <span class="ctrl-light-dot off" id="dot-{{ $lane }}-yellow"></span>YLW
                                </button>
                                <button class="ctrl-light-btn btn-green"  id="btn-{{ $lane }}-green"  onclick="setLight('{{ $lane }}','green')"  disabled>
                                    <span class="ctrl-light-dot off" id="dot-{{ $lane }}-green"></span>GRN
                                </button>
                            </div>
                            <button class="ctrl-emergency-btn" id="btn-{{ $lane }}-emergency" onclick="triggerEmergency('{{ $lane }}')" disabled>
                                🚨 EMERGENCY OVERRIDE
                            </button>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </div>

    </div>

    {{-- Right: Sidebar --}}
    <div>

        {{-- Live System Status --}}
        <div class="ctrl-card">
            <div class="ctrl-card-header">
                <span class="ctrl-card-title">📡 Live Status</span>
                <span style="font-size:.7rem;color:#94a3b8;" id="lastPoll">—</span>
            </div>
            <div class="ctrl-card-body" id="statusPanel">
                <div style="color:#94a3b8;font-size:12px;text-align:center;padding:8px 0;">
                    Waiting for node...
                </div>
            </div>
        </div>

        {{-- Activity Log --}}
        <div class="ctrl-card">
            <div class="ctrl-card-header">
                <span class="ctrl-card-title">📋 Activity Log</span>
                <button onclick="clearLog()" style="font-size:.7rem;color:#94a3b8;background:none;border:none;cursor:pointer;">Clear</button>
            </div>
            <div class="ctrl-card-body" style="padding:.75rem;">
                <div class="ctrl-log" id="activityLog">
                    <div class="ctrl-log-entry info">Admin panel loaded. Set Node IP to begin.</div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Toast --}}
<div class="ctrl-toast" id="toast"></div>

@endsection

@push('scripts')
<script>
    // ── Config ────────────────────────────────────────────────
    let NODE_IP   = localStorage.getItem('stap_node_ip') || '192.168.1.100';
    let curMode   = 'auto';
    let curLights = { NORTH: 'red', SOUTH: 'red', EAST: 'red', WEST: 'red' };
    const LANES   = ['NORTH', 'SOUTH', 'EAST', 'WEST'];

    document.getElementById('nodeIpInput').value = NODE_IP;

    // ── Apply Node IP ─────────────────────────────────────────
    function applyNodeIp() {
        NODE_IP = document.getElementById('nodeIpInput').value.trim();
        localStorage.setItem('stap_node_ip', NODE_IP);
        logActivity('Node IP set to ' + NODE_IP, 'info');
        startStatusPolling();
    }

    // ── Mode Control ──────────────────────────────────────────
    async function setMode(mode) {
        try {
            const res  = await postNode('/control/mode', { mode });
            const data = await res.json();

            if (data.success) {
                curMode = mode;
                updateModeUI(mode);
                logActivity(`Mode switched to ${mode.toUpperCase()}`, 'success');
                showToast(`✅ Mode: ${mode.toUpperCase()}`);
            } else {
                logActivity('Mode switch failed: ' + (data.message || 'Unknown error'), 'error');
                showToast('❌ ' + (data.message || 'Failed'), true);
            }
        } catch (e) {
            logActivity('Cannot reach STAP Node', 'error');
            showToast('❌ Node unreachable', true);
        }
    }

    function updateModeUI(mode) {
        ['auto','manual','hazard'].forEach(m => {
            document.getElementById('btn-' + m).className = 'ctrl-mode-btn';
        });
        document.getElementById('btn-' + mode).classList.add('active-' + mode);

        const isManual = mode === 'manual' || mode === 'hazard';
        document.getElementById('laneHint').textContent = isManual
            ? 'Select a light state per lane'
            : 'Switch to Manual or Hazard mode first';

        LANES.forEach(lane => {
            ['red','yellow','green'].forEach(state => {
                document.getElementById(`btn-${lane}-${state}`).disabled = !isManual;
            });
            document.getElementById(`btn-${lane}-emergency`).disabled = false; // always enabled
        });

        document.getElementById('modeHint').textContent = `Current: ${mode.toUpperCase()}`;
    }

    // ── Light Control ─────────────────────────────────────────
    async function setLight(lane, state) {
        try {
            const res  = await postNode('/control/light', { lane, state });
            const data = await res.json();

            if (data.success) {
                curLights[lane] = state;
                updateLaneBadge(lane, state);
                logActivity(`${lane} → ${state.toUpperCase()}`, 'success');
                showToast(`✅ ${lane}: ${state.toUpperCase()}`);
            } else {
                logActivity(`${lane} light failed: ` + (data.message || ''), 'error');
                showToast('❌ ' + (data.message || 'Failed'), true);
            }
        } catch (e) {
            logActivity('Cannot reach STAP Node', 'error');
            showToast('❌ Node unreachable', true);
        }
    }

    function updateLaneBadge(lane, state) {
        const badge = document.getElementById(`laneBadge-${lane}`);
        badge.textContent = state.toUpperCase();
        badge.className   = `ctrl-lane-badge ${state}`;

        ['red','yellow','green'].forEach(s => {
            const dot = document.getElementById(`dot-${lane}-${s}`);
            dot.className = `ctrl-light-dot ${s === state ? s : 'off'}`;
            document.getElementById(`btn-${lane}-${s}`).classList.toggle('active', s === state);
        });

        const card = document.getElementById(`laneCard-${lane}`);
        card.classList.toggle('active-lane', state === 'green');
    }

    // ── Emergency Override ────────────────────────────────────
    async function triggerEmergency(lane) {
        if (!confirm(`⚠️ Trigger EMERGENCY OVERRIDE for ${lane}?\n\nThis will immediately give ${lane} a green light.`)) return;

        try {
            const res  = await postNode('/control/emergency', { lane });
            const data = await res.json();

            if (data.success) {
                curMode = 'auto';
                updateModeUI('auto');
                logActivity(`🚨 EMERGENCY OVERRIDE — ${lane}`, 'error');
                showToast(`🚨 Emergency: ${lane} has priority`);
            } else {
                logActivity('Emergency failed: ' + (data.message || ''), 'error');
                showToast('❌ ' + (data.message || 'Failed'), true);
            }
        } catch (e) {
            logActivity('Cannot reach STAP Node', 'error');
            showToast('❌ Node unreachable', true);
        }
    }

    // ── Status Polling ────────────────────────────────────────
    let pollInterval = null;

    function startStatusPolling() {
        if (pollInterval) clearInterval(pollInterval);
        fetchStatus();
        pollInterval = setInterval(fetchStatus, 3000);
    }

    async function fetchStatus() {
        try {
            const res  = await fetch(`http://${NODE_IP}:5000/status`, { signal: AbortSignal.timeout(2000) });
            const data = await res.json();

            setNodeConnected(true);
            renderStatusPanel(data);
            document.getElementById('lastPoll').textContent = new Date().toLocaleTimeString();

            // Sync mode if node reports differently
            if (data.mode && data.mode !== curMode) {
                curMode = data.mode;
                updateModeUI(data.mode);
            }

        } catch (e) {
            setNodeConnected(false);
            document.getElementById('statusPanel').innerHTML =
                '<div style="color:#ef4444;font-size:12px;text-align:center;padding:8px 0;">⚠ Cannot reach STAP Node</div>';
        }
    }

    function renderStatusPanel(data) {
        const phaseClass = data.phase_state === 'GREEN'  ? 'ctrl-phase-green'
                         : data.phase_state === 'YELLOW' ? 'ctrl-phase-yellow'
                         : 'ctrl-phase-red';

        const losColors = { A:'#166534', B:'#166534', C:'#92400e', D:'#92400e', E:'#991b1b', F:'#991b1b' };

        let vehicleRows = '';
        for (const lane of LANES) {
            const count = data.vehicle_counts?.[lane] ?? 0;
            const los   = data.los?.[lane] ?? '—';
            const color = losColors[los] || '#334155';
            vehicleRows += `
                <div class="ctrl-status-row">
                    <span class="ctrl-status-label">${lane}</span>
                    <span class="ctrl-status-value">
                        ${count} &nbsp;<span style="color:${color};font-weight:800;">LOS ${los}</span>
                    </span>
                </div>`;
        }

        document.getElementById('statusPanel').innerHTML = `
            <div class="ctrl-status-row">
                <span class="ctrl-status-label">Active Lane</span>
                <span class="ctrl-status-value">${data.active_lane ?? '—'}</span>
            </div>
            <div class="ctrl-status-row">
                <span class="ctrl-status-label">Signal</span>
                <span class="ctrl-phase-badge ${phaseClass}">${data.phase_state ?? '—'}</span>
            </div>
            <div class="ctrl-status-row">
                <span class="ctrl-status-label">Remaining</span>
                <span class="ctrl-status-value">${data.remaining_secs ?? 0}s</span>
            </div>
            <div class="ctrl-status-row">
                <span class="ctrl-status-label">Mode</span>
                <span class="ctrl-status-value" style="text-transform:uppercase;">${data.mode ?? '—'}</span>
            </div>
            <div class="ctrl-status-row">
                <span class="ctrl-status-label">Rain</span>
                <span class="ctrl-status-value">${data.rain ? '🌧 Detected' : '☀ Clear'}</span>
            </div>
            <div style="margin:10px 0 4px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">Vehicle Counts</div>
            ${vehicleRows}
        `;
    }

    // ── Node Connection Indicator ─────────────────────────────
    function setNodeConnected(connected) {
        const dot   = document.getElementById('nodeConnDot');
        const label = document.getElementById('nodeConnLabel');
        dot.className   = 'ctrl-node-dot ' + (connected ? 'connected' : 'disconnected');
        label.textContent = connected ? 'Node Connected' : 'Node Disconnected';
    }

    // ── Helpers ───────────────────────────────────────────────
    function postNode(endpoint, body) {
        return fetch(`http://${NODE_IP}:5000${endpoint}`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(body),
            signal:  AbortSignal.timeout(3000)
        });
    }

    function logActivity(msg, type = 'info') {
        const log   = document.getElementById('activityLog');
        const entry = document.createElement('div');
        const time  = new Date().toLocaleTimeString();
        entry.className   = `ctrl-log-entry ${type}`;
        entry.textContent = `[${time}] ${msg}`;
        log.prepend(entry);
        // Keep max 50 entries
        while (log.children.length > 50) log.removeChild(log.lastChild);
    }

    function clearLog() {
        document.getElementById('activityLog').innerHTML =
            '<div class="ctrl-log-entry info">Log cleared.</div>';
    }

    let toastTimer = null;
    function showToast(msg, isError = false) {
        const toast = document.getElementById('toast');
        toast.textContent = msg;
        toast.className   = 'ctrl-toast show' + (isError ? ' toast-error' : '');
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // ── Init ──────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        if (NODE_IP) startStatusPolling();
    });
</script>
@endpush