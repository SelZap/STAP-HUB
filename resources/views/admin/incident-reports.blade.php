@extends('layouts.admin')

@section('title', 'Incident Reports')
@section('page-title', 'Incident Reports')

@push('styles')
  <style>
    .ir-modal-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-bottom: 16px;
    }

    .ir-field {
      background: #f8fafc;
      border-radius: 8px;
      padding: 10px 12px;
    }

    .ir-field-label {
      font-size: 10px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #64748b;
      margin-bottom: 4px;
    }

    .ir-field-value {
      font-size: 13px;
      font-weight: 600;
      color: #0f172a;
      line-height: 1.4;
    }

    .ir-field-full {
      grid-column: 1/-1;
    }

    .ir-section-title {
      font-size: 10px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #64748b;
      margin: 18px 0 10px;
      padding-bottom: 6px;
      border-bottom: 1px solid #e2e8f0;
    }

    .ir-modal-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 16px;
    }

    .ir-modal-title {
      font-size: 17px;
      font-weight: 800;
      color: #0f172a;
      margin: 0;
    }

    .ir-modal-badges {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      margin-top: 6px;
    }

    .ir-vtag {
      display: inline-block;
      padding: 2px 9px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      background: #e0f2fe;
      color: #0369a1;
    }

    .ir-actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      padding: 14px 0 0;
      border-top: 1px solid #e2e8f0;
      margin-top: 14px;
    }
  </style>
@endpush

@section('content')

  <div class="stap-tabs stap-mb-2">
    <button class="stap-tab active" onclick="filterReports(this,'')">All</button>
    <button class="stap-tab" onclick="filterReports(this,'pending')">Pending</button>
    <button class="stap-tab" onclick="filterReports(this,'reviewed')">Reviewed</button>
  </div>

  <div id="reportsList">
    <div class="stap-empty">Loading reports…</div>
  </div>

  <div class="stap-modal-overlay" id="reportModal">
    <div class="stap-modal"
      style="width:720px;max-width:calc(100vw - 32px);max-height:90vh;overflow-y:auto;border-radius:14px;">
      <button class="stap-modal-close" onclick="closeModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>
      <div id="reportModalContent" style="padding:4px 0 8px;"></div>
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    function jsonHeaders() {
      return { ...authHeaders(), 'Content-Type': 'application/json' };
    }

    let currentStatus = '';
    let allReports = [];

    async function loadReports() {
      const list = document.getElementById('reportsList');
      list.innerHTML = '<div class="stap-empty">Loading…</div>';
      try
      {
        const res = await fetch('/admin/incident-reports', { headers: authHeaders() });
        if (!res.ok) throw new Error();
        const data = await res.json();
        allReports = data.data ?? data;
        renderReports();
      } catch (e)
      {
        list.innerHTML = '<div class="stap-empty">Failed to load reports.</div>';
      }
    }

    function renderReports() {
      const list = document.getElementById('reportsList');
      const reports = currentStatus ? allReports.filter(r => r.status === currentStatus) : allReports;

      if (!reports.length) { list.innerHTML = '<div class="stap-empty">No incident reports found.</div>'; return; }

      list.innerHTML = reports.map(r => {
        const isPending = r.status === 'pending';
        const date = r.incident_date ?? '—';
        const submitted = r.created_at ? new Date(r.created_at).toLocaleDateString('en-PH') : '—';
        const statusBg = isPending ? 'color:var(--amber);background:var(--amber-bg)' : 'color:var(--green);background:var(--green-bg)';
        const rid = r.incident_id ?? r.id;

        return '<div class="stap-card stap-mb-1" style="padding:14px 18px;display:flex;align-items:center;gap:16px;">' +
          '<div style="flex:1;min-width:0;">' +
          '<div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap;">' +
          '<span style="font-weight:700;font-size:13.5px;">' + (r.reporting_party_name ?? '—') + '</span>' +
          '<span style="font-size:10px;font-weight:700;' + statusBg + ';padding:2px 9px;border-radius:20px;text-transform:uppercase;">' + (r.status ?? '—') + '</span>' +
          (r.people_hurt ? '<span style="font-size:10px;font-weight:700;color:#b91c1c;background:#fee2e2;padding:2px 9px;border-radius:20px;">⚠ INJURIES</span>' : '') +
          '</div>' +
          '<div class="stap-text-xs stap-muted">📅 Incident: <strong>' + date + '</strong> &nbsp;·&nbsp; Submitted: ' + submitted + '</div>' +
          '<div class="stap-text-xs stap-muted stap-mt-1" style="max-width:520px;">' + (r.description ?? '').substring(0, 100) + ((r.description?.length > 100) ? '…' : '') + '</div>' +
          '</div>' +
          '<div style="display:flex;gap:8px;flex-shrink:0;">' +
          '<button onclick="viewReport(' + rid + ')" class="stap-btn-primary" style="padding:8px 16px;font-size:12px;">View</button>' +
          (isPending ? '<button onclick="markReviewed(' + rid + ', this)" class="stap-btn-primary stap-btn-green" style="padding:8px 16px;font-size:12px;">Mark Reviewed</button>' : '') +
          '</div>' +
          '</div>';
      }).join('');
    }

    function filterReports(btn, status) {
      document.querySelectorAll('.stap-tab').forEach(t => t.classList.remove('active'));
      btn.classList.add('active');
      currentStatus = status;
      renderReports();
    }

    async function viewReport(id) {
      document.getElementById('reportModal').classList.add('is-open');
      document.getElementById('reportModalContent').innerHTML = '<div class="stap-empty" style="padding:40px 0;">Loading…</div>';

      let r = allReports.find(x => (x.incident_id ?? x.id) == id);
      if (!r)
      {
        try
        {
          const res = await fetch('/admin/incident-reports', { headers: authHeaders() });
          const data = await res.json();
          r = (data.data ?? data).find(x => (x.incident_id ?? x.id) == id);
        } catch (e) { }
      }
      if (!r) { document.getElementById('reportModalContent').innerHTML = '<div class="stap-empty">Report not found.</div>'; return; }

      const rid = r.incident_id ?? r.id;
      const isPending = r.status === 'pending';
      const statusBg = isPending ? '#fef3c7;color:#92400e' : '#dcfce7;color:#166534';
      const vtypes = r.vehicle_type
        ? r.vehicle_type.split(',').map(v => '<span class="ir-vtag">' + v.trim().replace('_', ' ') + '</span>').join('')
        : '<span style="color:#94a3b8;font-size:13px;">None specified</span>';

      let html =
        '<div class="ir-modal-header">' +
        '<div>' +
        '<p class="ir-modal-title">Incident Report #' + String(rid).padStart(5, '0') + '</p>' +
        '<div class="ir-modal-badges">' +
        '<span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:' + statusBg + ';">' + (r.status ?? '').toUpperCase() + '</span>' +
        (r.people_hurt ? '<span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:#fee2e2;color:#b91c1c;">⚠ INJURIES REPORTED</span>' : '') +
        '</div>' +
        '</div>' +
        '</div>' +

        '<div class="ir-section-title">Reporter Information</div>' +
        '<div class="ir-modal-grid">' +
        '<div class="ir-field"><div class="ir-field-label">Full Name</div><div class="ir-field-value">' + (r.reporting_party_name ?? '—') + '</div></div>' +
        '<div class="ir-field"><div class="ir-field-label">Email Address</div><div class="ir-field-value">' + (r.reporter_email ?? '<span style="color:#94a3b8;">Not provided</span>') + '</div></div>' +
        '</div>' +

        '<div class="ir-section-title">Incident Details</div>' +
        '<div class="ir-modal-grid">' +
        '<div class="ir-field"><div class="ir-field-label">Date</div><div class="ir-field-value">' + (r.incident_date ?? '—') + '</div></div>' +
        '<div class="ir-field"><div class="ir-field-label">Time</div><div class="ir-field-value">' + (r.incident_time ?? '—') + '</div></div>' +
        '<div class="ir-field"><div class="ir-field-label">Environment</div><div class="ir-field-value" style="text-transform:capitalize;">' + (r.environmental_condition ?? '—') + '</div></div>' +
        '<div class="ir-field"><div class="ir-field-label">Injuries</div><div class="ir-field-value">' + (r.people_hurt ? 'Yes — <strong>' + (r.injured_count ?? 0) + '</strong> injured' : 'None reported') + '</div></div>' +
        '<div class="ir-field ir-field-full"><div class="ir-field-label">Location</div><div class="ir-field-value">' + (r.location_description ?? '—') + '</div></div>' +
        '</div>' +

        '<div class="ir-section-title">Vehicle Information</div>' +
        '<div class="ir-modal-grid">' +
        '<div class="ir-field"><div class="ir-field-label">Vehicle Type(s)</div><div class="ir-field-value" style="display:flex;flex-wrap:wrap;gap:5px;margin-top:2px;">' + vtypes + '</div></div>' +
        '<div class="ir-field"><div class="ir-field-label">Vehicle Count</div><div class="ir-field-value">' + (r.vehicle_count ?? '—') + '</div></div>' +
        '</div>' +

        '<div class="ir-section-title">Description</div>' +
        '<div style="background:#f8fafc;border-radius:8px;padding:14px;font-size:13px;line-height:1.75;color:#334155;margin-bottom:4px;">' + (r.description ?? '<em style="color:#94a3b8;">No description provided.</em>') + '</div>' +

        '<div class="ir-actions">' +
        (isPending ? '<button onclick="markReviewed(' + rid + ', this); closeModal();" class="stap-btn-primary stap-btn-green" style="font-size:13px;padding:9px 20px;">✓ Mark as Reviewed</button>' : '') +
        '</div>';

      document.getElementById('reportModalContent').innerHTML = html;
    }

    async function markReviewed(id, btn) {
      if (btn) { btn.disabled = true; btn.textContent = '…'; }
      try
      {
        const res = await fetch('/admin/incident-reports/' + id + '/review', {
          method: 'PATCH', headers: authHeaders(), credentials: 'same-origin',
        });
        if (!res.ok) { const d = await res.json(); alert(d.message ?? 'Error'); return; }
        await loadReports();
      } catch (e) { alert('Request failed.'); }
    }

    function closeModal() {
      document.getElementById('reportModal').classList.remove('is-open');
    }

    loadReports();
  </script>
@endpush
