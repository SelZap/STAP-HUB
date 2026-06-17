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
    .req-email-box        { background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-top:14px; }
    .req-email-box-title  { font-size:12px;font-weight:700;color:#0f172a;margin-bottom:12px;display:flex;align-items:center;gap:6px; }
    .req-email-approve    { border-left:3px solid #22c55e; }
    .req-email-reject     { border-left:3px solid #ef4444; }
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
    pending:    'var(--amber)',
    under_review: 'var(--navy-muted)',
    approved:   'var(--green)',
    rejected:   'var(--red)',
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
            (r.status !== 'approved' ? '<button onclick="showEmailCompose(' + r.request_id + ', \'approved\')" class="stap-btn-primary stap-btn-green" style="font-size:13px;padding:9px 20px;">✓ Approve</button>' : '') +
            (r.status !== 'rejected' ? '<button onclick="showEmailCompose(' + r.request_id + ', \'rejected\')" class="stap-btn-primary" style="background:var(--red);font-size:13px;padding:9px 20px;">✗ Reject</button>' : '') +
            (r.status !== 'under_review' ? '<button onclick="updateStatus(' + r.request_id + ', \'under_review\', false)" class="stap-btn-primary" style="background:var(--navy-muted);font-size:13px;padding:9px 20px;">Mark Under Review</button>' : '') +
        '</div>';

        html += '<div id="emailComposeWrap_' + r.request_id + '" style="display:none;margin-top:14px;"></div>';

        document.getElementById('modalContent').innerHTML = html;
    } catch (e) {
        console.error('viewRequest:', e);
        document.getElementById('modalContent').innerHTML = '<div class="stap-empty">Failed to load request.</div>';
    }
}

function showEmailCompose(id, decision) {
    const r        = currentRequest;
    const hasEmail = !!(r?.requester_email);
    const wrap     = document.getElementById('emailComposeWrap_' + id);
    if (!wrap) return;

    const isApprove = decision === 'approved';
    const subject   = isApprove
        ? 'Your Footage Request #' + id + ' has been Approved — STAP Hub'
        : 'Update on Your Footage Request #' + id + ' — STAP Hub';
    const template  = isApprove ? buildApproveTemplate(r) : buildRejectTemplate(r);
    const boxClass  = isApprove ? 'req-email-approve' : 'req-email-reject';
    const btnLabel  = isApprove ? '✓ Confirm Approval &amp; Send Email' : '✗ Confirm Rejection &amp; Send Email';
    const btnStyle  = isApprove ? 'background:var(--green);' : 'background:var(--red);';

    wrap.innerHTML =
        '<div class="req-email-box ' + boxClass + '">' +
            '<div class="req-email-box-title">' + (isApprove ? '✅' : '❌') + ' ' + (isApprove ? 'Approve & Notify Requester' : 'Reject & Notify Requester') + '</div>' +
            (!hasEmail ? '<div style="font-size:12px;color:#b91c1c;margin-bottom:10px;">⚠ No email on file — status will be updated but no email will be sent.</div>' : '') +
            '<div class="stap-form-group"><label class="stap-form-label">Subject</label>' +
                '<input type="text" id="emailSubject_' + id + '" class="stap-form-input" value="' + subject.replace(/"/g, '&quot;') + '">' +
            '</div>' +
            '<div class="stap-form-group"><label class="stap-form-label">Message</label>' +
                '<textarea id="emailBody_' + id + '" class="stap-form-input" rows="12" style="font-family:monospace;font-size:12px;">' + escapeHtml(template) + '</textarea>' +
            '</div>' +
            '<div id="emailResult_' + id + '" style="display:none;font-size:12px;font-weight:600;padding:10px 14px;border-radius:8px;margin-bottom:10px;"></div>' +
            '<div style="display:flex;gap:8px;">' +
                '<button onclick="updateStatus(' + id + ', \'' + decision + '\', ' + hasEmail + ')" class="stap-btn-primary" style="' + btnStyle + 'font-size:13px;padding:9px 20px;">' + btnLabel + '</button>' +
                '<button onclick="document.getElementById(\'emailComposeWrap_' + id + '\').style.display=\'none\'" class="stap-btn-primary" style="background:var(--navy-muted);font-size:13px;padding:9px 16px;">Cancel</button>' +
            '</div>' +
        '</div>';

    wrap.style.display = 'block';
    wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function buildApproveTemplate(r) {
    const name = r.requester_name ?? 'Requester';
    const date = r.footage_date ?? (r.footage_date_start ? r.footage_date_start + ' to ' + r.footage_date_end : '—');
    return 'Dear ' + name + ',\n\nWe are pleased to inform you that your footage request (Request #' + r.request_id + ') has been approved.\n\nRequest Details:\n  Nature: ' + (r.request_nature ?? '—') + '\n  Footage Date: ' + date + '\n  Time Range: ' + (r.footage_time_start ?? '—') + ' – ' + (r.footage_time_end ?? '—') + '\n\nNext Steps:\n  1. Our MIS team has located and secured the requested footage.\n  2. Please prepare the following before claiming:\n     - Valid government-issued ID\n     - Authorization letter (if claiming on behalf of someone)\n     - [Add any other requirements here]\n  3. Visit the STAP Hub office during business hours (Mon–Fri, 8AM–5PM).\n  4. Reference this email and your Request #' + r.request_id + ' when you arrive.\n\nFor inquiries, you may reply to this email or contact us directly.\n\nRegards,\nSTAP Hub Administration\nMayor Gil Fernando Ave / Sumulong Highway, Marikina City';
}

function buildRejectTemplate(r) {
    const name = r.requester_name ?? 'Requester';
    const date = r.footage_date ?? (r.footage_date_start ? r.footage_date_start + ' to ' + r.footage_date_end : '—');
    return 'Dear ' + name + ',\n\nWe regret to inform you that your footage request (Request #' + r.request_id + ') has not been approved at this time.\n\nRequest Details:\n  Nature: ' + (r.request_nature ?? '—') + '\n  Footage Date: ' + date + '\n  Time Range: ' + (r.footage_time_start ?? '—') + ' – ' + (r.footage_time_end ?? '—') + '\n\nReason for Rejection:\n  [Please state the reason(s) here]\n\nIf you believe this was made in error or would like to resubmit, please contact us.\n\nWe apologize for any inconvenience.\n\nRegards,\nSTAP Hub Administration\nMayor Gil Fernando Ave / Sumulong Highway, Marikina City';
}


function closeModal() {
    document.getElementById('reqModal').classList.remove('is-open');
    currentRequest = null;
}

loadRequests();
</script>
@endpush