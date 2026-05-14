// Open/close login overlay
document.getElementById("openLoginBtn").addEventListener("click", function (e) {
    e.preventDefault();
    document.getElementById("loginOverlay").style.display = "flex";
});

document.getElementById("closeLoginBtn").addEventListener("click", function () {
    document.getElementById("loginOverlay").style.display = "none";
});

// Handle login form submit
document
    .querySelector(".login-form")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const admin_name = document.querySelector(
            'input[name="admin_name"]',
        ).value;
        const password = document.querySelector('input[name="password"]').value;
        const errorMsg = document.getElementById("errorMsg");
        errorMsg.classList.remove("show");

        try {
            const res = await fetch("/admin/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'input[name="_token"]',
                    ).value,
                },
                body: JSON.stringify({
                    admin_name: admin_name,
                    password: password,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                errorMsg.textContent = data.message ?? "Invalid credentials.";
                errorMsg.classList.add("show");
                return;
            }

            // Store JWT and admin info
            sessionStorage.setItem("admin_token", data.token);
            sessionStorage.setItem("admin_data", JSON.stringify(data.admin));

            //cookie is already set by the server
            window.location.href = "/admin/dashboard";

            // Redirect to admin dashboard
            window.location.href = "/admin/dashboard";
        } catch (err) {
            errorMsg.textContent = "Something went wrong. Please try again.";
            errorMsg.classList.add("show");
        }
    });
