/* ============================================================
   add_module.js  —  Logique du formulaire Ajouter un module
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

// ── Client-side validation ────────────────────────────────────
document.getElementById('addModuleForm').addEventListener('submit', function(e) {
  var name = document.getElementById('module_name').value.trim();
  if (!name) {
    e.preventDefault();
    alert('Le nom du module est obligatoire.');
  }
});
