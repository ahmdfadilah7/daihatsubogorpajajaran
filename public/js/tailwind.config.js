/* =========================================================================
   Konfigurasi Tailwind (CDN)
   Ubah warna brand di sini untuk mengganti tema keseluruhan situs.
   Harus dimuat SETELAH script Tailwind CDN dan SEBELUM elemen dirender.
   ========================================================================= */
tailwind.config = {
  theme: {
    extend: {
      colors: {
        // Palet diselaraskan dengan gambar di /img: biru royal + kuning, navy sbg gelap.
        brand:   { DEFAULT: '#0a5fd1', light: '#2e86ff', dark: '#123a8f' }, // Biru Daihatsu
        sky2:    '#2e86ff',   // biru terang
        navy:    '#123a8f',   // biru navy tua (seragam/outline)
        grape:   '#5b8def',   // biru muda pendukung
        mango:   '#ffc529',   // kuning aksen (dari balon teks)
        mint:    '#25d366',   // hijau WhatsApp (dari ikon)
        pink2:   '#4aa3ff',   // biru langit pendukung
        cream:   '#f2f8ff',   // putih kebiruan lembut
        ink:     { DEFAULT: '#10224a', 700: '#1c3566', 500: '#5b6a8c' }, // teks gelap kebiruan
      },
      fontFamily: {
        display: ['Poppins', 'sans-serif'],
        body: ['Inter', 'sans-serif'],
      },
    },
  },
};
