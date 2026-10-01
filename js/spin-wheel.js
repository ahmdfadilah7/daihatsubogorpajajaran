/* =========================================================================
   SPIN THE WHEEL — Game roda keberuntungan berhadiah (easter egg marketing).
   Dibuka lewat tombol #spinTrigger atau ketik "hoki" di keyboard.
   Hadiah dibaca dari App.WHEEL_PRIZES. Main sekali per sesi.
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, esc, prefersReducedMotion } = App;
  const PRIZES = App.WHEEL_PRIZES || [];

  const canvas  = $('#spinCanvas');
  const overlay = $('#spinOverlay');
  const trigger = $('#spinTrigger');
  const hub     = $('#spinHub');
  if (!canvas || !overlay || PRIZES.length === 0) return;

  const WA_NUMBER = '6281234567890';
  const ctx = canvas.getContext('2d');
  const N = PRIZES.length;
  const SLICE = (2 * Math.PI) / N;      // sudut per segmen (radian)
  const SIZE = canvas.width;            // 320
  const R = SIZE / 2;

  let spinning = false;
  let rotation = 0; // rotasi kumulatif (derajat) yang diterapkan ke canvas

  /* ---------- Gambar roda ---------- */
  function drawWheel() {
    ctx.clearRect(0, 0, SIZE, SIZE);
    for (let i = 0; i < N; i++) {
      const start = i * SLICE - Math.PI / 2;      // mulai dari atas
      const end = start + SLICE;

      // Segmen
      ctx.beginPath();
      ctx.moveTo(R, R);
      ctx.arc(R, R, R - 4, start, end);
      ctx.closePath();
      ctx.fillStyle = PRIZES[i].color;
      ctx.fill();

      // Teks
      ctx.save();
      ctx.translate(R, R);
      ctx.rotate(start + SLICE / 2);
      ctx.textAlign = 'right';
      ctx.fillStyle = '#ffffff';
      ctx.font = '700 13px Poppins, sans-serif';
      const lines = String(PRIZES[i].short).split('\n');
      lines.forEach((ln, li) => {
        ctx.fillText(ln, R - 20, 5 + (li - (lines.length - 1) / 2) * 15);
      });
      ctx.restore();
    }
  }
  drawWheel();

  /* ---------- Pilih hadiah berbobot ---------- */
  function pickPrizeIndex() {
    const total = PRIZES.reduce((s, p) => s + (p.weight || 1), 0);
    let r = Math.random() * total;
    for (let i = 0; i < N; i++) {
      r -= (PRIZES[i].weight || 1);
      if (r < 0) return i;
    }
    return N - 1;
  }

  /* ---------- Putar ---------- */
  function spin() {
    if (spinning) return;
    if (sessionStorage.getItem('spinDone') === '1') {
      App.showToast('<i class="fa-solid fa-circle-info text-mango mr-2"></i>Kamu sudah bermain hari ini 😊');
      return;
    }
    spinning = true;
    hub.disabled = true;

    const winner = pickPrizeIndex();

    // Penunjuk ada di atas (posisi -90°). Segmen i berpusat di sudut
    // (i + 0.5) * SLICE dari atas. Agar pusat segmen pemenang berada di
    // penunjuk, roda harus diputar sehingga sudut itu ke posisi atas.
    const sliceDeg = 360 / N;
    const targetCenter = (winner + 0.5) * sliceDeg;      // posisi pusat segmen dari atas (searah jarum jam)
    const extraTurns = 5 * 360;                          // beberapa putaran penuh dulu
    // Rotasi searah jarum jam sebesar (360 - targetCenter) menempatkan pusat segmen di atas
    const finalRotation = rotation + extraTurns + (360 - (rotation % 360)) + (360 - targetCenter);
    rotation = finalRotation;

    canvas.style.transform = `rotate(${rotation}deg)`;

    const done = () => showResult(winner);
    if (prefersReducedMotion) {
      setTimeout(done, 300);
    } else {
      canvas.addEventListener('transitionend', done, { once: true });
    }
  }

  /* ---------- Tampilkan hasil ---------- */
  function showResult(i) {
    const prize = PRIZES[i];
    const resultBox = $('#spinResult');
    $('#spinPrize').textContent = prize.label;

    const msg = `Halo! Saya baru saja memenangkan *${prize.msg}* dari Roda Keberuntungan di website. Saya mau klaim hadiahnya.`;
    $('#spinClaim').href = `https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(msg)}`;

    resultBox.hidden = false;
    hub.textContent = 'YEAY!';
    spinning = false;
    try { sessionStorage.setItem('spinDone', '1'); } catch (_) {}
    launchConfetti();
  }

  /* ---------- Konfeti sederhana ---------- */
  function launchConfetti() {
    if (prefersReducedMotion) return;
    const colors = PRIZES.map((p) => p.color);
    const modal = $('#spinModal');
    for (let i = 0; i < 40; i++) {
      const bit = document.createElement('span');
      bit.style.cssText = `position:absolute;top:40%;left:50%;width:8px;height:8px;border-radius:2px;pointer-events:none;z-index:5;background:${colors[i % colors.length]};`;
      modal.appendChild(bit);
      const angle = Math.random() * Math.PI * 2;
      const dist = 80 + Math.random() * 160;
      const dx = Math.cos(angle) * dist;
      const dy = Math.sin(angle) * dist - 60;
      bit.animate([
        { transform: 'translate(-50%,-50%) rotate(0)', opacity: 1 },
        { transform: `translate(calc(-50% + ${dx}px), calc(-50% + ${dy}px)) rotate(${Math.random()*720}deg)`, opacity: 0 }
      ], { duration: 900 + Math.random() * 700, easing: 'cubic-bezier(.2,.7,.2,1)' })
        .onfinish = () => bit.remove();
    }
  }

  /* ---------- Buka / tutup modal ---------- */
  function openModal() {
    drawWheel(); // gambar ulang (memastikan font sudah termuat)
    overlay.hidden = false;
    requestAnimationFrame(() => overlay.classList.add('show'));
    document.body.style.overflow = 'hidden';
    // Jika sudah pernah main sesi ini, nonaktifkan tombol putar
    if (sessionStorage.getItem('spinDone') === '1') { hub.disabled = true; hub.textContent = 'SELESAI'; }
  }
  function closeModal() {
    overlay.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => { overlay.hidden = true; }, 350);
  }

  trigger.addEventListener('click', openModal);
  hub.addEventListener('click', spin);
  $('#spinClose').addEventListener('click', closeModal);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !overlay.hidden) closeModal(); });

  /* ---------- Easter egg: ketik "hoki" ---------- */
  let buffer = '';
  document.addEventListener('keydown', (e) => {
    if (e.key.length !== 1) return;
    buffer = (buffer + e.key.toLowerCase()).slice(-4);
    if (buffer === 'hoki') { buffer = ''; openModal(); }
  });
})(window.App);
