@extends('layouts.admin')

@section('title', 'Traffic Logs')
@section('page-title', 'Traffic Logs')

@section('content')

{{-- Filters --}}
<div class="stap-card stap-mb-2" style="padding:16px 20px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <div>
            <label class="stap-form-label">From</label>
            <input type="date" id="filterFrom" class="stap-form-input" style="width:160px;">
        </div>
        <div>
            <label class="stap-form-label">To</label>
            <input type="date" id="filterTo" class="stap-form-input" style="width:160px;">
        </div>
        <button class="stap-btn-primary" onclick="loadLogs()">Apply Filter</button>
        <button class="stap-btn-primary" style="background:var(--navy-muted);" onclick="clearFilters()">Clear</button>
        <span id="logCount" class="stap-text-xs stap-muted" style="margin-left:auto;align-self:center;"></span>
    </div>
</div>

{{-- Table --}}
<div class="stap-card">
    <div class="stap-card-body" style="padding:0;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);background:var(--bg-input);">
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Time</th>
                    <th style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Camera</th>
                    <th style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Cars</th>
                    <th style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Trucks</th>
                    <th style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Motorcycles</th>
                    <th style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Jeepney</th>
                    <th style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Total</th>
                    <th style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">LOS</th>
                </tr>
            </thead>
            <tbody id="logsBody">
                <tr><td colspan="8" class="stap-empty" style="padding:28px;">Loading…</td></tr>
            </tbody>
        </table>
    </div>
    <div id="pagination" style="padding:14px 20px;display:flex;gap:8px;justify-content:flex-end;border-top:1px solid var(--border);"></div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;

const losColors = { A:'var(--green)', B:'var(--green)', C:'var(--amber)', D:'var(--amber)', E:'var(--red)', F:'var(--red)' };
const losLabels = { A:'Free Flow', B:'Near Free Flow', C:'Stable', D:'Approaching Unstable', E:'Unstable', F:'Forced Flow' };

async function loadLogs(page = 1) {
    currentPage = page;
    const from = document.getElementById('filterFrom').value;
    const to   = document.getElementById('filterTo').value;
    let url = `/admin/api/traffic-logs?page=${page}`;
    if (from) url += `&date_from=${from}`;
    if (to)   url += `&date_to=${to}`;

    const body = document.getElementById('logsBody');
    body.innerHTML = '<tr><td colspan="8" class="stap-empty" style="padding:28px;">Loading…</td></tr>';

    try {
        const res  = await fetch(url, { headers: authHeaders() });
        const data = await res.json();
        const rows = data.data ?? [];

        document.getElementById('logCount').textContent = `${data.total ?? rows.length} record(s)`;

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="8" class="stap-empty" style="padding:28px;">No records found.</td></tr>';
            return;
        }

        body.innerHTML = rows.map(s => {
            const total = (s.cars||0)+(s.trucks||0)+(s.motorcycles||0)+(s.mini_bus||0)+(s.ambulance||0)+(s.fire_truck||0)+(s.tricycle||0)+(s.jeepney||0);
            const los   = s.congestion ?? '—';
            const lc    = losColors[los] ?? 'var(--text-muted)';
            const date  = s.created_at ? new Date(s.created_at).toLocaleString() : '—';
            return `<tr style="border-bottom:1px solid var(--border);">
                <td style="padding:10px 16px;color:var(--text-secondary);font-size:12px;">${date}</td>
                <td style="padding:10px 16px;font-weight:600;">${s.camera?.label ?? 'Cam ' + s.camera_id}</td>
                <td style="padding:10px 16px;text-align:center;">${s.cars ?? 0}</td>
                <td style="padding:10px 16px;text-align:center;">${s.trucks ?? 0}</td>
                <td style="padding:10px 16px;text-align:center;">${s.motorcycles ?? 0}</td>
                <td style="padding:10px 16px;text-align:center;">${s.jeepney ?? 0}</td>
                <td style="padding:10px 16px;text-align:center;font-weight:700;">${total}</td>
                <td style="padding:10px 16px;text-align:center;">
                    <span style="font-size:11px;font-weight:700;color:${lc};background:${lc}1a;padding:2px 8px;border-radius:20px;" title="${losLabels[los]??''}">${los}</span>
                </td>
            </tr>`;
        }).join('');

        // Pagination
        const pg = document.getElementById('pagination');
        const lastPage = data.last_page ?? 1;
        pg.innerHTML = '';
        for (let i = 1; i <= lastPage; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'stap-btn-primary';
            btn.style.cssText = `padding:5px 11px;font-size:11px;${i===currentPage?'':'background:var(--navy-light);color:var(--navy);'}`;
            btn.onclick = () => loadLogs(i);
            pg.appendChild(btn);
        }

    } catch(e) { console.error(e); }
}

function clearFilters() {
    document.getElementById('filterFrom').value = '';
    document.getElementById('filterTo').value = '';
    loadLogs();
}

loadLogs();
</script>
@endpush
