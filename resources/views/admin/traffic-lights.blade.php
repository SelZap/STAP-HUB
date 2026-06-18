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
      .tlc-layout {
        grid-template-columns: 1fr;
      }
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
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
      padding: 7px 12px;
      border-radius: 6px;
      font-size: 12px;
      width: 150px;
    }

    .tlc-nodebar input::placeholder {
      color: rgba(255, 255, 255, 0.4);
    }

    .tlc-nodebar button {
      background: #fff;
      color: var(--navy);
      border: none;
      padding: 7px 16px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
    }

    .tlc-conn-badge {
      margin-left: auto;
      font-size: 11px;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 20px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .tlc-conn-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
    }

    .tlc-conn-badge.online {
      background: rgba(41, 179, 87, .18);
      color: #5be08a;
    }

    .tlc-conn-badge.online .tlc-conn-dot {
      background: #29B357;
    }

    .tlc-conn-badge.offline {
      background: rgba(224, 48, 64, .18);
      color: #ff8f99;
    }

    .tlc-conn-badge.offline .tlc-conn-dot {
      background: #E03040;
    }

    /* ── Control panel (compact) ──────────────────────── */
    .tlc-panel {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 16px;
      box-shadow: var(--shadow-sm);
    }

    .tlc-panel-title {
      font-size: 12px;
      font-weight: 800;
      color: var(--navy);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 2px;
    }

    .tlc-panel-sub {
      font-size: 11px;
      color: var(--text-muted);
      margin-bottom: 14px;
    }

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
      color: rgba(255, 255, 255, 0.55);
      transition: all 0.15s ease;
      border: 2px solid transparent;
      position: relative;
    }

    .tlc-btn svg {
      width: 18px;
      height: 18px;
    }

    .tlc-btn:hover:not(:disabled) {
      color: #fff;
      background: #243460;
    }

    .tlc-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    /* glow per mode when active */
    .tlc-btn.mode-ai.active {
      background: #1d3a2c;
      color: #5be08a;
      border-color: #29B357;
      box-shadow: 0 0 16px rgba(41, 179, 87, .45);
    }

    .tlc-btn.mode-manual.active {
      background: #2a2f4a;
      color: #93a9ff;
      border-color: #5b73e8;
      box-shadow: 0 0 16px rgba(91, 115, 232, .45);
    }

    .tlc-btn.mode-hazard.active {
      background: #3a2d12;
      color: #ffce5b;
      border-color: #F4B942;
      box-shadow: 0 0 16px rgba(244, 185, 66, .45);
      animation: tlc-pulse-amber 1.2s infinite;
    }

    .tlc-btn.mode-emergency.active {
      background: #3a1414;
      color: #ff7a7a;
      border-color: #E03040;
      box-shadow: 0 0 16px rgba(224, 48, 64, .5);
      animation: tlc-pulse-red 1s infinite;
    }

    @keyframes tlc-pulse-amber {

      0%,
      100% {
        box-shadow: 0 0 14px rgba(244, 185, 66, .4);
      }

      50% {
        box-shadow: 0 0 24px rgba(244, 185, 66, .75);
      }
    }

    @keyframes tlc-pulse-red {

      0%,
      100% {
        box-shadow: 0 0 14px rgba(224, 48, 64, .45);
      }

      50% {
        box-shadow: 0 0 26px rgba(224, 48, 64, .85);
      }
    }

    /* Lane D-pad */
    .tlc-dpad-label {
      font-size: 10px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin: 14px 0 8px;
    }

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
      color: rgba(255, 255, 255, 0.55);
      border-radius: 8px;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 0.4px;
      cursor: pointer;
      padding: 10px 4px;
      transition: all 0.15s ease;
    }

    .tlc-dpad-btn:hover:not(:disabled) {
      color: #fff;
      background: #243460;
    }

    .tlc-dpad-btn:disabled {
      opacity: 0.35;
      cursor: not-allowed;
    }

    .tlc-dpad-btn.lane-active {
      background: #1d3a2c;
      color: #5be08a;
      border-color: #29B357;
      box-shadow: 0 0 14px rgba(41, 179, 87, .45);
    }

    .tlc-dpad-n {
      grid-column: 2;
      grid-row: 1;
    }

    .tlc-dpad-w {
      grid-column: 1;
      grid-row: 2;
    }

    .tlc-dpad-center {
      grid-column: 2;
      grid-row: 2;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-muted);
      font-size: 9px;
      font-weight: 700;
    }

    .tlc-dpad-e {
      grid-column: 3;
      grid-row: 2;
    }

    .tlc-dpad-s {
      grid-column: 2;
      grid-row: 3;
    }

    .tlc-system-status {
      margin-top: 16px;
      padding-top: 14px;
      border-top: 1px solid var(--border);
      font-size: 11px;
      color: var(--text-muted);
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .tlc-system-status span.val {
      color: var(--navy);
      font-weight: 700;
    }

    /* ── Live feeds (large) ────────────────────────────── */
    .tlc-feeds-card {
      background: var(--navy);
      border-radius: var(--radius-md);
      padding: 14px;
    }

    .tlc-feeds-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 4px 12px;
      color: #fff;
    }

    .tlc-feeds-title {
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.5px;
    }

    .tlc-feeds-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    @media (max-width: 700px) {
      .tlc-feeds-grid {
        grid-template-columns: 1fr;
      }
    }

    .tlc-feed-cell {
      background: #0d1526;
      border-radius: 10px;
      overflow: hidden;
      aspect-ratio: 16/10;
      position: relative;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .tlc-feed-cell img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .tlc-feed-label {
      position: absolute;
      top: 8px;
      left: 10px;
      font-size: 11px;
      font-weight: 800;
      color: #fff;
      text-shadow: 0 1px 4px rgba(0, 0, 0, 0.7);
      letter-spacing: 0.5px;
    }

    .tlc-feed-dot {
      position: absolute;
      top: 10px;
      right: 10px;
      width: 8px;
      height: 8px;
      border-radius: 50%;
    }

    .tlc-feed-dot.live {
      background: #29B357;
      box-shadow: 0 0 6px #29B357;
    }

    .tlc-feed-dot.offline {
      background: #E03040;
    }

    .tlc-feed-offline {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      color: rgba(255, 255, 255, 0.3);
      font-size: 12px;
    }

    .tlc-feed-state-chip {
      position: absolute;
      bottom: 8px;
      left: 10px;
      font-size: 10px;
      font-weight: 800;
      padding: 3px 9px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    /* ── Log + Live status row ─────────────────────────── */
    .tlc-bottom-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-top: 16px;
    }

    @media (max-width: 900px) {
      .tlc-bottom-grid {
        grid-template-columns: 1fr;
      }
    }

    .tlc-log-entry {
      font-size: 11px;
      padding: 6px 0;
      border-bottom: 1px solid var(--border);
      color: var(--text-secondary);
    }

    .tlc-log-entry:last-child {
      border-bottom: none;
    }

    .tlc-log-entry.err {
      color: var(--red-text);
    }
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
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z" />
            <path d="M12 6v6l4 2" />
          </svg>
          AI Mode
        </button>
        <button class="tlc-btn mode-manual" id="btnModeManual" onclick="setMode('manual')" disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
            <path d="M9 18h6" />
          </svg>
          Manual
        </button>
        <button class="tlc-btn mode-hazard" id="btnModeHazard" onclick="setMode('hazard')" disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
          </svg>
          Hazard
        </button>
        <button class="tlc-btn mode-emergency" id="btnModeEmergency" onclick="setMode('emergency')" disabled>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 9v4M12 17h.01" />
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
          </svg>
          Emergency
        </button>
      </div>

      <div class="tlc-dpad-label">Lane Override (Manual Mode)</div>
      <div class="tlc-dpad">
        <button class="tlc-dpad-btn tlc-dpad-n" id="laneBtnNorth" onclick="setLane('NORTH')" disabled>NORTH</button>
        <button class="tlc-dpad-btn tlc-dpad-w" id="laneBtnWest" onclick="setLane('WEST')" disabled>WEST</button>
        <div class="tlc-dpad-center">LANE</div>
        <button class="tlc-dpad-btn tlc-dpad-e" id="laneBtnEast" onclick="setLane('EAST')" disabled>EAST</button>
        <button class="tlc-dpad-btn tlc-dpad-s" id="laneBtnSouth" onclick="setLane('SOUTH')" disabled>SOUTH</button>
      </div>

      <div class="tlc-system-status">
        <div>Mode: <span class="val" id="statMode">—</span></div>
        <div>Active Lane: <span class="val" id="statLane">—</span></div>
        <div>Rain Detected: <span class="val" id="statRain">—</span></div>
      </div>
    </div>

    {{-- RIGHT: Live feeds (large) --}}
    <div class="tlc-feeds-card">
      <div class="tlc-feeds-header">
        <span class="tlc-feeds-title">LIVE FEEDS</span>
        <span style="font-size:11px;color:rgba(255,255,255,0.6);" id="feedsCamCount">4 cameras</span>
      </div>
      <div class="tlc-feeds-grid">
        @foreach (['NORTH', 'SOUTH', 'EAST', 'WEST'] as $lane)
          <div class="tlc-feed-cell">
            <img id="feedImg{{ $lane }}" src="" alt="{{ $lane }}" style="display:none;">
            <div class="tlc-feed-offline" id="feedOffline{{ $lane }}">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10" />
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
              </svg>
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

  {{-- Bottom: Live status + Log --}}
  <div class="tlc-bottom-grid">
    <div class="stap-card">
      <div class="stap-card-header">
        <span class="stap-card-title">📡 LIVE STATUS</span>
      </div>
      <div class="stap-card-body" id="liveStatusBody" style="font-size:12px;color:var(--text-muted);">
        Cannot reach node
      </div>
    </div>

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
       Talks DIRECTLY to the Flask STAP Node (CORS enabled there)
       using the IP entered in the Node IP bar above.
       Mirrors ESP32 firmware modes exactly: AUTO / MANUAL / HAZARD / EMERGENCY
       and lanes: NORTH / SOUTH / EAST / WEST
       ============================================================ */

    let nodeIp = localStorage.getItem('stap_node_ip') || '';
    const useNodeProxy = window.location.protocol === 'https:';
    let nodeConnected = false;
    let currentMode = null;   // 'auto' | 'manual' | 'hazard' | 'emergency'
    let currentLane = null;   // 'NORTH' | 'SOUTH' | 'EAST' | 'WEST'
    let pollTimer = null;

    const LANES = ['NORTH', 'SOUTH', 'EAST', 'WEST'];

    function logLine(msg, isErr = false) {
      const body = document.getElementById('logBody');
      const time = new Date().toLocaleTimeString();
      const div = document.createElement('div');
      div.className = 'tlc-log-entry' + (isErr ? ' err' : '');
      div.textContent = `[${time}] ${msg}`;
      body.prepend(div);
      while (body.children.length > 50) body.removeChild(body.lastChild);
    }

    function clearLog() {
      document.getElementById('logBody').innerHTML = '';
    }

    function applyNodeIp() {
      const ip = document.getElementById('nodeIpInput').value.trim();
      if (!ip) return;
      localStorage.setItem('stap_node_ip', ip);
      nodeIp = ip;
      logLine(`Node IP set to ${ip}. Connecting…`);
      wireFeeds();
      pollStatus();
    }

    // Restore saved IP into input on load
    (function initIp() {
      const saved = localStorage.getItem('stap_node_ip');
      if (saved) document.getElementById('nodeIpInput').value = saved;
    })();

    function setConnBadge(online) {
      nodeConnected = online;
      const badge = document.getElementById('connBadge');
      const text = document.getElementById('connBadgeText');
      badge.className = 'tlc-conn-badge ' + (online ? 'online' : 'offline');
      text.textContent = online ? 'Node Connected' : 'Node Disconnected';

      ['btnModeAuto', 'btnModeManual', 'btnModeHazard', 'btnModeEmergency'].forEach(id => {
        document.getElementById(id).disabled = !online;
      });
      updateLaneButtonsEnabled();
    }

    function updateLaneButtonsEnabled() {
      const enabled = nodeConnected && currentMode === 'manual';
      LANES.forEach(lane => {
        document.getElementById('laneBtn' + capitalize(lane)).disabled = !enabled;
      });
    }

    function capitalize(s) { return s.charAt(0) + s.slice(1).toLowerCase(); }

    function buildNodeUrl(path) {
      if (!nodeIp) return null;

      if (useNodeProxy)
      {
        const joiner = path.includes('?') ? '&' : '?';
        return `${window.location.origin}/admin/traffic-lights/proxy${path}${joiner}node_ip=${encodeURIComponent(nodeIp)}`;
      }

      return `http://${nodeIp}:5000${path}`;
    }

    async function callControl(path, body) {
      const url = buildNodeUrl(path);
      if (!url)
      {
        logLine('No Node IP set.', true);
        return null;
      }
      try
      {
        const res = await fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ payload: body || {} }),
        });
        const data = await res.json();
        if (!res.ok || data.success === false)
        {
          logLine(data.message || `Request to ${path} failed.`, true);
          return null;
        }
        return data;
      } catch (e)
      {
        logLine(`Connection failed: ${e.message}`, true);
        setConnBadge(false);
        return null;
      }
    }

    // ── Mode buttons: AI / Manual / Hazard / Emergency ─────────
    // All four modes are simple "set system mode" calls — Hazard and
    // Emergency both flash all four lanes red, no lane needs to be picked.
    async function setMode(mode) {
      const data = await callControl('/control/mode', { mode });
      if (!data) return;
      logLine(`Mode set to ${mode.toUpperCase()}.`);
      currentMode = mode;
      if (mode !== 'manual') currentLane = null;
      refreshModeButtons();
      refreshLaneButtons();
      updateLaneButtonsEnabled();
    }

    // ── Lane D-pad: only active in Manual mode ─────────────────
    async function setLane(lane) {
      if (currentMode !== 'manual')
      {
        logLine('Switch to Manual mode before setting a lane.', true);
        return;
      }
      const data = await callControl('/control/light', { lane, state: 'green' });
      if (!data) return;
      logLine(`Lane ${lane} set to GREEN (manual).`);
      currentLane = lane;
      refreshLaneButtons();
    }

    function refreshModeButtons() {
      document.getElementById('btnModeAuto').classList.toggle('active', currentMode === 'auto');
      document.getElementById('btnModeManual').classList.toggle('active', currentMode === 'manual');
      document.getElementById('btnModeHazard').classList.toggle('active', currentMode === 'hazard');
      document.getElementById('btnModeEmergency').classList.toggle('active', currentMode === 'emergency');
      document.getElementById('statMode').textContent = currentMode ? currentMode.toUpperCase() : '—';
    }

    function refreshLaneButtons() {
      LANES.forEach(lane => {
        document.getElementById('laneBtn' + capitalize(lane)).classList.toggle('lane-active', currentLane === lane);
      });
      document.getElementById('statLane').textContent = currentLane || '—';
    }

    // ── Status polling: /status endpoint ───────────────────────
    async function pollStatus() {
      const url = buildNodeUrl('/status');
      if (!url) return;
      try
      {
        const res = await fetch(url, { method: 'GET' });
        if (!res.ok) throw new Error('Bad response');
        const data = await res.json();

        if (!nodeConnected) logLine('Connected to STAP Node.');
        setConnBadge(true);

        currentMode = data.mode; // 'auto' | 'manual' | 'hazard' | 'emergency'
        currentLane = data.active_lane;
        refreshModeButtons();
        refreshLaneButtons();
        updateLaneButtonsEnabled();

        document.getElementById('statRain').textContent = data.rain ? 'Yes' : 'No';

        renderLiveStatus(data);
        renderFeedChips(data);

      } catch (e)
      {
        if (nodeConnected) logLine('Lost connection to STAP Node.', true);
        setConnBadge(false);
        document.getElementById('liveStatusBody').textContent = 'Cannot reach node';
      }
    }

    function renderLiveStatus(data) {
      const body = document.getElementById('liveStatusBody');
      const counts = data.vehicle_counts || {};
      const los = data.los || {};
      const statuses = data.lane_statuses || {};

      body.innerHTML = LANES.map(lane => {
        const isEmg = statuses[lane] === 'EMERGENCY';
        return `<div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border);font-size:12px;">
              <span style="font-weight:700;${isEmg ? 'color:var(--red);' : ''}">${lane}${isEmg ? ' 🚨' : ''}</span>
              <span>Queue: <strong>${counts[lane] ?? '—'}</strong> · LOS: <strong>${los[lane] ?? '—'}</strong></span>
          </div>`;
      }).join('') + `<div style="margin-top:8px;font-size:11px;color:var(--text-muted);">
          Active: <strong>${data.active_lane ?? '—'}</strong> · Phase: <strong>${data.phase_state ?? '—'}</strong> · ${data.remaining_secs ?? 0}s remaining
      </div>`;
    }

    function renderFeedChips(data) {
      const statuses = data.lane_statuses || {};
      LANES.forEach(lane => {
        const chip = document.getElementById('feedChip' + lane);
        const s = statuses[lane];
        if (s === 'EMERGENCY')
        {
          chip.textContent = 'EMERGENCY';
          chip.style.background = 'var(--red)'; chip.style.color = '#fff';
          chip.style.display = 'inline-block';
        } else if (s === 'VEHICLE')
        {
          chip.textContent = 'VEHICLE';
          chip.style.background = 'var(--amber)'; chip.style.color = '#3a2d12';
          chip.style.display = 'inline-block';
        } else
        {
          chip.style.display = 'none';
        }
      });
    }

    // ── MJPEG feed wiring ───────────────────────────────────────
    function wireFeeds() {
      LANES.forEach(lane => {
        const img = document.getElementById('feedImg' + lane);
        const offline = document.getElementById('feedOffline' + lane);
        const dot = document.getElementById('feedDot' + lane);
        const url = buildNodeUrl(`/video_feed/${lane.toLowerCase()}`);

        if (!url)
        {
          img.style.display = 'none';
          offline.style.display = 'flex';
          dot.className = 'tlc-feed-dot offline';
          return;
        }

        img.src = url;
        img.onload = () => { img.style.display = 'block'; offline.style.display = 'none'; dot.className = 'tlc-feed-dot live'; };
        img.onerror = () => { img.style.display = 'none'; offline.style.display = 'flex'; dot.className = 'tlc-feed-dot offline'; };
      });
    }

    // ── Boot ──────────────────────────────────────────────────
    if (nodeIp)
    {
      wireFeeds();
      pollStatus();
    }
    pollTimer = setInterval(() => { if (nodeIp) pollStatus(); }, 2000);
  </script>
@endpush
