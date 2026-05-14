@extends('layouts.admin')

@section('title', 'Cameras')
@section('page-title', 'Camera Management')

@section('content')

<div class="stap-card stap-mb-2" style="padding:16px 20px;">
    <div class="stap-flex-between">
        <span class="stap-card-title">ALL CAMERAS</span>
        <span id="cameraCount" class="stap-text-xs stap-muted">Loading…</span>
    </div>
</div>

<div id="cameraGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
    <div class="stap-empty">Loading cameras…</div>
</div>

@endsection

@push('scripts')
<script>
async function loadCameras() {
    try {
        const res  = await fetch('/admin/api/cameras', { headers: authHeaders() });
        const data = await res.json();
        const cameras = data.data ?? data;

        document.getElementById('cameraCount').textContent = cameras.length + ' camera(s)';

        const grid = document.getElementById('cameraGrid');
        if (!cameras.length) {
            grid.innerHTML = '<div class="stap-empty">No cameras found.</div>';
            return;
        }

        grid.innerHTML = cameras.map(cam => {
            const statusColor = cam.status === 'online' ? 'var(--green)' : cam.status === 'warning' ? 'var(--amber)' : 'var(--red)';
            return `
            <div class="stap-card" style="padding:0;overflow:hidden;">
                <div class="stap-cam-cell" style="border-radius:0;aspect-ratio:16/9;">
                    <div style="color:rgba(255,255,255,0.3);font-size:11px;font-weight:600;">NO FEED</div>
                    <div class="stap-cam-label">${cam.label ?? 'Camera ' + cam.camera_id}</div>
                    <div class="stap-cam-status" style="background:${statusColor};"></div>
                </div>
                <div style="padding:12px 16px;">
                    <div class="stap-flex-between stap-mb-1">
                        <span style="font-size:13px;font-weight:700;">${cam.label ?? '—'}</span>
                        <span style="font-size:11px;font-weight:700;color:${statusColor};background:${statusColor}1a;padding:2px 8px;border-radius:20px;text-transform:uppercase;">${cam.status ?? '—'}</span>
                    </div>
                    <div class="stap-text-xs stap-muted">Direction: ${cam.direction ?? '—'} &nbsp;·&nbsp; USB: ${cam.usb_index ?? '—'}</div>
                    <div class="stap-text-xs stap-muted stap-mt-1">Node: ${cam.node?.node_name ?? cam.node?.name ?? '—'}</div>
                </div>
            </div>`;
        }).join('');

    } catch(e) { console.error(e); }
}

loadCameras();
</script>
@endpush
