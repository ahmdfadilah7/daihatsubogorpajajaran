<?php

/*
|--------------------------------------------------------------------------
| Daftar Ikon Font Awesome (Free Solid)
|--------------------------------------------------------------------------
|
| Daftar ikon terkurasi yang dipakai bersama di seluruh dashboard admin.
| Setiap entri memetakan kelas Font Awesome (mis. 'fa-gas-pump') ke label
| berbahasa Indonesia yang mudah dibaca admin. Dipakai oleh komponen
| <x-admin.icon-select> maupun repeater JS pada form kuis (via @json).
|
| Semua ikon di sini WAJIB mencakup ikon default pada seeder (marquee &
| kuis) agar data lama tetap valid dan bisa dipilih kembali.
|
*/

return [

    'list' => [
        // — Ikon pada seeder (marquee) —
        'fa-gas-pump' => 'Pompa Bensin',
        'fa-shield-halved' => 'Perisai (Garansi)',
        'fa-wrench' => 'Kunci Pas (Servis)',
        'fa-hand-holding-dollar' => 'Tangan & Uang (Cicilan)',
        'fa-users' => 'Banyak Orang',
        'fa-award' => 'Penghargaan',

        // — Ikon pada seeder (kuis) —
        'fa-bullseye' => 'Target',
        'fa-city' => 'Perkotaan',
        'fa-people-roof' => 'Keluarga di Rumah',
        'fa-mountain-sun' => 'Gunung & Matahari',
        'fa-truck-fast' => 'Truk Cepat',
        'fa-user' => 'Satu Orang',
        'fa-user-group' => 'Grup Kecil',
        'fa-people-group' => 'Grup Besar',
        'fa-heart' => 'Hati / Favorit',
        'fa-wand-magic-sparkles' => 'Tongkat Ajaib (Gaya)',
        'fa-tag' => 'Label Harga',
        'fa-wallet' => 'Dompet',
        'fa-coins' => 'Koin',
        'fa-money-bill' => 'Lembar Uang',
        'fa-gem' => 'Permata',

        // — Mobil & dealer —
        'fa-car' => 'Mobil',
        'fa-car-side' => 'Mobil (Samping)',
        'fa-car-rear' => 'Mobil (Belakang)',
        'fa-truck' => 'Truk',
        'fa-van-shuttle' => 'Mobil Van',
        'fa-road' => 'Jalan',
        'fa-gauge-high' => 'Spidometer',
        'fa-oil-can' => 'Kaleng Oli',
        'fa-screwdriver-wrench' => 'Obeng & Kunci',
        'fa-couch' => 'Kursi / Kenyamanan',
        'fa-key' => 'Kunci',
        'fa-gears' => 'Gigi Mesin',
        'fa-battery-full' => 'Baterai Penuh',

        // — Promo, harga & finansial —
        'fa-gift' => 'Hadiah',
        'fa-percent' => 'Persen / Diskon',
        'fa-money-bill-wave' => 'Uang Melambai',
        'fa-piggy-bank' => 'Celengan',
        'fa-credit-card' => 'Kartu Kredit',
        'fa-receipt' => 'Struk',
        'fa-sack-dollar' => 'Kantong Uang',
        'fa-handshake' => 'Jabat Tangan',

        // — Kualitas & kepercayaan —
        'fa-star' => 'Bintang',
        'fa-check' => 'Centang',
        'fa-circle-check' => 'Centang Lingkaran',
        'fa-thumbs-up' => 'Jempol',
        'fa-medal' => 'Medali',
        'fa-trophy' => 'Piala',
        'fa-crown' => 'Mahkota',
        'fa-bolt' => 'Petir / Cepat',
        'fa-fire' => 'Api / Populer',

        // — Umum / UI —
        'fa-phone' => 'Telepon',
        'fa-location-dot' => 'Lokasi',
        'fa-calendar' => 'Kalender',
        'fa-clock' => 'Jam',
        'fa-envelope' => 'Amplop',
        'fa-circle-info' => 'Informasi',
        'fa-circle-question' => 'Tanda Tanya',
        'fa-house' => 'Rumah',
        'fa-leaf' => 'Daun / Ramah Lingkungan',
        'fa-globe' => 'Globe',
    ],

];
