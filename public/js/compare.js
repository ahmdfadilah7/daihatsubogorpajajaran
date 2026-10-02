/* =========================================================================
   COMPARE — Fitur "Bandingkan Mobil".
   Pengguna memilih hingga 3 mobil lewat tombol banding di kartu,
   lalu melihat tabel perbandingan spesifikasi berdampingan.
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, $$, esc, formatRupiah } = App;
  const CARS = App.CARS;
  const CAT_STYLE = App.CAT_STYLE;

  // Nomor WhatsApp dari pengaturan admin (window.App.WA), fallback default.
  const WA_NUMBER = (window.App && window.App.WA) ? window.App.WA : '6281234567890';

  const MAX = 3;
  const selected = []; // berisi id mobil terpilih

  const carGrid   = $('#carGrid');
  const tray      = $('#compareTray');
  const slotsWrap = $('#compareSlots');
  const countEl   = $('#compareCount');
  const openBtn   = $('#compareOpen');
  const clearBtn  = $('#compareClear');
  const overlay   = $('#compareOverlay');
  const modalBody = $('#compareBody');
  if (!carGrid || !tray) return;

  const carById = (id) => CARS.find((c) => c.id === id);

  /* ---------- Sinkronkan tampilan tombol banding di kartu ---------- */
  function syncButtons() {
    $$('.compare-btn', carGrid).forEach((btn) => {
      const on = selected.includes(+btn.dataset.id);
      btn.setAttribute('aria-pressed', String(on));
      btn.innerHTML = `<i class="fa-solid ${on ? 'fa-check' : 'fa-code-compare'}" aria-hidden="true"></i>`;
    });
  }

  /* ---------- Render tray (slot mobil terpilih) ---------- */
  function renderTray() {
    countEl.textContent = `(${selected.length}/${MAX})`;

    let html = '';
    for (let i = 0; i < MAX; i++) {
      const id = selected[i];
      if (id != null) {
        const c = carById(id);
        html += `
          <div class="compare-slot" title="Daihatsu ${esc(c.model)}">
            <img src="${esc(c.img)}" alt="${esc(c.model)}" onerror="this.onerror=null;this.src=FALLBACK_IMG;" />
            <button type="button" data-remove="${c.id}" aria-label="Hapus ${esc(c.model)}"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
          </div>`;
      } else {
        html += `<div class="compare-slot-empty" aria-hidden="true"></div>`;
      }
    }
    slotsWrap.innerHTML = html;

    openBtn.disabled = selected.length < 2; // minimal 2 untuk dibandingkan
    // Tampilkan/sembunyikan tray
    if (selected.length > 0) {
      tray.hidden = false;
      requestAnimationFrame(() => tray.classList.add('show'));
    } else {
      tray.classList.remove('show');
      setTimeout(() => { if (selected.length === 0) tray.hidden = true; }, 450);
    }
  }

  /* ---------- Tambah / hapus mobil dari perbandingan ---------- */
  function toggle(id) {
    const idx = selected.indexOf(id);
    if (idx >= 0) {
      selected.splice(idx, 1);
    } else {
      if (selected.length >= MAX) {
        App.showToast(`<i class="fa-solid fa-triangle-exclamation text-mango mr-2"></i>Maksimal ${MAX} mobil untuk dibandingkan`);
        return;
      }
      selected.push(id);
    }
    syncButtons();
    renderTray();
  }

  // Klik tombol banding di kartu (event delegation)
  carGrid.addEventListener('click', (e) => {
    const btn = e.target.closest('.compare-btn');
    if (btn) { e.preventDefault(); toggle(+btn.dataset.id); }
  });
  App.syncCompareButtons = syncButtons; // dipanggil ulang saat grid dirender ulang (filter)

  // Hapus dari slot tray
  slotsWrap.addEventListener('click', (e) => {
    const rm = e.target.closest('[data-remove]');
    if (rm) { toggle(+rm.dataset.remove); }
  });

  clearBtn.addEventListener('click', () => {
    selected.length = 0;
    syncButtons();
    renderTray();
  });

  /* ---------- Panel perbandingan ---------- */
  function buildTable() {
    const cars = selected.map(carById);

    // Definisi baris: label, nilai, dan penanda "terbaik" (opsional)
    const priceMin = Math.min(...cars.map((c) => c.price));
    const seatMax  = Math.max(...cars.map((c) => c.seats));

    const rows = [
      { label: 'Harga', render: (c) => `<span class="${c.price === priceMin ? 'best' : ''}">${formatRupiah(c.price)}</span>` },
      { label: 'Kategori', render: (c) => (CAT_STYLE[c.category] || {}).label || c.category },
      { label: 'Tipe', render: (c) => esc(c.type) },
      { label: 'Tahun', render: (c) => c.year },
      { label: 'Transmisi', render: (c) => esc(c.transmission) },
      { label: 'Bahan Bakar', render: (c) => esc(c.fuel) },
      { label: 'Kapasitas', render: (c) => `<span class="${c.seats === seatMax ? 'best' : ''}">${c.seats} Kursi</span>` },
    ];

    const head = `
      <thead>
        <tr>
          <th class="row-label">Spesifikasi</th>
          ${cars.map((c) => `
            <th>
              <div class="w-28 mx-auto">
                <img src="${esc(c.img)}" alt="${esc(c.model)}" class="w-full h-20 object-cover rounded-xl mb-2" onerror="this.onerror=null;this.src=FALLBACK_IMG;" />
                <p class="font-display font-bold text-ink text-sm leading-tight">Daihatsu ${esc(c.model)}</p>
              </div>
            </th>`).join('')}
        </tr>
      </thead>`;

    const bodyRows = rows.map((r) => `
      <tr>
        <td class="row-label">${r.label}</td>
        ${cars.map((c) => `<td class="text-ink text-sm">${r.render(c)}</td>`).join('')}
      </tr>`).join('');

    const cta = `
      <tr>
        <td class="row-label"></td>
        ${cars.map((c) => `
          <td>
            <a href="https://wa.me/${WA_NUMBER}?text=${encodeURIComponent('Halo, saya tertarik Daihatsu ' + c.model)}" target="_blank" rel="noopener"
               class="btn-fun text-white text-xs font-bold px-4 py-2 rounded-full inline-flex items-center gap-1.5">
              <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Tanya
            </a>
          </td>`).join('')}
      </tr>`;

    modalBody.innerHTML = `
      <div class="p-2 sm:p-4">
        <table class="compare-table">${head}<tbody>${bodyRows}${cta}</tbody></table>
        <p class="text-ink-500 text-xs text-center mt-4 flex items-center justify-center gap-1">
          <i class="fa-solid fa-star text-mango" aria-hidden="true"></i> menandai nilai terbaik antar pilihan
        </p>
      </div>`;
  }

  function openModal() {
    if (selected.length < 2) return;
    buildTable();
    overlay.hidden = false;
    requestAnimationFrame(() => overlay.classList.add('show'));
    document.body.style.overflow = 'hidden';
  }
  function closeModal() {
    overlay.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => { overlay.hidden = true; }, 350);
  }

  openBtn.addEventListener('click', openModal);
  $('#compareModalClose').addEventListener('click', closeModal);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !overlay.hidden) closeModal(); });

  // Inisialisasi
  renderTray();
})(window.App);
