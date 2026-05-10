/* ============================================================
   STAP HUB — Data / Footage Request JS
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    const form          = document.getElementById('drForm');
    const submitBtn     = document.getElementById('drSubmitBtn');
    const btnText       = document.getElementById('drBtnText');
    const btnSpinner    = document.getElementById('drBtnSpinner');
    const successBanner = document.getElementById('drSuccess');
    const successText   = document.getElementById('drSuccessText');
    const errorBanner   = document.getElementById('drErrorBanner');

    if (!form) return;

    // ── "Other" nature → show specify input ───────────────
    form.querySelectorAll('[name="request_nature"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const wrap = document.getElementById('otherReasonWrap');
            if (wrap) wrap.style.display = this.value === 'other' ? '' : 'none';
        });
    });

    // ── Multi-date toggle ──────────────────────────────────
    const multiDateToggle = document.getElementById('multiDateToggle');
    const multiDateFlag   = document.getElementById('multiDateFlag');
    const singleDateWrap  = document.getElementById('singleDateWrap');
    const multiDateWrap   = document.getElementById('multiDateWrap');

    if (multiDateToggle) {
        multiDateToggle.addEventListener('change', function () {
            const isMulti = this.checked;

            multiDateFlag.value          = isMulti ? '1' : '0';
            singleDateWrap.style.display = isMulti ? 'none' : '';
            multiDateWrap.style.display  = isMulti ? ''     : 'none';

            // Enable/disable so FormData only sends the active fields
            const singleInput = document.getElementById('footage_date');
            if (singleInput) singleInput.disabled = isMulti;

            multiDateWrap.querySelectorAll('input[type="date"]').forEach(inp => {
                inp.disabled = !isMulti;
            });

            // Clear date errors on toggle
            ['footage_date', 'footage_date_start', 'footage_date_end'].forEach(f => {
                const el = document.getElementById('err_' + f);
                if (el) el.textContent = '';
            });
        });
    }

    // ── Clear per-field error on user input ────────────────
    form.querySelectorAll('input, textarea').forEach(el => {
        ['change', 'input'].forEach(ev => el.addEventListener(ev, function () {
            const errEl = document.getElementById('err_' + this.name);
            if (errEl) errEl.textContent = '';
            this.classList.remove('dr-input-invalid');
        }));
    });

    // ── Form submit ────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        setLoading(true);

        fetch(window.STAP_DR_ROUTES.store, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                Accept: 'application/json',
            },
            body: new FormData(form),
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            setLoading(false);
            if (ok && data.success) {
                onSuccess(data.message);
            } else if (data.errors) {
                showFieldErrors(data.errors);
                showError('Please fix the errors below and try again.');
            } else {
                showError(data.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(() => {
            setLoading(false);
            showError('Network error. Please check your connection and try again.');
        });
    });

    // ── Helpers ────────────────────────────────────────────
    function setLoading(on) {
        submitBtn.disabled       = on;
        btnText.style.display    = on ? 'none'         : 'inline';
        btnSpinner.style.display = on ? 'inline-block' : 'none';
    }

    function clearErrors() {
        successBanner.style.display = 'none';
        errorBanner.style.display   = 'none';
        form.querySelectorAll('.dr-err').forEach(el => el.textContent = '');
        form.querySelectorAll('.dr-input-invalid').forEach(el => el.classList.remove('dr-input-invalid'));
    }

    function showError(msg) {
        errorBanner.textContent   = msg;
        errorBanner.style.display = 'block';
        errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function onSuccess(msg) {
        form.reset();

        // Reset multi-date toggle state
        if (multiDateToggle) {
            multiDateToggle.checked      = false;
            multiDateFlag.value          = '0';
            singleDateWrap.style.display = '';
            multiDateWrap.style.display  = 'none';
            document.getElementById('footage_date').disabled = false;
            multiDateWrap.querySelectorAll('input[type="date"]').forEach(inp => {
                inp.disabled = true;
            });
        }

        const otherWrap = document.getElementById('otherReasonWrap');
        if (otherWrap) otherWrap.style.display = 'none';

        successText.textContent     = msg || 'Your request has been submitted. We will contact you via email.';
        successBanner.style.display = 'flex';
        successBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function showFieldErrors(errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const errEl = document.getElementById('err_' + field);
            if (errEl) errEl.textContent = messages[0];

            // Mark the actual inputs (camera_id radios are named camera_id directly)
            form.querySelectorAll(`[name="${field}"]`)
                .forEach(el => el.classList.add('dr-input-invalid'));
        });

        // Scroll to the first visible error message or invalid input
        const firstErr = form.querySelector('.dr-err:not(:empty)') ||
                         form.querySelector('.dr-input-invalid');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

});