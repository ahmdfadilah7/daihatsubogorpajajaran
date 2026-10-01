<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ImageAndColorRules;
use Illuminate\Foundation\Http\FormRequest;

class TestimonialUpdateRequest extends FormRequest
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
            'city' => ['required', 'string', 'max:80'],
            'car' => ['required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'color' => $this->hexRule(),
            'text' => ['required', 'string'],
            // UPDATE: both image inputs optional (absence = keep current).
            'img' => $this->imageStringRule(),
            'image' => $this->imageUploadRule(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Kolom ini wajib diisi.',
            'rating.between' => 'Rating harus antara 1 dan 5.',
            'rating.integer' => 'Rating harus berupa angka.',
            'color.regex' => 'Warna harus berupa kode hex (contoh #0a5fd1).',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 4 MB.',
        ];
    }
}
