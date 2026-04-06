/* ============================================================
   STAP HUB — Incident Report JS
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    const form           = document.getElementById('irForm');
    const submitBtn      = document.getElementById('irSubmitBtn');
    const btnText        = document.getElementById('irBtnText');
    const btnSpinner     = document.getElementById('irBtnSpinner');
    const successBanner  = document.getElementById('irSuccess');
    const errorBanner    = document.getElementById('irErrorBanner');
    const injuredGroup   = document.getElementById('injuredCountGroup');
    const descCount      = document.getElementById('descCount');

    // --------------------------------------------------------
    // Show/hide injured count based on people_hurt
    // --------------------------------------------------------
    document.querySelectorAll('input[name="people_hurt"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            const show = this.value === '1';
            injuredGroup.style.display = show ? 'block' : 'none';
            const injuredInput = document.getElementById('injured_count');
            if (!show) injuredInput.value = '';
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
    // Clear field errors on input
    // --------------------------------------------------------
    form.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('input', function () {
            const errEl = document.getElementById('err_' + this.name);
            if (errEl) errEl.textContent = '';
            this.classList.remove('ir-input-invalid');
        });
    });

    // --------------------------------------------------------
    // Form submit
    // --------------------------------------------------------
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        setLoading(true);

        const data = new FormData(form);

        fetch('{{ route("incident.store") }}', {
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
        submitBtn.disabled  = loading;
        btnText.style.display    = loading ? 'none'         : 'inline';
        btnSpinner.style.display = loading ? 'inline-block' : 'none';
    }

    function clearErrors() {
        successBanner.style.display = 'none';
        errorBanner.style.display   = 'none';
        document.querySelectorAll('.ir-field-error').forEach(function (el) { el.textContent = ''; });
        document.querySelectorAll('.ir-input-invalid').forEach(function (el) { el.classList.remove('ir-input-invalid'); });
    }

    function showFieldErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            const errEl = document.getElementById('err_' + field);
            if (errEl) errEl.textContent = errors[field][0];
            const input = document.querySelector('[name="' + field + '"]');
            if (input) input.classList.add('ir-input-invalid');
        });
        // Scroll to first error
        const first = document.querySelector('.ir-input-invalid');
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

});