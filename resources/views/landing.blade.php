{{-- resources/views/public/landing.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STAP Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

    {{-- Slideshow Background --}}
    <div class="slideshow">
        <div class="slide slide-1"></div>
        <div class="slide slide-2"></div>
        <div class="slide slide-3"></div>
        <div class="slide slide-4"></div>
        <div class="slide slide-5"></div>
        <div class="slide slide-6"></div>
        <div class="slide slide-7"></div>
        <div class="slide slide-8"></div>
    </div>

    {{-- Landing Panel --}}
    <div class="page">
        <div class="left-panel">

            <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Logo" class="logo-img">

            <div class="btn-stack">
                <a href="#" class="btn-pill btn-user">Enter as User</a>
                <a href="#" class="btn-pill btn-admin" id="openLoginBtn">Enter as Admin</a>
            </div>

        </div>
        <div class="right-panel"></div>
    </div>

    {{-- Admin Login Overlay --}}
    <div class="login-overlay" id="loginOverlay">
        <div class="login-panel">

            <img src="{{ asset('images/stap-logo.png') }}" alt="STAP Logo" class="logo-img">

            <form class="login-form" method="POST" action="#">
                @csrf

                <div class="field-group">
                    <label class="field-label">Admin ID:</label>
                    <input type="text" name="admin_name" class="field-input" autocomplete="username" value="{{ old('admin_name') }}">
                </div>

                <div class="field-group">
                    <label class="field-label">Password:</label>
                    <input type="password" name="password" class="field-input" autocomplete="current-password">
                </div>

                @if ($errors->any())
                    <span class="error-msg show">{{ $errors->first() }}</span>
                @else
                    <span class="error-msg" id="errorMsg"></span>
                @endif

                <button type="submit" class="btn-login">Log In</button>
            </form>

            <button class="back-link" id="closeLoginBtn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M5 12l7 7M5 12l7-7"/>
                </svg>
                Back
            </button>

        </div>
        <div class="login-panel-right"></div>
    </div>

    <script src="{{ asset('js/landing.js') }}"></script>

</body>
</html>