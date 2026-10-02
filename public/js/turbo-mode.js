/* =========================================================================
   HACKER MODE — Easter egg tersembunyi yang mencolok.
   Ketuk logo di navbar 3x cepat (dalam ~1.5 detik) untuk MENYALAKAN "mode
   hacker"; ketuk 3x lagi (atau klik badge) untuk MEMATIKANNYA (toggle).

   Saat aktif, SELURUH situs berubah drastis: tema gelap hijau-terminal,
   hujan kode ala Matrix di latar (canvas), scanline CRT, font monospace,
   dan efek glitch sesaat. Murni efek visual sisi klien — tanpa pengaturan.

   Menghormati prefers-reduced-motion (hujan kode & glitch dilewati, tema
   warna tetap diterapkan sebagai isyarat). Dibaca dari window.App agar
   konsisten dengan modul lain (toast, dll).
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { prefersReducedMotion } = App;

  const logo = document.querySelector('#navbar a[href="#home"]');
  if (!logo) return;

  const TAP_WINDOW = 1500; // ms untuk 3 ketukan
  const TAPS_NEEDED = 3;
  let taps = [];
  let active = false;

  let canvas = null;
  let rafId = null;
  let badgeEl = null;

  /* ---------- Toggle utama ---------- */
  function toggleHacker() {
    active = !active;
    document.body.classList.toggle('hacker-on', active);

    if (active) {
      startMatrix();
      spawnBadge();
      glitchFlash();
      if (App.showToast) {
        App.showToast('<i class="fa-solid fa-terminal mr-2"></i><b>ACCESS GRANTED</b> — Hacker Mode aktif 👾');
      }
    } else {
      stopMatrix();
      removeBadge();
      if (App.showToast) {
        App.showToast('<i class="fa-solid fa-power-off mr-2"></i>Koneksi diputus. Mode normal kembali.');
      }
    }
  }

  /* ---------- Deteksi 3x ketuk logo ---------- */
  function registerTap() {
    const now = Date.now();
    taps.push(now);
    taps = taps.filter((t) => now - t <= TAP_WINDOW);
    if (taps.length >= TAPS_NEEDED) {
      taps = [];
      toggleHacker();
    }
  }
  logo.addEventListener('click', (e) => {
    if (taps.length >= 1) e.preventDefault(); // cegah lompat ke #home saat beruntun
    registerTap();
  });

  /* ---------- Matrix rain (canvas latar) ---------- */
  const GLYPHS = 'アイウエオカキクケコサシスセソタチツテトナニヌ0123456789$#%&*{}[]<>/\\=+DAIHATSU'.split('');
  let columns = [];
  let fontSize = 16;

  function sizeCanvas() {
    if (!canvas) return;
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    const cols = Math.floor(canvas.width / fontSize);
    columns = new Array(cols).fill(0).map(() => Math.random() * -canvas.height);
  }

  function drawMatrix() {
    const ctx = canvas.getContext('2d');
    // Jejak memudar: lapisan hitam transparan tiap frame.
    ctx.fillStyle = 'rgba(2, 10, 6, 0.12)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.font = fontSize + "px 'Courier New', monospace";

    for (let i = 0; i < columns.length; i++) {
      const x = i * fontSize;
      const y = columns[i];
      const ch = GLYPHS[Math.floor(Math.random() * GLYPHS.length)];
      // Karakter terdepan lebih terang (putih kehijauan), ekornya hijau.
      ctx.fillStyle = Math.random() > 0.975 ? '#d7ffe6' : '#25d366';
      ctx.fillText(ch, x, y);
      if (y > canvas.height && Math.random() > 0.975) {
        columns[i] = Math.random() * -200;
      } else {
        columns[i] = y + fontSize;
      }
    }
    rafId = requestAnimationFrame(drawMatrix);
  }

  function startMatrix() {
    if (prefersReducedMotion) return; // tema tetap aktif, hanya hujan kode dilewati
    if (!canvas) {
      canvas = document.createElement('canvas');
      canvas.id = 'matrixCanvas';
      canvas.setAttribute('aria-hidden', 'true');
      document.body.appendChild(canvas);
      window.addEventListener('resize', sizeCanvas);
    }
    sizeCanvas();
    cancelAnimationFrame(rafId);
    drawMatrix();
  }
  function stopMatrix() {
    cancelAnimationFrame(rafId);
    rafId = null;
    if (canvas) { canvas.remove(); canvas = null; }
  }

  /* ---------- Badge status (penanda + tombol mematikan) ---------- */
  function spawnBadge() {
    if (badgeEl) return;
    badgeEl = document.createElement('button');
    badgeEl.type = 'button';
    badgeEl.id = 'hackerBadge';
    badgeEl.className = 'hacker-badge';
    badgeEl.setAttribute('aria-label', 'Matikan Hacker Mode');
    badgeEl.innerHTML = '<span class="hacker-dot"></span><span>SYSTEM BREACHED</span>';
    badgeEl.addEventListener('click', toggleHacker);
    document.body.appendChild(badgeEl);
  }
  function removeBadge() {
    if (badgeEl) { badgeEl.remove(); badgeEl = null; }
  }

  /* ---------- Kilatan glitch sesaat saat dinyalakan ---------- */
  function glitchFlash() {
    if (prefersReducedMotion) return;
    document.body.classList.add('hacker-glitch');
    setTimeout(() => document.body.classList.remove('hacker-glitch'), 700);
  }

  /* ---------- Hemat sumber daya saat tab tersembunyi ---------- */
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) { cancelAnimationFrame(rafId); rafId = null; }
    else if (active && !prefersReducedMotion && canvas) { drawMatrix(); }
  });
})(window.App);
