/* =========================================================================
   CORNER WIDGET — Gambar melayang di pojok kanan yang berganti otomatis.
   Membaca daftar gambar dari App.CORNER_IMAGES (folder /img).
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, esc, prefersReducedMotion } = App;
  const IMAGES = App.CORNER_IMAGES || [];

  const widget = $('#cornerWidget');
  if (!widget || IMAGES.length === 0) return;

  // Nomor & pesan WhatsApp tujuan saat widget diklik (ubah sesuai kebutuhan)
  const WA_NUMBER = '6281234567890';
  const WA_TEXT = 'Halo, saya mau tanya soal mobil Daihatsu';
  const WA_URL = `https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(WA_TEXT)}`;

  const dotsWrap = $('#cornerDots');

  // Elemen gambar (satu <img> yang sumbernya diganti bergantian)
  const img = document.createElement('img');
  img.className = 'corner-img';
  img.src = IMAGES[0].src;
  img.alt = IMAGES[0].alt || '';
  img.loading = 'lazy';
  img.onerror = function () { this.onerror = null; this.src = App.FALLBACK_IMG; };
  widget.insertBefore(img, dotsWrap);

  // Titik indikator
  dotsWrap.innerHTML = IMAGES.map((_, i) =>
    `<span class="${i === 0 ? 'active' : ''}"></span>`).join('');
  const dots = [...dotsWrap.children];

  let current = 0;
  let timer = null;
  const INTERVAL = 3500;

  function show(index) {
    current = (index + IMAGES.length) % IMAGES.length;
    // Fade out -> ganti sumber -> fade in
    img.classList.add('fading');
    setTimeout(() => {
      const item = IMAGES[current];
      img.src = item.src;
      img.alt = item.alt || '';
      img.classList.remove('fading');
    }, 250);
    dots.forEach((d, i) => d.classList.toggle('active', i === current));
  }

  const next = () => show(current + 1);

  function play() { if (!prefersReducedMotion && IMAGES.length > 1) { stop(); timer = setInterval(next, INTERVAL); } }
  function stop() { if (timer) { clearInterval(timer); timer = null; } }

  // Klik widget -> buka WhatsApp di tab baru
  widget.style.cursor = 'pointer';
  widget.setAttribute('role', 'link');
  widget.setAttribute('tabindex', '0');
  widget.setAttribute('aria-label', 'Chat WhatsApp dengan kami');
  function goWA() { window.open(WA_URL, '_blank', 'noopener'); }
  widget.addEventListener('click', goWA);
  widget.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); goWA(); } });

  // Jeda saat hover
  widget.addEventListener('mouseenter', stop);
  widget.addEventListener('mouseleave', play);

  // Hentikan saat tab tidak terlihat
  document.addEventListener('visibilitychange', () => { document.hidden ? stop() : play(); });

  play();
})(window.App);
