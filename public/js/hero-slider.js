/* =========================================================================
   HERO SLIDER — Carousel otomatis pada hero section.
   Membaca data dari App.HERO_SLIDES.
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, $$, esc, prefersReducedMotion } = App;
  const HERO_SLIDES = App.HERO_SLIDES;

  const slidesWrap = $('#heroSlides');
  const dotsWrap   = $('#heroDots');
  const slider     = $('#heroSlider');
  if (!slidesWrap) return;

  // Bangun slide gambar + dot dari data
  slidesWrap.innerHTML = HERO_SLIDES.map((s, i) => `
    <div class="hero-slide ${i === 0 ? 'active' : ''}" role="group" aria-roledescription="slide" aria-label="${i + 1} dari ${HERO_SLIDES.length}: ${esc(s.name)}">
      <img src="${esc(s.img)}" alt="${esc(s.name)}" loading="${i === 0 ? 'eager' : 'lazy'}" onerror="this.onerror=null;this.src=FALLBACK_IMG;" />
    </div>`).join('');

  dotsWrap.innerHTML = HERO_SLIDES.map((s, i) => `
    <button type="button" class="slider-dot ${i === 0 ? 'active' : ''}" role="tab"
            aria-label="Slide ${i + 1}: ${esc(s.name)}" aria-selected="${i === 0}" data-i="${i}"></button>`).join('');

  const slideEls = $$('.hero-slide', slidesWrap);
  const dotEls   = $$('.slider-dot', dotsWrap);
  const nameEl   = $('#heroName'), descEl = $('#heroDesc'), tagEl = $('#heroTag');
  const priceEl  = $('#heroPrice');

  let current = 0;
  let timer = null;
  const INTERVAL = 4000;

  function goTo(index) {
    current = (index + HERO_SLIDES.length) % HERO_SLIDES.length;
    const s = HERO_SLIDES[current];

    slideEls.forEach((el, i) => el.classList.toggle('active', i === current));
    dotEls.forEach((el, i) => {
      el.classList.toggle('active', i === current);
      el.setAttribute('aria-selected', String(i === current));
    });

    // Update teks & badge dengan efek fade singkat
    [nameEl, descEl, priceEl].forEach((el) => { el.style.opacity = '0'; });
    setTimeout(() => {
      nameEl.textContent = s.name; descEl.textContent = s.desc;
      tagEl.textContent = s.tag; priceEl.textContent = s.price;
      [nameEl, descEl, priceEl].forEach((el) => { el.style.opacity = '1'; });
    }, 200);
  }

  const next = () => goTo(current + 1);
  const prev = () => goTo(current - 1);

  function play() { if (!prefersReducedMotion) { stop(); timer = setInterval(next, INTERVAL); } }
  function stop() { if (timer) { clearInterval(timer); timer = null; } }

  // Kontrol panah & dot
  $('#heroNext').addEventListener('click', () => { next(); play(); });
  $('#heroPrev').addEventListener('click', () => { prev(); play(); });
  dotEls.forEach((dot) => dot.addEventListener('click', () => { goTo(+dot.dataset.i); play(); }));

  // Jeda saat hover, lanjut saat mouse keluar
  slider.addEventListener('mouseenter', stop);
  slider.addEventListener('mouseleave', play);

  // Hentikan auto-play saat tab tidak terlihat (hemat resource)
  document.addEventListener('visibilitychange', () => { document.hidden ? stop() : play(); });

  play();
})(window.App);
