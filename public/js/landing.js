// public/js/landing.js

const overlay       = document.getElementById('loginOverlay');
const openLoginBtn  = document.getElementById('openLoginBtn');
const closeLoginBtn = document.getElementById('closeLoginBtn');

openLoginBtn.addEventListener('click', function(e) {
    e.preventDefault();
    overlay.classList.add('active');
});

closeLoginBtn.addEventListener('click', function() {
    overlay.classList.remove('active');
    document.querySelector('input[name="admin_name"]').value = '';
    document.querySelector('input[name="password"]').value   = '';
    var err = document.getElementById('errorMsg');
    if (err) err.classList.remove('show');
});