@extends('layouts.admin')
@section('title', 'Traffic Light Control')
@section('page-title', 'Traffic Light Control')

@push('styles')
  <style>
    /* ── Base ─────────────────────────────────────────────────── */
    .tl-page {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    /* ── Node IP Bar ─────────────────────────────────────────── */
    .tl-ip-bar {
      background: #1a1a2e;
      border: 1px solid rgba(255, 255, 255, .08);
      border-radius: 12px;
      padding: 10px 18px;
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      font-size: 12px;
      color: rgba(255, 255, 255, .6);
    }

    .tl-ip-bar input {
      background: rgba(255, 255, 255, .07);
      border: 1.5px solid rgba(255, 255, 255, .15);
      border-radius: 7px;
      padding: 5px 12px;
      font-size: 12px;
      font-family: monospace;
      width: clamp(100px, 18vw, 160px);
      min-width: 0;
      color: #fff;
      outline: none;
    }

    .tl-ip-bar input:focus {
      border-color: rgba(255, 255, 255, .4);
    }

    .tl-ip-bar button {
      background: rgba(255, 255, 255, .1);
      color: #fff;
      border: 1px solid rgba(255, 255, 255, .15);
      border-radius: 7px;
      padding: 5px 14px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: background .15s;
    }

    .tl-ip-bar button:hover {
      background: rgba(255, 255, 255, .18);
    }

    .tl-node-indicator {
      margin-left: auto;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 700;
      color: rgba(255, 255, 255, .7);
      white-space: nowrap;
    }

    .tl-node-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #475569;
      transition: background .3s;
    }

    .tl-node-dot.connected {
      background: #22c55e;
      box-shadow: 0 0 0 3px rgba(34, 197, 94, .25);
    }

    .tl-node-dot.disconnected {
      background: #ef4444;
    }

    /* ── Main Layout ─────────────────────────────────────────── */
    .tl-main {
      display: grid;
      grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
      gap: 1.25rem;
      align-items: start;
    }

    .tl-main>* {
      min-width: 0;
    }

    /* ── Bottom Row ──────────────────────────────────────────── */
    .tl-bottom {
      display: grid;
      grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
      gap: 1.25rem;
      align-items: start;
    }

    .tl-bottom>* {
      min-width: 0;
    }

    @media (max-width: 900px) {
      .tl-main {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 600px) {

      .tl-main,
      .tl-bottom {
        grid-template-columns: 1fr;
        gap: 1rem;
      }

      .tl-ip-bar {
        padding: 8px 12px;
        gap: 8px;
      }

      .tl-ip-bar input {
        flex: 1 1 120px;
      }

      .tl-node-indicator {
        margin-left: 0;
        width: 100%;
      }

      .tl-card-body {
        padding: .875rem;
      }
    }

    /* ── Card ────────────────────────────────────────────────── */
    .tl-card {
      background: #fff;
      border-radius: 14px;
      border: 1px solid rgba(15, 23, 42, .08);
      box-shadow: 0 4px 18px rgba(15, 23, 42, .07);
      overflow: hidden;
      min-width: 0;
    }

    .tl-card-header {
      padding: .8rem 1.2rem;
      border-bottom: 1px solid rgba(15, 23, 42, .07);
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

    .tl-card-body {
      padding: 1.2rem;
      overflow: hidden;
    }

    /* ── CONTROL BOX panel ───────────────────────────────────── */
    .tl-controlbox {
      background: #d1d5db;
      border-radius: 16px;
      border: 3px solid #9ca3af;
      padding: 1.5rem 1.25rem 1.25rem;
      box-shadow: inset 0 2px 6px rgba(0, 0, 0, .15), 0 8px 24px rgba(0, 0, 0, .12);
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
      overflow: hidden;
      min-width: 0;
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
      flex-wrap: wrap;
    }

    /* Square MANUAL button */
    .tl-btn-manual {
      width: 64px;
      height: 64px;
      border-radius: 10px;
      background: linear-gradient(145deg, #374151, #1f2937);
      border: 3px solid #111827;
      box-shadow: 0 4px 0 #111, inset 0 1px 0 rgba(255, 255, 255, .1);
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 3px;
      color: rgba(255, 255, 255, .6);
      font-size: .6rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .05em;
      transition: all .12s;
    }

    .tl-btn-manual:hover {
      transform: translateY(-1px);
      box-shadow: 0 5px 0 #111, inset 0 1px 0 rgba(255, 255, 255, .15);
    }

    .tl-btn-manual:active {
      transform: translateY(2px);
      box-shadow: 0 2px 0 #111;
    }

    .tl-btn-manual.active {
      background: linear-gradient(145deg, #1e3a5f, #1e40af);
      border-color: #1d4ed8;
      color: #fff;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, .4), 0 4px 0 #1e3a5f;
    }

    /* Round colored mode buttons */
    .tl-btn-round {
      width: clamp(52px, 8vw, 68px);
      height: clamp(52px, 8vw, 68px);
      border-radius: 50%;
      border: 3px solid transparent;
      box-shadow: 0 4px 0 rgba(0, 0, 0, .35), inset 0 1px 0 rgba(255, 255, 255, .25);
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 3px;
      font-size: .52rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .03em;
      color: rgba(255, 255, 255, .85);
      transition: all .12s;
      overflow: hidden;
      padding: 4px;
      text-align: center;
      line-height: 1.1;
    }

    /* Round Manual button */
    .tl-btn-manual-round {
      background: linear-gradient(145deg, #374151, #1f2937);
      border-color: #111827;
      box-shadow: 0 4px 0 #111, inset 0 1px 0 rgba(255, 255, 255, .1);
      color: rgba(255, 255, 255, .75);
    }

    .tl-btn-manual-round:hover {
      transform: translateY(-2px);
      filter: brightness(1.1);
    }

    .tl-btn-manual-round:active {
      transform: translateY(2px);
      box-shadow: 0 1px 0 #111;
    }

    .tl-btn-manual-round.active {
      background: linear-gradient(145deg, #1e3a5f, #1e40af);
      border-color: #1d4ed8;
      color: #fff;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, .4), 0 4px 0 #1e3a5f;
    }

    .tl-btn-round:hover {
      transform: translateY(-2px);
      filter: brightness(1.1);
    }

    .tl-btn-round:active {
      transform: translateY(2px);
      box-shadow: 0 1px 0 rgba(0, 0, 0, .35);
    }

    .tl-btn-stap {
      background: radial-gradient(circle at 35% 35%, #60a5fa, #2563eb);
      border-color: #1d4ed8;
      box-shadow: 0 4px 0 #1e3a5f, inset 0 1px 0 rgba(255, 255, 255, .25);
    }

    .tl-btn-stap.active {
      box-shadow: 0 0 0 4px rgba(59, 130, 246, .45), 0 4px 0 #1e3a5f;
    }

    .tl-btn-hazard {
      background: radial-gradient(circle at 35% 35%, #fb923c, #ea580c);
      border-color: #c2410c;
    }

    .tl-btn-hazard.active {
      box-shadow: 0 0 0 4px rgba(249, 115, 22, .45), 0 4px 0 #7c2d12;
    }

    .tl-btn-emergency-mode {
      background: radial-gradient(circle at 35% 35%, #f87171, #dc2626);
      border-color: #b91c1c;
    }

    .tl-btn-emergency-mode.active {
      box-shadow: 0 0 0 4px rgba(239, 68, 68, .45), 0 4px 0 #7f1d1d;
      animation: emergencyPulse 1s infinite;
    }

    /* ── STAP / Manual Active Indicator ──────────────────────── */
    .tl-stap-indicator {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      font-size: .62rem;
      font-weight: 900;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: #16a34a;
      background: #dcfce7;
      border: 1px solid #86efac;
      border-radius: 20px;
      padding: 3px 10px;
      visibility: hidden;
    }

    .tl-stap-indicator.on {
      visibility: visible;
    }

    .tl-stap-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #22c55e;
      box-shadow: 0 0 0 3px rgba(34, 197, 94, .3);
      animation: stapPulse 1.4s infinite;
    }

    @keyframes stapPulse {

      0%,
      100% {
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .25);
      }

      50% {
        box-shadow: 0 0 0 6px rgba(34, 197, 94, .08);
      }
    }

    @keyframes emergencyPulse {

      0%,
      100% {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, .45), 0 4px 0 #7f1d1d;
      }

      50% {
        box-shadow: 0 0 0 8px rgba(239, 68, 68, .2), 0 4px 0 #7f1d1d;
      }
    }

    /* ── Lane Buttons ────────────────────────────────────────── */
    .tl-lane-compass {
      display: grid;
      grid-template-columns: 1fr;
      gap: .5rem;
      width: 100%;
    }

    .tl-lane-btn {
      border-radius: 10px;
      background: #f8fafc;
      border: 2px solid #cbd5e1;
      box-shadow: 0 2px 0 rgba(15, 23, 42, .08);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .4rem;
      padding: .5rem .65rem;
      color: #334155;
      transition: all .12s;
      width: 100%;
      min-width: 0;
    }

    .tl-lane-btn-main {
      display: flex;
      align-items: center;
      gap: .4rem;
      min-width: 0;
      flex-shrink: 0;
    }

    .tl-lane-btn-arrow {
      font-size: 1rem;
      line-height: 1;
      flex-shrink: 0;
    }

    .tl-lane-btn-name {
      font-size: .72rem;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: .04em;
      white-space: nowrap;
    }

    .tl-lane-btn-status {
      display: flex;
      align-items: center;
      gap: .3rem;
      font-size: .65rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .04em;
      flex-shrink: 0;
    }

    .tl-lane-btn-dot {
      width: 9px;
      height: 9px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .tl-lane-btn:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 3px 0 rgba(15, 23, 42, .1);
    }

    .tl-lane-btn:active:not(:disabled) {
      transform: translateY(1px);
      box-shadow: 0 1px 0 rgba(15, 23, 42, .08);
    }

    .tl-lane-btn:disabled {
      opacity: .5;
      cursor: not-allowed;
    }

    .tl-lane-btn.active-green {
      background: #f0fdf4;
      border-color: #22c55e;
    }

    .tl-lane-btn.active-green .tl-lane-btn-dot {
      background: #22c55e;
      box-shadow: 0 0 0 3px rgba(34, 197, 94, .25);
    }

    .tl-lane-btn.active-green .tl-lane-btn-status {
      color: #15803d;
    }

    .tl-lane-btn.active-red {
      background: #fef2f2;
      border-color: #ef4444;
    }

    .tl-lane-btn.active-red .tl-lane-btn-dot {
      background: #ef4444;
      box-shadow: 0 0 0 3px rgba(239, 68, 68, .2);
    }

    .tl-lane-btn.active-red .tl-lane-btn-status {
      color: #b91c1c;
    }

    .tl-lane-btn.active-yellow {
      background: #fefce8;
      border-color: #eab308;
    }

    .tl-lane-btn.active-yellow .tl-lane-btn-dot {
      background: #eab308;
      box-shadow: 0 0 0 3px rgba(234, 179, 8, .2);
    }

    .tl-lane-btn.active-yellow .tl-lane-btn-status {
      color: #a16207;
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
      grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
      gap: .75rem;
    }

    .tl-cam-cell {
      border-radius: 10px;
      overflow: hidden;
      background: #0f172a;
      border: 1px solid rgba(255, 255, 255, .06);
      position: relative;
      aspect-ratio: 16/9;
    }

    .tl-cam-cell img.mjpeg {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: none;
    }

    .tl-cam-offline {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
      color: rgba(255, 255, 255, .25);
      font-size: .65rem;
      font-weight: 600;
      text-align: center;
    }

    .tl-cam-dir-tag {
      position: absolute;
      top: 5px;
      left: 5px;
      background: rgba(15, 23, 42, .75);
      color: rgba(255, 255, 255, .9);
      font-size: .6rem;
      font-weight: 800;
      padding: 2px 7px;
      border-radius: 5px;
      letter-spacing: .06em;
      backdrop-filter: blur(4px);
    }

    .tl-cam-status-dot {
      position: absolute;
      top: 6px;
      right: 6px;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #ef4444;
    }

    .tl-cam-status-dot.online {
      background: #22c55e;
      box-shadow: 0 0 0 3px rgba(34, 197, 94, .2);
    }

    /* ── Sidebar ─────────────────────────────────────────────── */
    .tl-sidebar {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .tl-status-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 6px 0;
      border-bottom: 1px solid #f1f5f9;
      font-size: 12px;
    }

    .tl-status-row:last-child {
      border-bottom: none;
    }

    .tl-status-label {
      color: #64748b;
      font-weight: 600;
    }

    .tl-status-value {
      font-weight: 700;
      color: #0f172a;
    }

    .tl-phase-badge {
      padding: 2px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
    }

    .tl-phase-green {
      background: #dcfce7;
      color: #166534;
    }

    .tl-phase-yellow {
      background: #fef3c7;
      color: #92400e;
    }

    .tl-phase-red {
      background: #fee2e2;
      color: #991b1b;
    }

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

    .tl-log-entry.success {
      border-left-color: #22c55e;
    }

    .tl-log-entry.error {
      border-left-color: #ef4444;
    }

    .tl-log-entry.info {
      border-left-color: #3b82f6;
    }

    /* ── Toast ───────────────────────────────────────────────── */
    .tl-toast {
      position: fixed;
      bottom: 1.5rem;
      right: 1.5rem;
      background: #0f172a;
      color: #fff;
      padding: .75rem 1.25rem;
      border-radius: .75rem;
      font-size: .85rem;
      font-weight: 600;
      box-shadow: 0 8px 24px rgba(15, 23, 42, .3);
      z-index: 9999;
      transform: translateY(130%);
      transition: transform .25s;
      max-width: 280px;
    }

    .tl-toast.show {
      transform: translateY(0);
    }

    .tl-toast.toast-err {
      background: #991b1b;
    }

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

      0%,
      100% {
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .2);
      }

      50% {
        box-shadow: 0 0 0 6px rgba(34, 197, 94, .06);
      }
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
          <div class="tl-card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <span class="tl-card-title">Control Panel</span>
              <span style="font-size:.72rem;color:#94a3b8; display: block;" id="modeHint">Select a mode</span>
            </div>

            {{-- Back Button (Hidden Initially) --}}
            <button id="btn-back-to-modes" onclick="resetToSystemModes()"
              style="display: none; background: #334155; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: background 0.2s;">
              &larr; Back to System Modes
            </button>
          </div>

          <div class="tl-card-body">
            <div class="tl-controlbox">

              {{-- VIEW 1: Initial System Modes (Only Manual & STAP Root) --}}
              <div id="main-mode-selection">
                <div class="tl-controlbox-label">System Mode</div>
                <div class="tl-mode-row" style="display: flex; justify-content: center; gap: 20px;">

                  {{-- Manual Entry Button --}}
                  <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
                    <div class="tl-stap-indicator" id="manualIndicator">
                      <span class="tl-stap-dot"></span>
                      <span>ON</span>
                    </div>
                    <button class="tl-btn-round tl-btn-manual-round" id="btn-manual-root" onclick="switchToManualMode()"
                      title="Manual Override">
                      <span style="font-size:1.1rem;">&#129489;&#8205;&#9992;&#65039;</span>
                      <span style="font-size:.52rem;font-weight:800;letter-spacing:.04em;">MANUAL</span>
                    </button>
                  </div>

                  {{-- STAP Entry Button --}}
                  <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
                    <div class="tl-stap-indicator" id="stapIndicator">
                      <span class="tl-stap-dot"></span>
                      <span>ON</span>
                    </div>
                    <button class="tl-btn-round tl-btn-stap" id="btn-stap-root" onclick="activateStap()"
                      title="Activate STAP Auto Mode">
                      <span style="font-size:1.1rem;">&#129302;</span>
                      <span style="font-size:.52rem;font-weight:800;letter-spacing:.04em;">STAP</span>
                    </button>
                  </div>

                </div>
              </div>

              {{-- VIEW 2: STAP Sub-Modes Grid - Removed, STAP now activates instantly via activateStap() --}}

              {{-- VIEW 3: Manual Mode - Hazard, Emergency & Lane Control - Hidden Initially --}}
              <div id="lane-control-section" style="display: none;">

                <div class="tl-controlbox-label">Manual Controls</div>
                <div class="tl-mode-row" style="display: flex; justify-content: center; gap: 20px; margin-bottom: 1rem;">

                  <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
                    <button class="tl-btn-round tl-btn-hazard" id="btn-hazard" onclick="setMode('hazard')"
                      title="Hazard Mode">
                      <span style="font-size:1.1rem;">&#9888;&#65039;</span>
                      <span style="font-size:.52rem;font-weight:800;letter-spacing:.03em;">HAZARD</span>
                    </button>
                  </div>

                  <div style="display:flex;flex-direction:column;align-items:center;gap:5px;">
                    <button class="tl-btn-round tl-btn-emergency-mode" id="btn-emergency-panel"
                      onclick="showEmergencyPicker()" title="Emergency Override">
                      <span style="font-size:1.1rem;">&#128680;</span>
                      <span style="font-size:.52rem;font-weight:800;letter-spacing:.03em;">EMERGENCY</span>
                    </button>
                  </div>

                </div>

                <div class="tl-divider" style="margin-bottom: 1rem;"></div>

                <div>
                  <div class="tl-controlbox-label">Lane Control</div>
                  <div class="tl-lane-compass">
                    <button class="tl-lane-btn" id="laneBtn-NORTH" data-lane="NORTH"
                      onclick="cycleLaneLight(this.dataset.lane)" disabled>
                      <span class="tl-lane-btn-main">
                        <span class="tl-lane-btn-arrow">&#8593;</span>
                        <span class="tl-lane-btn-name">North</span>
                      </span>
                      <span class="tl-lane-btn-status">
                        <span class="tl-lane-btn-dot"></span>
                        <span id="laneState-NORTH">RED</span>
                      </span>
                    </button>
                    <button class="tl-lane-btn" id="laneBtn-SOUTH" data-lane="SOUTH"
                      onclick="cycleLaneLight(this.dataset.lane)" disabled>
                      <span class="tl-lane-btn-main">
                        <span class="tl-lane-btn-arrow">&#8595;</span>
                        <span class="tl-lane-btn-name">South</span>
                      </span>
                      <span class="tl-lane-btn-status">
                        <span class="tl-lane-btn-dot"></span>
                        <span id="laneState-SOUTH">RED</span>
                      </span>
                    </button>
                    <button class="tl-lane-btn" id="laneBtn-WEST" data-lane="WEST"
                      onclick="cycleLaneLight(this.dataset.lane)" disabled>
                      <span class="tl-lane-btn-main">
                        <span class="tl-lane-btn-arrow">&#8592;</span>
                        <span class="tl-lane-btn-name">West</span>
                      </span>
                      <span class="tl-lane-btn-status">
                        <span class="tl-lane-btn-dot"></span>
                        <span id="laneState-WEST">RED</span>
                      </span>
                    </button>
                    <button class="tl-lane-btn" id="laneBtn-EAST" data-lane="EAST"
                      onclick="cycleLaneLight(this.dataset.lane)" disabled>
                      <span class="tl-lane-btn-main">
                        <span class="tl-lane-btn-arrow">&#8594;</span>
                        <span class="tl-lane-btn-name">East</span>
                      </span>
                      <span class="tl-lane-btn-status">
                        <span class="tl-lane-btn-dot"></span>
                        <span id="laneState-EAST">RED</span>
                      </span>
                    </button>
                  </div>
                  <div class="tl-mode-hint" style="margin-top:.75rem;" id="laneHint">Manual mode activated. Click
                    directional nodes to route traffic.</div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      {{-- Col 2: Camera Feeds --}}
      <div class="tl-card" style="background:#0f172a;">
        <div class="tl-card-header" style="background:#0f172a;border-bottom-color:rgba(255,255,255,.07);">
          <span class="tl-card-title" style="color:#fff;">Live Feeds</span>
          <div style="display:flex;align-items:center;gap:.4rem;">
            <span
              style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;animation:livePulse 1.6s infinite;"></span>
            <span style="font-size:.72rem;color:rgba(255,255,255,.5);">4 cameras</span>
          </div>
        </div>
        <div class="tl-card-body" style="padding:.875rem;">
          <div class="tl-cam-grid">

            <div class="tl-cam-cell">
              <img class="mjpeg" id="stream-north" src="" alt="NORTH" onerror="handleStreamError(this)">
              <div class="tl-cam-offline" id="offline-north">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                </svg>
                <span>Offline</span>
              </div>
              <span class="tl-cam-dir-tag">NORTH</span>
              <span class="tl-cam-status-dot" id="camDot-north"></span>
            </div>

            <div class="tl-cam-cell">
              <img class="mjpeg" id="stream-south" src="" alt="SOUTH" onerror="handleStreamError(this)">
              <div class="tl-cam-offline" id="offline-south">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                </svg>
                <span>Offline</span>
              </div>
              <span class="tl-cam-dir-tag">SOUTH</span>
              <span class="tl-cam-status-dot" id="camDot-south"></span>
            </div>

            <div class="tl-cam-cell">
              <img class="mjpeg" id="stream-east" src="" alt="EAST" onerror="handleStreamError(this)">
              <div class="tl-cam-offline" id="offline-east">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                </svg>
                <span>Offline</span>
              </div>
              <span class="tl-cam-dir-tag">EAST</span>
              <span class="tl-cam-status-dot" id="camDot-east"></span>
            </div>

            <div class="tl-cam-cell">
              <img class="mjpeg" id="stream-west" src="" alt="WEST" onerror="handleStreamError(this)">
              <div class="tl-cam-offline" id="offline-west">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                </svg>
                <span>Offline</span>
              </div>
              <span class="tl-cam-dir-tag">WEST</span>
              <span class="tl-cam-status-dot" id="camDot-west"></span>
            </div>

          </div>
        </div>
      </div>
    </div>

    {{-- Bottom panel: Live status and Log --}}
    <div class="tl-bottom">

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
          <button onclick="clearLog()"
            style="font-size:.68rem;color:#94a3b8;background:none;border:none;cursor:pointer;">Clear</button>
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
  <div id="emergencyModal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9998;align-items:center;justify-content:center;">
    <div
      style="background:#fff;border-radius:16px;padding:2rem;max-width:340px;width:90%;text-align:center;box-shadow:0 24px 48px rgba(0,0,0,.3);">
      <div style="font-size:2rem;margin-bottom:.5rem;">&#128680;</div>
      <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:.35rem;">Emergency Override</div>
      <div style="font-size:.82rem;color:#64748b;margin-bottom:1.25rem;">Select the lane that needs immediate green light
        access.</div>
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
    let NODE_IP = localStorage.getItem('stap_node_ip') || '192.168.1.100';
    let curMode = 'manual';
    const LANES = ['NORTH', 'SOUTH', 'EAST', 'WEST'];
    const DIRS = ['north', 'south', 'east', 'west'];
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
      curMode = mode;
      updateModeUI(mode);

      try
      {
        const res = await postNode('/control/mode', { mode });
        const data = await res.json();
        if (data.success)
        {
          logActivity('Mode switched to ' + mode.toUpperCase(), 'success');
          showToast('Mode: ' + mode.toUpperCase());
        } else
        {
          showToast(data.message || 'Failed', true);
          logActivity('Mode switch failed: ' + (data.message || ''), 'error');
        }
      } catch (e)
      {
        showToast('Node unreachable', true);
        logActivity('Cannot reach STAP Node', 'error');
      }
    }

    function updateModeUI(mode) {
      ['btn-manual-root', 'btn-stap-root', 'btn-hazard', 'btn-emergency-panel'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('active');
      });

      if (mode === 'manual') { var el = document.getElementById('btn-manual-root'); if (el) el.classList.add('active'); }
      if (mode === 'auto') { var el = document.getElementById('btn-stap-root'); if (el) el.classList.add('active'); }
      if (mode === 'hazard') { var el = document.getElementById('btn-hazard'); if (el) el.classList.add('active'); }

      var stapIndicator = document.getElementById('stapIndicator');
      if (stapIndicator) stapIndicator.classList.toggle('on', mode === 'auto');

      var manualIndicator = document.getElementById('manualIndicator');
      if (manualIndicator) manualIndicator.classList.toggle('on', mode === 'manual' || mode === 'hazard');

      const isManual = mode === 'manual' || mode === 'hazard';
      LANES.forEach(function (lane) {
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
      try
      {
        const res = await postNode('/control/light', { lane: lane, state: state });
        const data = await res.json();
        if (data.success)
        {
          laneStates[lane] = state;
          updateLaneBtn(lane, state);
          logActivity(lane + ' set to ' + state.toUpperCase(), 'success');
          showToast(lane + ': ' + state.toUpperCase());
        } else
        {
          showToast(data.message || 'Failed', true);
          logActivity(lane + ' failed: ' + (data.message || ''), 'error');
        }
      } catch (e)
      {
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
      try
      {
        const res = await postNode('/control/emergency', { lane: lane });
        const data = await res.json();
        if (data.success)
        {
          curMode = 'auto';
          updateModeUI('auto');
          document.getElementById('btn-emergency-panel').classList.add('active');
          logActivity('EMERGENCY OVERRIDE: ' + lane + ' has priority', 'error');
          showToast('Emergency: ' + lane + ' priority lane');
        } else
        {
          showToast(data.message || 'Failed', true);
        }
      } catch (e)
      {
        showToast('Node unreachable', true);
        logActivity('Cannot reach STAP Node', 'error');
      }
    }

    function loadStreams() {
      DIRS.forEach(function (dir) {
        var img = document.getElementById('stream-' + dir);
        var offline = document.getElementById('offline-' + dir);
        var dot = document.getElementById('camDot-' + dir);

        img.onload = function () {
          img.style.display = 'block';
          offline.style.display = 'none';
          dot.classList.add('online');
        };

        img.onerror = function () { handleStreamError(img); };
        img.src = 'http://' + NODE_IP + ':5000/video_feed/' + dir;
      });
    }

    function handleStreamError(img) {
      var dir = img.id.replace('stream-', '');
      var offline = document.getElementById('offline-' + dir);
      var dot = document.getElementById('camDot-' + dir);

      img.style.display = 'none';
      offline.style.display = 'flex';
      dot.classList.remove('online');

      setTimeout(function () {
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
      try
      {
        const res = await fetch('http://' + NODE_IP + ':5000/status', { signal: AbortSignal.timeout(2000) });
        const data = await res.json();

        setNodeConnected(true);
        renderStatusPanel(data);
        document.getElementById('lastPoll').textContent = new Date().toLocaleTimeString();

        if (data.mode && data.mode !== curMode)
        {
          curMode = data.mode;
          updateModeUI(data.mode);
        }
      } catch (e)
      {
        setNodeConnected(false);
        document.getElementById('statusPanel').innerHTML =
          '<div style="color:#ef4444;font-size:12px;text-align:center;padding:8px 0;">Cannot reach node</div>';
      }
    }

    function renderStatusPanel(data) {
      var phaseClass = data.phase_state === 'GREEN' ? 'tl-phase-green'
        : data.phase_state === 'YELLOW' ? 'tl-phase-yellow'
          : 'tl-phase-red';

      var losColors = { A: '#166534', B: '#166534', C: '#92400e', D: '#92400e', E: '#991b1b', F: '#991b1b' };

      var rows = '';
      LANES.forEach(function (lane) {
        var count = (data.vehicle_counts && data.vehicle_counts[lane] !== undefined) ? data.vehicle_counts[lane] : 0;
        var los = (data.los && data.los[lane]) ? data.los[lane] : '?';
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
      document.getElementById('nodeConnDot').className = 'tl-node-dot ' + (ok ? 'connected' : 'disconnected');
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
      var log = document.getElementById('activityLog');
      var entry = document.createElement('div');
      entry.className = 'tl-log-entry ' + type;
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
      toastTimer = setTimeout(function () { t.classList.remove('show'); }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function () {
      updateModeUI(curMode);
      if (NODE_IP)
      {
        loadStreams();
        startStatusPolling();
      }
    });

    /* ==========================================================================
        INTERACTIVE NAVIGATION CONTROL 
       ========================================================================== */
    const mainModeSelection = document.getElementById('main-mode-selection');
    const laneControlSection = document.getElementById('lane-control-section');
    const backButton = document.getElementById('btn-back-to-modes');
    const modeHint = document.getElementById('modeHint');

    function switchToManualMode() {
      displayManualView();
      setMode('manual');
    }

    async function activateStap() {
      await setMode('auto');
      logActivity('STAP Adaptive Traffic Automation System is ON', 'success');
      showToast('STAP Adaptive Traffic Automation System is ON');
    }

    function displayManualView() {
      mainModeSelection.style.display = 'none';
      laneControlSection.style.display = 'block';
      backButton.style.display = 'block';
    }

    function resetToSystemModes() {
      mainModeSelection.style.display = 'block';
      laneControlSection.style.display = 'none';
      backButton.style.display = 'none';

      modeHint.textContent = "Select a mode";
      modeHint.style.color = "#94a3b8";

      LANES.forEach(function (lane) {
        document.getElementById('laneBtn-' + lane).disabled = true;
      });
    }
  </script>
@endpush
