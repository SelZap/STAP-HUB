@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'System Dashboard')

@section('content')

{{-- Stat Cards --}}
<div class="stap-stat-grid stap-grid-4" id="statGrid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">

    <div class="stap-card" style="padding:18px 20px;">
        <div class="stap-text-xs stap-muted" style="font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;">STAP Nodes</div>
        <div id="statNodes" style="font-size:28px;font-weight:800;color:var(--navy);">—</div>
    </div>

    <div class="stap-card" style="padding:18px 20px;">
        <div class="stap-text-xs stap-muted" style="font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;">Cameras</div>
        <div id="statCameras" style="font-size:28px;font-weight:800;color:var(--navy);">—</div>
    </div>

    <div class="stap-card" style="padding:18px 20px;">
        <div class="stap-text-xs stap-muted" style="font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;">Active Alerts</div>
        <div id="statAlerts" style="font-size:28px;font-weight:800;color:var(--red);">—</div>
    </div>

    <div class="stap-card" style="padding:18px 20px;">
        <div class="stap-text-xs stap-muted" style="font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;">Online Nodes</div>
        <div id="statOnline" style="font-size:28px;font-weight:800;color:var(--green);">—</div>
    </div>

</div>

<div class="stap-grid-2 stap-mt-3">

    {{-- Nodes Status --}}
    <div class="stap-card">
        <div class="stap-card-header">
            <span class="stap-card-title">STAP NODE STATUS</span>
        </div>
        <div class="stap-card-body" id="nodesBody">
            <div class="stap-empty">Loading nodes…</div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="stap-card">
        <div class="stap-card-header">
            <span class="stap-card-title">RECENT ACTIVITY</span>
        </div>
        <div class="stap-card-body" id="activityBody">
            <div class="stap-empty">Loading activity…</div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
async function loadDashboard() {
    try {
        const res = await fetch('/admin/api/dashboard/summary', { headers: authHeaders() });
        const data = await res.json();

        document.getElementById('statNodes').textContent   = data.nodes?.length ?? 0;
        document.getElementById('statCameras').textContent = data.camera_count ?? 0;
        document.getElementById('statAlerts').textContent  = data.active_alerts ?? 0;

        const online = (data.nodes || []).filter(n => n.status === 'online').length;
        document.getElementById('statOnline').textContent = online;

        // Nodes
        const nodesBody = document.getElementById('nodesBody');
        if (!data.nodes?.length) {
            nodesBody.innerHTML = '<div class="stap-empty">No nodes registered.</div>';
        } else {
            nodesBody.innerHTML = data.nodes.map(node => {
                const color = node.status === 'online' ? 'var(--green)' : node.status === 'warning' ? 'var(--amber)' : 'var(--red)';
                return `
                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);">
                    <div>
                        <div style="font-weight:700;font-size:13px;">${node.name ?? node.node_name ?? '—'}</div>
                        <div class="stap-text-xs stap-muted">Mode: ${node.mode ?? '—'}</div>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:${color};background:${color}1a;padding:3px 10px;border-radius:20px;text-transform:uppercase;">
                        ${node.status ?? '—'}
                    </span>
                </div>`;
            }).join('');
        }

        // Activity
        const actBody = document.getElementById('activityBody');
        if (!data.recent_activity?.length) {
            actBody.innerHTML = '<div class="stap-empty">No recent activity.</div>';
        } else {
            actBody.innerHTML = data.recent_activity.map(log => `
                <div style="display:flex;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid var(--border);">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--navy-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:11px;font-weight:700;color:var(--navy);">
                        ${(log.admin?.name || 'SY').substring(0,2).toUpperCase()}
                    </div>
                    <div style="min-width:0;">
                        <div style="font-size:12px;font-weight:600;">${log.action_type ?? log.action ?? '—'}</div>
                        <div class="stap-text-xs stap-muted">${log.admin?.name ?? 'System'}</div>
                    </div>
                </div>
            `).join('');
        }

    } catch(e) {
        console.error(e);
    }
}

loadDashboard();
</script>
@endpush
