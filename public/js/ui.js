/* =========================================================================
   UI — Interaksi umum antarmuka:
   navbar sticky, menu mobile, highlight menu aktif, scroll reveal,
   counter angka, toast, dan tombol "kembali ke atas".
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, $$ } = App;

  /* ---------- Sticky navbar + tombol ke atas ---------- */
  const navbar = $('#navbar');
  const toTop  = $('#toTop');
  let ticking = false;

  function onScroll() {
    const y = window.scrollY;
    navbar.classList.toggle('scrolled', y > 50);

    const showTop = y > 600;
    toTop.classList.toggle('opacity-0', !showTop);
    toTop.classList.toggle('pointer-events-none', !showTop);
    toTop.classList.toggle('translate-y-4', !showTop);

    ticking = false;
  }
  window.addEventListener('scroll', () => {
    if (!ticking) { requestAnimationFrame(onScroll); ticking = true; }
  }, { passive: true });
  onScroll();

  /* ---------- Menu mobile ---------- */
  const menuToggle = $('#menuToggle');
  const mobileMenu = $('#mobileMenu');
  function setMenu(open) {
    mobileMenu.classList.toggle('open', open);
    menuToggle.setAttribute('aria-expanded', String(open));
    menuToggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    menuToggle.innerHTML = `<i class="fa-solid ${open ? 'fa-xmark' : 'fa-bars'} text-lg" aria-hidden="true"></i>`;
    // Beri background pada navbar saat menu terbuka; kembalikan sesuai scroll saat ditutup
    if (open) navbar.classList.add('scrolled');
    else onScroll();
  }
  menuToggle.addEventListener('click', () => setMenu(!mobileMenu.classList.contains('open')));
  $$('#mobileMenu a').forEach((a) => a.addEventListener('click', () => setMenu(false)));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setMenu(false); });

  /* ---------- Highlight menu aktif sesuai section terlihat ---------- */
  const navLinks = $$('.nav-link');
  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        navLinks.forEach((l) => l.classList.toggle('active', l.getAttribute('href') === '#' + entry.target.id));
      }
    });
  }, { rootMargin: '-45% 0px -50% 0px' });
  ['home', 'inventory', 'services', 'kuis', 'testimoni', 'contact'].forEach((id) => {
    const el = document.getElementById(id);
    if (el) sectionObserver.observe(el);
  });

  /* ---------- Scroll reveal (Intersection Observer) ---------- */
  const revealObserver = new IntersectionObserver((entries, obs) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) { entry.target.classList.add('is-visible'); obs.unobserve(entry.target); }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  // Diekspos agar modul lain bisa mengamati elemen baru yang dirender dinamis
  App.observeReveals = (root = document) =>
    $$('.reveal:not(.is-visible)', root).forEach((el) => revealObserver.observe(el));

  /* ---------- Counter angka ---------- */
  function animateCounter(el) {
    const target = +el.dataset.target, duration = 1600, start = performance.now();
    const step = (now) => {
      const p = Math.min((now - start) / duration, 1);
      el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 3))); // easeOutCubic
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }
  setTimeout(() => $$('.counter').forEach(animateCounter), 800);

  /* ---------- Toast (notifikasi kecil) ---------- */
  const toast = $('#toast');
  let toastTimer;
  App.showToast = function (html) {
    toast.innerHTML = html;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 2600);
  };
})(window.App);
