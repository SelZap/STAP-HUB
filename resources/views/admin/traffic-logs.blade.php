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
      {{-- Added table-layout: fixed to keep columns perfectly locked in width --}}
      <table style="width:100%;border-collapse:collapse;font-size:13px;table-layout:fixed;min-width:800px;">
        <colgroup>
          <col style="width: 22%;"> {{-- Time --}}
          <col style="width: 13%;"> {{-- Camera --}}
          <col style="width: 10%;"> {{-- Cars --}}
          <col style="width: 10%;"> {{-- Trucks --}}
          <col style="width: 13%;"> {{-- Motorcycles --}}
          <col style="width: 11%;"> {{-- Jeepney --}}
          <col style="width: 11%;"> {{-- Total --}}
          <col style="width: 10%;"> {{-- LOS --}}
        </colgroup>
        <thead>
          <tr style="border-bottom:1px solid var(--border);background:var(--bg-input);">
            <th
              style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">
              Time</th>
            <th
              style="padding:11px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">
              Camera</th>
            <th
              style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">
              Cars</th>
            <th
              style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">
              Trucks</th>
            <th
              style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">
              Motorcycles</th>
            <th
              style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">
              Jeepney</th>
            <th
              style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">
              Total</th>
            <th
              style="padding:11px 16px;text-align:center;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">
              LOS</th>
          </tr>
        </thead>
        <tbody id="logsBody">
          <tr>
            <td colspan="8" class="stap-empty" style="padding:28px;text-align:center;">Loading…</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="pagination"
      style="padding:14px 20px;display:flex;gap:4px;justify-content:flex-end;border-top:1px solid var(--border);flex-wrap:wrap;">
    </div>
  </div>

@endsection

@push('scripts')
  <script>
    let currentPage = 1;

    const losColors = { A: 'var(--green)', B: 'var(--green)', C: 'var(--amber)', D: 'var(--amber)', E: 'var(--red)', F: 'var(--red)' };
    const losLabels = { A: 'Free Flow', B: 'Near Free Flow', C: 'Stable', D: 'Approaching Unstable', E: 'Unstable', F: 'Forced Flow' };

    async function loadLogs(page = 1) {
      currentPage = page;
      const from = document.getElementById('filterFrom').value;
      const to = document.getElementById('filterTo').value;
      let url = `/admin/api/traffic-logs?page=${page}`;
      if (from) url += `&date_from=${from}`;
      if (to) url += `&date_to=${to}`;

      const body = document.getElementById('logsBody');
      body.innerHTML = '<tr><td colspan="8" class="stap-empty" style="padding:28px;text-align:center;">Loading…</td></tr>';

      try
      {
        const res = await fetch(url, { headers: authHeaders() });
        const data = await res.json();
        const rows = data.data ?? [];

        document.getElementById('logCount').textContent = `${data.total ?? rows.length} record(s)`;

        if (!rows.length)
        {
          body.innerHTML = '<tr><td colspan="8" class="stap-empty" style="padding:28px;text-align:center;">No records found.</td></tr>';
          return;
        }

        body.innerHTML = rows.map(s => {
          const total = (s.cars || 0) + (s.trucks || 0) + (s.motorcycles || 0) + (s.mini_bus || 0) + (s.ambulance || 0) + (s.fire_truck || 0) + (s.tricycle || 0) + (s.jeepney || 0);

          // 1. FIX DATE: Try created_at, fallback to updated_at, timestamp, or logged_at
          const rawDate = s.created_at ?? s.updated_at ?? s.timestamp ?? s.logged_at;
          let dateStr = '—';
          if (rawDate)
          {
            const d = new Date(rawDate);
            dateStr = isNaN(d.getTime()) ? rawDate : d.toLocaleString();
          }

          // 2. FIX LOS: Check if the backend returns it as 'los' or 'congestion'
          const los = s.los ?? s.congestion ?? '—';
          const lc = losColors[los] ?? 'var(--text-muted)';

          return `<tr style="border-bottom:1px solid var(--border);">
              <td style="padding:10px 16px;color:var(--text-secondary);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${dateStr}</td>
              <td style="padding:10px 16px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${s.camera?.label ?? 'Cam ' + s.camera_id}</td>
              <td style="padding:10px 16px;text-align:center;">${s.cars ?? 0}</td>
              <td style="padding:10px 16px;text-align:center;">${s.trucks ?? 0}</td>
              <td style="padding:10px 16px;text-align:center;">${s.motorcycles ?? 0}</td>
              <td style="padding:10px 16px;text-align:center;">${s.jeepney ?? 0}</td>
              <td style="padding:10px 16px;text-align:center;font-weight:700;">${total}</td>
              <td style="padding:10px 16px;text-align:center;">
                  <span style="font-size:11px;font-weight:700;color:${lc};background:${lc}1a;padding:2px 8px;border-radius:20px;" title="${losLabels[los] ?? ''}">${los}</span>
              </td>
          </tr>`;
        }).join('');

        // Smart Windowed Pagination Layout (prevents rendering 48+ buttons out-of-bounds)
        renderPagination(data.last_page ?? 1);

      } catch (e)
      {
        console.error(e);
        body.innerHTML = '<tr><td colspan="8" class="stap-empty" style="padding:28px;text-align:center;color:var(--red);">Failed to load data.</td></tr>';
      }
    }

    function renderPagination(lastPage) {
      const pg = document.getElementById('pagination');
      pg.innerHTML = '';

      if (lastPage <= 1) return;

      const range = 2; // Number of page buttons to display on either side of active item
      let pages = [];

      // Always include page 1
      pages.push(1);

      if (currentPage - range > 2)
      {
        pages.push('...');
      }

      for (let i = Math.max(2, currentPage - range); i <= Math.min(lastPage - 1, currentPage + range); i++)
      {
        pages.push(i);
      }

      if (currentPage + range < lastPage - 1)
      {
        pages.push('...');
      }

      // Always include last page
      if (lastPage > 1)
      {
        pages.push(lastPage);
      }

      pages.forEach(p => {
        if (p === '...')
        {
          const span = document.createElement('span');
          span.textContent = '...';
          span.style.cssText = 'padding:5px 8px;color:var(--text-muted);font-size:12px;align-self:center;';
          pg.appendChild(span);
        } else
        {
          const btn = document.createElement('button');
          btn.textContent = p;
          btn.className = 'stap-btn-primary';
          btn.style.cssText = `padding:5px 11px;font-size:11px;min-width:32px;${p === currentPage ? '' : 'background:var(--navy-light);color:var(--navy);'}`;
          btn.onclick = () => loadLogs(p);
          pg.appendChild(btn);
        }
      });
    }

    function clearFilters() {
      document.getElementById('filterFrom').value = '';
      document.getElementById('filterTo').value = '';
      loadLogs();
    }

    loadLogs();
  </script>
@endpush
