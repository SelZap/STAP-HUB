@extends('layouts.admin')

@section('title', 'Traffic Lights')
@section('page-title', 'Traffic Light Control')

@section('content')

<div id="lightsGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
    <div class="stap-empty">Loading traffic lights…</div>
</div>

@endsection

@push('scripts')
<script>
const stateColors = { red: '#E03040', yellow: '#F4B942', green: '#29B357' };

async function loadLights() {
    try {
        const res   = await fetch('/admin/api/traffic-lights', { headers: authHeaders() });
        const data  = await res.json();
        const lights = data.data ?? data;
        const grid  = document.getElementById('lightsGrid');

        if (!lights.length) {
            grid.innerHTML = '<div class="stap-empty">No traffic lights found.</div>';
            return;
        }

        grid.innerHTML = lights.map(l => {
            const nodeMode = l.node?.mode ?? '—';
            const canControl = ['manual', 'hazard'].includes(nodeMode);
            const cur = l.current_state ?? 'red';

            const dots = ['red','yellow','green'].map(s => `
                <div style="width:36px;height:36px;border-radius:50%;background:${cur===s ? stateColors[s] : 'rgba(255,255,255,0.1)'};
                    transition:background 0.3s;border:2px solid rgba(255,255,255,0.2);"></div>
            `).join('');

            const controls = canControl ? `
                <div style="display:flex;gap:8px;margin-top:14px;">
                    ${['red','yellow','green'].map(s => `
                        <button onclick="setState(${l.light_id ?? l.id},'${s}')" class="stap-btn-primary"
                            style="flex:1;padding:7px 4px;font-size:11px;background:${stateColors[s]};letter-spacing:0;">
                            ${s.toUpperCase()}
                        </button>
                    `).join('')}
                </div>
            ` : `<div class="stap-text-xs stap-muted stap-mt-2" style="text-align:center;">Set node to Manual or Hazard to control</div>`;

            return `
            <div class="stap-card" style="padding:0;overflow:hidden;" id="light-${l.light_id ?? l.id}">
                <div style="background:var(--navy);padding:18px;display:flex;justify-content:center;gap:16px;">
                    ${dots}
                </div>
                <div style="padding:14px 16px;">
                    <div class="stap-flex-between stap-mb-1">
                        <span style="font-weight:700;font-size:13px;">${l.location_label ?? 'Light ' + (l.light_id ?? l.id)}</span>
                        <span style="font-size:11px;font-weight:700;color:${stateColors[cur]};text-transform:uppercase;">${cur}</span>
                    </div>
                    <div class="stap-text-xs stap-muted">Node: ${l.node?.node_name ?? l.node?.name ?? '—'} &nbsp;·&nbsp; Mode: <strong>${nodeMode}</strong></div>
                    ${controls}
                </div>
            </div>`;
        }).join('');

    } catch(e) { console.error(e); }
}

async function setState(lightId, state) {
    try {
        const res = await fetch(`/admin/traffic-lights/${lightId}/state`, {
            method: 'POST',
            headers: authHeaders(),
            body: JSON.stringify({ state })
        });
        const data = await res.json();
        if (!res.ok) { alert(data.message ?? 'Error'); return; }
        loadLights();
    } catch(e) { alert('Request failed.'); }
}

loadLights();
</script>
@endpush
