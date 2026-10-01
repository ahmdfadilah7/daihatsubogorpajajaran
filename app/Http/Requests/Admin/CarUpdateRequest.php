<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ImageAndColorRules;
use Illuminate\Foundation\Http\FormRequest;

class CarUpdateRequest extends FormRequest
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
            'model' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:120'],
            'category' => ['required', 'in:LCGC,MPV,SUV,Niaga'],
            'year' => ['required', 'integer', 'between:1990,2100'],
            'price' => ['required', 'integer', 'min:0'],
            'transmission' => ['required', 'in:CVT,Manual,Otomatis'],
            'fuel' => ['required', 'string', 'max:30'],
            'seats' => ['required', 'integer', 'between:1,20'],
            'badge' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:1000'],
            'accent1' => $this->hexRule(),
            'accent2' => $this->hexRule(),
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
            'category.in' => 'Kategori harus salah satu dari LCGC, MPV, SUV, atau Niaga.',
            'year.between' => 'Tahun harus antara 1990 dan 2100.',
            'price.min' => 'Harga tidak boleh negatif.',
            'price.integer' => 'Harga harus berupa angka.',
            'transmission.in' => 'Transmisi harus CVT, Manual, atau Otomatis.',
            'seats.between' => 'Jumlah kursi harus antara 1 dan 20.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'accent1.regex' => 'Warna aksen 1 harus berupa kode hex (contoh #0a5fd1).',
            'accent2.regex' => 'Warna aksen 2 harus berupa kode hex (contoh #0a5fd1).',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 4 MB.',
        ];
    }
}
