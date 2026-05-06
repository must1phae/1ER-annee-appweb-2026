/* ============================================================
   assets/js/auth.js — Validation formulaires Login & Register
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Toggle mot de passe ── */
  document.querySelectorAll('.btn-toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.input-wrap').querySelector('input');
      const isHidden = input.type === 'password';
      input.type   = isHidden ? 'text' : 'password';
      btn.textContent = isHidden ? '🙈' : '👁️';
    });
  });

  /* ── Helpers ── */
  const showError = (field, msg) => {
    field.classList.add('is-invalid');
    const err = field.closest('.form-group').querySelector('.field-error');
    if (err) { err.textContent = msg; err.classList.add('visible'); }
  };

  const clearError = field => {
    field.classList.remove('is-invalid');
    const err = field.closest('.form-group')?.querySelector('.field-error');
    if (err) err.classList.remove('visible');
  };

  const isValidEmail = email =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());

  /* ── Password strength (register only) ── */
  const pwInput = document.getElementById('password');
  const bar     = document.querySelector('.strength-bar');

  if (pwInput && bar) {
    pwInput.addEventListener('input', () => {
      const v = pwInput.value;
      let score = 0;
      if (v.length >= 8)          score++;
      if (/[A-Z]/.test(v))        score++;
      if (/[0-9]/.test(v))        score++;
      if (/[^A-Za-z0-9]/.test(v)) score++;

      const configs = [
        { w: '0%',   bg: 'transparent' },
        { w: '25%',  bg: '#f06a6a' },
        { w: '50%',  bg: '#fbbf24' },
        { w: '75%',  bg: '#60a5fa' },
        { w: '100%', bg: '#34d1a0' },
      ];

      bar.style.width      = configs[score].w;
      bar.style.background = configs[score].bg;
    });
  }

  /* ── Real-time clearing on input ── */
  document.querySelectorAll('.form-control').forEach(f => {
    f.addEventListener('input', () => clearError(f));
  });

  /* ── Login validation ── */
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', e => {
      let ok = true;

      const email = document.getElementById('email');
      const pw    = document.getElementById('password');

      if (!email.value.trim()) {
        showError(email, 'L\'email est requis.'); ok = false;
      } else if (!isValidEmail(email.value)) {
        showError(email, 'Format email invalide.'); ok = false;
      }

      if (!pw.value) {
        showError(pw, 'Le mot de passe est requis.'); ok = false;
      }

      if (!ok) {
        e.preventDefault();
        return;
      }

      /* Loading state */
      const btn = loginForm.querySelector('.btn-submit');
      btn.classList.add('loading');
      btn.disabled = true;
    });
  }

  /* ── Register validation ── */
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', e => {
      let ok = true;

      const name    = document.getElementById('name');
      const email   = document.getElementById('email');
      const pw      = document.getElementById('password');
      const confirm = document.getElementById('confirm_password');

      if (!name.value.trim() || name.value.trim().length < 2) {
        showError(name, 'Nom requis (minimum 2 caractères).'); ok = false;
      }

      if (!email.value.trim()) {
        showError(email, 'L\'email est requis.'); ok = false;
      } else if (!isValidEmail(email.value)) {
        showError(email, 'Format email invalide.'); ok = false;
      }

      if (pw.value.length < 8) {
        showError(pw, 'Minimum 8 caractères.'); ok = false;
      }

      if (confirm && pw.value !== confirm.value) {
        showError(confirm, 'Les mots de passe ne correspondent pas.'); ok = false;
      }

      if (!ok) {
        e.preventDefault();
        return;
      }

      const btn = registerForm.querySelector('.btn-submit');
      btn.classList.add('loading');
      btn.disabled = true;
    });
  }

});