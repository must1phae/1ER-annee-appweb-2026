

document.addEventListener('DOMContentLoaded', () => {

  /* ── Animate progress bars on load ── */
  document.querySelectorAll('.progress-bar-fill').forEach(bar => {
    const target = bar.dataset.progress || '0';
    const pct    = parseInt(target, 10);

    // Color based on value
    let color;
    if (pct >= 70)      color = '#34d1a0';   // green
    else if (pct >= 40) color = '#e8a838';   // amber
    else                color = '#f06a6a';   // red

    bar.style.background = color;

    // Trigger animation after a small delay
    setTimeout(() => {
      bar.style.width = pct + '%';
    }, 200);
  });

  /* ── Countdown: days until exam ── */
  document.querySelectorAll('.exam-date').forEach(el => {
    const dateStr = el.dataset.date;
    if (!dateStr) return;

    const exam  = new Date(dateStr);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    exam.setHours(0, 0, 0, 0);

    const diff  = Math.round((exam - today) / (1000 * 60 * 60 * 24));
    const span  = el.querySelector('.days-left');

    if (!span) return;

    if (diff < 0) {
      span.textContent = 'Passé';
      span.style.color = '#f06a6a';
    } else if (diff === 0) {
      span.textContent = "Aujourd'hui !";
      span.style.color = '#f06a6a';
    } else if (diff <= 7) {
      span.textContent = `Dans ${diff} jour${diff > 1 ? 's' : ''}`;
      span.style.color = '#fbbf24';
    } else {
      span.textContent = `Dans ${diff} jours`;
    }
  });

  /* ── Staggered row animation ── */
  const rows = document.querySelectorAll('.modules-table tbody tr');
  rows.forEach((row, i) => {
    row.style.opacity   = '0';
    row.style.transform = 'translateY(8px)';
    row.style.transition = `opacity .3s ${i * 0.06}s ease, transform .3s ${i * 0.06}s ease`;
    setTimeout(() => {
      row.style.opacity   = '1';
      row.style.transform = 'none';
    }, 100);
  });

});