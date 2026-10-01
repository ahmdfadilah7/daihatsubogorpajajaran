<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ImageAndColorRules;
use Illuminate\Foundation\Http\FormRequest;

class HeroSlideStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:200'],
            'tag' => ['required', 'string', 'max:40'],
            'price' => ['required', 'string', 'max:40'],
            'img' => array_merge(['required_without:image'], $this->imageStringRule()),
            'image' => array_merge(['required_without:img'], $this->imageUploadRule()),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Kolom ini wajib diisi.',
            'img.required_without' => 'Isi URL/path gambar atau unggah berkas gambar.',
            'image.required_without' => 'Unggah berkas gambar atau isi URL/path gambar.',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 4 MB.',
        ];
    }
}
