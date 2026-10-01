/* =========================================================================
   CATALOG — Render kartu mobil, efek 3D tilt, favorit, tombol detail,
   serta pencarian & filter (chip kategori + dropdown).
   Mengekspos App.renderCars() dan App.initCatalog().
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, $$, esc, formatRupiah, uniqueSorted, canHover, prefersReducedMotion } = App;
  const CARS = App.CARS;
  const CAT_STYLE = App.CAT_STYLE;

  const carGrid     = $('#carGrid');
  const emptyState  = $('#emptyState');
  const resultCount = $('#resultCount');

  /* ---------- Template kartu mobil ---------- */
  function carCardTemplate(car, index) {
    const cat = CAT_STYLE[car.category] || { bg: '#0a5fd1', label: car.category };
    const badge = car.badge
      ? `<span class="absolute top-4 left-4 z-10 text-white text-[11px] font-bold uppercase tracking-wide px-3 py-1 rounded-full" style="background:${car.accent[0]}">${esc(car.badge)}</span>`
      : '';
    const fuelIcon = car.fuel === 'Listrik' ? 'fa-bolt' : 'fa-gas-pump';

    return `
      <article class="car-card reveal reveal-zoom group rounded-3xl overflow-hidden bg-white" style="transition-delay:${(index % 4) * 0.08}s">
        <div class="card-top" style="--c1:${car.accent[0]};--c2:${car.accent[1]}"></div>
        <div class="relative aspect-[4/3] overflow-hidden">
          ${badge}
          <button type="button" class="fav-btn absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/90 shadow text-ink-500 hover:text-brand transition-colors"
                  aria-label="Simpan Daihatsu ${esc(car.model)} ke favorit" aria-pressed="false">
            <i class="fa-regular fa-heart" aria-hidden="true"></i>
          </button>
          <button type="button" class="compare-btn absolute top-16 right-4 z-10 w-10 h-10 rounded-full bg-white/90 shadow text-ink-500 hover:text-brand transition-colors" data-id="${car.id}"
                  aria-label="Bandingkan Daihatsu ${esc(car.model)}" aria-pressed="false" title="Bandingkan">
            <i class="fa-solid fa-code-compare" aria-hidden="true"></i>
          </button>
          <img src="${esc(car.img)}" alt="Daihatsu ${esc(car.model)} ${car.year}" loading="lazy"
               class="car-img w-full h-full object-cover" onerror="this.onerror=null;this.src=FALLBACK_IMG;" />
          <div class="absolute inset-x-0 bottom-4 flex justify-center">
            <button type="button" class="detail-btn btn-fun text-white text-sm font-display font-bold px-6 py-3 rounded-full inline-flex items-center gap-2" data-id="${car.id}">
              Lihat Detail <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
          </div>
        </div>

        <div class="p-5">
          <div class="flex items-center justify-between">
            <span class="text-white text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full" style="background:${cat.bg}">${esc(cat.label)}</span>
            <span class="text-ink-500 text-xs font-semibold">${car.year}</span>
          </div>
          <h3 class="font-display font-bold text-base text-ink mt-2.5 leading-snug">Daihatsu ${esc(car.model)}</h3>
          <p class="text-ink-500 text-xs">${esc(car.type)}</p>
          <p class="font-display font-extrabold text-lg text-brand mt-2">${formatRupiah(car.price)}</p>

          <ul class="grid grid-cols-3 gap-1 mt-4 pt-4 border-t border-ink/5 text-[11px] text-ink-500 font-medium">
            <li class="flex flex-col items-center gap-1 text-center">
              <i class="fa-solid fa-gears text-sky2 text-sm" aria-hidden="true"></i>
              <span><span class="sr-only">Transmisi: </span>${esc(car.transmission)}</span>
            </li>
            <li class="flex flex-col items-center gap-1 text-center border-x border-ink/5">
              <i class="fa-solid ${fuelIcon} text-mango text-sm" aria-hidden="true"></i>
              <span><span class="sr-only">Bahan bakar: </span>${esc(car.fuel)}</span>
            </li>
            <li class="flex flex-col items-center gap-1 text-center">
              <i class="fa-solid fa-users text-grape text-sm" aria-hidden="true"></i>
              <span><span class="sr-only">Kapasitas: </span>${car.seats} Kursi</span>
            </li>
          </ul>
        </div>
      </article>`;
  }

  const swipeHint = $('#swipeHint');

  function renderCars(list) {
    carGrid.innerHTML = list.map(carCardTemplate).join('');
    resultCount.textContent = list.length;
    emptyState.classList.toggle('hidden', list.length > 0);
    carGrid.classList.toggle('hidden', list.length === 0);
    // Sembunyikan petunjuk geser bila tidak ada hasil
    if (swipeHint) swipeHint.classList.toggle('hidden', list.length === 0);
    App.observeReveals(carGrid);
    attachTilt();
    // Pulihkan status tombol "bandingkan" pada kartu yang baru dirender
    if (App.syncCompareButtons) App.syncCompareButtons();
  }
  App.renderCars = renderCars;

  /* ---------- Efek 3D tilt pada kartu (hanya perangkat mouse) ---------- */
  function attachTilt() {
    if (!canHover || prefersReducedMotion) return;
    $$('.car-card', carGrid).forEach((card) => {
      card.addEventListener('mousemove', (e) => {
        const r = card.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - 0.5;
        const y = (e.clientY - r.top) / r.height - 0.5;
        card.style.transitionDelay = '0s';
        card.style.transform = `perspective(1000px) rotateX(${(-y * 7).toFixed(2)}deg) rotateY(${(x * 7).toFixed(2)}deg) translateY(-8px) scale(1.02)`;
      });
      card.addEventListener('mouseleave', () => { card.style.transform = ''; });
    });
  }

  /* ---------- Favorit & lihat detail (event delegation) ---------- */
  carGrid.addEventListener('click', (e) => {
    const fav = e.target.closest('.fav-btn');
    if (fav) {
      const active = fav.getAttribute('aria-pressed') !== 'true';
      fav.setAttribute('aria-pressed', String(active));
      fav.classList.toggle('text-brand', active);
      fav.innerHTML = `<i class="${active ? 'fa-solid' : 'fa-regular'} fa-heart" aria-hidden="true"></i>`;
      App.showToast(active ? '<i class="fa-solid fa-heart text-brand mr-2"></i>Ditambahkan ke favorit' : 'Dihapus dari favorit');
      return;
    }
    const detail = e.target.closest('.detail-btn');
    if (detail) {
      // Buka panel detail (drawer meluncur dari kanan)
      if (App.openCarDetail) App.openCarDetail(+detail.dataset.id);
    }
  });

  /* ---------- Search & filter ---------- */
  const fModel = $('#fModel');
  const fType  = $('#fType');
  const fYear  = $('#fYear');
  const fPrice = $('#fPrice');
  let activeCat = ''; // dari chip

  function fillOptions(select, values, placeholder) {
    select.innerHTML = `<option value="">${placeholder}</option>` +
      values.map((v) => `<option value="${esc(v)}">${esc(v)}</option>`).join('');
  }

  fillOptions(fModel, uniqueSorted(CARS.map((c) => c.model)), 'Semua Model');
  fillOptions(fYear, uniqueSorted(CARS.map((c) => c.year)).reverse(), 'Semua Tahun');
  fillOptions(fType, uniqueSorted(CARS.map((c) => c.type)), 'Semua Tipe');

  // Tipe bergantung pada model yang dipilih
  fModel.addEventListener('change', () => {
    if (!fModel.value) { fillOptions(fType, uniqueSorted(CARS.map((c) => c.type)), 'Semua Tipe'); return; }
    const types = uniqueSorted(CARS.filter((c) => c.model === fModel.value).map((c) => c.type));
    fillOptions(fType, types, 'Semua Tipe');
  });

  function applyFilter() {
    const [minP, maxP] = fPrice.value ? fPrice.value.split('-').map(Number) : [0, Infinity];
    const result = CARS.filter((c) =>
      (!activeCat     || c.category === activeCat) &&
      (!fModel.value  || c.model === fModel.value) &&
      (!fType.value   || c.type === fType.value) &&
      (!fYear.value   || c.year === +fYear.value) &&
      c.price >= minP && c.price < maxP
    );
    renderCars(result);
    return result;
  }

  $('#searchForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const result = applyFilter();
    App.showToast(`<i class="fa-solid fa-magnifying-glass text-mint mr-2"></i>${result.length} mobil ditemukan`);
    document.getElementById('inventory').scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth' });
  });

  // Chip filter cepat
  const chips = $$('#quickChips .chip');
  chips.forEach((chip) => chip.addEventListener('click', () => {
    chips.forEach((c) => c.setAttribute('aria-pressed', 'false'));
    chip.setAttribute('aria-pressed', 'true');
    activeCat = chip.dataset.cat;
    applyFilter();
  }));

  function resetFilter() {
    $('#searchForm').reset();
    activeCat = '';
    chips.forEach((c) => c.setAttribute('aria-pressed', String(c.dataset.cat === '')));
    fillOptions(fType, uniqueSorted(CARS.map((c) => c.type)), 'Semua Tipe');
    renderCars(CARS);
  }
  $('#resetFilter').addEventListener('click', resetFilter);
  $('#emptyReset').addEventListener('click', resetFilter);
})(window.App);
