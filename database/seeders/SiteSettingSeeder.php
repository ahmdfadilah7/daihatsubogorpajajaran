<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Seed the default site settings. Idempotent via updateOrCreate per key;
     * defaults match the CURRENT public site so nothing changes until edited.
     */
    public function run(): void
    {
        $defaults = [
            'site_name' => 'Daihatsu Sahabat',
            'tagline' => '',
            'logo' => '',
            'favicon' => '',
            'meta_title' => 'Daihatsu Sahabat | Dealer Resmi Daihatsu',
            'meta_description' => 'Daihatsu Sahabat - Dealer resmi Daihatsu. Temukan Ayla, Sigra, Terios, Rocky, Xenia & lainnya dengan promo, cicilan ringan, dan servis terpercaya.',
            'meta_keywords' => 'Daihatsu, dealer Daihatsu, Ayla, Sigra, Terios, Rocky, Xenia, promo mobil, kredit mobil',
            'meta_author' => 'Daihatsu Sahabat',
            'og_image' => '',
            'og_title' => '',
            'og_description' => '',
            'contact_whatsapp' => '',
            'contact_email' => '',
            'contact_address' => '',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_youtube' => '',
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Feature toggles for interactive public-site widgets. Default ENABLED
        // ('1') so the site looks identical to today. Use firstOrCreate so
        // re-seeding never clobbers a value the admin has already changed.
        $featureFlags = [
            'feature_quiz' => '1',
            'feature_corner' => '1',
            'feature_wheel' => '1',
        ];

        foreach ($featureFlags as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        // Public landing-page copy. firstOrCreate so re-seeding preserves admin edits.
        // Defaults = the CURRENT literals in home.blade.php => page unchanged until edited.
        $texts = [
            // A — Promo (4)
            'promo_badge' => 'Promo Spesial!',
            'promo_text' => 'DP mulai 15 Juta',
            'promo_text_extra' => '+ gratis servis 1 tahun.',
            'promo_cta' => 'Lihat mobil →',           // glyph, NOT &rarr;
            // B — Navbar (1)
            'nav_cta' => 'Hubungi Kami',
            // C — Hero (14)
            'hero_badge' => 'PROMO',
            'hero_countdown_label' => 'Promo berakhir dalam',
            'hero_countdown_label_short' => 'Berakhir',
            'hero_title' => 'Mobil Keluarga',
            'hero_title_hl' => 'Ceria',
            'hero_title_suffix' => 'untuk Semua!',
            'hero_desc' => 'Dari Ayla yang irit sampai Terios yang gagah — temukan Daihatsu impian keluargamu. Cicilan ringan, servis gampang, sahabat di setiap perjalanan.',
            'hero_benefit_1' => 'DP mulai 15 Juta',
            'hero_benefit_2' => 'Cicilan s/d 6 Tahun',
            'hero_benefit_3' => 'Garansi 3 Tahun',
            'hero_btn_primary' => 'Lihat Semua Mobil',
            'hero_btn_whatsapp' => 'Test Drive',
            'hero_wa_message' => 'Halo, saya mau test drive mobil Daihatsu',
            'hero_price_label' => 'Mulai',
            // D — Inventory (9)
            'sec_inventory_eyebrow' => 'Pilihan Mobil',
            'sec_inventory_title' => 'Koleksi',
            'sec_inventory_title_hl' => 'Daihatsu',
            'sec_inventory_subtitle_pre' => 'Menampilkan',
            'sec_inventory_subtitle_post' => 'mobil. Semua unit bergaransi resmi & siap antar ke rumahmu.',
            'inventory_swipe_hint' => 'Geser untuk melihat mobil lainnya',
            'inventory_empty_title' => 'Mobil tidak ditemukan',
            'inventory_empty_desc' => 'Coba ubah kriteria pencarian kamu.',
            'inventory_empty_btn' => 'Tampilkan Semua',
            // E — Filter (1)
            'filter_heading' => 'Filter Mobil',
            // F — Credit calculator (12)
            'sec_credit_eyebrow' => 'Simulasi Kredit',
            'sec_credit_title' => 'Hitung Cicilan',
            'sec_credit_title_hl' => 'Impianmu',
            'sec_credit_subtitle' => 'Atur harga, uang muka, dan tenor sesukamu untuk melihat perkiraan angsuran bulanan. Gampang, cepat, tanpa perlu daftar.',
            'calc_label_price' => 'Harga Mobil',
            'calc_label_dp' => 'Uang Muka (DP)',
            'calc_label_tenor' => 'Tenor',
            'calc_label_result' => 'Perkiraan Angsuran / Bulan',
            'calc_label_total_dp' => 'Total DP',
            'calc_label_total_loan' => 'Total Pinjaman',
            'calc_btn' => 'Ajukan Kredit',
            'calc_footnote' => '*Estimasi bunga flat 4%/tahun. Angka sebenarnya menyesuaikan leasing.',
            // G — Quiz (4)
            'sec_quiz_eyebrow' => 'Bingung Pilih?',
            'sec_quiz_title' => 'Cari Mobil',
            'sec_quiz_title_hl' => 'Idealmu',
            'sec_quiz_subtitle' => 'Jawab 4 pertanyaan singkat, biar kami rekomendasikan Daihatsu yang paling pas buatmu.',
            // H — Testimoni (4)
            'sec_testi_eyebrow' => 'Kata Mereka',
            'sec_testi_title' => 'Cerita',
            'sec_testi_title_hl' => 'Sahabat Daihatsu',
            'sec_testi_subtitle' => 'Ribuan keluarga sudah mempercayakan perjalanannya pada kami. Ini kata mereka.',
            // I — Spin wheel (7)
            'wheel_eyebrow' => 'Roda Keberuntungan',
            'wheel_title' => 'Putar & Menangkan Hadiah!',
            'wheel_subtitle' => 'Coba keberuntunganmu — setiap putaran pasti dapat hadiah spesial.',
            'wheel_trigger_label' => 'Menangkan Hadiah!',
            'wheel_result_lead' => 'Selamat! Kamu mendapatkan',
            'wheel_claim_btn' => 'Klaim Hadiah Sekarang',
            'wheel_claim_note' => '*Tunjukkan hadiah ini saat menghubungi kami. Berlaku selama periode promo.',
            // J — Footer (11)
            'footer_cta_heading' => 'Siap Bawa Pulang Daihatsu Impianmu?',
            'footer_cta_subtitle' => 'Hubungi kami sekarang, gratis konsultasi & jadwal test drive.',
            'footer_cta_wa_label' => 'Chat WhatsApp',
            'footer_cta_phone_label' => 'Telepon',
            'footer_about' => 'Dealer resmi Daihatsu yang menemani keluarga Indonesia sejak 2011. Sahabat di setiap perjalanan.',
            'footer_contact_heading' => 'Hubungi Kami',
            'footer_hours' => 'Sen – Sab, 08.00 – 20.00 WIB',
            'footer_map_heading' => 'Lokasi Kami',
            'footer_map_cta' => 'Buka di Google Maps',
            'footer_copyright' => 'All rights reserved.',
            'footer_credit' => 'Dibuat dengan ❤ untuk keluarga Indonesia.',   // glyph ❤, not an icon
        ];

        foreach ($texts as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        SiteSetting::flushCache();
    }
}
