/* =========================================================================
   CAR DETAIL — Panel drawer yang meluncur dari kanan untuk menampilkan
   detail lengkap sebuah mobil. Dibuka lewat App.openCarDetail(id).
   ========================================================================= */
window.App = window.App || {};

(function (App) {
  const { $, esc, formatRupiah } = App;
  const CARS = App.CARS;
  const CAT_STYLE = App.CAT_STYLE;

  // Nomor WhatsApp dari pengaturan admin (window.App.WA), fallback default.
  const WA_NUMBER = (window.App && window.App.WA) ? window.App.WA : '6281234567890';

  const overlay = $('#detailOverlay');
  const panel   = $('#detailPanel');
  const body    = $('#detailBody');
  const closeBtn = $('#detailClose');
  if (!panel) return;

  let lastFocused = null; // untuk mengembalikan fokus setelah panel ditutup

  /* Deskripsi singkat otomatis berdasarkan kategori */
  const CAT_DESC = {
    LCGC:  'Mobil hemat dan lincah, pas untuk mobilitas harian di perkotaan dengan konsumsi BBM yang irit.',
    MPV:   'Kabin lapang untuk keluarga, nyaman untuk perjalanan jauh maupun aktivitas harian bersama.',
    SUV:   'Tampilan gagah dengan ground clearance tinggi, tangguh di berbagai medan dan tetap nyaman.',
    Niaga: 'Andalan untuk usaha dengan daya angkut besar, tangguh, dan biaya operasional rendah.',
  };

  /* Fitur unggulan generik (ditampilkan sebagai chip) */
  const HIGHLIGHTS = ['Dual SRS Airbag', 'Rem ABS + EBD', 'Kamera Mundur', 'Layar Sentuh', 'Kunci Immobilizer', 'AC Digital'];

  function specRow(icon, color, label, value) {
    return `
      <div class="d-spec">
        <i class="fa-solid ${icon}" style="color:${color}" aria-hidden="true"></i>
        <div>
          <p class="text-ink-500 text-xs">${label}</p>
          <p class="font-display font-bold text-ink text-sm">${esc(value)}</p>
        </div>
      </div>`;
  }

  function render(car) {
    const cat = CAT_STYLE[car.category] || { bg: '#0a5fd1', label: car.category };
    const fuelIcon = car.fuel === 'Listrik' ? 'fa-bolt' : 'fa-gas-pump';
    const desc = car.desc || CAT_DESC[car.category] || 'Kendaraan Daihatsu berkualitas dengan layanan purna jual terpercaya.';
    const waText = encodeURIComponent(`Halo, saya tertarik dengan Daihatsu ${car.model} ${car.type}. Bisa dibantu info & test drive?`);

    const feats = (Array.isArray(car.features) && car.features.length) ? car.features : HIGHLIGHTS;
    const chips = feats.map((f) =>
      `<span class="inline-flex items-center gap-1.5 bg-cream text-ink text-xs font-semibold px-3 py-1.5 rounded-full">
        <i class="fa-solid fa-check text-brand text-[10px]" aria-hidden="true"></i> ${esc(f)}
      </span>`).join('');

    body.innerHTML = `
      <!-- Gambar header -->
      <div class="relative">
        <img src="${esc(car.img)}" alt="Daihatsu ${esc(car.model)} ${car.year}"
             class="w-full h-60 object-cover" onerror="this.onerror=null;this.src=FALLBACK_IMG;" />
        <div class="absolute inset-0" style="background:linear-gradient(0deg, #fff 2%, transparent 45%)"></div>
        ${car.badge ? `<span class="absolute top-4 left-4 text-white text-[11px] font-bold uppercase tracking-wide px-3 py-1 rounded-full" style="background:${car.accent[0]}">${esc(car.badge)}</span>` : ''}
      </div>

      <div class="px-6 pb-8 -mt-6 relative">
        <!-- Judul & harga -->
        <div class="d-anim d1">
          <div class="flex items-center gap-2">
            <span class="text-white text-[11px] font-bold uppercase tracking-wide px-2.5 py-0.5 rounded-full" style="background:${cat.bg}">${esc(cat.label)}</span>
            <span class="text-ink-500 text-sm font-semibold">${car.year}</span>
          </div>
          <h2 id="detailTitle" class="font-display font-extrabold text-2xl text-ink mt-2">Daihatsu ${esc(car.model)}</h2>
          <p class="text-ink-500">${esc(car.type)}</p>
          <p class="font-display font-black text-3xl text-brand mt-3">${formatRupiah(car.price)}</p>
          <p class="text-ink-500 text-xs">Harga OTR* &bull; belum termasuk aksesori</p>
        </div>

        <!-- Spesifikasi -->
        <div class="d-anim d2 grid grid-cols-2 gap-3 mt-6">
          ${specRow('fa-gears', '#2e86ff', 'Transmisi', car.transmission)}
          ${specRow(fuelIcon, '#ffc529', 'Bahan Bakar', car.fuel)}
          ${specRow('fa-users', '#0a5fd1', 'Kapasitas', car.seats + ' Kursi')}
          ${specRow('fa-tag', '#123a8f', 'Kategori', cat.label)}
        </div>

        <!-- Deskripsi -->
        <div class="d-anim d3 mt-6">
          <h3 class="font-display font-bold text-ink text-sm uppercase tracking-wide">Tentang Mobil Ini</h3>
          <p class="text-ink-500 leading-relaxed mt-2 text-sm">${esc(desc)}</p>
        </div>

        <!-- Fitur unggulan -->
        <div class="d-anim d3 mt-5">
          <h3 class="font-display font-bold text-ink text-sm uppercase tracking-wide mb-3">Fitur Unggulan</h3>
          <div class="flex flex-wrap gap-2">${chips}</div>
        </div>

        <!-- Tombol aksi -->
        <div class="d-anim d4 flex flex-col gap-3 mt-8">
          <a href="https://wa.me/${WA_NUMBER}?text=${waText}" target="_blank" rel="noopener"
             class="btn-fun text-white font-display font-bold py-3.5 rounded-full text-center inline-flex items-center justify-center gap-2">
            <i class="fa-brands fa-whatsapp text-lg" aria-hidden="true"></i> Pesan Test Drive
          </a>
          <a href="#services" data-close-detail
             class="btn-soft text-ink font-display font-bold py-3.5 rounded-full text-center inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-calculator text-brand" aria-hidden="true"></i> Simulasi Kredit
          </a>
        </div>
      </div>`;
  }

  function open(id) {
    const car = CARS.find((c) => c.id === id);
    if (!car) return;
    lastFocused = document.activeElement;
    render(car);

    overlay.hidden = false;
    // paksa reflow agar transisi berjalan
    requestAnimationFrame(() => {
      overlay.classList.add('show');
      panel.classList.add('open');
    });
    panel.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden'; // kunci scroll latar
    closeBtn.focus();
  }
  App.openCarDetail = open;

  function close() {
    overlay.classList.remove('show');
    panel.classList.remove('open');
    panel.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    // sembunyikan overlay setelah animasi selesai
    setTimeout(() => { overlay.hidden = true; }, 450);
    if (lastFocused) lastFocused.focus();
  }

  // Kontrol tutup
  closeBtn.addEventListener('click', close);
  overlay.addEventListener('click', close);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && panel.classList.contains('open')) close();
  });
  // Tombol di dalam panel yang harus menutup panel (mis. "Simulasi Kredit")
  body.addEventListener('click', (e) => {
    if (e.target.closest('[data-close-detail]')) close();
  });
})(window.App);
