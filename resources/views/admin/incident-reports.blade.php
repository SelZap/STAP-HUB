@extends('layouts.admin')

@section('title', 'Incident Reports')
@section('page-title', 'Incident Reports')

@section('content')

<div class="stap-tabs stap-mb-2">
    <button class="stap-tab active" onclick="filterReports(this,'')">All</button>
    <button class="stap-tab" onclick="filterReports(this,'pending')">Pending</button>
    <button class="stap-tab" onclick="filterReports(this,'reviewed')">Reviewed</button>
</div>

<div id="reportsList">
    <div class="stap-empty">Loading reports…</div>
</div>

{{-- Detail Modal --}}
<div class="stap-modal-overlay" id="reportModal">
    <div class="stap-modal" style="width:520px;max-width:calc(100vw - 40px);max-height:80vh;overflow-y:auto;">
        <button class="stap-modal-close" onclick="closeModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div id="reportModalContent" style="margin-top:8px;"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentStatus = '';

async function loadReports() {
    let url = '/admin/incident-reports';
    const list = document.getElementById('reportsList');
    list.innerHTML = '<div class="stap-empty">Loading…</div>';

    try {
        const res = await fetch(url, { 
            method: 'GET',
            headers: {
                ...authHeaders(), // The "..." spreads the existing auth headers into this object
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            } 
        });

        if (!res.ok) throw new Error('Network response was not ok');

        const data = await res.json();
        let reports = data.data ?? data;

        if (currentStatus) reports = reports.filter(r => r.status === currentStatus);

        if (!reports.length) {
            list.innerHTML = '<div class="stap-empty">No incident reports found.</div>';
            return;
        }

        list.innerHTML = reports.map(r => {
            const isPending = r.status === 'pending';
            const date = r.incident_date ?? '—';
            const submitted = r.created_at ? new Date(r.created_at).toLocaleDateString() : '—';
            return `
            <div class="stap-card stap-mb-1" style="padding:14px 18px;display:flex;align-items:center;gap:16px;">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <span style="font-weight:700;font-size:13px;">${r.reporting_party_name ?? '—'}</span>
                        <span style="font-size:10px;font-weight:700;${isPending?'color:var(--amber);background:var(--amber-bg)':'color:var(--green);background:var(--green-bg)'};padding:2px 8px;border-radius:20px;text-transform:uppercase;">${r.status ?? '—'}</span>
                        ${r.people_hurt ? '<span style="font-size:10px;font-weight:700;color:var(--red);background:var(--red-bg);padding:2px 8px;border-radius:20px;">INJURIES</span>' : ''}
                    </div>
                    <div class="stap-text-xs stap-muted">Incident: ${date} &nbsp;·&nbsp; Submitted: ${submitted}</div>
                    <div class="stap-text-xs stap-muted stap-mt-1">${(r.description ?? '').substring(0,80)}${(r.description?.length > 80) ? '…' : ''}</div>
                </div>
                <div style="display:flex;gap:8px;flex-shrink:0;">
                    <button onclick="viewReport(${r.incident_id ?? r.id})" class="stap-btn-primary" style="padding:7px 14px;font-size:11px;">View</button>
                    ${isPending ? `<button onclick="markReviewed(${r.incident_id ?? r.id},this)" class="stap-btn-primary stap-btn-green" style="padding:7px 14px;font-size:11px;">Mark Reviewed</button>` : ''}
                </div>
            </div>`;
        }).join('');
    } catch(e) { console.error(e); }
}

function filterReports(btn, status) {
    document.querySelectorAll('.stap-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    currentStatus = status;
    loadReports();
}

async function viewReport(id) {
    document.getElementById('reportModal').classList.add('is-open');
    document.getElementById('reportModalContent').innerHTML = '<div class="stap-empty">Loading…</div>';

    try {
        const res = await fetch(`/admin/incident-reports`, { headers: authHeaders() });
        const data = await res.json();
        const reports = data.data ?? data;
        const r = reports.find(x => (x.incident_id ?? x.id) == id);
        if (!r) { document.getElementById('reportModalContent').innerHTML = '<div class="stap-empty">Not found.</div>'; return; }

        document.getElementById('reportModalContent').innerHTML = `
            <h3 style="font-size:15px;font-weight:800;margin-bottom:14px;">Incident Report #${r.incident_id ?? r.id}</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                ${field('Reporter', r.reporting_party_name)}
                ${field('Email', r.reporter_email)}
                ${field('Incident Date', r.incident_date)}
                ${field('Incident Time', r.incident_time)}
                ${field('Vehicle Type', r.vehicle_type)}
                ${field('Vehicle Count', r.vehicle_count)}
                ${field('People Hurt', r.people_hurt ? 'Yes' : 'No')}
                ${field('Injured Count', r.injured_count ?? '—')}
                ${field('Environment', r.environmental_condition)}
                ${field('Status', r.status)}
            </div>
            ${r.location_description ? `<div style="margin-bottom:10px;">${field('Location', r.location_description)}</div>` : ''}
            ${r.description ? `<div style="margin-bottom:14px;"><div class="stap-form-label">Description</div><div style="font-size:13px;line-height:1.6;">${r.description}</div></div>` : ''}
            ${r.status === 'pending' ? `<button onclick="markReviewed(${r.incident_id??r.id},this);closeModal();loadReports();" class="stap-btn-primary stap-btn-green" style="font-size:12px;padding:8px 16px;">Mark as Reviewed</button>` : ''}
        `;
    } catch(e) { console.error(e); }
}

function field(label, val) {
    return `<div><div class="stap-form-label">${label}</div><div style="font-size:13px;font-weight:500;">${val ?? '—'}</div></div>`;
}

async function markReviewed(id, btn) {
    if (btn) { btn.disabled = true; btn.textContent = '…'; }
    try {
        const res = await fetch(`/admin/incident-reports/${id}/review`, {
            method: 'PATCH', headers: authHeaders()
        });
        if (!res.ok) { const d = await res.json(); alert(d.message ?? 'Error'); return; }
        loadReports();
    } catch(e) { alert('Request failed.'); }
}

function closeModal() {
    document.getElementById('reportModal').classList.remove('is-open');
}

loadReports();
</script>
@endpush
