@extends('layouts.admin')

@section('title', 'Footage Requests')
@section('page-title', 'Footage Requests')

@section('content')

{{-- Filter --}}
<div class="stap-card stap-mb-2" style="padding:14px 20px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div>
            <label class="stap-form-label">Status</label>
            <select id="filterStatus" class="stap-form-input" style="width:180px;" onchange="loadRequests()">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="reviewed">Reviewed</option>
                <option value="requirements_sent">Requirements Sent</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <span id="reqCount" class="stap-text-xs stap-muted" style="align-self:center;margin-left:auto;"></span>
    </div>
</div>

{{-- Table --}}
<div class="stap-card">
    <div class="stap-card-body" style="padding:0;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);background:var(--bg-input);">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">#</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Requester</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Nature</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Date Submitted</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Status</th>
                    <th style="padding:11px 16px;"></th>
                </tr>
            </thead>
            <tbody id="reqBody">
                <tr><td colspan="6" class="stap-empty" style="padding:28px;">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Detail Modal --}}
<div class="stap-modal-overlay" id="reqModal">
    <div class="stap-modal" style="width:520px;max-width:calc(100vw - 40px);max-height:80vh;overflow-y:auto;">
        <button class="stap-modal-close" onclick="closeModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div id="modalContent" style="margin-top:8px;"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const statusColors = {
    pending:'var(--amber)', reviewed:'var(--navy-muted)',
    requirements_sent:'var(--navy)', approved:'var(--green)', rejected:'var(--red)'
};

async function loadRequests() {
    const status = document.getElementById('filterStatus').value;
    let url = '/admin/api/requests';
    if (status) url += `?status=${status}`;
    const body = document.getElementById('reqBody');
    body.innerHTML = '<tr><td colspan="6" class="stap-empty" style="padding:28px;">Loading…</td></tr>';

    try {
        const res  = await fetch(url, { headers: authHeaders() });
        const data = await res.json();
        const rows = data.data ?? data;
        document.getElementById('reqCount').textContent = `${data.total ?? rows.length} request(s)`;

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="stap-empty" style="padding:28px;">No requests found.</td></tr>';
            return;
        }

        body.innerHTML = rows.map(r => {
            const sc = statusColors[r.status] ?? 'var(--text-muted)';
            const date = r.created_at ? new Date(r.created_at).toLocaleDateString() : '—';
            return `<tr style="border-bottom:1px solid var(--border);">
                <td style="padding:10px 16px;color:var(--text-muted);font-size:12px;">#${r.request_id ?? r.id}</td>
                <td style="padding:10px 16px;">
                    <div style="font-weight:600;">${r.requester_name ?? '—'}</div>
                    <div class="stap-text-xs stap-muted">${r.requester_email ?? ''}</div>
                </td>
                <td style="padding:10px 16px;">${r.request_nature ?? r.nature ?? '—'}</td>
                <td style="padding:10px 16px;color:var(--text-secondary);font-size:12px;">${date}</td>
                <td style="padding:10px 16px;">
                    <span style="font-size:11px;font-weight:700;color:${sc};background:${sc}1a;padding:2px 9px;border-radius:20px;text-transform:uppercase;">${r.status ?? '—'}</span>
                </td>
                <td style="padding:10px 16px;">
                    <button onclick="viewRequest(${r.request_id ?? r.id})" class="stap-btn-primary" style="padding:6px 12px;font-size:11px;">View</button>
                </td>
            </tr>`;
        }).join('');
    } catch(e) { console.error(e); }
}

async function viewRequest(id) {
    document.getElementById('reqModal').classList.add('is-open');
    document.getElementById('modalContent').innerHTML = '<div class="stap-empty">Loading…</div>';

    try {
        const res  = await fetch(`/admin/api/requests/${id}`, { headers: authHeaders() });
        const r    = await res.json();
        document.getElementById('modalContent').innerHTML = `
            <h3 style="font-size:15px;font-weight:800;margin-bottom:14px;">Request #${r.request_id ?? r.id}</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                ${field('Name', r.requester_name)}
                ${field('Email', r.requester_email)}
                ${field('Organization', r.requester_organization)}
                ${field('Contact', r.requester_contact)}
                ${field('Nature', r.request_nature)}
                ${field('Status', r.status)}
                ${field('Footage Date', r.footage_date)}
                ${field('Camera', r.camera_id ? 'Camera #' + r.camera_id : '—')}
            </div>
            ${r.incident_description ? `<div style="margin-bottom:14px;"><div class="stap-form-label">Description</div><div style="font-size:13px;line-height:1.6;">${r.incident_description}</div></div>` : ''}
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                ${r.status !== 'approved' ? `<button onclick="updateStatus(${r.request_id ?? r.id},'approved')" class="stap-btn-primary stap-btn-green" style="font-size:12px;padding:8px 16px;">Approve</button>` : ''}
                ${r.status !== 'rejected' ? `<button onclick="updateStatus(${r.request_id ?? r.id},'rejected')" class="stap-btn-primary" style="background:var(--red);font-size:12px;padding:8px 16px;">Reject</button>` : ''}
            </div>`;
    } catch(e) { console.error(e); }
}

function field(label, val) {
    return `<div><div class="stap-form-label">${label}</div><div style="font-size:13px;font-weight:500;">${val ?? '—'}</div></div>`;
}

async function updateStatus(id, status) {
    try {
        const res = await fetch(`/admin/requests/${id}/status`, {
            method: 'POST', headers: authHeaders(), body: JSON.stringify({ status })
        });
        const data = await res.json();
        if (!res.ok) { alert(data.message ?? 'Error'); return; }
        closeModal();
        loadRequests();
    } catch(e) { alert('Request failed.'); }
}

function closeModal() {
    document.getElementById('reqModal').classList.remove('is-open');
}

loadRequests();
</script>
@endpush
