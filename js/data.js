/* =========================================================================
   DATA — Semua konten dinamis situs terpusat di sini.
   Ubah/tambah item di array-array ini; komponen menyesuaikan otomatis.
   Diekspos lewat namespace global window.App agar bisa dipakai modul lain
   tanpa perlu module bundler / server (bisa dibuka langsung via file://).
   ========================================================================= */
window.App = window.App || {};

/* ---------- Data mobil Daihatsu ----------
   category : LCGC | MPV | SUV | Niaga  -> dipakai chip filter cepat
   accent   : [warna1, warna2]          -> warna garis atas kartu           */
App.CARS = [
  { id: 1,  model: 'Ayla',     type: '1.2 R Deluxe',  category: 'LCGC', year: 2024, price: 165000000, transmission: 'CVT',      fuel: 'Bensin', seats: 5, badge: 'Irit',     accent: ['#2e86ff','#0a5fd1'],
    img: 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=900&q=80' },
  { id: 2,  model: 'Sigra',    type: '1.2 R',         category: 'LCGC', year: 2024, price: 175000000, transmission: 'Manual',   fuel: 'Bensin', seats: 7, badge: 'Keluarga', accent: ['#ffc529','#f59e0b'],
    img: 'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=900&q=80' },
  { id: 3,  model: 'Xenia',    type: '1.3 X CVT',     category: 'MPV',  year: 2024, price: 245000000, transmission: 'CVT',      fuel: 'Bensin', seats: 7, badge: 'Terlaris', accent: ['#0a5fd1','#123a8f'],
    img: 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=900&q=80' },
  { id: 4,  model: 'Terios',   type: '1.5 R AT',      category: 'SUV',  year: 2024, price: 305000000, transmission: 'Otomatis', fuel: 'Bensin', seats: 7, badge: 'Tangguh',  accent: ['#123a8f','#2e86ff'],
    img: 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=900&q=80' },
  { id: 5,  model: 'Rocky',    type: '1.0 Turbo ASA', category: 'SUV',  year: 2024, price: 285000000, transmission: 'CVT',      fuel: 'Bensin', seats: 5, badge: 'Turbo',    accent: ['#2e86ff','#123a8f'],
    img: 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=900&q=80' },
  { id: 6,  model: 'Gran Max', type: 'Blind Van',     category: 'Niaga',year: 2023, price: 165000000, transmission: 'Manual',   fuel: 'Bensin', seats: 2, badge: '',         accent: ['#5b6a8c','#2e86ff'],
    img: 'https://images.unsplash.com/photo-1600661653561-629509216228?auto=format&fit=crop&w=900&q=80' },
  { id: 7,  model: 'Sirion',   type: '1.3 AT',        category: 'MPV',  year: 2023, price: 235000000, transmission: 'Otomatis', fuel: 'Bensin', seats: 5, badge: 'Gaya',     accent: ['#4aa3ff','#ffc529'],
    img: 'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=900&q=80' },
  { id: 8,  model: 'Luxio',    type: '1.5 X',         category: 'MPV',  year: 2023, price: 215000000, transmission: 'Manual',   fuel: 'Bensin', seats: 8, badge: 'Lega',     accent: ['#0a5fd1','#ffc529'],
    img: 'https://images.unsplash.com/photo-1519245659620-e859806a8d3b?auto=format&fit=crop&w=900&q=80' },
  { id: 9,  model: 'Terios',   type: '1.5 X MT',      category: 'SUV',  year: 2022, price: 268000000, transmission: 'Manual',   fuel: 'Bensin', seats: 7, badge: '',         accent: ['#123a8f','#4aa3ff'],
    img: 'https://images.unsplash.com/photo-1533106418989-88406c7cc8ca?auto=format&fit=crop&w=900&q=80' },
];

/* Warna & label kategori untuk badge kecil pada kartu mobil */
App.CAT_STYLE = {
  LCGC:  { bg: '#2e86ff', label: 'Hemat' },
  MPV:   { bg: '#0a5fd1', label: 'Keluarga' },
  SUV:   { bg: '#123a8f', label: 'SUV' },
  Niaga: { bg: '#5b6a8c', label: 'Niaga' },
};

/* ---------- Kuis "Mobil apa yang cocok untukmu?" ----------
   Tiap opsi memberi poin ke satu/lebih model (harus cocok dgn 'model' di CARS).
   Hasil akhir = model dengan skor tertinggi.                                */
App.QUIZ = [
  {
    q: 'Untuk apa mobil ini terutama akan digunakan?',
    icon: 'fa-bullseye',
    options: [
      { t: 'Harian di kota & ngantor', icon: 'fa-city',        score: { Ayla: 3, Sirion: 2, Rocky: 1 } },
      { t: 'Antar-jemput keluarga',    icon: 'fa-people-roof',  score: { Xenia: 3, Sigra: 2, Luxio: 2 } },
      { t: 'Petualangan & jalan jauh', icon: 'fa-mountain-sun', score: { Terios: 3, Rocky: 2 } },
      { t: 'Usaha / angkut barang',    icon: 'fa-truck-fast',   score: { 'Gran Max': 3, Luxio: 1 } },
    ],
  },
  {
    q: 'Berapa orang yang biasa ikut?',
    icon: 'fa-users',
    options: [
      { t: '1–2 orang',       icon: 'fa-user',    score: { Ayla: 2, Sirion: 2, 'Gran Max': 1 } },
      { t: '3–5 orang',       icon: 'fa-user-group', score: { Rocky: 2, Sirion: 1, Ayla: 1 } },
      { t: '6–8 orang',       icon: 'fa-people-group', score: { Xenia: 3, Sigra: 2, Luxio: 3, Terios: 1 } },
    ],
  },
  {
    q: 'Apa yang paling kamu utamakan?',
    icon: 'fa-heart',
    options: [
      { t: 'Irit bahan bakar', icon: 'fa-gas-pump',   score: { Ayla: 3, Sigra: 2, Sirion: 1 } },
      { t: 'Gaya & modern',    icon: 'fa-wand-magic-sparkles', score: { Rocky: 3, Sirion: 2, Terios: 1 } },
      { t: 'Tangguh & lega',   icon: 'fa-shield-halved', score: { Terios: 3, Luxio: 2, Xenia: 1 } },
      { t: 'Harga terjangkau', icon: 'fa-tag',        score: { Sigra: 3, Ayla: 2, 'Gran Max': 2 } },
    ],
  },
  {
    q: 'Berapa perkiraan bujet kamu?',
    icon: 'fa-wallet',
    options: [
      { t: 'Di bawah 180 Juta', icon: 'fa-coins',    score: { Ayla: 3, Sigra: 3, 'Gran Max': 2 } },
      { t: '180 – 260 Juta',    icon: 'fa-money-bill', score: { Luxio: 2, Sirion: 2, Xenia: 1 } },
      { t: 'Di atas 260 Juta',  icon: 'fa-gem',       score: { Terios: 3, Rocky: 3, Xenia: 2 } },
    ],
  },
];

/* ---------- Hadiah roda keberuntungan (Spin the Wheel) ----------
   'weight' = bobot peluang (makin besar makin sering keluar).
   'color'  = warna segmen roda.                                            */
App.WHEEL_PRIZES = [
  { label: 'Diskon 5 Juta',    short: 'Diskon\n5 Juta',   color: '#0a5fd1', weight: 3, msg: 'Voucher Diskon Rp 5 Juta' },
  { label: 'Gratis Servis 1th',short: 'Gratis\nServis',   color: '#ffc529', weight: 3, msg: 'Gratis Servis 1 Tahun' },
  { label: 'Voucher BBM',      short: 'Voucher\nBBM',     color: '#2e86ff', weight: 4, msg: 'Voucher BBM Rp 500rb' },
  { label: 'Kaca Film Gratis', short: 'Kaca Film\nGratis',color: '#123a8f', weight: 3, msg: 'Kaca Film Gratis' },
  { label: 'Diskon 10 Juta',   short: 'Diskon\n10 Juta',  color: '#25d366', weight: 1, msg: 'JACKPOT! Diskon Rp 10 Juta' },
  { label: 'Cashback 2 Juta',  short: 'Cashback\n2 Juta', color: '#4aa3ff', weight: 3, msg: 'Cashback Rp 2 Juta' },
];

/* ---------- Gambar widget melayang di pojok kanan (berganti otomatis) ----------
   File berada di folder /img. Tambah/kurangi item sesuai kebutuhan.        */
App.CORNER_IMAGES = [
  { src: 'img/halo.jpeg',    alt: 'Halo dari Daihatsu Sahabat' },
  { src: 'img/bingung.jpeg', alt: 'Bingung pilih mobil?' },
  { src: 'img/hubungi.jpeg', alt: 'Hubungi kami sekarang' },
];

/* ---------- Slide hero (carousel) ---------- */
App.HERO_SLIDES = [
  { name: 'Daihatsu Terios', desc: 'SUV tangguh 7 penumpang',   tag: 'Terlaris', price: 'Rp 219 Jt',
    img: 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1000&q=80' },
  { name: 'Daihatsu Rocky',  desc: 'Compact SUV bermesin turbo', tag: 'Turbo',   price: 'Rp 285 Jt',
    img: 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=1000&q=80' },
  { name: 'Daihatsu Xenia',  desc: 'MPV keluarga paling nyaman', tag: 'Favorit', price: 'Rp 245 Jt',
    img: 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1000&q=80' },
  { name: 'Daihatsu Ayla',   desc: 'Mobil kota super irit',      tag: 'Hemat',   price: 'Rp 165 Jt',
    img: 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=1000&q=80' },
];

/* ---------- Testimoni pelanggan ----------
   img   : foto momen serah terima / pelanggan bersama mobilnya (di ATAS kartu)
   color : warna aksen kartu (garis atas & badge)                             */
App.TESTIMONIALS = [
  { name: 'Rina Kartika',  city: 'Bekasi',    car: 'Daihatsu Xenia',   rating: 5, color: '#0a5fd1',
    img: 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=700&q=80',
    text: 'Xenia bikin mudik sekeluarga jadi nyaman banget. Bagasi luas, AC dingin sampai baris ketiga. Prosesnya juga cepat dan ramah.' },
  { name: 'Budi Santoso',  city: 'Depok',     car: 'Daihatsu Terios',  rating: 5, color: '#123a8f',
    img: 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=700&q=80',
    text: 'Terios tangguh diajak ke mana saja, dari kota sampai jalan kampung. Servisnya gampang dan sparepart terjangkau. Puas!' },
  { name: 'Siti Aminah',   city: 'Tangerang', car: 'Daihatsu Ayla',    rating: 4, color: '#2e86ff',
    img: 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=700&q=80',
    text: 'Ayla hemat BBM banget buat harian ngantor. Lincah di jalan sempit dan gampang parkir. Cicilannya juga ringan.' },
  { name: 'Andi Pratama',  city: 'Jakarta',   car: 'Daihatsu Rocky',   rating: 5, color: '#4aa3ff',
    img: 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=700&q=80',
    text: 'Rocky turbo-nya responsif, desainnya keren dan modern. Fitur keselamatannya lengkap. Anak muda wajib coba!' },
  { name: 'Dewi Lestari',  city: 'Bogor',     car: 'Daihatsu Sigra',   rating: 5, color: '#ffc529',
    img: 'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&w=700&q=80',
    text: 'Mobil pertama keluarga kami. Sigra muat 7 orang, harganya bersahabat. Sales-nya sabar bantu kami sampai deal.' },
  { name: 'Hendra Wijaya', city: 'Karawang',  car: 'Daihatsu Gran Max',rating: 4, color: '#0a5fd1',
    img: 'https://images.unsplash.com/photo-1600661653561-629509216228?auto=format&fit=crop&w=700&q=80',
    text: 'Buat usaha, Gran Max andalan saya. Muatan banyak, bandel, irit. Balik modalnya cepat. Recommended untuk pebisnis.' },
];
