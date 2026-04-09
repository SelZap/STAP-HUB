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
    const cameraSelect  = document.getElementById('camera_id');

    if (!form) return;

    // --------------------------------------------------------
    // Load cameras into the select dropdown
    // --------------------------------------------------------
    fetch(window.STAP_DR_ROUTES.cameras, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(function (res) { return res.json(); })
    .then(function (cameras) {
        cameraSelect.innerHTML = '<option value="" disabled selected>Select a camera / direction</option>';

        if (!cameras.length) {
            cameraSelect.innerHTML = '<option value="" disabled selected>No cameras available</option>';
            return;
        }

        // Group by location
        const grouped = {};
        cameras.forEach(function (cam) {
            const loc = cam.location || 'Unknown Location';
            if (!grouped[loc]) grouped[loc] = [];
            grouped[loc].push(cam);
        });

        Object.keys(grouped).forEach(function (location) {
            const group = document.createElement('optgroup');
            group.label = location;

            grouped[location].forEach(function (cam) {
                const opt   = document.createElement('option');
                opt.value   = cam.camera_id;
                opt.textContent = cam.label + (cam.direction ? ' — ' + cam.direction : '');
                group.appendChild(opt);
            });

            cameraSelect.appendChild(group);
        });
    })
    .catch(function () {
        cameraSelect.innerHTML = '<option value="" disabled selected>Could not load cameras</option>';
    });

    // --------------------------------------------------------
    // Clear field errors on input
    // --------------------------------------------------------
    form.querySelectorAll('input, select, textarea').forEach(function (el) {
        el.addEventListener('change', function () {
            const key   = this.name.replace('[]', '');
            const errEl = document.getElementById('err_' + key);
            if (errEl) errEl.textContent = '';
            this.classList.remove('dr-input-invalid');
        });
        el.addEventListener('input', function () {
            const key   = this.name.replace('[]', '');
            const errEl = document.getElementById('err_' + key);
            if (errEl) errEl.textContent = '';
            this.classList.remove('dr-input-invalid');
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

        fetch(window.STAP_DR_ROUTES.store, {
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
                // Reload cameras dropdown after reset
                cameraSelect.innerHTML = '<option value="" disabled selected>Select a camera / direction</option>';

                successText.textContent = res.message || 'Your request has been submitted. We will contact you via email.';
                successBanner.style.display = 'flex';
                successBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Re-populate camera dropdown
                cameraSelect.dispatchEvent(new Event('reload'));

            } else if (res.errors) {
                showFieldErrors(res.errors);
                errorBanner.textContent = 'Please fix the errors below and try again.';
                errorBanner.style.display = 'block';
                errorBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });

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
        document.querySelectorAll('.dr-err').forEach(function (el) { el.textContent = ''; });
        document.querySelectorAll('.dr-input-invalid').forEach(function (el) { el.classList.remove('dr-input-invalid'); });
    }

    function showFieldErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            const key   = field.replace('[]', '');
            const errEl = document.getElementById('err_' + key);
            if (errEl) errEl.textContent = errors[field][0];
            const input = form.querySelector('[name="' + field + '"]');
            if (input) input.classList.add('dr-input-invalid');
        });
        const first = form.querySelector('.dr-input-invalid');
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

});