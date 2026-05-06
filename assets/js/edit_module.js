/* ============================================================
   edit_module.js  —  Logique du formulaire Modifier un module
   ============================================================ */

// ── Range slider live update ──────────────────────────────────
var rangeEl = document.getElementById('progress');
var valEl   = document.getElementById('progressVal');

rangeEl.addEventListener('input', function() {
  valEl.textContent = rangeEl.value + '%';
  rangeEl.style.background =
    'linear-gradient(to right, var(--accent) ' + rangeEl.value + '%, var(--border) ' + rangeEl.value + '%)';
});

// Init gradient on page load
rangeEl.dispatchEvent(new Event('input'));
