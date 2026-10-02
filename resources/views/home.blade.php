<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  @php
    $settings = $settings ?? [];
    $sName = $settings['site_name'] ?? 'Daihatsu Sahabat';
    $sMetaTitle = $settings['meta_title'] ?? 'Daihatsu Sahabat | Dealer Resmi Daihatsu';
    $sMetaDesc = $settings['meta_description'] ?? 'Daihatsu Sahabat - Dealer resmi Daihatsu. Temukan Ayla, Sigra, Terios, Rocky, Xenia & lainnya dengan promo, cicilan ringan, dan servis terpercaya.';
    $sKeywords = $settings['meta_keywords'] ?? '';
    $sAuthor = $settings['meta_author'] ?? '';
    $sOgTitle = ($settings['og_title'] ?? '') !== '' ? $settings['og_title'] : $sMetaTitle;
    $sOgDesc = ($settings['og_description'] ?? '') !== '' ? $settings['og_description'] : $sMetaDesc;
    $sLogo = $settings['logo'] ?? '';
    $sFavicon = $settings['favicon'] ?? '';
    $sOgImage = $settings['og_image'] ?? '';
    $assetUrl = function ($path) {
        if ($path === null || $path === '') {
            return null;
        }
        return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : asset($path);
    };
    $sFaviconUrl = $assetUrl($sFavicon);
    $sOgImageUrl = $assetUrl($sOgImage);
    // Feature toggles — default ENABLED when the key is missing/empty so the
    // site is unchanged until the admin turns a feature off in Pengaturan.
    $flagOn = function ($key) use ($settings) {
        $raw = $settings[$key] ?? '1';
        if ($raw === null || $raw === '') {
            return true;
        }
        return in_array(strtolower(trim((string) $raw)), ['1', 'true', 'on', 'yes'], true);
    };
    $quizEnabled = $flagOn('feature_quiz');
    $cornerEnabled = $flagOn('feature_corner');
    $wheelEnabled = $flagOn('feature_wheel');
    // Text fallback: return the stored value when present & non-empty,
    // otherwise the current literal passed as the default. Keeps the page
    // identical to today until an admin edits a key in Pengaturan.
    $t = fn ($key, $default = '') => (($settings[$key] ?? '') !== '') ? $settings[$key] : $default;
  @endphp
  <meta name="description" content="{{ $sMetaDesc }}" />
  @if ($sKeywords !== '')<meta name="keywords" content="{{ $sKeywords }}" />@endif
  @if ($sAuthor !== '')<meta name="author" content="{{ $sAuthor }}" />@endif
  <title>{{ $sMetaTitle }}</title>

  <!-- ========== Open Graph / Twitter ========== -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="{{ url()->current() }}" />
  <meta property="og:title" content="{{ $sOgTitle }}" />
  <meta property="og:description" content="{{ $sOgDesc }}" />
  @if ($sOgImageUrl)<meta property="og:image" content="{{ $sOgImageUrl }}" />@endif
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="{{ $sOgTitle }}" />
  <meta name="twitter:description" content="{{ $sOgDesc }}" />
  @if ($sOgImageUrl)<meta name="twitter:image" content="{{ $sOgImageUrl }}" />@endif
  @if ($sFaviconUrl)<link rel="icon" href="{{ $sFaviconUrl }}" />@endif

  <!-- ========== Google Fonts: Poppins (heading) + Inter (body) ========== -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800;900&display=swap" rel="stylesheet" />

  <!-- ========== Font Awesome (ikon) ========== -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

  <!-- ========== Tailwind CSS (CDN) + konfigurasi tema ========== -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="{{ asset('js/tailwind.config.js') }}"></script>

  <!-- ========== Custom CSS (animasi, gradien, slider, dll) ========== -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>

<body class="antialiased overflow-x-hidden">

  <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:bg-brand focus:text-white focus:px-4 focus:py-2 focus:rounded-lg">Lewati ke konten utama</a>

  <!-- =====================================================================
       0. BANNER PROMO (bar tipis di paling atas, bisa ditutup)
       ===================================================================== -->
  <div id="promoBar" class="fixed top-0 inset-x-0 z-[55] text-white" style="background:linear-gradient(90deg,#0a5fd1,#123a8f)">
    <div class="max-w-7xl mx-auto pl-4 pr-11 sm:px-8 h-10 flex items-center justify-center gap-2 text-center overflow-hidden">
      <i class="fa-solid fa-gift text-mango animate-pulse shrink-0" aria-hidden="true"></i>
      <p class="text-xs sm:text-sm font-medium truncate">
        <span class="font-bold">{{ $t('promo_badge', 'Promo Spesial!') }}</span> {{ $t('promo_text', 'DP mulai 15 Juta') }}
        <span class="hidden sm:inline">{{ $t('promo_text_extra', '+ gratis servis 1 tahun.') }}</span>
        <a href="#inventory" class="underline underline-offset-2 hover:text-mango font-semibold ml-1 whitespace-nowrap">{{ $t('promo_cta', 'Lihat mobil →') }}</a>
      </p>
      <button type="button" id="promoClose" class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full hover:bg-white/20 grid place-items-center transition-colors shrink-0" aria-label="Tutup banner promo">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </div>
  </div>

  <!-- =====================================================================
       1. NAVBAR (Sticky, transparan -> putih saat scroll)
       ===================================================================== -->
  <header id="navbar" class="fixed top-10 inset-x-0 z-50 py-4">
    <nav class="max-w-7xl mx-auto px-5 lg:px-8 flex items-center justify-between" aria-label="Navigasi utama">
      <a href="#home" class="flex items-center gap-2.5 group" aria-label="{{ $sName }} - Beranda">
        @if (($sLogoUrl = $assetUrl($sLogo)))
          <img src="{{ $sLogoUrl }}" alt="{{ $sName }}" class="w-11 h-11 rounded-2xl object-cover">
        @else
          <span class="w-11 h-11 rounded-2xl btn-fun flex items-center justify-center text-white text-lg font-display font-black">D</span>
        @endif
        <span class="font-display font-extrabold text-xl tracking-tight text-ink leading-none">
          @if (($settings['site_name'] ?? '') !== '')
            {{ $sName }}
          @else
            Daihatsu<span class="text-brand"> Sahabat</span>
          @endif
        </span>
      </a>

      <ul class="hidden md:flex items-center gap-9 font-display text-sm font-semibold text-ink-500">
        <li><a href="#home" class="nav-link hover:text-ink transition-colors">Home</a></li>
        <li><a href="#inventory" class="nav-link hover:text-ink transition-colors">Mobil</a></li>
        <li><a href="#services" class="nav-link hover:text-ink transition-colors">Layanan</a></li>
        @if ($quizEnabled)<li><a href="#kuis" class="nav-link hover:text-ink transition-colors">Kuis</a></li>@endif
        <li><a href="#testimoni" class="nav-link hover:text-ink transition-colors">Testimoni</a></li>
        <li><a href="#contact" class="nav-link hover:text-ink transition-colors">Kontak</a></li>
      </ul>

      <!-- CTA kanan: langsung ke kontak (tanpa login) -->
      <a href="#contact" class="hidden md:inline-flex btn-fun text-sm font-semibold text-white px-5 py-2.5 rounded-full items-center gap-2">
        <i class="fa-solid fa-headset" aria-hidden="true"></i> {{ $t('nav_cta', 'Hubungi Kami') }}
      </a>

      <button id="menuToggle" class="md:hidden w-11 h-11 rounded-xl bg-white shadow flex items-center justify-center text-ink"
              aria-label="Buka menu" aria-expanded="false" aria-controls="mobileMenu">
        <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
      </button>
    </nav>

    <div id="mobileMenu" class="md:hidden mx-5 mt-3 rounded-2xl bg-white shadow-xl">
      <ul class="flex flex-col p-4 gap-1 font-display font-semibold text-ink-500">
        <li><a href="#home" class="block px-4 py-3 rounded-xl hover:bg-cream hover:text-brand">Home</a></li>
        <li><a href="#inventory" class="block px-4 py-3 rounded-xl hover:bg-cream hover:text-brand">Mobil</a></li>
        <li><a href="#services" class="block px-4 py-3 rounded-xl hover:bg-cream hover:text-brand">Layanan</a></li>
        @if ($quizEnabled)<li><a href="#kuis" class="block px-4 py-3 rounded-xl hover:bg-cream hover:text-brand">Kuis</a></li>@endif
        <li><a href="#testimoni" class="block px-4 py-3 rounded-xl hover:bg-cream hover:text-brand">Testimoni</a></li>
        <li><a href="#contact" class="block px-4 py-3 rounded-xl hover:bg-cream hover:text-brand">Kontak</a></li>
        <li class="pt-3 mt-2 border-t border-ink/10">
          <a href="#contact" class="flex items-center justify-center gap-2 btn-fun text-white py-3 rounded-full text-sm font-semibold">
            <i class="fa-solid fa-headset" aria-hidden="true"></i> {{ $t('nav_cta', 'Hubungi Kami') }}
          </a>
        </li>
      </ul>
    </div>
  </header>

  <main id="main">

    <!-- =====================================================================
         2. HERO SECTION
         ===================================================================== -->
    <section id="home" class="relative min-h-screen flex items-center overflow-hidden pb-24 md:pb-20">
      <!-- Blob warna-warni dekoratif -->
      <div class="blob w-72 h-72 bg-mango left-[8%] top-24 float"></div>
      <div class="blob w-80 h-80 bg-sky2 right-[6%] top-40 float-2"></div>
      <div class="blob w-72 h-72 bg-grape left-1/3 bottom-10 float"></div>
      <!-- Bentuk dekoratif -->
      <svg class="spin-slow absolute right-16 top-1/3 w-24 h-24 text-mint/40 hidden lg:block" viewBox="0 0 100 100" fill="none" aria-hidden="true">
        <path d="M50 5 L61 39 L97 39 L68 61 L79 95 L50 74 L21 95 L32 61 L3 39 L39 39 Z" fill="currentColor"/>
      </svg>

      <div class="relative z-10 max-w-7xl mx-auto px-5 lg:px-8 w-full pt-32 grid lg:grid-cols-2 gap-12 items-center">
        <!-- Kiri: teks -->
        <div>
          <!-- Badge promo + countdown urgensi -->
          <div class="fade-up inline-flex items-center gap-2 bg-white shadow-md rounded-full pl-2 pr-3.5 py-1.5 text-xs font-semibold text-brand max-w-full">
            <span class="bg-brand text-white px-2.5 py-1 rounded-full text-[11px] shrink-0">{{ $t('hero_badge', 'PROMO') }}</span>
            <span class="hidden sm:inline">{{ $t('hero_countdown_label', 'Promo berakhir dalam') }}</span>
            <span class="sm:hidden">{{ $t('hero_countdown_label_short', 'Berakhir') }}</span>
            <span id="heroCountdown" class="font-display font-extrabold text-ink tabular-nums whitespace-nowrap">--</span>
          </div>

          <h1 class="fade-up delay-1 font-display font-black text-4xl sm:text-6xl lg:text-[4.2rem] leading-[1.05] mt-6 text-ink">{{ $t('hero_title', 'Mobil Keluarga') }} <span class="text-rainbow">{{ $t('hero_title_hl', 'Ceria') }}</span><br /> {{ $t('hero_title_suffix', 'untuk Semua!') }}</h1>

          <p class="fade-up delay-2 text-ink-500 text-base sm:text-lg mt-5 max-w-lg leading-relaxed">
            {{ $t('hero_desc', 'Dari Ayla yang irit sampai Terios yang gagah — temukan Daihatsu impian keluargamu. Cicilan ringan, servis gampang, sahabat di setiap perjalanan.') }}
          </p>

          <!-- Highlight benefit cepat -->
          <ul class="fade-up delay-2 flex flex-wrap gap-x-5 gap-y-2 mt-6 text-sm font-semibold text-ink">
            <li class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-mint" aria-hidden="true"></i> {{ $t('hero_benefit_1', 'DP mulai 15 Juta') }}</li>
            <li class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-mint" aria-hidden="true"></i> {{ $t('hero_benefit_2', 'Cicilan s/d 6 Tahun') }}</li>
            <li class="inline-flex items-center gap-2"><i class="fa-solid fa-circle-check text-mint" aria-hidden="true"></i> {{ $t('hero_benefit_3', 'Garansi 3 Tahun') }}</li>
          </ul>

          <div class="fade-up delay-3 flex flex-col sm:flex-row gap-4 mt-8">
            <a href="#inventory" class="btn-fun text-white font-display font-bold px-8 py-4 rounded-full inline-flex items-center justify-center gap-3">
              {{ $t('hero_btn_primary', 'Lihat Semua Mobil') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
            <a href="https://wa.me/6281234567890?text={{ rawurlencode($t('hero_wa_message', 'Halo, saya mau test drive mobil Daihatsu')) }}"
               target="_blank" rel="noopener"
               class="btn-soft text-ink font-display font-bold px-8 py-4 rounded-full inline-flex items-center justify-center gap-3">
              <i class="fa-brands fa-whatsapp text-brand text-lg" aria-hidden="true"></i> {{ $t('hero_btn_whatsapp', 'Test Drive') }}
            </a>  
          </div>

        </div>

        <!-- Kanan: SLIDER mobil unggulan (mengambang) - tampil di semua device -->
        <div class="fade-up delay-2 relative">
          <div class="absolute -inset-6 bg-gradient-to-tr from-brand/20 via-mango/20 to-sky2/20 rounded-[2.5rem] blur-xl"></div>

          <!-- Kartu slider. Slide diisi otomatis oleh JS dari array HERO_SLIDES. -->
          <div id="heroSlider" class="relative bg-white rounded-[2rem] p-4 shadow-2xl float"
               role="region" aria-roledescription="carousel" aria-label="Mobil unggulan">
            <!-- Panggung gambar -->
            <div class="relative rounded-3xl overflow-hidden h-72">
              <div id="heroSlides" class="w-full h-full"></div>

              <!-- Tombol prev / next -->
              <button type="button" id="heroPrev" class="slider-arrow left-3" aria-label="Sebelumnya">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
              </button>
              <button type="button" id="heroNext" class="slider-arrow right-3" aria-label="Berikutnya">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
              </button>

              <!-- Dot navigasi -->
              <div id="heroDots" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2 z-10" role="tablist" aria-label="Pilih slide"></div>
            </div>

            <!-- Info slide (nama + deskripsi + label), berganti mengikuti slide -->
            <div class="flex items-center justify-between mt-4 px-2">
              <div>
                <p id="heroName" class="font-display font-bold text-ink text-lg transition-all duration-300">Daihatsu Terios</p>
                <p id="heroDesc" class="text-ink-500 text-sm transition-all duration-300">SUV tangguh 7 penumpang</p>
              </div>
              <span id="heroTag" class="btn-fun text-white text-sm font-bold px-4 py-2 rounded-full">Terlaris</span>
            </div>
          </div>

          <!-- Badge kecil melayang (harga ikut berganti per slide) -->
          <div class="absolute left-2 sm:-left-6 top-8 bg-white rounded-2xl shadow-xl px-4 py-3 float-2 z-20">
            <p class="text-xs text-ink-500">{{ $t('hero_price_label', 'Mulai') }}</p>
            <p id="heroPrice" class="font-display font-extrabold text-brand">Rp 219 Jt</p>
          </div>
        </div>
      </div>

      <div class="hidden lg:flex absolute bottom-10 left-1/2 -translate-x-1/2 z-10 flex-col items-center gap-2 text-ink-500 text-xs tracking-widest uppercase">
        <div class="scroll-mouse" aria-hidden="true"></div>
        Scroll
      </div>
    </section>

    <!-- =====================================================================
         3. STRIP KEUNGGULAN BERJALAN (marquee ganda, warna-warni)
         Dua baris berjalan berlawanan arah + pil warna agar lebih hidup.
         ===================================================================== -->
    <section class="relative mt-4 py-10 overflow-hidden" aria-hidden="true">
      <!-- Fade di tepi kiri/kanan biar mulus -->
      <div class="marquee-mask">
        <!-- Track berisi 2 grup identik. Setiap grup adalah satu blok utuh,
             animasi menggeser sejauh lebar 1 grup -> loop mulus tanpa celah. -->
        <div class="marquee-track marquee-ltr">
          <div class="marquee-group">
            @foreach ($marqueeItems as $item)
              <span class="pill" style="--pc:{{ $item->color }}"><i class="fa-solid {{ $item->icon }}"></i> {{ $item->text }}</span>
            @endforeach
          </div>
          <!-- Grup kedua: salinan identik (aria-hidden karena hanya untuk loop visual) -->
          <div class="marquee-group">
            @foreach ($marqueeItems as $item)
              <span class="pill" style="--pc:{{ $item->color }}"><i class="fa-solid {{ $item->icon }}"></i> {{ $item->text }}</span>
            @endforeach
          </div>
        </div>
      </div>
    </section>

    <!-- =====================================================================
         4. DAFTAR MOBIL + FILTER (filter tepat di atas grid mobil)
         ===================================================================== -->
    <section id="inventory" class="relative py-16 md:py-24 scroll-mt-20">
      <div class="relative max-w-7xl mx-auto px-5 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
          <div class="reveal reveal-left">
            <p class="text-brand font-display font-bold text-sm tracking-[.2em] uppercase">{{ $t('sec_inventory_eyebrow', 'Pilihan Mobil') }}</p>
            <h2 class="font-display font-extrabold text-3xl sm:text-5xl text-ink mt-3">{{ $t('sec_inventory_title', 'Koleksi') }} <span class="text-rainbow">{{ $t('sec_inventory_title_hl', 'Daihatsu') }}</span></h2>
          </div>
          <p class="reveal reveal-right text-ink-500 max-w-md">
            {{ $t('sec_inventory_subtitle_pre', 'Menampilkan') }} <span id="resultCount" class="text-brand font-bold">0</span> {{ $t('sec_inventory_subtitle_post', 'mobil. Semua unit bergaransi resmi & siap antar ke rumahmu.') }}
          </p>
        </div>

        <!-- ---------- SEARCH & FILTER BAR (tepat di atas list mobil) ---------- -->
        <div aria-labelledby="searchTitle" class="bg-white rounded-3xl p-6 md:p-7 shadow-xl shadow-ink/5 border border-ink/5 mb-10 reveal">
          <div class="flex items-center justify-between mb-5">
            <h3 id="searchTitle" class="font-display font-bold text-ink text-lg flex items-center gap-2">
              <i class="fa-solid fa-sliders text-brand" aria-hidden="true"></i> {{ $t('filter_heading', 'Filter Mobil') }}
            </h3>
            <button type="button" id="resetFilter" class="text-xs font-semibold text-ink-500 hover:text-brand transition-colors">
              <i class="fa-solid fa-rotate-left mr-1" aria-hidden="true"></i> Reset
            </button>
          </div>

          <!-- Chip filter cepat berdasarkan kategori -->
          <div class="flex flex-wrap gap-2 mb-6" id="quickChips" role="group" aria-label="Filter cepat kategori">
            <button type="button" class="chip bg-cream border border-ink/10 text-ink-500 text-sm font-semibold px-4 py-2 rounded-full" data-cat="" aria-pressed="true">Semua</button>
            <button type="button" class="chip bg-cream border border-ink/10 text-ink-500 text-sm font-semibold px-4 py-2 rounded-full" data-cat="LCGC" aria-pressed="false">Hemat (LCGC)</button>
            <button type="button" class="chip bg-cream border border-ink/10 text-ink-500 text-sm font-semibold px-4 py-2 rounded-full" data-cat="MPV" aria-pressed="false">Keluarga (MPV)</button>
            <button type="button" class="chip bg-cream border border-ink/10 text-ink-500 text-sm font-semibold px-4 py-2 rounded-full" data-cat="SUV" aria-pressed="false">SUV</button>
            <button type="button" class="chip bg-cream border border-ink/10 text-ink-500 text-sm font-semibold px-4 py-2 rounded-full" data-cat="Niaga" aria-pressed="false">Niaga</button>
          </div>

          <form id="searchForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4" novalidate>
            <div>
              <label for="fModel" class="block text-xs font-semibold uppercase tracking-wider text-ink-500 mb-2">Model</label>
              <select id="fModel" class="field-select w-full bg-cream border-2 border-ink/10 rounded-xl px-4 py-3.5 text-sm text-ink">
                <option value="">Semua Model</option>
              </select>
            </div>
            <div>
              <label for="fType" class="block text-xs font-semibold uppercase tracking-wider text-ink-500 mb-2">Tipe</label>
              <select id="fType" class="field-select w-full bg-cream border-2 border-ink/10 rounded-xl px-4 py-3.5 text-sm text-ink">
                <option value="">Semua Tipe</option>
              </select>
            </div>
            <div>
              <label for="fYear" class="block text-xs font-semibold uppercase tracking-wider text-ink-500 mb-2">Tahun</label>
              <select id="fYear" class="field-select w-full bg-cream border-2 border-ink/10 rounded-xl px-4 py-3.5 text-sm text-ink">
                <option value="">Semua Tahun</option>
              </select>
            </div>
            <div>
              <label for="fPrice" class="block text-xs font-semibold uppercase tracking-wider text-ink-500 mb-2">Harga</label>
              <select id="fPrice" class="field-select w-full bg-cream border-2 border-ink/10 rounded-xl px-4 py-3.5 text-sm text-ink">
                <option value="">Semua Harga</option>
                <option value="0-170000000">&lt; Rp 170 Juta</option>
                <option value="170000000-250000000">Rp 170 – 250 Juta</option>
                <option value="250000000-350000000">Rp 250 – 350 Juta</option>
                <option value="350000000-999999999999">&gt; Rp 350 Juta</option>
              </select>
            </div>
            <div class="flex items-end">
              <button type="submit" class="btn-fun w-full text-white font-display font-bold rounded-xl py-3.5 inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Cari
              </button>
            </div>
          </form>
        </div>

        <!-- Layar kecil: slider geser horizontal | Layar besar: grid 4 kolom -->
        <div id="carGrid" class="car-collection" aria-live="polite"></div>
        <!-- Petunjuk geser (hanya tampil di layar kecil) -->
        <p id="swipeHint" class="sm:hidden text-center text-ink-500 text-xs mt-4">
          <i class="fa-solid fa-arrows-left-right text-brand mr-1" aria-hidden="true"></i> {{ $t('inventory_swipe_hint', 'Geser untuk melihat mobil lainnya') }}
        </p>

        <div id="emptyState" class="hidden text-center py-20 bg-white rounded-3xl shadow-lg">
          <i class="fa-solid fa-car-side text-5xl text-ink-500/40" aria-hidden="true"></i>
          <h3 class="font-display font-bold text-xl text-ink mt-5">{{ $t('inventory_empty_title', 'Mobil tidak ditemukan') }}</h3>
          <p class="text-ink-500 mt-2">{{ $t('inventory_empty_desc', 'Coba ubah kriteria pencarian kamu.') }}</p>
          <button type="button" id="emptyReset" class="btn-fun mt-6 text-white font-semibold px-6 py-3 rounded-full">{{ $t('inventory_empty_btn', 'Tampilkan Semua') }}</button>
        </div>
      </div>
    </section>

    <!-- =====================================================================
         5. LAYANAN / KEUNGGULAN
         ===================================================================== -->
    <section id="services" class="relative py-20 md:py-28 scroll-mt-20 overflow-hidden">
      <div class="blob w-80 h-80 bg-pink2 -left-24 top-20 float"></div>
      <div class="blob w-80 h-80 bg-sky2 -right-24 bottom-10 float-2"></div>

      <div class="relative max-w-7xl mx-auto px-5 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 reveal">
          <p class="text-brand font-display font-bold text-sm tracking-[.2em] uppercase">{{ $t('sec_credit_eyebrow', 'Simulasi Kredit') }}</p>
          <h2 class="font-display font-extrabold text-3xl sm:text-5xl text-ink mt-3">{{ $t('sec_credit_title', 'Hitung Cicilan') }} <span class="text-rainbow">{{ $t('sec_credit_title_hl', 'Impianmu') }}</span></h2>
          <p class="text-ink-500 mt-5">{{ $t('sec_credit_subtitle', 'Atur harga, uang muka, dan tenor sesukamu untuk melihat perkiraan angsuran bulanan. Gampang, cepat, tanpa perlu daftar.') }}</p>
        </div>

        @php
          // Local credit params for the SSR slider attributes (so the markup
          // and window.App.CREDIT agree). Names chosen to avoid colliding with
          // the head @php helpers ($t/$flagOn/$assetUrl/$s*). Defaults match
          // today's slider attributes + RATE 0.04. Defaults clamped into bounds.
          $cNum = fn ($key, $default) => (($settings[$key] ?? '') !== '') ? 0 + $settings[$key] : $default;
          $cMinDp = $cNum('credit_min_dp', 10);
          $cMaxDp = $cNum('credit_max_dp', 50);
          $cDpStep = $cNum('credit_dp_step', 5);
          $cDefDp = max($cMinDp, min($cMaxDp, $cNum('credit_default_dp', 20)));
          $cMinTenor = $cNum('credit_min_tenor', 1);
          $cMaxTenor = $cNum('credit_max_tenor', 6);
          $cDefTenor = max($cMinTenor, min($cMaxTenor, $cNum('credit_default_tenor', 4)));
          $cFirstPrice = (int) ($cars[0]['price'] ?? 0);
        @endphp
        <!-- Kalkulator cicilan interaktif (satu card) -->
        <div class="reveal bg-white rounded-3xl shadow-xl p-5 sm:p-8 md:p-10 max-w-3xl mx-auto">
          <!-- Slider input -->
          <div class="space-y-6">
            <div>
              <div class="flex justify-between items-center text-sm font-semibold text-ink mb-2 gap-3">
                <label for="calcCar" class="inline-flex items-center gap-1.5 shrink-0">
                  <i class="fa-solid fa-car text-brand" aria-hidden="true"></i> {{ $t('calc_label_price', 'Harga Mobil') }}
                </label>
                <span id="calcPriceLabel" class="font-display font-extrabold text-brand text-right tabular-nums break-all">{{ $cars ? 'Rp '.number_format($cFirstPrice, 0, ',', '.') : 'Rp 0' }}</span>
              </div>
              <!-- Select mobil dengan ikon + chevron kustom (.field-select), lega & jelas di mobile -->
              <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-brand">
                  <i class="fa-solid fa-car-side" aria-hidden="true"></i>
                </span>
                <select id="calcCar" aria-label="Pilih mobil untuk simulasi"
                        class="field-select w-full rounded-xl border-2 border-ink/10 bg-cream pl-11 pr-10 py-3.5 text-sm sm:text-base font-semibold text-ink cursor-pointer">
                  @forelse ($cars as $car)
                    <option value="{{ $car['id'] }}" data-price="{{ (int) $car['price'] }}" @selected($loop->first)>
                      {{ $car['model'] }}{{ $car['type'] ? ' '.$car['type'] : '' }} — Rp {{ number_format((int) $car['price'], 0, ',', '.') }}
                    </option>
                  @empty
                    <option value="" data-price="0">Belum ada mobil</option>
                  @endforelse
                </select>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm font-semibold text-ink mb-2">
                <label for="calcDp">{{ $t('calc_label_dp', 'Uang Muka (DP)') }}</label>
                <span id="calcDpLabel" class="text-brand">{{ $cDefDp }}%</span>
              </div>
              <input id="calcDp" type="range" min="{{ $cMinDp }}" max="{{ $cMaxDp }}" step="{{ $cDpStep }}" value="{{ $cDefDp }}"
                     class="w-full accent-brand cursor-pointer" />
            </div>
            <div>
              <div class="flex justify-between text-sm font-semibold text-ink mb-2">
                <label for="calcTenor">{{ $t('calc_label_tenor', 'Tenor') }}</label>
                <span id="calcTenorLabel" class="text-brand">{{ $cDefTenor }} Tahun</span>
              </div>
              <input id="calcTenor" type="range" min="{{ $cMinTenor }}" max="{{ $cMaxTenor }}" step="1" value="{{ $cDefTenor }}"
                     class="w-full accent-brand cursor-pointer" />
            </div>
          </div>

          <!-- Hasil perhitungan -->
          <div class="mt-8 pt-8 border-t border-ink/10 text-center">
            <p class="text-ink-500 font-semibold text-sm">{{ $t('calc_label_result', 'Perkiraan Angsuran / Bulan') }}</p>
            <p id="calcResult" class="font-display font-black text-4xl sm:text-5xl text-brand mt-2">Rp 0</p>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 text-sm max-w-md mx-auto">
              <div class="bg-cream rounded-2xl p-3 sm:p-4 min-w-0">
                <p class="text-ink-500 text-xs sm:text-sm">{{ $t('calc_label_total_dp', 'Total DP') }}</p>
                <p id="calcDpAmount" class="font-display font-bold text-base sm:text-lg text-ink mt-1 tabular-nums break-all leading-tight">Rp 0</p>
              </div>
              <div class="bg-cream rounded-2xl p-3 sm:p-4 min-w-0">
                <p class="text-ink-500 text-xs sm:text-sm">{{ $t('calc_label_total_loan', 'Total Pinjaman') }}</p>
                <p id="calcLoan" class="font-display font-bold text-base sm:text-lg text-ink mt-1 tabular-nums break-all leading-tight">Rp 0</p>
              </div>
            </div>
            <a href="#contact" class="mt-7 inline-flex items-center gap-2 btn-fun text-white font-display font-bold px-6 py-3 rounded-full">
              {{ $t('calc_btn', 'Ajukan Kredit') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
            <p class="text-[11px] text-ink-500 mt-4">{{ $t('calc_footnote', '*Estimasi bunga flat 4%/tahun. Angka sebenarnya menyesuaikan leasing.') }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- =====================================================================
         5b. KUIS: MOBIL APA YANG COCOK UNTUKMU?
         Dirender & dikontrol oleh js/quiz.js
         ===================================================================== -->
    @if ($quizEnabled)
    <section id="kuis" class="relative py-20 md:py-28 scroll-mt-20 overflow-hidden">
      <div class="blob w-80 h-80 bg-mango -left-24 top-10 float"></div>
      <div class="blob w-72 h-72 bg-sky2 -right-24 bottom-10 float-2"></div>

      <div class="relative max-w-3xl mx-auto px-5 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10 reveal">
          <p class="text-brand font-display font-bold text-sm tracking-[.2em] uppercase">{{ $t('sec_quiz_eyebrow', 'Bingung Pilih?') }}</p>
          <h2 class="font-display font-extrabold text-3xl sm:text-5xl text-ink mt-3">{{ $t('sec_quiz_title', 'Cari Mobil') }} <span class="text-rainbow">{{ $t('sec_quiz_title_hl', 'Idealmu') }}</span></h2>
          <p class="text-ink-500 mt-5">{{ $t('sec_quiz_subtitle', 'Jawab 4 pertanyaan singkat, biar kami rekomendasikan Daihatsu yang paling pas buatmu.') }}</p>
        </div>

        <div id="quizCard" class="reveal bg-white rounded-3xl shadow-xl p-6 sm:p-10 relative overflow-hidden">
          <!-- Progress -->
          <div id="quizProgressWrap" class="mb-8">
            <div class="flex items-center justify-between text-xs font-semibold text-ink-500 mb-2">
              <span id="quizStepLabel">Pertanyaan 1 dari 4</span>
              <span id="quizPercent">25%</span>
            </div>
            <div class="h-2 rounded-full bg-cream overflow-hidden">
              <div id="quizBar" class="h-full rounded-full transition-all duration-500" style="width:25%;background:linear-gradient(90deg,#0a5fd1,#2e86ff)"></div>
            </div>
          </div>

          <!-- Panggung pertanyaan / hasil (diisi JS) -->
          <div id="quizStage" aria-live="polite"></div>
        </div>
      </div>
    </section>
    @endif

    <!-- =====================================================================
         6. TESTIMONI & GALERI PELANGGAN
         ===================================================================== -->
    <section id="testimoni" class="relative py-20 md:py-28 scroll-mt-20 overflow-hidden">
      <div class="blob w-80 h-80 bg-mango -right-24 top-10 float"></div>
      <div class="blob w-72 h-72 bg-mint -left-24 bottom-10 float-2"></div>

      <div class="relative max-w-6xl mx-auto px-5 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 reveal">
          <p class="text-brand font-display font-bold text-sm tracking-[.2em] uppercase">{{ $t('sec_testi_eyebrow', 'Kata Mereka') }}</p>
          <h2 class="font-display font-extrabold text-3xl sm:text-5xl text-ink mt-3">{{ $t('sec_testi_title', 'Cerita') }} <span class="text-rainbow">{{ $t('sec_testi_title_hl', 'Sahabat Daihatsu') }}</span></h2>
          <p class="text-ink-500 mt-5">{{ $t('sec_testi_subtitle', 'Ribuan keluarga sudah mempercayakan perjalanannya pada kami. Ini kata mereka.') }}</p>
        </div>

        <!-- Spotlight testimonial slider (dirender & dikontrol oleh js/testimonials.js) -->
        <div id="testiSlider" class="reveal" aria-roledescription="carousel" aria-label="Testimoni pelanggan">
          <div class="testi-stage">
            <button type="button" id="testiPrev" class="testi-nav testi-prev" aria-label="Testimoni sebelumnya">
              <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </button>

            <!-- Slide diisi oleh JS -->
            <div id="testiTrack" class="testi-track"></div>

            <button type="button" id="testiNext" class="testi-nav testi-next" aria-label="Testimoni berikutnya">
              <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
          </div>

          <!-- Bar progress auto-play -->
          <div class="testi-progress"><span id="testiBar"></span></div>

          <!-- Navigasi avatar -->
          <div id="testiAvatars" class="testi-avatars" role="tablist" aria-label="Pilih testimoni"></div>
        </div>
      </div>
    </section>
  </main>

  <!-- =====================================================================
       6. FOOTER
       ===================================================================== -->
  <footer id="contact" class="relative pt-20 pb-10 scroll-mt-20 text-white overflow-hidden" style="background:linear-gradient(160deg,#10224a,#123a8f)">
    <div class="blob w-72 h-72 bg-brand/60 -left-20 top-0"></div>
    <div class="blob w-72 h-72 bg-sky2/50 right-0 bottom-0"></div>

    <div class="relative max-w-7xl mx-auto px-5 lg:px-8">
      <!-- CTA atas -->
      <div class="reveal bg-white/5 border border-white/10 rounded-3xl p-8 sm:p-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-16 backdrop-blur">
        <div>
          <h3 class="font-display font-extrabold text-2xl sm:text-3xl">{{ $t('footer_cta_heading', 'Siap Bawa Pulang Daihatsu Impianmu?') }}</h3>
          <p class="text-white/70 mt-2">{{ $t('footer_cta_subtitle', 'Hubungi kami sekarang, gratis konsultasi & jadwal test drive.') }}</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <a href="https://wa.me/6281234567890" class="btn-fun text-white font-display font-bold px-7 py-4 rounded-full inline-flex items-center justify-center gap-2">
            <i class="fa-brands fa-whatsapp text-lg" aria-hidden="true"></i> {{ $t('footer_cta_wa_label', 'Chat WhatsApp') }}
          </a>
          <a href="tel:+622100000000" class="btn-soft text-ink font-display font-bold px-7 py-4 rounded-full inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $t('footer_cta_phone_label', 'Telepon') }}
          </a>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-12">
        <div class="lg:col-span-4 reveal">
          <a href="#home" class="flex items-center gap-2.5">
            @if (($sLogoUrl = $assetUrl($sLogo)))
              <img src="{{ $sLogoUrl }}" alt="{{ $sName }}" class="w-11 h-11 rounded-2xl object-cover">
            @else
              <span class="w-11 h-11 rounded-2xl btn-fun flex items-center justify-center text-white text-lg font-display font-black">D</span>
            @endif
            <span class="font-display font-extrabold text-xl">
              @if (($settings['site_name'] ?? '') !== '')
                {{ $sName }}
              @else
                Daihatsu <span class="text-brand-light">Sahabat</span>
              @endif
            </span>
          </a>
          <p class="text-white/60 mt-5 leading-relaxed max-w-sm">{{ $t('footer_about', 'Dealer resmi Daihatsu yang menemani keluarga Indonesia sejak 2011. Sahabat di setiap perjalanan.') }}</p>
          <div class="flex gap-3 mt-6">
            <a href="{{ ($settings['social_instagram'] ?? '') !== '' ? $settings['social_instagram'] : '#' }}" aria-label="Instagram" class="w-11 h-11 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand transition-all hover:-translate-y-1"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
            <a href="{{ ($settings['social_facebook'] ?? '') !== '' ? $settings['social_facebook'] : '#' }}" aria-label="Facebook" class="w-11 h-11 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand transition-all hover:-translate-y-1"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
            <a href="{{ ($settings['social_youtube'] ?? '') !== '' ? $settings['social_youtube'] : '#' }}" aria-label="YouTube" class="w-11 h-11 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand transition-all hover:-translate-y-1"><i class="fa-brands fa-youtube" aria-hidden="true"></i></a>
            <a href="{{ ($settings['social_tiktok'] ?? '') !== '' ? $settings['social_tiktok'] : '#' }}" aria-label="TikTok" class="w-11 h-11 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand transition-all hover:-translate-y-1"><i class="fa-brands fa-tiktok" aria-hidden="true"></i></a>
          </div>
        </div>

        <address class="not-italic lg:col-span-3 reveal" style="transition-delay:.2s">
          <h3 class="font-display font-bold uppercase tracking-wider text-sm">{{ $t('footer_contact_heading', 'Hubungi Kami') }}</h3>
          <ul class="mt-6 space-y-4 text-white/60">
            <li class="flex gap-3"><i class="fa-solid fa-location-dot text-brand-light mt-1" aria-hidden="true"></i><span>{{ $t('contact_address', 'Jl. Raya Sahabat No. 88, Jakarta') }}</span></li>
            <li class="flex gap-3"><i class="fa-solid fa-phone text-brand-light mt-1" aria-hidden="true"></i><a href="tel:+622100000000" class="hover:text-white">+62 21 0000 0000</a></li>
            <li class="flex gap-3"><i class="fa-solid fa-envelope text-brand-light mt-1" aria-hidden="true"></i><a href="mailto:{{ $t('contact_email', 'halo@example.com') }}" class="hover:text-white">{{ $t('contact_email', 'halo@example.com') }}</a></li>
            <li class="flex gap-3"><i class="fa-regular fa-clock text-brand-light mt-1" aria-hidden="true"></i><span>{{ $t('footer_hours', 'Sen – Sab, 08.00 – 20.00 WIB') }}</span></li>
          </ul>
        </address>

        <!-- Lokasi showroom (Google Maps) -->
        @php
          // Footer map wiring. The admin sets ONE field `maps_url` (the link
          // copied from the browser / Maps "Share"). We derive both the
          // clickable link and the iframe embed src from it:
          //   LINK  : use maps_url as-is (fallback to the sample when empty).
          //   EMBED : a plain Maps URL is not always directly embeddable, so —
          //     * if it already contains output=embed, use it as-is;
          //     * else if it's a google.com/maps URL, append output=embed
          //       (?/& chosen by whether it already has a query string);
          //     * else (e.g. a maps.app.goo.gl short link, which Google does
          //       NOT allow inside an iframe) fall back to a q-based embed of
          //       the admin's contact_address, or the hardcoded sample.
          //   This stays defensive: a weird URL never yields a broken iframe —
          //   worst case it embeds the contact address / sample location.
          $mapsSample = 'https://www.google.com/maps?q=Jl.+Jenderal+Sudirman,+Jakarta';
          $mapsUrl = ($settings['maps_url'] ?? '') !== '' ? $settings['maps_url'] : $mapsSample;

          $mapsQueryFallback = ($settings['contact_address'] ?? '') !== ''
              ? $settings['contact_address']
              : 'Jl. Jenderal Sudirman, Jakarta';
          $mapsFallbackEmbed = 'https://www.google.com/maps?q=' . urlencode($mapsQueryFallback) . '&output=embed';

          if (str_contains($mapsUrl, 'output=embed')) {
              $mapsEmbed = $mapsUrl;
          } elseif (str_contains($mapsUrl, 'google.com/maps')) {
              $mapsEmbed = $mapsUrl . (str_contains($mapsUrl, '?') ? '&' : '?') . 'output=embed';
          } else {
              $mapsEmbed = $mapsFallbackEmbed;
          }
        @endphp
        <div class="sm:col-span-2 lg:col-span-5 reveal" style="transition-delay:.3s">
          <h3 class="font-display font-bold uppercase tracking-wider text-sm flex items-center gap-2">
            <i class="fa-solid fa-map-location-dot text-brand-light" aria-hidden="true"></i> {{ $t('footer_map_heading', 'Lokasi Kami') }}
          </h3>
          <div class="rounded-2xl overflow-hidden border border-white/10 shadow-lg">
            <iframe
              title="Lokasi Showroom Daihatsu Sahabat"
              src="{{ $mapsEmbed }}"
              width="100%" height="200" style="border:0; display:block;"
              loading="lazy" referrerpolicy="no-referrer-when-downgrade"
              allowfullscreen></iframe>
          </div>
          <a href="{{ $mapsUrl }}" target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 text-brand-light text-sm font-semibold mt-3 hover:text-white transition-colors">
            <i class="fa-solid fa-diamond-turn-right" aria-hidden="true"></i> {{ $t('footer_map_cta', 'Buka di Google Maps') }}
          </a>
        </div>
      </div>

      <div class="mt-14 pt-8 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-white/50">
        <p>&copy; <span id="year"></span> {{ $sName }}. {{ $t('footer_copyright', 'All rights reserved.') }}</p>
        <p><span aria-label="Dibuat dengan cinta untuk keluarga Indonesia">{{ $t('footer_credit', 'Dibuat dengan ❤ untuk keluarga Indonesia.') }}</span></p>
      </div>
    </div>
  </footer>

  <!-- =====================================================================
       BANDINGKAN MOBIL — tray pilihan + panel perbandingan
       Diisi & dikontrol oleh js/compare.js
       ===================================================================== -->
  <!-- Tray mengambang berisi mobil terpilih -->
  <div id="compareTray" class="compare-tray" aria-live="polite" hidden>
    <div class="compare-tray-inner">
      <div class="flex items-center gap-2 text-ink font-display font-bold">
        <i class="fa-solid fa-code-compare text-brand" aria-hidden="true"></i>
        <span>Bandingkan</span>
        <span id="compareCount" class="text-ink-500 text-sm font-medium">(0/3)</span>
      </div>
      <div id="compareSlots" class="compare-slots"></div>
      <div class="flex items-center gap-2">
        <button type="button" id="compareClear" class="text-ink-500 text-sm font-semibold hover:text-brand transition-colors px-3 py-2">Bersihkan</button>
        <button type="button" id="compareOpen" class="btn-fun text-white font-display font-bold px-5 py-2.5 rounded-full text-sm disabled:opacity-50 disabled:cursor-not-allowed">
          Bandingkan Sekarang
        </button>
      </div>
    </div>
  </div>

  <!-- Panel perbandingan (modal) -->
  <div id="compareOverlay" class="compare-overlay" hidden>
    <div id="compareModal" class="compare-modal" role="dialog" aria-modal="true" aria-labelledby="compareTitle">
      <div class="flex items-center justify-between p-5 border-b border-ink/10">
        <h2 id="compareTitle" class="font-display font-extrabold text-xl text-ink flex items-center gap-2">
          <i class="fa-solid fa-code-compare text-brand" aria-hidden="true"></i> Perbandingan Mobil
        </h2>
        <button type="button" id="compareModalClose" class="w-10 h-10 rounded-full bg-cream hover:bg-brand hover:text-white grid place-items-center transition-colors" aria-label="Tutup">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
      </div>
      <div id="compareBody" class="compare-body"><!-- tabel diisi oleh JS --></div>
    </div>
  </div>

  <!-- =====================================================================
       PANEL DETAIL MOBIL (drawer meluncur dari kanan)
       Diisi & dikontrol oleh js/car-detail.js
       ===================================================================== -->
  <div id="detailOverlay" class="detail-overlay" hidden></div>
  <aside id="detailPanel" class="detail-panel" role="dialog" aria-modal="true" aria-labelledby="detailTitle" aria-hidden="true">
    <button type="button" id="detailClose" class="detail-close" aria-label="Tutup detail">
      <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
    <div id="detailBody" class="detail-body"><!-- konten diisi oleh JS --></div>
  </aside>

  <!-- =====================================================================
       GAME: SPIN THE WHEEL (roda keberuntungan berhadiah)
       Dibuka lewat tombol #spinTrigger atau easter egg keyboard (ketik "hoki").
       Dikontrol oleh js/spin-wheel.js
       ===================================================================== -->
  @if ($wheelEnabled)
  <button type="button" id="spinTrigger" class="spin-trigger" aria-label="Main roda keberuntungan">
    <i class="fa-solid fa-gift" aria-hidden="true"></i>
    <span class="spin-trigger-label">{{ $t('wheel_trigger_label', 'Menangkan Hadiah!') }}</span>
  </button>

  <div id="spinOverlay" class="spin-overlay" hidden>
    <div id="spinModal" class="spin-modal" role="dialog" aria-modal="true" aria-labelledby="spinTitle">
      <button type="button" id="spinClose" class="spin-close" aria-label="Tutup">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>

      <div class="text-center">
        <p class="text-brand font-display font-bold text-xs tracking-[.2em] uppercase">{{ $t('wheel_eyebrow', 'Roda Keberuntungan') }}</p>
        <h2 id="spinTitle" class="font-display font-extrabold text-2xl sm:text-3xl text-ink mt-1">{{ $t('wheel_title', 'Putar & Menangkan Hadiah!') }}</h2>
        <p class="text-ink-500 text-sm mt-2">{{ $t('wheel_subtitle', 'Coba keberuntunganmu — setiap putaran pasti dapat hadiah spesial.') }}</p>
      </div>

      <!-- Roda -->
      <div class="spin-wheel-wrap">
        <div class="spin-pointer" aria-hidden="true"><i class="fa-solid fa-caret-down"></i></div>
        <canvas id="spinCanvas" width="320" height="320" role="img" aria-label="Roda hadiah"></canvas>
        <button type="button" id="spinHub" class="spin-hub" aria-label="Putar roda">SPIN</button>
      </div>

      <!-- Hasil -->
      <div id="spinResult" class="spin-result" hidden>
        <p class="text-ink-500 text-sm">{{ $t('wheel_result_lead', 'Selamat! Kamu mendapatkan') }}</p>
        <p id="spinPrize" class="font-display font-black text-2xl text-brand mt-1">-</p>
        <a id="spinClaim" href="#" target="_blank" rel="noopener"
           class="btn-fun text-white font-display font-bold px-6 py-3 rounded-full inline-flex items-center gap-2 mt-4">
          <i class="fa-brands fa-whatsapp text-lg" aria-hidden="true"></i> {{ $t('wheel_claim_btn', 'Klaim Hadiah Sekarang') }}
        </a>
        <p class="text-ink-500 text-[11px] mt-3">{{ $t('wheel_claim_note', '*Tunjukkan hadiah ini saat menghubungi kami. Berlaku selama periode promo.') }}</p>
      </div>
    </div>
  </div>
  @endif

  <!-- Widget gambar melayang di pojok kanan (diisi & diputar oleh js/corner-widget.js) -->
  @if ($cornerEnabled)
  <div id="cornerWidget" aria-label="Info Daihatsu" aria-live="polite">
    <div id="cornerDots" aria-hidden="true"></div>
  </div>
  @endif

  <a href="#home" id="toTop" aria-label="Kembali ke atas"
     class="fixed bottom-6 left-6 z-40 w-12 h-12 rounded-full btn-fun text-white flex items-center justify-center opacity-0 pointer-events-none translate-y-4 transition-all duration-500">
    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
  </a>

  <div id="toast" role="status" aria-live="polite"
       class="fixed bottom-6 left-1/2 z-50 bg-ink text-white rounded-2xl px-5 py-4 text-sm shadow-2xl opacity-0 pointer-events-none"
       style="transform: translate(-50%, 150%);"></div>


  <!-- =====================================================================
       JAVASCRIPT (Vanilla, modular)
       Urutan pemuatan penting:
       1) data & utils  -> menyiapkan data dan helper (namespace App)
       2) ui            -> navbar, reveal, counter, toast (mengekspos App.showToast, App.observeReveals)
       3) fitur         -> hero slider, katalog+filter, testimoni, kalkulator
       4) main          -> newsletter + render awal (dimuat terakhir)
       ===================================================================== -->
  @include('partials.app-data')
  <script src="{{ asset('js/utils.js') }}"></script>
  <script src="{{ asset('js/ui.js') }}"></script>
  <script src="{{ asset('js/hero-slider.js') }}"></script>
  <script src="{{ asset('js/catalog.js') }}"></script>
  <script src="{{ asset('js/compare.js') }}"></script>
  <script src="{{ asset('js/car-detail.js') }}"></script>
  <script src="{{ asset('js/quiz.js') }}"></script>
  <script src="{{ asset('js/testimonials.js') }}"></script>
  <script src="{{ asset('js/calculator.js') }}"></script>
  <script src="{{ asset('js/corner-widget.js') }}"></script>
  <script src="{{ asset('js/spin-wheel.js') }}"></script>
  <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
