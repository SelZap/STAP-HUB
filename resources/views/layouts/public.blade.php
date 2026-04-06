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

{{-- Admin Login Modal --}}
@include('partials.admin-login-modal')

{{-- App JS --}}
<script src="{{ asset('js/app.js') }}"></script>

{{-- Page-specific scripts --}}
@stack('scripts')

</body>
</html>