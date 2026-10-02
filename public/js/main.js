/* =========================================================================
   MAIN — Inisialisasi & modul kecil (newsletter, tahun footer).
   Dimuat TERAKHIR: memicu render awal setelah semua modul siap.
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $ } = App;

  /* ---------- Banner promo (bisa ditutup) ----------
     Banner hanya disembunyikan untuk kunjungan saat ini; setiap halaman
     dimuat ulang banner akan muncul kembali. */
  const promoClose = $('#promoClose');
  if (promoClose) {
    promoClose.addEventListener('click', () => {
      document.body.classList.add('promo-hidden');
    });
  }

  /* ---------- Countdown promo di hero (urgensi marketing) ----------
     Menghitung mundur ke akhir bulan berjalan. Ubah 'deadline' bila perlu. */
  const cd = $('#heroCountdown');
  if (cd) {
    const now = new Date();
    // Target hitung mundur dari pengaturan admin (App.PROMO_DEADLINE,
    // 'YYYY-MM-DDTHH:MM' => diurai sebagai waktu LOKAL browser, sesuai niat
    // admin). Bila kosong / tidak valid, pakai akhir bulan berjalan 23:59:59
    // seperti perilaku sebelumnya. isNaN menjaga nilai rusak tak merusak timer.
    const raw = (window.App && window.App.PROMO_DEADLINE) ? window.App.PROMO_DEADLINE : '';
    let deadline = raw ? new Date(raw) : null;
    if (!deadline || isNaN(deadline.getTime())) {
      deadline = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
    }
    const pad = (n) => String(n).padStart(2, '0');

    function tick() {
      const diff = deadline - new Date();
      if (diff <= 0) { cd.textContent = 'Segera'; return; }
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      cd.textContent = `${d}h ${pad(h)}:${pad(m)}:${pad(s)}`;
    }
    tick();
    setInterval(tick, 1000);
  }

  /* ---------- Tahun berjalan di footer ---------- */
  const yearEl = $('#year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  /* ---------- Render awal ----------
     renderCars & observeReveals didefinisikan di catalog.js & ui.js. */
  if (App.renderCars) App.renderCars(App.CARS);
  if (App.observeReveals) App.observeReveals();
})(window.App);
