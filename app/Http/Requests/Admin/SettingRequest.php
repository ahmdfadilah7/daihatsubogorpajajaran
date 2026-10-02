<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ImageAndColorRules;
use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    use ImageAndColorRules;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Identitas Situs
            'site_name' => ['nullable', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'logo' => $this->imageStringRule(),
            'logo_file' => $this->imageUploadRule(),
            'favicon' => $this->imageStringRule(),
            'favicon_file' => $this->imageUploadRule(),

            // SEO / Meta
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta_author' => ['nullable', 'string', 'max:120'],

            // Social / Open Graph
            'og_image' => $this->imageStringRule(),
            'og_image_file' => $this->imageUploadRule(),
            'og_title' => ['nullable', 'string', 'max:160'],
            'og_description' => ['nullable', 'string', 'max:300'],

            // Kontak & Lainnya
            'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:120'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_youtube' => ['nullable', 'string', 'max:255'],

            // Fitur Situs (toggle on/off)
            'feature_quiz' => ['nullable', 'in:0,1'],
            'feature_corner' => ['nullable', 'in:0,1'],
            'feature_wheel' => ['nullable', 'in:0,1'],

            // === Teks Halaman Publik (67 keys) ===
            // A — Promo
            'promo_badge' => ['nullable', 'string', 'max:255'],
            'promo_text' => ['nullable', 'string', 'max:255'],
            'promo_text_extra' => ['nullable', 'string', 'max:255'],
            'promo_cta' => ['nullable', 'string', 'max:255'],
            // B — Navbar
            'nav_cta' => ['nullable', 'string', 'max:255'],
            // C — Hero
            'hero_badge' => ['nullable', 'string', 'max:255'],
            'hero_countdown_label' => ['nullable', 'string', 'max:255'],
            'hero_countdown_label_short' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_title_hl' => ['nullable', 'string', 'max:255'],
            'hero_title_suffix' => ['nullable', 'string', 'max:255'],
            'hero_desc' => ['nullable', 'string', 'max:1000'],
            'hero_benefit_1' => ['nullable', 'string', 'max:255'],
            'hero_benefit_2' => ['nullable', 'string', 'max:255'],
            'hero_benefit_3' => ['nullable', 'string', 'max:255'],
            'hero_btn_primary' => ['nullable', 'string', 'max:255'],
            'hero_btn_whatsapp' => ['nullable', 'string', 'max:255'],
            'hero_wa_message' => ['nullable', 'string', 'max:500'],
            'hero_price_label' => ['nullable', 'string', 'max:255'],
            // D — Inventory
            'sec_inventory_eyebrow' => ['nullable', 'string', 'max:255'],
            'sec_inventory_title' => ['nullable', 'string', 'max:255'],
            'sec_inventory_title_hl' => ['nullable', 'string', 'max:255'],
            'sec_inventory_subtitle_pre' => ['nullable', 'string', 'max:255'],
            'sec_inventory_subtitle_post' => ['nullable', 'string', 'max:1000'],
            'inventory_swipe_hint' => ['nullable', 'string', 'max:255'],
            'inventory_empty_title' => ['nullable', 'string', 'max:255'],
            'inventory_empty_desc' => ['nullable', 'string', 'max:1000'],
            'inventory_empty_btn' => ['nullable', 'string', 'max:255'],
            // E — Filter
            'filter_heading' => ['nullable', 'string', 'max:255'],
            // F — Credit calculator
            'sec_credit_eyebrow' => ['nullable', 'string', 'max:255'],
            'sec_credit_title' => ['nullable', 'string', 'max:255'],
            'sec_credit_title_hl' => ['nullable', 'string', 'max:255'],
            'sec_credit_subtitle' => ['nullable', 'string', 'max:1000'],
            'calc_label_price' => ['nullable', 'string', 'max:255'],
            'calc_label_dp' => ['nullable', 'string', 'max:255'],
            'calc_label_tenor' => ['nullable', 'string', 'max:255'],
            'calc_label_result' => ['nullable', 'string', 'max:255'],
            'calc_label_total_dp' => ['nullable', 'string', 'max:255'],
            'calc_label_total_loan' => ['nullable', 'string', 'max:255'],
            'calc_btn' => ['nullable', 'string', 'max:255'],
            'calc_footnote' => ['nullable', 'string', 'max:1000'],
            // G — Quiz
            'sec_quiz_eyebrow' => ['nullable', 'string', 'max:255'],
            'sec_quiz_title' => ['nullable', 'string', 'max:255'],
            'sec_quiz_title_hl' => ['nullable', 'string', 'max:255'],
            'sec_quiz_subtitle' => ['nullable', 'string', 'max:1000'],
            // H — Testimoni
            'sec_testi_eyebrow' => ['nullable', 'string', 'max:255'],
            'sec_testi_title' => ['nullable', 'string', 'max:255'],
            'sec_testi_title_hl' => ['nullable', 'string', 'max:255'],
            'sec_testi_subtitle' => ['nullable', 'string', 'max:1000'],
            // I — Spin wheel
            'wheel_eyebrow' => ['nullable', 'string', 'max:255'],
            'wheel_title' => ['nullable', 'string', 'max:255'],
            'wheel_subtitle' => ['nullable', 'string', 'max:1000'],
            'wheel_trigger_label' => ['nullable', 'string', 'max:255'],
            'wheel_result_lead' => ['nullable', 'string', 'max:255'],
            'wheel_claim_btn' => ['nullable', 'string', 'max:255'],
            'wheel_claim_note' => ['nullable', 'string', 'max:1000'],
            // J — Footer
            'footer_cta_heading' => ['nullable', 'string', 'max:255'],
            'footer_cta_subtitle' => ['nullable', 'string', 'max:1000'],
            'footer_cta_wa_label' => ['nullable', 'string', 'max:255'],
            'footer_cta_phone_label' => ['nullable', 'string', 'max:255'],
            'footer_about' => ['nullable', 'string', 'max:1000'],
            'footer_contact_heading' => ['nullable', 'string', 'max:255'],
            'footer_hours' => ['nullable', 'string', 'max:255'],
            'footer_map_heading' => ['nullable', 'string', 'max:255'],
            'footer_map_cta' => ['nullable', 'string', 'max:255'],
            'footer_copyright' => ['nullable', 'string', 'max:255'],
            'footer_credit' => ['nullable', 'string', 'max:1000'],

            // === Simulasi Kredit (parameter rumus) ===
            'credit_interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'credit_min_dp' => ['nullable', 'numeric', 'min:0', 'max:100', 'lte:credit_max_dp'],
            'credit_max_dp' => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:credit_min_dp'],
            'credit_default_dp' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'credit_dp_step' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'credit_min_tenor' => ['nullable', 'integer', 'min:1', 'max:30', 'lte:credit_max_tenor'],
            'credit_max_tenor' => ['nullable', 'integer', 'min:1', 'max:30', 'gte:credit_min_tenor'],
            'credit_default_tenor' => ['nullable', 'integer', 'min:1', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'site_name.max' => 'Nama situs maksimal 120 karakter.',
            'meta_title.max' => 'Judul meta maksimal 160 karakter.',
            'meta_description.max' => 'Deskripsi meta maksimal 300 karakter.',
            'meta_keywords.max' => 'Kata kunci meta maksimal 255 karakter.',
            'contact_email.email' => 'Alamat email tidak valid.',

            'feature_quiz.in' => 'Nilai fitur Kuis tidak valid.',
            'feature_corner.in' => 'Nilai fitur Gambar Pojok tidak valid.',
            'feature_wheel.in' => 'Nilai fitur Hadiah Roda tidak valid.',

            'logo_file.image' => 'Berkas logo harus berupa gambar.',
            'logo_file.mimes' => 'Logo harus berformat jpg, jpeg, png, atau webp.',
            'logo_file.max' => 'Ukuran logo maksimal 4 MB.',
            'favicon_file.image' => 'Berkas favicon harus berupa gambar.',
            'favicon_file.mimes' => 'Favicon harus berformat jpg, jpeg, png, atau webp.',
            'favicon_file.max' => 'Ukuran favicon maksimal 4 MB.',
            'og_image_file.image' => 'Berkas gambar OG harus berupa gambar.',
            'og_image_file.mimes' => 'Gambar OG harus berformat jpg, jpeg, png, atau webp.',
            'og_image_file.max' => 'Ukuran gambar OG maksimal 4 MB.',

            // Teks Halaman Publik — pesan untuk field panjang.
            'hero_desc.max' => 'Deskripsi hero maksimal 1000 karakter.',
            'hero_wa_message.max' => 'Pesan WhatsApp hero maksimal 500 karakter.',
            'inventory_empty_desc.max' => 'Deskripsi hasil kosong maksimal 1000 karakter.',
            'sec_inventory_subtitle_post.max' => 'Subjudul mobil maksimal 1000 karakter.',
            'sec_credit_subtitle.max' => 'Subjudul simulasi maksimal 1000 karakter.',
            'calc_footnote.max' => 'Catatan kalkulator maksimal 1000 karakter.',
            'sec_quiz_subtitle.max' => 'Subjudul kuis maksimal 1000 karakter.',
            'sec_testi_subtitle.max' => 'Subjudul testimoni maksimal 1000 karakter.',
            'wheel_subtitle.max' => 'Subjudul roda maksimal 1000 karakter.',
            'wheel_claim_note.max' => 'Catatan klaim maksimal 1000 karakter.',
            'footer_cta_subtitle.max' => 'Subjudul CTA footer maksimal 1000 karakter.',
            'footer_about.max' => 'Teks tentang maksimal 1000 karakter.',
            'footer_credit.max' => 'Teks kredit footer maksimal 1000 karakter.',

            // Simulasi Kredit (parameter rumus)
            'credit_interest_rate.numeric' => 'Bunga harus berupa angka.',
            'credit_interest_rate.min' => 'Bunga tidak boleh kurang dari 0.',
            'credit_interest_rate.max' => 'Bunga tidak boleh lebih dari 100.',
            'credit_min_dp.lte' => 'DP minimum tidak boleh lebih besar dari DP maksimum.',
            'credit_max_dp.gte' => 'DP maksimum tidak boleh lebih kecil dari DP minimum.',
            'credit_min_dp.numeric' => 'DP minimum harus berupa angka.',
            'credit_max_dp.numeric' => 'DP maksimum harus berupa angka.',
            'credit_default_dp.numeric' => 'DP default harus berupa angka.',
            'credit_dp_step.numeric' => 'Kelipatan DP harus berupa angka.',
            'credit_dp_step.min' => 'Kelipatan DP minimal 1.',
            'credit_min_tenor.integer' => 'Tenor minimum harus bilangan bulat.',
            'credit_max_tenor.integer' => 'Tenor maksimum harus bilangan bulat.',
            'credit_default_tenor.integer' => 'Tenor default harus bilangan bulat.',
            'credit_min_tenor.lte' => 'Tenor minimum tidak boleh lebih besar dari tenor maksimum.',
            'credit_max_tenor.gte' => 'Tenor maksimum tidak boleh lebih kecil dari tenor minimum.',
        ];
    }
}
