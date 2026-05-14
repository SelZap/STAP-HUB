@extends('layouts.admin')

@section('title', 'Alerts')
@section('page-title', 'System Alerts')

@section('content')

{{-- Filter Tabs --}}
<div class="stap-tabs stap-mb-2">
    <button class="stap-tab active" onclick="filterAlerts(this, '')">All</button>
    <button class="stap-tab" onclick="filterAlerts(this, 'false')">Unresolved</button>
    <button class="stap-tab" onclick="filterAlerts(this, 'true')">Resolved</button>
</div>

<div id="alertsList">
    <div class="stap-empty">Loading alerts…</div>
</div>

@endsection

@push('scripts')
<script>
const severityColors = { critical:'var(--red)', warning:'var(--amber)', info:'var(--navy-muted)' };

let currentResolved = '';

async function loadAlerts() {
    let url = '/admin/api/alerts';
    if (currentResolved !== '') url += `?resolved=${currentResolved}`;

    const list = document.getElementById('alertsList');
    list.innerHTML = '<div class="stap-empty">Loading…</div>';

    try {
        const res  = await fetch(url, { headers: authHeaders() });
        const data = await res.json();
        const alerts = data.data ?? data;

        if (!alerts.length) {
            list.innerHTML = '<div class="stap-empty">No alerts found.</div>';
            return;
        }

        list.innerHTML = alerts.map(a => {
            const sc = severityColors[a.severity] ?? 'var(--text-muted)';
            const resolved = a.is_resolved ?? a.resolved;
            const date = a.triggered_at ? new Date(a.triggered_at).toLocaleString() : '—';
            return `
            <div class="stap-card stap-mb-1" style="padding:14px 18px;display:flex;align-items:center;gap:16px;">
                <div style="width:10px;height:10px;border-radius:50%;background:${sc};flex-shrink:0;"></div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
                        <span style="font-weight:700;font-size:13px;">${a.type ?? '—'}</span>
                        <span style="font-size:10px;font-weight:700;color:${sc};background:${sc}1a;padding:2px 8px;border-radius:20px;text-transform:uppercase;">${a.severity ?? '—'}</span>
                        ${resolved ? '<span style="font-size:10px;font-weight:700;color:var(--green);background:var(--green-bg);padding:2px 8px;border-radius:20px;">RESOLVED</span>' : ''}
                    </div>
                    <div class="stap-text-xs stap-muted">${a.message ?? ''}</div>
                    <div class="stap-text-xs stap-muted stap-mt-1">Node: ${a.node?.node_name ?? a.node?.name ?? '—'} &nbsp;·&nbsp; ${date}</div>
                </div>
                ${!resolved ? `
                <button onclick="resolveAlert(${a.alert_id ?? a.id}, this)" class="stap-btn-primary" style="padding:7px 14px;font-size:11px;flex-shrink:0;">
                    Resolve
                </button>` : ''}
            </div>`;
        }).join('');

    } catch(e) { console.error(e); }
}

function filterAlerts(btn, resolved) {
    document.querySelectorAll('.stap-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    currentResolved = resolved;
    loadAlerts();
}

async function resolveAlert(alertId, btn) {
    btn.disabled = true;
    btn.textContent = '…';
    try {
        const res = await fetch(`/admin/alerts/${alertId}/resolve`, {
            method: 'POST', headers: authHeaders()
        });
        const data = await res.json();
        if (!res.ok) { alert(data.message ?? 'Error'); btn.disabled = false; btn.textContent = 'Resolve'; return; }
        loadAlerts();
    } catch(e) { btn.disabled = false; btn.textContent = 'Resolve'; }
}

loadAlerts();
</script>
@endpush
