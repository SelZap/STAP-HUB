@extends('layouts.public')
@section('title', 'Live Camera Feed')
@section('page-title', 'Live Camera Feed')

@push('styles')
<style>
    .feed-layout {
        display: grid;
        grid-template-columns: 1fr 290px;
        gap: 1.25rem;
        align-items: start;
    }

    @media (max-width: 900px) {
        .feed-layout { grid-template-columns: 1fr; }
        .feed-sidebar { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    }

    @media (max-width: 600px) {
        .feed-sidebar { grid-template-columns: 1fr; }
    }

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
        letter-spacing: 0.03em;
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
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        position: relative;
        overflow: hidden;
        display: grid;
        place-items: center;
    }

    .feed-card-media iframe,
    .feed-card-media video,
    .feed-card-media img {
        width: 100%; height: 100%; object-fit: cover; border: 0;
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
    }

    .feed-card-body {
        padding: .75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .feed-card-name {
        font-size: .9rem;
        font-weight: 700;
        color: #0f172a;
    }

    .feed-card-node {
        font-size: .78rem;
        color: #64748b;
        margin-top: 1px;
    }

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

    /* Sidebar */
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

    .feed-info-body {
        padding: .875rem 1rem;
    }

    .feed-intersection-name {
        font-size: .95rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.35;
        margin-bottom: .25rem;
    }

    .feed-intersection-sub {
        font-size: .82rem;
        color: #64748b;
    }

    .feed-cam-index {
        display: grid;
        gap: .55rem;
    }

    .feed-cam-index-row {
        display: flex;
        align-items: center;
        gap: .65rem;
        font-size: .82rem;
    }

    .feed-cam-index-dot {
        width: .5rem;
        height: .5rem;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .feed-cam-index-dot.online  { background: #22c55e; }
    .feed-cam-index-dot.offline { background: #ef4444; }

    .feed-cam-index-label {
        flex: 1;
        color: #334155;
        font-weight: 500;
    }

    .feed-cam-index-tag {
        font-size: .7rem;
        font-weight: 700;
        color: #64748b;
    }

    .feed-note {
        border-radius: .875rem;
        background: linear-gradient(135deg, #0f172a, #1e3a5f);
        padding: 1rem;
        color: rgba(255,255,255,.75);
        font-size: .8rem;
        line-height: 1.6;
    }

    .feed-placeholder {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0%, 100% { opacity: 1; }
        50%       { opacity: .7; }
    }
</style>
@endpush

@section('content')
<div class="feed-layout">

    {{-- Left: 2×2 Camera Grid --}}
    <div>
        <div class="feed-cam-grid-header">
            <span class="feed-cam-grid-title">Mayor Gil Fernando Ave / Sumulong Hwy</span>
            <div class="feed-live-badge">
                <span class="feed-live-dot"></span>
                <span id="cam-count">Loading…</span>
            </div>
        </div>
        <div class="feed-cam-grid" id="camera-grid">
            @for ($i = 0; $i < 4; $i++)
            <div class="feed-card">
                <div class="feed-card-media feed-placeholder"></div>
                <div class="feed-card-body">
                    <div>
                        <div class="feed-card-name" style="background:#e2e8f0;height:12px;border-radius:4px;width:120px;"></div>
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- Right: Sidebar --}}
    <div class="feed-sidebar">

        <div class="feed-info-card">
            <div class="feed-info-header">Intersection</div>
            <div class="feed-info-body">
                <div class="feed-intersection-name">Mayor Gil Fernando Ave<br>× Sumulong Highway</div>
                <div class="feed-intersection-sub" style="margin-top:.4rem;">Marikina City, Metro Manila</div>
                <div class="feed-intersection-sub" style="margin-top:.5rem;" id="cam-online-count"></div>
                <div class="feed-intersection-sub" id="cam-last-updated" style="margin-top:.2rem;font-size:.76rem;"></div>
            </div>
        </div>

        <div class="feed-info-card">
            <div class="feed-info-header">Camera Index</div>
            <div class="feed-info-body">
                <div class="feed-cam-index" id="camera-index">
                    @foreach (['Northbound','Southbound','Eastbound','Westbound'] as $dir)
                    <div class="feed-cam-index-row">
                        <span class="feed-cam-index-dot offline"></span>
                        <span class="feed-cam-index-label">{{ $dir }}</span>
                        <span class="feed-cam-index-tag">Loading</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="feed-note">
            Streams are sourced directly from STAP Node hardware at the intersection.
            For access issues, contact STAP Hub administration.
        </div>

    </div>
</div>

<div id="camera-error" style="display:none;padding:2rem;text-align:center;color:#64748b;border-radius:.875rem;background:rgba(15,23,42,.03);border:1px dashed rgba(15,23,42,.14);margin-top:1rem;">
    Unable to load camera feeds right now.
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const grid      = document.getElementById('camera-grid');
    const countEl   = document.getElementById('cam-count');
    const onlineEl  = document.getElementById('cam-online-count');
    const updatedEl = document.getElementById('cam-last-updated');
    const indexEl   = document.getElementById('camera-index');
    const errorEl   = document.getElementById('camera-error');

    try {
        const res = await fetch("{{ route('public.live.cameras') }}", {
            headers: { 'Accept': 'application/json' }
        });

        if (!res.ok) throw new Error('Failed');

        const cameras = await res.json();
        const online  = cameras.filter(c => c.status === 'active' || c.status === 'online').length;

        countEl.textContent   = `${cameras.length} camera${cameras.length !== 1 ? 's' : ''}`;
        onlineEl.textContent  = `${online} of ${cameras.length} online`;
        updatedEl.textContent = `Updated ${new Date().toLocaleTimeString()}`;

        // Build 2×2 camera grid
        if (!cameras.length) {
            grid.innerHTML = `<div style="grid-column:1/-1;padding:3rem;text-align:center;color:#64748b;">No cameras configured.</div>`;
        } else {
            // Pad to 4 slots
            const slots = [...cameras];
            while (slots.length < 4) slots.push(null);

            grid.innerHTML = slots.slice(0, 4).map((cam) => {
                if (!cam) return `
                    <div class="feed-card">
                        <div class="feed-card-media" style="background:#f1f5f9;">
                            <span style="color:#94a3b8;font-size:.82rem;">No camera</span>
                        </div>
                        <div class="feed-card-body">
                            <div><div class="feed-card-name" style="color:#94a3b8;">—</div></div>
                        </div>
                    </div>`;

                const isOnline  = cam.status === 'active' || cam.status === 'online';
                const direction = cam.direction ?? cam.label ?? `Camera ${cam.id}`;
                const nodeName  = cam.node?.name ?? '';
                const stream    = cam.stream_url ?? '';

                const media = stream
                    ? `<iframe src="${stream}" title="${direction}" loading="lazy" referrerpolicy="no-referrer"></iframe>`
                    : `<div class="feed-card-offline">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:.4rem;opacity:.4;"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                            <div>Stream unavailable</div>
                        </div>`;

                return `
                    <div class="feed-card">
                        <div class="feed-card-media">
                            ${media}
                            <span class="feed-card-dir">${direction}</span>
                        </div>
                        <div class="feed-card-body">
                            <div>
                                <div class="feed-card-name">${cam.label ?? direction}</div>
                                ${nodeName ? `<div class="feed-card-node">${nodeName}</div>` : ''}
                            </div>
                            <span class="feed-card-status ${isOnline ? 'online' : 'offline'}">${isOnline ? 'Online' : 'Offline'}</span>
                        </div>
                    </div>`;
            }).join('');
        }

        // Build sidebar index
        const directions = ['Northbound', 'Southbound', 'Eastbound', 'Westbound'];
        indexEl.innerHTML = directions.map(dir => {
            const cam = cameras.find(c => (c.direction ?? '').toLowerCase() === dir.toLowerCase());
            const online = cam && (cam.status === 'active' || cam.status === 'online');
            const label  = cam ? (cam.label ?? dir) : dir;
            return `
                <div class="feed-cam-index-row">
                    <span class="feed-cam-index-dot ${cam ? (online ? 'online' : 'offline') : 'offline'}"></span>
                    <span class="feed-cam-index-label">${label}</span>
                    <span class="feed-cam-index-tag">${cam ? (online ? 'Online' : 'Offline') : 'None'}</span>
                </div>`;
        }).join('');

    } catch (e) {
        grid.innerHTML = '';
        errorEl.style.display = 'block';
        countEl.textContent = 'Error';
        console.error(e);
    }
});
</script>
@endpush