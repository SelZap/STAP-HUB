@extends('layouts.admin')

@section('title', 'Footage Requests')
@section('page-title', 'Footage Requests')

@push('styles')
<style>
    .req-modal-grid       { display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px; }
    .req-field            { background:#f8fafc;border-radius:8px;padding:10px 12px; }
    .req-field-label      { font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:4px; }
    .req-field-value      { font-size:13px;font-weight:600;color:#0f172a;line-height:1.4; }
    .req-field-full       { grid-column:1/-1; }
    .req-section-title    { font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin:18px 0 10px;padding-bottom:6px;border-bottom:1px solid #e2e8f0; }
    .req-actions          { display:flex;gap:8px;flex-wrap:wrap;padding:14px 0 0;border-top:1px solid #e2e8f0;margin-top:14px; }
</style>
@endpush

@section('content')

<div class="stap-card stap-mb-2" style="padding:0;overflow:hidden;">
    <div style="display:flex;border-bottom:1px solid var(--border);">
        <button id="tab-new"      onclick="switchTab('new')"      style="flex:1;padding:13px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;border:none;border-bottom:2px solid var(--navy);background:transparent;color:var(--navy);cursor:pointer;">New Requests</button>
        <button id="tab-ongoing"  onclick="switchTab('ongoing')"  style="flex:1;padding:13px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;border:none;border-bottom:2px solid transparent;background:transparent;color:var(--text-muted);cursor:pointer;">Ongoing</button>
        <button id="tab-rejected" onclick="switchTab('rejected')" style="flex:1;padding:13px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;border:none;border-bottom:2px solid transparent;background:transparent;color:var(--text-muted);cursor:pointer;">Rejected</button>
    </div>
    <div style="padding:10px 20px;">
        <span id="reqCount" class="stap-text-xs stap-muted"></span>
    </div>
</div>

<div class="stap-card">
    <div style="padding:0;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);background:var(--bg-input);">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">#</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Requester</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Nature</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Date Submitted</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Handled By</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Status</th>
                    <th style="padding:11px 16px;"></th>
                </tr>
            </thead>
            <tbody id="reqBody">
                <tr><td colspan="7" class="stap-empty" style="padding:28px;">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="stap-modal-overlay" id="reqModal">
    <div class="stap-modal" style="width:700px;max-width:calc(100vw - 32px);max-height:90vh;overflow-y:auto;border-radius:14px;">
        <button class="stap-modal-close" onclick="closeModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div id="modalContent" style="padding:4px 0 8px;"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// FIX: actually use the json parameter to set Content-Type when sending JSON bodies
function buildHeaders(includeJson = false) {
    const h = { ...authHeaders() };
    if (includeJson) h['Content-Type'] = 'application/json';
    return h;
}

const statusColors = {
    pending:            'var(--amber)',
    reviewed:           'var(--navy-muted)',
    requirements_sent:  'var(--navy-muted)',
    approved:           'var(--green)',
    rejected:           'var(--red)',
};

let currentTab     = 'new';
let currentRequest = null;

function switchTab(tab) {
    currentTab = tab;
    ['new', 'ongoing', 'rejected'].forEach(t => {
        const btn = document.getElementById('tab-' + t);
        btn.style.color             = t === tab ? 'var(--navy)' : 'var(--text-muted)';
        btn.style.borderBottomColor = t === tab ? 'var(--navy)' : 'transparent';
    });
    loadRequests();
}

async function loadRequests() {
    const body = document.getElementById('reqBody');
    body.innerHTML = '<tr><td colspan="7" class="stap-empty" style="padding:28px;">Loading…</td></tr>';
    try {
        const res  = await fetch('/admin/api/requests?tab=' + currentTab, { headers: buildHeaders() });
        if (!res.ok) throw new Error('Failed to load');
        const data = await res.json();
        const rows = data.data ?? data;
        document.getElementById('reqCount').textContent = (data.total ?? rows.length) + ' request(s)';

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="7" class="stap-empty" style="padding:28px;">No requests found.</td></tr>';
            return;
        }

        body.innerHTML = rows.map(r => {
            const sc      = statusColors[r.status] ?? 'var(--text-muted)';
            const date    = r.created_at ? new Date(r.created_at).toLocaleDateString('en-PH') : '—';
            const handler = r.handler?.admin_name ?? '—';
            return '<tr style="border-bottom:1px solid var(--border);">' +
                '<td style="padding:10px 16px;color:var(--text-muted);font-size:12px;">#' + r.request_id + '</td>' +
                '<td style="padding:10px 16px;"><div style="font-weight:600;">' + (r.requester_name ?? '—') + '</div><div class="stap-text-xs stap-muted">' + (r.requester_email ?? '') + '</div></td>' +
                '<td style="padding:10px 16px;text-transform:capitalize;">' + (r.request_nature ?? '—') + '</td>' +
                '<td style="padding:10px 16px;color:var(--text-secondary);font-size:12px;">' + date + '</td>' +
                '<td style="padding:10px 16px;font-size:12px;color:var(--text-secondary);">' + handler + '</td>' +
                '<td style="padding:10px 16px;"><span style="font-size:11px;font-weight:700;color:' + sc + ';background:' + sc + '1a;padding:2px 9px;border-radius:20px;text-transform:uppercase;">' + (r.status ?? '—') + '</span></td>' +
                '<td style="padding:10px 16px;"><button onclick="viewRequest(' + r.request_id + ')" class="stap-btn-primary" style="padding:6px 14px;font-size:11px;">View</button></td>' +
            '</tr>';
        }).join('');
    } catch (e) {
        console.error('loadRequests:', e);
        body.innerHTML = '<tr><td colspan="7" class="stap-empty" style="padding:28px;">Failed to load requests.</td></tr>';
    }
}

async function viewRequest(id) {
    document.getElementById('reqModal').classList.add('is-open');
    document.getElementById('modalContent').innerHTML = '<div class="stap-empty" style="padding:40px 0;">Loading…</div>';
    try {
        const res = await fetch('/admin/api/requests/' + id, { headers: buildHeaders() });
        if (!res.ok) throw new Error('Not found');
        const r       = await res.json();
        currentRequest = r;

        const sc          = statusColors[r.status] ?? 'var(--text-muted)';
        const handlerName = r.handler?.admin_name ?? (r.handled_by ? 'Admin #' + r.handled_by : 'Not yet assigned');
        const footageDate = r.footage_date ?? (r.footage_date_start ? r.footage_date_start + ' → ' + r.footage_date_end : '—');

        let html = '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;">' +
            '<div>' +
                '<p style="font-size:17px;font-weight:800;color:#0f172a;margin:0 0 6px;">Footage Request #' + r.request_id + '</p>' +
                '<span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;color:' + sc + ';background:' + sc + '1a;text-transform:uppercase;">' + (r.status ?? '—') + '</span>' +
            '</div>' +
        '</div>';

        html += '<div class="req-section-title">Requester Information</div>';
        html += '<div class="req-modal-grid">' +
            '<div class="req-field"><div class="req-field-label">Full Name</div><div class="req-field-value">' + (r.requester_name ?? '—') + '</div></div>' +
            '<div class="req-field"><div class="req-field-label">Email</div><div class="req-field-value">' + (r.requester_email ?? '<span style="color:#94a3b8;">Not provided</span>') + '</div></div>' +
            '<div class="req-field"><div class="req-field-label">Organization</div><div class="req-field-value">' + (r.requester_organization ?? '—') + '</div></div>' +
            '<div class="req-field"><div class="req-field-label">Contact</div><div class="req-field-value">' + (r.requester_contact ?? '—') + '</div></div>' +
            (r.requester_address ? '<div class="req-field req-field-full"><div class="req-field-label">Address</div><div class="req-field-value">' + r.requester_address + '</div></div>' : '') +
        '</div>';

        html += '<div class="req-section-title">Footage Details</div>';
        html += '<div class="req-modal-grid">' +
            '<div class="req-field"><div class="req-field-label">Nature</div><div class="req-field-value" style="text-transform:capitalize;">' + (r.request_nature ?? '—') + '</div></div>' +
            '<div class="req-field"><div class="req-field-label">Handled By</div><div class="req-field-value">' + handlerName + '</div></div>' +
            '<div class="req-field"><div class="req-field-label">Footage Date</div><div class="req-field-value">' + footageDate + '</div></div>' +
            '<div class="req-field"><div class="req-field-label">Camera</div><div class="req-field-value">' + (r.camera_id ? 'Camera #' + r.camera_id : 'All cameras') + '</div></div>' +
            '<div class="req-field"><div class="req-field-label">Time Range</div><div class="req-field-value">' + (r.footage_time_start ?? '—') + ' – ' + (r.footage_time_end ?? '—') + '</div></div>' +
        '</div>';

        if (r.incident_description) {
            html += '<div class="req-section-title">Incident Description</div>' +
                '<div style="background:#f8fafc;border-radius:8px;padding:14px;font-size:13px;line-height:1.75;color:#334155;margin-bottom:4px;">' + r.incident_description + '</div>';
        }

        html += '<div class="req-actions">' +
            (r.status !== 'approved' ? '<button onclick="updateStatus(' + r.request_id + ', \'approved\')" class="stap-btn-primary stap-btn-green" style="font-size:13px;padding:9px 20px;">✓ Approve</button>' : '') +
            (r.status !== 'rejected' ? '<button onclick="updateStatus(' + r.request_id + ', \'rejected\')" class="stap-btn-primary" style="background:var(--red);font-size:13px;padding:9px 20px;">✗ Reject</button>' : '') +
            (r.status !== 'reviewed' ? '<button onclick="updateStatus(' + r.request_id + ', \'reviewed\')" class="stap-btn-primary" style="background:var(--navy-muted);font-size:13px;padding:9px 20px;">Mark Under Review</button>' : '') +
        '</div>';

        document.getElementById('modalContent').innerHTML = html;
    } catch (e) {
        console.error('viewRequest:', e);
        document.getElementById('modalContent').innerHTML = '<div class="stap-empty">Failed to load request.</div>';
    }
}

async function updateStatus(id, status) {
    try {
        const res = await fetch('/admin/requests/' + id + '/status', {
            method: 'POST',
            headers: buildHeaders(true),
            credentials: 'same-origin',
            body: JSON.stringify({ status }),
        });
        const data = await res.json();
        if (!res.ok) {
            alert(data.message ?? 'Failed to update status.');
            return;
        }
        closeModal();
        await loadRequests();
    } catch (e) {
        console.error('updateStatus:', e);
        alert('Request failed.');
    }
}

function closeModal() {
    document.getElementById('reqModal').classList.remove('is-open');
    currentRequest = null;
}

loadRequests();
</script>
@endpush