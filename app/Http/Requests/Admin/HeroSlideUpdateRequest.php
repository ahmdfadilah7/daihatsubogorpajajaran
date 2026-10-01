<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ImageAndColorRules;
use Illuminate\Foundation\Http\FormRequest;

class HeroSlideUpdateRequest extends FormRequest
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
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 4 MB.',
        ];
    }
}
