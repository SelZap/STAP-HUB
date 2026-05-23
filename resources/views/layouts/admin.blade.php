<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — STAP Hub Admin Panel</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/STAP.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')

    <style>
        .stap-admin-panel-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--navy);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
        }
        .stap-admin-panel-badge::before {
            content: '';
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #4ade80;
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.5; transform: scale(0.8); }
        }
        .stap-welcome-toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            background: var(--navy);
            color: #fff;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 9999;
            transform: translateY(80px);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(.22,1,.36,1), opacity 0.4s ease;
        }
        .stap-welcome-toast.show { transform: translateY(0); opacity: 1; }
        .stap-welcome-toast .toast-icon { font-size: 18px; }
        .stap-welcome-toast .toast-sub  { font-size: 11px; font-weight: 400; opacity: 0.7; margin-top: 2px; }
    </style>
</head>
<body>

<div class="stap-wrapper">

    @include('partials.admin-sidebar')

    <div class="stap-main">

        <div class="stap-topbar">
            <div class="stap-topbar-left" style="display:flex;align-items:center;gap:12px;">
                <h1 class="stap-page-title">@yield('page-title', 'Dashboard')</h1>
                <span class="stap-admin-panel-badge">Admin Panel</span>
            </div>
            <div class="stap-topbar-right" style="display:flex;align-items:center;gap:12px;">
                <span id="adminNameBadge" style="font-size:12px;font-weight:600;color:var(--text-secondary);"></span>
                <span class="stap-date">{{ \Carbon\Carbon::now()->format('F d, Y') }}</span>

                <a href="{{ url('/') }}" target="_blank" class="stap-btn-primary"
                   style="padding:7px 14px;font-size:11px;letter-spacing:0.5px;background:var(--navy-muted);text-decoration:none;">
                    ← PUBLIC SIDE
                </a>

                <button id="adminLogoutBtn" class="stap-btn-primary" style="padding:7px 14px;font-size:11px;letter-spacing:0.5px;">
                    LOG OUT
                </button>
            </div>
        </div>

        <div class="stap-content">
            @yield('content')
        </div>

    </div>
</div>

<div class="stap-welcome-toast" id="welcomeToast">
    <span class="toast-icon">👋</span>
    <div>
        <div id="welcomeToastName">Welcome back!</div>
        <div class="toast-sub">You are logged in to the Admin Dashboard</div>
    </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
<script>
    const adminToken = sessionStorage.getItem('admin_token');
    if (!adminToken) window.location.href = '/';

    const adminData = JSON.parse(sessionStorage.getItem('admin_data') || '{}');

    const badge = document.getElementById('adminNameBadge');
    if (badge && adminData.name) {
        badge.textContent = '👤 ' + adminData.name + (adminData.is_superuser ? ' · Superuser' : '');
    }

    const toastShown = sessionStorage.getItem('stap_toast_shown');
    if (!toastShown && adminData.name) {
        const toast = document.getElementById('welcomeToast');
        document.getElementById('welcomeToastName').textContent = 'Welcome, ' + adminData.name + '!';
        setTimeout(() => toast.classList.add('show'), 400);
        setTimeout(() => toast.classList.remove('show'), 4000);
        sessionStorage.setItem('stap_toast_shown', '1');
    }

    document.getElementById('adminLogoutBtn')?.addEventListener('click', async () => {
        try {
            await fetch('/admin/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Authorization': 'Bearer ' + adminToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });
        } catch(e) {}
        sessionStorage.removeItem('admin_token');
        sessionStorage.removeItem('admin_data');
        sessionStorage.removeItem('stap_toast_shown');
        window.location.href = '/';
    });

    /**
     * Base headers for all admin fetch() calls.
     * NOTE: Content-Type is intentionally omitted here.
     * - For JSON bodies: add 'Content-Type': 'application/json' manually at the call site.
     * - For FormData bodies: never set Content-Type — the browser sets multipart/form-data
     *   with the correct boundary automatically. Setting it manually breaks file uploads.
     */
    function authHeaders() {
        return {
            'Accept':            'application/json',
            'X-Requested-With':  'XMLHttpRequest',
            'X-CSRF-TOKEN':      document.querySelector('meta[name="csrf-token"]').content,
            'Authorization':     'Bearer ' + (sessionStorage.getItem('admin_token') ?? ''),
        };
    }
</script>
@stack('scripts')

</body>
</html>