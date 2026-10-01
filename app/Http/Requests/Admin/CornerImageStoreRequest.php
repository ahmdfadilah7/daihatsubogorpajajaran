<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ImageAndColorRules;
use Illuminate\Foundation\Http\FormRequest;

class CornerImageStoreRequest extends FormRequest
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
            'alt' => ['required', 'string', 'max:160'],
            'src' => array_merge(['required_without:image'], $this->imageStringRule()),
            'image' => array_merge(['required_without:src'], $this->imageUploadRule()),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Kolom ini wajib diisi.',
            'alt.required' => 'Teks alternatif wajib diisi.',
            'src.required_without' => 'Isi URL/path gambar atau unggah berkas gambar.',
            'image.required_without' => 'Unggah berkas gambar atau isi URL/path gambar.',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 4 MB.',
        ];
    }
}
