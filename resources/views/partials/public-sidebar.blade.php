<aside class="stap-sidebar" id="stapSidebar">

    {{-- Logo --}}
    <div class="stap-sidebar-logo">
        <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Hub" class="stap-logo-img stap-logo-full">
        <img src="{{ asset('images/STAP.ico') }}" alt="STAP" class="stap-logo-icon">
    </div>

    {{-- Navigation --}}
    <nav class="stap-sidebar-nav">

        <a href="{{ route('public.dashboard') }}"
           class="stap-nav-item {{ request()->routeIs('public.dashboard') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span class="stap-nav-label">Dashboard</span>
        </a>

        <a href="{{ route('public.live') }}"
           class="stap-nav-item {{ request()->routeIs('public.live') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="15" height="10" rx="2"/>
                <path d="M17 9l5-3v12l-5-3"/>
            </svg>
            <span class="stap-nav-label">Live Camera Feed</span>
        </a>

        <a href="{{ route('public.request') }}"
           class="stap-nav-item {{ request()->routeIs('public.request') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
            </svg>
            <span class="stap-nav-label">Data Request</span>
        </a>

        <a href="{{ route('incident.create') }}"
            class="stap-nav-item {{ request()->routeIs('incident.create') ? 'active' : '' }}">
            <svg class="stap-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
             <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span class="stap-nav-label">Incident Report</span>
        </a>

    </nav>

    {{-- Bottom: Admin Login --}}
    <div class="stap-sidebar-bottom">
        <button class="stap-admin-login-btn" id="openAdminLogin">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span class="stap-nav-label">Log in as Admin</span>
        </button>
    </div>

</aside>