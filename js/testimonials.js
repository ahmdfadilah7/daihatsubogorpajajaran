/* =========================================================================
   TESTIMONIALS — Spotlight slider interaktif.
   Menampilkan satu testimoni besar (foto + kutipan) yang berganti otomatis,
   dengan navigasi panah, avatar, dan bar progress auto-play.
   Membaca data dari App.TESTIMONIALS.
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, $$, esc, prefersReducedMotion } = App;
  const DATA = App.TESTIMONIALS || [];

  const slider   = $('#testiSlider');
  const track    = $('#testiTrack');
  const avatars  = $('#testiAvatars');
  const bar      = $('#testiBar');
  if (!slider || DATA.length === 0) return;

  const INTERVAL = 6000; // ms per slide

  const starsHTML = (rating) =>
    Array.from({ length: 5 }, (_, k) => `<i class="fa-${k < rating ? 'solid' : 'regular'} fa-star"></i>`).join('');

  // Bangun slide
  track.innerHTML = DATA.map((t, i) => `
    <article class="testi-slide ${i === 0 ? 'active' : ''}" style="--tc:${t.color}"
             role="group" aria-roledescription="slide" aria-label="${i + 1} dari ${DATA.length}">
      <div class="t-photo">
        <img src="${esc(t.img)}" alt="Serah terima ${esc(t.car)} kepada ${esc(t.name)}" loading="lazy" onerror="this.onerror=null;this.src=FALLBACK_IMG;" />
        <span class="t-badge"><i class="fa-solid fa-car-side" aria-hidden="true"></i> ${esc(t.car)}</span>
      </div>
      <div class="t-body">
        <span class="t-quote-mark" aria-hidden="true">&rdquo;</span>
        <div class="t-stars text-sm" aria-label="${t.rating} dari 5 bintang">${starsHTML(t.rating)}</div>
        <p class="t-text font-body">${esc(t.text)}</p>
        <div class="mt-5 pt-4 border-t border-ink/10">
          <p class="font-display font-extrabold text-ink text-lg">${esc(t.name)}</p>
          <p class="text-ink-500 text-sm">${esc(t.city)} &bull; Pemilik ${esc(t.car)}</p>
        </div>
      </div>
    </article>`).join('');

  // Bangun navigasi avatar
  avatars.innerHTML = DATA.map((t, i) => `
    <button type="button" class="${i === 0 ? 'active' : ''}" style="--tc:${t.color}"
            role="tab" aria-selected="${i === 0}" aria-label="Testimoni ${esc(t.name)}" data-i="${i}">
      <img src="${esc(t.img)}" alt="${esc(t.name)}" loading="lazy" onerror="this.onerror=null;this.src=FALLBACK_IMG;" />
    </button>`).join('');

  const slides   = $$('.testi-slide', track);
  const avaBtns  = $$('button', avatars);

  let current = 0;
  let timer = null;

  // Jalankan animasi bar progress dari 0 -> 100% selama INTERVAL
  function runBar() {
    if (prefersReducedMotion) return;
    bar.classList.remove('run');
    bar.style.setProperty('--dur', INTERVAL + 'ms');
    // reflow agar transisi restart
    void bar.offsetWidth;
    bar.classList.add('run');
  }

  function goTo(index) {
    current = (index + DATA.length) % DATA.length;
    slides.forEach((el, i) => el.classList.toggle('active', i === current));
    avaBtns.forEach((el, i) => {
      el.classList.toggle('active', i === current);
      el.setAttribute('aria-selected', String(i === current));
    });
    runBar();
  }

  const next = () => goTo(current + 1);
  const prev = () => goTo(current - 1);

  function play() {
    if (prefersReducedMotion || DATA.length < 2) return;
    stop();
    runBar();
    timer = setInterval(next, INTERVAL);
  }
  function stop() {
    if (timer) { clearInterval(timer); timer = null; }
    bar.classList.remove('run');
  }

  // Kontrol
  $('#testiNext').addEventListener('click', () => { next(); play(); });
  $('#testiPrev').addEventListener('click', () => { prev(); play(); });
  avaBtns.forEach((btn) => btn.addEventListener('click', () => { goTo(+btn.dataset.i); play(); }));

  // Jeda saat hover
  slider.addEventListener('mouseenter', stop);
  slider.addEventListener('mouseleave', play);

  // Geser (swipe) di perangkat sentuh
  let startX = 0;
  track.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; stop(); }, { passive: true });
  track.addEventListener('touchend', (e) => {
    const dx = e.changedTouches[0].clientX - startX;
    if (Math.abs(dx) > 50) (dx < 0 ? next() : prev());
    play();
  }, { passive: true });

  // Navigasi keyboard saat slider difokuskan/di-hover
  slider.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') { next(); play(); }
    else if (e.key === 'ArrowLeft') { prev(); play(); }
  });

  // Hentikan auto-play saat tab tidak terlihat
  document.addEventListener('visibilitychange', () => { document.hidden ? stop() : play(); });

  play();
})(window.App);
