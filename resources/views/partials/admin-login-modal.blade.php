<div class="stap-modal-overlay" id="adminLoginOverlay">
    <div class="stap-modal" id="adminLoginModal">

        <button class="stap-modal-close" id="closeAdminLogin">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        {{-- Modal Logo --}}
        <div class="stap-modal-logo">
            <div class="stap-tl stap-tl-sm">
                <span class="stap-tl-dot" style="background:#E03040"></span>
                <span class="stap-tl-dot" style="background:#F4B942"></span>
                <span class="stap-tl-dot" style="background:#29B357"></span>
            </div>
            <span class="stap-logo-text">STAP HUB</span>
        </div>
        <p class="stap-modal-subtitle">Admin Portal &nbsp;·&nbsp; Authorized Personnel Only</p>

        {{-- Login Form --}}
        <form id="adminLoginForm" action="{{ route('admin.login.post') }}" method="POST">
            @csrf

            <div class="stap-form-group">
                <label class="stap-form-label" for="admin_name">Username</label>
                <input
                    type="text"
                    id="admin_name"
                    name="admin_name"
                    class="stap-form-input"
                    placeholder="Enter admin username"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="stap-form-group">
                <label class="stap-form-label" for="password">Password</label>
                <div class="stap-input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="stap-form-input"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="stap-pw-toggle" id="togglePassword">
                        <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Error message placeholder --}}
            <div class="stap-form-error" id="loginError" style="display:none;"></div>

            <button type="submit" class="stap-btn-primary stap-btn-full" id="loginSubmit">
                <span id="loginBtnText">LOG IN</span>
                <span id="loginBtnSpinner" class="stap-spinner" style="display:none;"></span>
            </button>

        </form>

        <p class="stap-modal-footer">Smart Traffic Automation Program &nbsp;·&nbsp; STAP Hub v1.0</p>

    </div>
</div>