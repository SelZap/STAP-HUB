/* ============================================================
   STAP HUB — App JavaScript
   ============================================================ */

function authHeaders() {
    const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
    };

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrf) {
        headers["X-CSRF-TOKEN"] = csrf;
    }

    const token = sessionStorage.getItem("admin_token");
    if (token) {
        headers.Authorization = "Bearer " + token;
    }

    return headers;
}

window.authHeaders = authHeaders;

document.addEventListener("DOMContentLoaded", function () {
    // --------------------------------------------------------
    // Admin Login Modal
    // --------------------------------------------------------
    const overlay = document.getElementById("adminLoginOverlay");
    const openBtn = document.getElementById("openAdminLogin");
    const closeBtn = document.getElementById("closeAdminLogin");
    const loginForm = document.getElementById("adminLoginForm");
    const loginError = document.getElementById("loginError");
    const loginSubmit = document.getElementById("loginSubmit");
    const loginText = document.getElementById("loginBtnText");
    const loginSpinner = document.getElementById("loginBtnSpinner");

    function openModal() {
        overlay.classList.add("is-open");
        document.body.style.overflow = "hidden";
        setTimeout(() => overlay.querySelector("input")?.focus(), 250);
    }

    function closeModal() {
        overlay.classList.remove("is-open");
        document.body.style.overflow = "";
        if (loginError) {
            loginError.style.display = "none";
            loginError.textContent = "";
        }
    }

    if (openBtn) openBtn.addEventListener("click", openModal);
    if (closeBtn) closeBtn.addEventListener("click", closeModal);
    if (overlay)
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) closeModal();
        });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && overlay?.classList.contains("is-open"))
            closeModal();
    });

    if (overlay?.dataset.open === "true") openModal();

    if (loginForm) {
        loginForm.addEventListener("submit", async (event) => {
            event.preventDefault();

            if (loginError) {
                loginError.style.display = "none";
                loginError.textContent = "";
            }

            if (loginText) loginText.style.display = "none";
            if (loginSpinner) loginSpinner.style.display = "inline-block";
            if (loginSubmit) loginSubmit.disabled = true;

            const adminName =
                loginForm.querySelector('input[name="admin_name"]')?.value ??
                "";
            const password =
                loginForm.querySelector('input[name="password"]')?.value ?? "";

            try {
                const response = await fetch("/admin/login", {
                    method: "POST",
                    headers: authHeaders(),
                    body: JSON.stringify({ admin_name: adminName, password }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    if (loginError) {
                        loginError.textContent =
                            payload.message ?? "Invalid credentials.";
                        loginError.style.display = "block";
                    }
                    return;
                }

                sessionStorage.setItem("admin_token", payload.token);
                sessionStorage.setItem(
                    "admin_data",
                    JSON.stringify(payload.admin ?? {}),
                );
                sessionStorage.removeItem("stap_toast_shown");

                window.location.href = "/admin/dashboard";
            } catch (error) {
                if (loginError) {
                    loginError.textContent =
                        "Something went wrong. Please try again.";
                    loginError.style.display = "block";
                }
            } finally {
                if (loginText) loginText.style.display = "inline";
                if (loginSpinner) loginSpinner.style.display = "none";
                if (loginSubmit) loginSubmit.disabled = false;
            }
        });
    }

    // --------------------------------------------------------
    // Password toggle
    // --------------------------------------------------------
    const pwToggle = document.getElementById("togglePassword");
    const pwInput = document.getElementById("password");

    if (pwToggle && pwInput) {
        pwToggle.addEventListener("click", () => {
            const show = pwInput.type === "password";
            pwInput.type = show ? "text" : "password";
            pwToggle.querySelector("svg").style.opacity = show ? "0.5" : "1";
        });
    }

    // --------------------------------------------------------
    // ApexCharts — Global Defaults
    // --------------------------------------------------------
    if (typeof Apex !== "undefined") {
        Apex.chart = {
            fontFamily: "'Inter', -apple-system, sans-serif",
            toolbar: { show: false },
            animations: {
                enabled: true,
                easing: "easeinout",
                speed: 600,
                animateGradually: { enabled: true, delay: 80 },
                dynamicAnimation: { enabled: true, speed: 400 },
            },
        };
        Apex.tooltip = {
            theme: "light",
            style: { fontSize: "12px", fontFamily: "'Inter', sans-serif" },
        };
        Apex.grid = {
            borderColor: "#E8ECF4",
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
        };
        Apex.dataLabels = { enabled: false };
        Apex.stroke = { curve: "smooth", width: 2.5 };
    }

    // --------------------------------------------------------
    // Count-up animation
    // --------------------------------------------------------
    function animateCount(el, target, duration) {
        const startTime = performance.now();
        const isFloat = target % 1 !== 0;
        (function update(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = isFloat
                ? (target * ease).toFixed(1)
                : Math.round(target * ease).toLocaleString();
            if (progress < 1) requestAnimationFrame(update);
        })(startTime);
    }

    document.querySelectorAll("[data-count]").forEach((el) => {
        const target = parseFloat(el.dataset.count);
        if (!isNaN(target)) animateCount(el, target, 1000);
    });

    // --------------------------------------------------------
    // Animate progress bars
    // --------------------------------------------------------
    document.querySelectorAll(".stap-bar-fill[data-width]").forEach((el) => {
        el.style.width = "0%";
        setTimeout(() => (el.style.width = el.dataset.width + "%"), 200);
    });
});
