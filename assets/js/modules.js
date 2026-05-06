/* ============================================================
   modules.js  —  Logique de la page Mes Modules
   ============================================================ */

// ── Modal open / close ────────────────────────────────────────
const overlay = document.getElementById('modalOverlay');

function openModal()  { overlay.classList.add('open'); }
function closeModal() { overlay.classList.remove('open'); }

['openModal', 'openModal2', 'openModal3'].forEach(function(id) {
  var el = document.getElementById(id);
  if (el) el.addEventListener('click', openModal);
});

document.getElementById('closeModal').addEventListener('click', closeModal);
document.getElementById('closeModal2').addEventListener('click', closeModal);

overlay.addEventListener('click', function(e) {
  if (e.target === overlay) closeModal();
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeModal();
});

// ── Progress range live update ────────────────────────────────
var rangeEl = document.getElementById('progress');
var valEl   = document.getElementById('progressVal');

rangeEl.addEventListener('input', function() {
  valEl.textContent = rangeEl.value + '%';
  rangeEl.style.background =
    'linear-gradient(to right, var(--accent) ' + rangeEl.value + '%, var(--border) ' + rangeEl.value + '%)';
});
rangeEl.dispatchEvent(new Event('input'));

// ── Animate progress bars in table ───────────────────────────
document.querySelectorAll('.prog-bar-fill').forEach(function(bar) {
  var target = bar.dataset.progress;
  requestAnimationFrame(function() {
    setTimeout(function() { bar.style.width = target + '%'; }, 100);
  });
});
