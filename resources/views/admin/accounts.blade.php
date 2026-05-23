@extends('layouts.admin')

@section('title', 'Accounts')
@section('page-title', 'Admin Accounts')

@section('content')

<div class="stap-flex-between stap-mb-2">
    <span class="stap-text-xs stap-muted" id="accountCount"></span>
    <button class="stap-btn-primary" onclick="openCreateModal()">+ New Admin</button>
</div>

<div id="accountsList">
    <div class="stap-empty">Loading accounts…</div>
</div>

{{-- Create / Edit Modal --}}
<div class="stap-modal-overlay" id="accountModal">
    <div class="stap-modal" style="width:420px;max-width:calc(100vw - 40px);">
        <button class="stap-modal-close" onclick="closeModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <h3 id="modalTitle" style="font-size:15px;font-weight:800;margin-bottom:16px;">New Admin Account</h3>
        <div id="formError" class="stap-form-error" style="display:none;"></div>

        <div class="stap-form-group">
            <label class="stap-form-label">Name</label>
            <input type="text" id="fieldName" class="stap-form-input" placeholder="Full name">
        </div>
        <div class="stap-form-group">
            <label class="stap-form-label">Email</label>
            <input type="email" id="fieldEmail" class="stap-form-input" placeholder="email@stap.gov">
        </div>
        <div class="stap-form-group">
            <label class="stap-form-label">Password</label>
            <input type="password" id="fieldPassword" class="stap-form-input" placeholder="Min. 8 characters">
        </div>
        <div class="stap-form-group">
            <label class="stap-form-label">Confirm Password</label>
            <input type="password" id="fieldPasswordConfirm" class="stap-form-input" placeholder="Repeat password">
        </div>

        <button class="stap-btn-primary stap-btn-full stap-mt-2" id="modalSubmit" onclick="submitModal()">
            <span id="modalBtnText">Create Account</span>
            <span id="modalSpinner" class="stap-spinner" style="display:none;"></span>
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
let editingId = null;

async function loadAccounts() {
    try {
        const res      = await fetch('/admin/accounts', { headers: authHeaders() });
        const data     = await res.json();
        const accounts = data.data ?? data;
        const me       = JSON.parse(sessionStorage.getItem('admin_data') || '{}');
        const myId     = me.admin_id ?? me.id;

        document.getElementById('accountCount').textContent = `${accounts.length} admin account(s)`;

        const list = document.getElementById('accountsList');
        if (!accounts.length) {
            list.innerHTML = '<div class="stap-empty">No accounts found.</div>';
            return;
        }

        list.innerHTML = accounts.map(a => {
            const aId   = a.admin_id ?? a.id;
            const aName = a.admin_name ?? a.name ?? '—';
            return `
            <div class="stap-card stap-mb-1" style="padding:14px 18px;display:flex;align-items:center;gap:14px;">
                <div style="width:38px;height:38px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0;">
                    👤
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px;">
                        <span style="font-weight:700;font-size:13px;">${aName}</span>
                        ${a.is_superuser ? '<span style="font-size:10px;font-weight:700;color:var(--navy);background:var(--navy-light);padding:2px 8px;border-radius:20px;">SUPERUSER</span>' : ''}
                        ${aId == myId ? '<span style="font-size:10px;color:var(--text-muted);">(you)</span>' : ''}
                    </div>
                    <div class="stap-text-xs stap-muted">${a.email ?? '—'}</div>
                </div>
                <div style="display:flex;gap:8px;flex-shrink:0;">
                    <button onclick="openEditModal(${aId},'${aName.replace(/'/g,"\\'")}','${a.email ?? ''}')" class="stap-btn-primary" style="padding:6px 12px;font-size:11px;background:var(--navy-muted);">Edit</button>
                    ${aId != myId ? `<button onclick="deleteAccount(${aId})" class="stap-btn-primary" style="padding:6px 12px;font-size:11px;background:var(--red);">Delete</button>` : ''}
                </div>
            </div>`;
        }).join('');
    } catch(e) { console.error(e); }
}

function openCreateModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent    = 'New Admin Account';
    document.getElementById('modalBtnText').textContent  = 'Create Account';
    ['fieldName','fieldEmail','fieldPassword','fieldPasswordConfirm'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('formError').style.display = 'none';
    document.getElementById('accountModal').classList.add('is-open');
}

function openEditModal(id, name, email) {
    editingId = id;
    document.getElementById('modalTitle').textContent    = 'Edit Admin Account';
    document.getElementById('modalBtnText').textContent  = 'Save Changes';
    document.getElementById('fieldName').value           = name;
    document.getElementById('fieldEmail').value          = email;
    document.getElementById('fieldPassword').value       = '';
    document.getElementById('fieldPasswordConfirm').value = '';
    document.getElementById('formError').style.display  = 'none';
    document.getElementById('accountModal').classList.add('is-open');
}

function closeModal() {
    document.getElementById('accountModal').classList.remove('is-open');
}

async function submitModal() {
    const btn  = document.getElementById('modalSubmit');
    const text = document.getElementById('modalBtnText');
    const spin = document.getElementById('modalSpinner');
    const err  = document.getElementById('formError');
    err.style.display = 'none';

    const body = {
        name:                  document.getElementById('fieldName').value,
        email:                 document.getElementById('fieldEmail').value,
        password:              document.getElementById('fieldPassword').value,
        password_confirmation: document.getElementById('fieldPasswordConfirm').value,
    };

    btn.disabled = true; text.style.display = 'none'; spin.style.display = 'inline-block';

    try {
        const url    = editingId ? `/admin/accounts/${editingId}` : '/admin/accounts';
        const method = editingId ? 'PUT' : 'POST';
        const res    = await fetch(url, { method, headers: authHeaders(), body: JSON.stringify(body) });
        const data   = await res.json();

        if (!res.ok) {
            err.textContent = data.message ?? 'An error occurred.';
            err.style.display = 'block';
        } else {
            closeModal();
            loadAccounts();
        }
    } catch(e) {
        err.textContent = 'Request failed.';
        err.style.display = 'block';
    }

    btn.disabled = false; text.style.display = 'inline'; spin.style.display = 'none';
}

async function deleteAccount(id) {
    if (!confirm('Delete this admin account? This cannot be undone.')) return;
    try {
        const res  = await fetch(`/admin/accounts/${id}`, { method: 'DELETE', headers: authHeaders() });
        const data = await res.json();
        if (!res.ok) { alert(data.message ?? 'Error'); return; }
        loadAccounts();
    } catch(e) { alert('Request failed.'); }
}

loadAccounts();
</script>
@endpush