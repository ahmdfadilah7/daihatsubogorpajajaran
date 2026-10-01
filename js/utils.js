/* =========================================================================
   UTILS — Helper yang dipakai di banyak modul.
   ========================================================================= */
window.App = window.App || {};

/* Query selector singkat */
App.$  = (sel, ctx = document) => ctx.querySelector(sel);
App.$$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/* Format angka -> Rupiah */
App.formatRupiah = (n) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);

/* Preferensi pengguna: kurangi animasi */
App.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* Perangkat mendukung hover + pointer presisi (mouse) */
App.canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

/* Escape teks sebelum disisipkan ke innerHTML (cegah XSS bila data dari API) */
App.esc = (s) => String(s).replace(/[&<>"']/g, (c) =>
  ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

/* Nilai unik + terurut dari sebuah array */
App.uniqueSorted = (arr) => [...new Set(arr)].sort();

/* Gambar cadangan (SVG) bila URL gambar gagal dimuat.
   Dipakai lewat atribut onerror pada <img>. */
App.FALLBACK_IMG = 'data:image/svg+xml;utf8,' + encodeURIComponent(
  '<svg xmlns="http://www.w3.org/2000/svg" width="900" height="600">' +
  '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">' +
  '<stop offset="0" stop-color="#2e86ff"/><stop offset="1" stop-color="#0a5fd1"/>' +
  '</linearGradient></defs><rect width="100%" height="100%" fill="url(#g)"/>' +
  '<text x="50%" y="52%" fill="#fff" font-family="Arial" font-size="42" font-weight="bold" text-anchor="middle">DAIHATSU</text></svg>'
);
/* Agar bisa dipanggil dari atribut inline onerror="...FALLBACK_IMG" */
window.FALLBACK_IMG = App.FALLBACK_IMG;
