/* ============================================================
   STAP HUB — Incident Report JS
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    const form          = document.getElementById('irForm');
    const submitBtn     = document.getElementById('irSubmitBtn');
    const btnText       = document.getElementById('irBtnText');
    const btnSpinner    = document.getElementById('irBtnSpinner');
    const successBanner = document.getElementById('irSuccess');
    const successMsg    = document.getElementById('irSuccessMsg');
    const errorBanner   = document.getElementById('irErrorBanner');
    const injuredGroup  = document.getElementById('injuredCountGroup');
    const descCount     = document.getElementById('descCount');

    if (!form) return;

    let emailValid = true; // tracks async email domain check result

    // --------------------------------------------------------
    // Show/hide injured count based on people_hurt
    // --------------------------------------------------------
    document.querySelectorAll('input[name="people_hurt"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            const show = this.value === '1';
            injuredGroup.style.display = show ? 'block' : 'none';
            if (!show) document.getElementById('injured_count').value = '';
        });
    });

    // --------------------------------------------------------
    // Character count for description
    // --------------------------------------------------------
    const descTextarea = document.getElementById('description');
    if (descTextarea) {
        descTextarea.addEventListener('input', function () {
            descCount.textContent = this.value.length;
        });
    }

    // --------------------------------------------------------
    // Email blur validation (optional field — only fires if filled)
    // --------------------------------------------------------
    const emailInput = document.getElementById('reporter_email');
    if (emailInput) {
        emailInput.addEventListener('blur', function () {
            const val = this.value.trim();
            if (!val) {
                emailValid = true; // empty is fine — field is optional
                document.getElementById('err_reporter_email').textContent = '';
                this.classList.remove('ir-input-invalid');
                return;
            }
            checkEmailDomain(val);
        });

        emailInput.addEventListener('input', function () {
            // re-enable submit while user is typing a correction
            emailValid = true;
            document.getElementById('err_reporter_email').textContent = '';
            this.classList.remove('ir-input-invalid');
        });
    }

    function checkEmailDomain(email) {
        fetch(window.STAP_EMAIL_VALIDATE_ROUTE, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ email }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            emailValid = data.valid;
            const errEl = document.getElementById('err_reporter_email');
            if (!data.valid) {
                errEl.textContent = data.message || 'Please enter a valid email address.';
                emailInput.classList.add('ir-input-invalid');
            } else {
                errEl.textContent = '';
                emailInput.classList.remove('ir-input-invalid');
            }
        })
        .catch(function () {
            emailValid = true; // silent fail — don't block submission on network error
        });
    }

    // --------------------------------------------------------
    // Clear field errors on input
    // --------------------------------------------------------
    form.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('input', function () {
            if (this.name === 'reporter_email') return; // handled separately above
            const key   = this.name.replace('[]', '');
            const errEl = document.getElementById('err_' + key);
            if (errEl) errEl.textContent = '';
            this.classList.remove('ir-input-invalid');
        });
    });

    // --------------------------------------------------------
    // Form submit
    // --------------------------------------------------------
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Block submission if async email check failed
        if (!emailValid) {
            document.getElementById('reporter_email').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        clearErrors();
        setLoading(true);

        const data = new FormData(form);

        fetch(window.STAP_INCIDENT_ROUTE, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: data,
        })
        .then(function (res) { return res.json(); })
        .then(function (res) {
            setLoading(false);
            if (res.success) {
                form.reset();
                injuredGroup.style.display = 'none';
                descCount.textContent = '0';
                emailValid = true;
                if (successMsg) successMsg.textContent = res.message;
                successBanner.style.display = 'flex';
                successBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else if (res.errors) {
                showFieldErrors(res.errors);
                errorBanner.textContent = 'Please fix the errors below and try again.';
                errorBanner.style.display = 'block';
            } else {
                errorBanner.textContent = res.message || 'Something went wrong. Please try again.';
                errorBanner.style.display = 'block';
            }
        })
        .catch(function () {
            setLoading(false);
            errorBanner.textContent = 'Network error. Please check your connection and try again.';
            errorBanner.style.display = 'block';
        });
    });

    // --------------------------------------------------------
    // Helpers
    // --------------------------------------------------------
    function setLoading(loading) {
        submitBtn.disabled       = loading;
        btnText.style.display    = loading ? 'none'         : 'inline';
        btnSpinner.style.display = loading ? 'inline-block' : 'none';
    }

    function clearErrors() {
        successBanner.style.display = 'none';
        errorBanner.style.display   = 'none';
        document.querySelectorAll('.ir-err').forEach(function (el) { el.textContent = ''; });
        document.querySelectorAll('.ir-input-invalid').forEach(function (el) { el.classList.remove('ir-input-invalid'); });
    }

    function showFieldErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            const key   = field.replace(/\.\*$/, '').replace('[]', '');
            const errEl = document.getElementById('err_' + key);
            if (errEl) errEl.textContent = errors[field][0];
            const input = document.querySelector('[name="' + field + '"]');
            if (input) input.classList.add('ir-input-invalid');
        });
        const first = document.querySelector('.ir-input-invalid');
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

});