@extends('layouts.admin')

@section('title', 'Announcements')
@section('page-title', 'Announcements')

@section('content')

<div style="display:grid;grid-template-columns:380px 1fr;gap:20px;align-items:start;">

    <div class="stap-card" style="padding:20px;">
        <h3 style="font-size:13px;font-weight:800;margin-bottom:16px;text-transform:uppercase;letter-spacing:0.5px;">Create Announcement</h3>

        <div id="createError" class="stap-form-error" style="display:none;margin-bottom:12px;"></div>
        <div id="createSuccess" style="display:none;font-size:12px;font-weight:600;color:#166534;background:#dcfce7;padding:10px 12px;border-radius:8px;margin-bottom:12px;"></div>

        <div class="stap-form-group">
            <label class="stap-form-label">Title <span style="color:var(--red);">*</span></label>
            <input type="text" id="aTitle" class="stap-form-input" placeholder="e.g. Road closure on Sumulong Hwy">
        </div>

        <div class="stap-form-group">
            <label class="stap-form-label">Type <span style="color:var(--red);">*</span></label>
            <select id="aType" class="stap-form-input">
                <option value="general">📢 General</option>
                <option value="incident">🚨 Incident</option>
                <option value="weather">🌧️ Weather</option>
                <option value="maintenance">🔧 Maintenance</option>
                <option value="emergency">⚠️ Emergency</option>
            </select>
        </div>

        <div class="stap-form-group">
            <label class="stap-form-label">Message <span style="color:var(--red);">*</span></label>
            <textarea id="aContent" class="stap-form-input" rows="4" placeholder="Announcement details…"></textarea>
        </div>

        <div class="stap-form-group">
            <label class="stap-form-label">Attachment <span style="font-weight:400;color:var(--text-muted);">(Optional — image, video, or PDF)</span></label>
            <input type="file" id="aAttachment" class="stap-form-input" accept="image/*,video/mp4,.pdf" style="padding:8px;cursor:pointer;">
            <div id="aAttachmentName" style="font-size:11px;color:var(--text-muted);margin-top:4px;display:none;"></div>
        </div>

        <div class="stap-form-group">
            <label class="stap-form-label">Link to Incident Report <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
            <select id="aIncidentReport" class="stap-form-input">
                <option value="">— None —</option>
            </select>
        </div>

        <div class="stap-form-group">
            <label class="stap-form-label">Expires At <span style="font-weight:400;color:var(--text-muted);">(Optional)</span></label>
            <input type="datetime-local" id="aExpiresAt" class="stap-form-input" min="{{ now()->format('Y-m-d\TH:i') }}">
        </div>

        <div class="stap-form-group" style="display:flex;align-items:center;gap:10px;">
            <input type="checkbox" id="aIsActive" checked style="width:16px;height:16px;cursor:pointer;">
            <label for="aIsActive" class="stap-form-label" style="margin-bottom:0;cursor:pointer;">Publish immediately</label>
        </div>

        <button onclick="createAnnouncement()" class="stap-btn-primary stap-btn-full stap-mt-2" id="createBtn">
            <span id="createBtnText">Publish Announcement</span>
            <span id="createSpinner" class="stap-spinner" style="display:none;"></span>
        </button>
    </div>

    <div>
        <div style="margin-bottom:12px;">
            <span class="stap-text-xs stap-muted" id="annCount"></span>
        </div>
        <div id="annList"><div class="stap-empty">Loading announcements…</div></div>
        <div id="annPagination" style="display:flex;gap:8px;justify-content:center;margin-top:14px;"></div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function buildHeaders() {
    return { ...authHeaders() };
}

const typeInfo = {
    general:     { icon: '📢', label: 'General',     color: 'var(--navy)'   },
    incident:    { icon: '🚨', label: 'Incident',    color: '#b91c1c'       },
    weather:     { icon: '🌧️', label: 'Weather',     color: '#1d4ed8'       },
    maintenance: { icon: '🔧', label: 'Maintenance', color: '#7c3aed'       },
    emergency:   { icon: '⚠️', label: 'Emergency',   color: '#c2410c'       },
};

document.getElementById('aAttachment').addEventListener('change', function () {
    const el = document.getElementById('aAttachmentName');
    el.textContent   = this.files.length ? '📎 ' + this.files[0].name : '';
    el.style.display = this.files.length ? 'block' : 'none';
});

async function loadIncidentReportOptions() {
    try {
        const res  = await fetch('/admin/incident-reports', { headers: buildHeaders() });
        const data = await res.json();
        const list = Array.isArray(data) ? data : (data.data ?? []);
        const sel  = document.getElementById('aIncidentReport');
        list.forEach(r => {
            const opt       = document.createElement('option');
            opt.value       = r.incident_id;
            opt.textContent = '#' + r.incident_id + ' — ' + (r.reporting_party_name ?? '?') + ' (' + (r.incident_date ?? r.created_at?.slice(0, 10) ?? '?') + ')';
            sel.appendChild(opt);
        });
    } catch (e) {
        console.error('loadIncidentReportOptions:', e);
    }
}

async function createAnnouncement() {
    const btn  = document.getElementById('createBtn');
    const text = document.getElementById('createBtnText');
    const spin = document.getElementById('createSpinner');
    const err  = document.getElementById('createError');
    const suc  = document.getElementById('createSuccess');

    err.style.display = suc.style.display = 'none';

    const title   = document.getElementById('aTitle').value.trim();
    const content = document.getElementById('aContent').value.trim();

    if (!title || !content) {
        err.textContent   = 'Title and Message are required.';
        err.style.display = 'block';
        return;
    }

    btn.disabled       = true;
    text.style.display = 'none';
    spin.style.display = 'inline-block';

    try {
        const fd = new FormData();
        fd.append('title',     title);
        fd.append('type',      document.getElementById('aType').value);
        fd.append('content',   content);
        fd.append('is_active', document.getElementById('aIsActive').checked ? '1' : '0');

        const incidentId = document.getElementById('aIncidentReport').value;
        if (incidentId) fd.append('incident_report_id', incidentId);

        const expiresAt = document.getElementById('aExpiresAt').value;
        if (expiresAt) fd.append('expires_at', expiresAt);

        const file = document.getElementById('aAttachment');
        if (file.files.length) fd.append('attachment', file.files[0]);

        const res  = await fetch('/admin/announcements', {
            method:      'POST',
            headers:     buildHeaders(),
            credentials: 'same-origin',
            body:        fd,
        });
        const data = await res.json();

        if (!res.ok) {
            err.textContent   = data.message ?? (data.errors ? Object.values(data.errors).flat().join(' ') : 'An error occurred.');
            err.style.display = 'block';
        } else {
            suc.textContent   = 'Announcement published!';
            suc.style.display = 'block';
            resetForm();
            loadAnnouncements();
        }
    } catch (e) {
        console.error('createAnnouncement:', e);
        err.textContent   = 'Request failed. Please try again.';
        err.style.display = 'block';
    }

    btn.disabled       = false;
    text.style.display = 'inline';
    spin.style.display = 'none';
}

function resetForm() {
    ['aTitle', 'aContent', 'aExpiresAt'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('aType').value           = 'general';
    document.getElementById('aIncidentReport').value = '';
    document.getElementById('aIsActive').checked     = true;
    document.getElementById('aAttachment').value     = '';
    document.getElementById('aAttachmentName').style.display = 'none';
}

async function loadAnnouncements(page = 1) {
    document.getElementById('annList').innerHTML = '<div class="stap-empty">Loading…</div>';
    try {
        const res  = await fetch('/admin/api/announcements?page=' + page, { headers: buildHeaders() });
        const data = await res.json();
        const rows = data.data ?? [];

        document.getElementById('annCount').textContent = (data.total ?? rows.length) + ' announcement(s)';

        if (!rows.length) {
            document.getElementById('annList').innerHTML       = '<div class="stap-empty">No announcements yet.</div>';
            document.getElementById('annPagination').innerHTML = '';
            return;
        }

        document.getElementById('annList').innerHTML = rows.map(renderRow).join('');
        renderPagination(data.current_page ?? 1, data.last_page ?? 1);
    } catch (e) {
        console.error('loadAnnouncements:', e);
        document.getElementById('annList').innerHTML = '<div class="stap-empty">Failed to load announcements.</div>';
    }
}

function renderRow(a) {
    const ti        = typeInfo[a.type] ?? typeInfo.general;
    const creator   = a.creator?.admin_name ?? ('Admin #' + a.created_by);
    const createdAt = a.created_at ? new Date(a.created_at).toLocaleString('en-PH') : '—';

    let expiresLabel = 'No expiry';
    let isExpired    = false;
    if (a.expires_at) {
        const d   = new Date(a.expires_at.replace(' ', 'T'));
        isExpired    = d < new Date();
        expiresLabel = d.toLocaleString('en-PH', { dateStyle: 'medium', timeStyle: 'short' });
    }

    const statusLabel = !a.is_active ? 'Inactive' : isExpired ? 'Expired' : 'Active';
    const statusColor = !a.is_active ? 'var(--text-muted)' : isExpired ? 'var(--amber)' : 'var(--green)';
    const linked      = a.incident_report ? 'Incident #' + a.incident_report.incident_id : null;
    const attachment  = a.attachment_url
        ? '<a href="' + a.attachment_url + '" target="_blank" style="font-size:11px;color:var(--navy);font-weight:600;text-decoration:none;">📎 ' + (a.attachment_name ?? 'View Attachment') + '</a>'
        : '';

    return '<div class="stap-card stap-mb-1" style="padding:14px 18px;">' +
        '<div style="display:flex;align-items:flex-start;gap:12px;">' +
            '<div style="font-size:22px;flex-shrink:0;margin-top:2px;">' + ti.icon + '</div>' +
            '<div style="flex:1;min-width:0;">' +
                '<div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap;">' +
                    '<span style="font-weight:700;font-size:13px;">' + a.title + '</span>' +
                    '<span style="font-size:10px;font-weight:700;color:' + ti.color + ';background:' + ti.color + '1a;padding:2px 8px;border-radius:20px;text-transform:uppercase;">' + ti.label + '</span>' +
                    '<span style="font-size:10px;font-weight:700;color:' + statusColor + ';background:' + statusColor + '1a;padding:2px 8px;border-radius:20px;">' + statusLabel + '</span>' +
                '</div>' +
                '<div style="font-size:12px;color:var(--text-secondary);margin-bottom:7px;line-height:1.5;">' + a.content + '</div>' +
                '<div style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">' +
                    '<span class="stap-text-xs stap-muted">By <strong>' + creator + '</strong></span>' +
                    '<span class="stap-text-xs stap-muted">Created ' + createdAt + '</span>' +
                    '<span class="stap-text-xs stap-muted">⏱ ' + (a.expires_at ? 'Expires ' + expiresLabel : 'No expiry') + '</span>' +
                    (linked ? '<span class="stap-text-xs stap-muted">🔗 ' + linked + '</span>' : '') +
                    attachment +
                '</div>' +
            '</div>' +
            '<div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0;">' +
                '<button onclick="toggleAnnouncement(' + a.announcement_id + ')" class="stap-btn-primary" style="padding:6px 14px;font-size:11px;background:' + (a.is_active ? 'var(--amber)' : 'var(--green)') + ';">' +
                    (a.is_active ? 'Deactivate' : 'Activate') +
                '</button>' +
                '<button onclick="deleteAnnouncement(' + a.announcement_id + ')" class="stap-btn-primary" style="padding:6px 14px;font-size:11px;background:var(--red);">Delete</button>' +
            '</div>' +
        '</div>' +
    '</div>';
}

function renderPagination(current, last) {
    if (last <= 1) { document.getElementById('annPagination').innerHTML = ''; return; }
    document.getElementById('annPagination').innerHTML = Array.from({ length: last }, (_, i) => i + 1)
        .map(p => '<button onclick="loadAnnouncements(' + p + ')" class="stap-btn-primary" style="padding:6px 12px;font-size:11px;' + (p === current ? '' : 'background:var(--navy-muted);') + '">' + p + '</button>')
        .join('');
}

async function toggleAnnouncement(id) {
    try {
        const res  = await fetch('/admin/announcements/' + id + '/toggle', {
            method: 'PATCH', headers: buildHeaders(), credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok) { alert(data.message ?? 'Failed to toggle.'); return; }
        loadAnnouncements();
    } catch (e) {
        console.error('toggleAnnouncement:', e);
        alert('Request failed. Please try again.');
    }
}

async function deleteAnnouncement(id) {
    if (!confirm('Delete this announcement? It will no longer appear on the public site.')) return;
    try {
        const res  = await fetch('/admin/announcements/' + id, {
            method: 'DELETE', headers: buildHeaders(), credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok) { alert(data.message ?? 'Failed to delete.'); return; }
        loadAnnouncements();
    } catch (e) {
        console.error('deleteAnnouncement:', e);
        alert('Request failed. Please try again.');
    }
}

loadIncidentReportOptions();
loadAnnouncements();
</script>
@endpush