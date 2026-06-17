<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'STAP Hub') — Smart Traffic Automation Program</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/STAP.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>

    {{-- App CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Page-specific styles --}}
    @stack('styles')

    <style>
        /* ── Announcement Notification Stack ── */
        #stap-ann-stack {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column-reverse;
            gap: 10px;
            width: 340px;
            max-width: calc(100vw - 48px);
            pointer-events: none;
        }

        .stap-ann-card {
            pointer-events: all;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 14px 14px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.5;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
            animation: stap-ann-in 0.25s ease;
            position: relative;
        }

        @keyframes stap-ann-in {
            from { opacity: 0; transform: translateX(24px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .stap-ann-card.dismissing {
            animation: stap-ann-out 0.2s ease forwards;
        }

        @keyframes stap-ann-out {
            to { opacity: 0; transform: translateX(32px); }
        }

        .stap-ann-card.type-general     { background: #1b2744; color: #fff; }
        .stap-ann-card.type-incident    { background: #7f1d1d; color: #fff; }
        .stap-ann-card.type-weather     { background: #1e3a8a; color: #fff; }
        .stap-ann-card.type-maintenance { background: #4c1d95; color: #fff; }
        .stap-ann-card.type-emergency   { background: #c2410c; color: #fff; }

        /* Accent left border per type */
        .stap-ann-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: 10px 0 0 10px;
            background: rgba(255, 255, 255, 0.35);
        }

        .stap-ann-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

        .stap-ann-body  { flex: 1; min-width: 0; }
        .stap-ann-title { display: block; font-weight: 700; font-size: 13px; margin-bottom: 2px; }
        .stap-ann-msg   { display: block; opacity: 0.88; font-size: 12.5px; }
        .stap-ann-meta  { display: block; font-size: 11px; opacity: 0.55; margin-top: 5px; }

        /* ── Attachment preview ── */
        .stap-ann-attachment-img {
            display: block;
            width: 100%;
            max-height: 140px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 8px;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.15);
        }

        .stap-ann-attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 8px;
            font-size: 11.5px;
            font-weight: 600;
            color: inherit;
            opacity: 0.85;
            text-decoration: underline;
        }
        .stap-ann-attachment-link:hover { opacity: 1; }

        .stap-ann-close {
            background: rgba(255, 255, 255, 0.12);
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 13px;
            line-height: 1;
            padding: 4px 6px;
            border-radius: 5px;
            flex-shrink: 0;
            align-self: flex-start;
            transition: background 0.15s;
        }
        .stap-ann-close:hover { background: rgba(255, 255, 255, 0.25); }
    </style>
</head>
<body>

<div class="stap-wrapper">

    {{-- Sidebar --}}
    @include('partials.public-sidebar')

    {{-- Main content area --}}
    <div class="stap-main">

        {{-- Top bar --}}
        <div class="stap-topbar">
            <div class="stap-topbar-left">
                <h1 class="stap-page-title">@yield('page-title', 'Dashboard')</h1>
            </div>
            <div class="stap-topbar-right">
                <span class="stap-date">{{ \Carbon\Carbon::now()->format('F d, Y') }}</span>
            </div>
        </div>

        {{-- Page content --}}
        <div class="stap-content">
            @yield('content')
        </div>

    </div>
</div>

{{-- ── Floating Announcement Stack ── --}}
<div id="stap-ann-stack"></div>

{{-- Admin Login Modal --}}
@include('partials.admin-login-modal')

{{-- App JS --}}
<script src="{{ asset('js/app.js') }}"></script>

{{-- ── Announcement Stack Script ── --}}
<script>
(function () {
    const typeIcons = {
        general:     '📢',
        incident:    '🚨',
        weather:     '🌧️',
        maintenance: '🔧',
        emergency:   '⚠️',
    };

    // Extensions we treat as an inline image preview. Anything else
    // (mp4, pdf) falls back to a plain attachment link instead.
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    function isImageAttachment(url, name) {
        const source = (name || url || '').toLowerCase();
        const ext = source.split('.').pop();
        return imageExtensions.includes(ext);
    }

    const dismissed = JSON.parse(sessionStorage.getItem('stap_dismissed_ann') || '[]');
    const stack = document.getElementById('stap-ann-stack');

    function renderCard(a) {
        const icon    = typeIcons[a.type] ?? '📢';
        const expires = a.expires_at
            ? 'Expires ' + new Date(a.expires_at).toLocaleString()
            : '';
        const incident = a.incident_report
            ? `Incident #${a.incident_report.incident_id}`
            : '';
        const meta = [expires, incident].filter(Boolean).join(' · ');

        // ── Attachment block ──
        // a.attachment_url is the Cloudinary secure_url set server-side
        // (see AnnouncementController::withUrl()). If it's an image, show
        // an inline thumbnail that opens the full image in a new tab when
        // clicked. Otherwise (video/pdf), show a plain download/view link.
        let attachmentHtml = '';
        if (a.attachment_url) {
            if (isImageAttachment(a.attachment_url, a.attachment_name)) {
                attachmentHtml = `
                    <a href="${a.attachment_url}" target="_blank" rel="noopener">
                        <img class="stap-ann-attachment-img" src="${a.attachment_url}" alt="${a.attachment_name ?? 'Attachment'}" loading="lazy">
                    </a>`;
            } else {
                attachmentHtml = `
                    <a class="stap-ann-attachment-link" href="${a.attachment_url}" target="_blank" rel="noopener">
                        📎 ${a.attachment_name ?? 'View Attachment'}
                    </a>`;
            }
        }

        const card = document.createElement('div');
        card.className = `stap-ann-card type-${a.type}`;
        card.id = `ann-${a.announcement_id}`;
        card.innerHTML = `
            <span class="stap-ann-icon">${icon}</span>
            <div class="stap-ann-body">
                <span class="stap-ann-title">${a.title}</span>
                <span class="stap-ann-msg">${a.content}</span>
                ${attachmentHtml}
                ${meta ? `<span class="stap-ann-meta">${meta}</span>` : ''}
            </div>
            <button class="stap-ann-close" title="Dismiss">✕</button>
        `;

        card.querySelector('.stap-ann-close').addEventListener('click', () => dismissAnnouncement(a.announcement_id));
        return card;
    }

    async function loadAnnouncements() {
        try {
            const res = await fetch('/api/announcements/active', {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;

            const announcements = await res.json();
            const visible = announcements.filter(a => !dismissed.includes(a.announcement_id));

            stack.innerHTML = '';
            visible.forEach(a => stack.appendChild(renderCard(a)));

        } catch (e) {
            console.error('Failed to load announcements', e);
        }
    }

    window.dismissAnnouncement = function (id) {
        dismissed.push(id);
        sessionStorage.setItem('stap_dismissed_ann', JSON.stringify(dismissed));

        const card = document.getElementById('ann-' + id);
        if (!card) return;

        card.classList.add('dismissing');
        card.addEventListener('animationend', () => card.remove(), { once: true });
    };

    loadAnnouncements();
})();
</script>

{{-- Page-specific scripts --}}
@stack('scripts')

</body>
</html>