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

            'logo_file.image' => 'Berkas logo harus berupa gambar.',
            'logo_file.mimes' => 'Logo harus berformat jpg, jpeg, png, atau webp.',
            'logo_file.max' => 'Ukuran logo maksimal 4 MB.',
            'favicon_file.image' => 'Berkas favicon harus berupa gambar.',
            'favicon_file.mimes' => 'Favicon harus berformat jpg, jpeg, png, atau webp.',
            'favicon_file.max' => 'Ukuran favicon maksimal 4 MB.',
            'og_image_file.image' => 'Berkas gambar OG harus berupa gambar.',
            'og_image_file.mimes' => 'Gambar OG harus berformat jpg, jpeg, png, atau webp.',
            'og_image_file.max' => 'Ukuran gambar OG maksimal 4 MB.',
        ];
    }
}
