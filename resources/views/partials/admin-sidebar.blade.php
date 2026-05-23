<aside class="stap-sidebar" id="stapSidebar">

    <div class="stap-sidebar-logo">
        <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Hub" class="stap-logo-full">
        <img src="{{ asset('images/STAP.ico') }}" alt="STAP" class="stap-logo-icon">
    </div>

    <div style="padding: 0 10px 8px; font-size:10px; font-weight:700; color:rgba(255,255,255,0.35); letter-spacing:1.2px; text-transform:uppercase;">
        Admin Panel
    </div>

    <nav class="stap-sidebar-nav">

        <a href="{{ route('admin.dashboard') }}"
           class="stap-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span class="stap-nav-label">Dashboard</span>
        </a>

        <a href="{{ route('admin.cameras') }}"
           class="stap-nav-item {{ request()->routeIs('admin.cameras') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="15" height="10" rx="2"/>
                <path d="M17 9l5-3v12l-5-3"/>
            </svg>
            <span class="stap-nav-label">Cameras</span>
        </a>

        <a href="{{ route('admin.traffic-logs') }}"
           class="stap-nav-item {{ request()->routeIs('admin.traffic-logs') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 4-6"/>
            </svg>
            <span class="stap-nav-label">Traffic Logs</span>
        </a>

        <a href="{{ route('admin.traffic-lights') }}"
           class="stap-nav-item {{ request()->routeIs('admin.traffic-lights') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="8" y="2" width="8" height="20" rx="2"/>
                <circle cx="12" cy="7" r="2"/>
                <circle cx="12" cy="12" r="2"/>
                <circle cx="12" cy="17" r="2"/>
            </svg>
            <span class="stap-nav-label">Traffic Lights</span>
        </a>

        <a href="{{ route('admin.alerts') }}"
           class="stap-nav-item {{ request()->routeIs('admin.alerts') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span class="stap-nav-label">Alerts</span>
            <span id="alertBadge" style="margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:20px;display:none;"></span>
        </a>

        <a href="{{ route('admin.requests') }}"
           class="stap-nav-item {{ request()->routeIs('admin.requests*') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            </svg>
            <span class="stap-nav-label">Footage Requests</span>
        </a>

        <a href="{{ route('admin.incident-reports.index') }}"
           class="stap-nav-item {{ request()->routeIs('admin.incident-reports*') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="12" y1="18" x2="12" y2="12"/>
                <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
            <span class="stap-nav-label">Incident Reports</span>
        </a>

        <a href="{{ route('admin.announcements') }}"
           class="stap-nav-item {{ request()->routeIs('admin.announcements*') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
            <span class="stap-nav-label">Announcements</span>
        </a>

        <div id="accountsNavItem" style="display:none;">
            <a href="{{ route('admin.accounts') }}"
               class="stap-nav-item {{ request()->routeIs('admin.accounts*') ? 'active' : '' }}">
                <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                    <path d="M16 3.13a4 4 0 010 7.75"/>
                </svg>
                <span class="stap-nav-label">Accounts</span>
            </a>
        </div>

    </nav>

</aside>

<script>
    // Show Accounts link only for superusers
    const _adminData = JSON.parse(sessionStorage.getItem('admin_data') || '{}');
    if (_adminData.is_superuser) {
        document.getElementById('accountsNavItem').style.display = 'block';
    }

    // Load unresolved alert count for badge
    fetch('/admin/api/alerts?resolved=false', {
        headers: { 'Authorization': 'Bearer ' + sessionStorage.getItem('admin_token') }
    }).then(r => r.json()).then(data => {
        const count = data.total || 0;
        const badge = document.getElementById('alertBadge');
        if (count > 0 && badge) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        }
    }).catch(() => {});
</script>