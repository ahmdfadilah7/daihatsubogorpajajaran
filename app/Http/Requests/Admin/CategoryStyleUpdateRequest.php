<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ImageAndColorRules;
use Illuminate\Foundation\Http\FormRequest;

class CategoryStyleUpdateRequest extends FormRequest
{
    use ImageAndColorRules;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * category is read-only after create (design B.11): the update path
     * validates and mutates only bg/label, never the category join key.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bg' => $this->hexRule(),
            'label' => ['required', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Kolom ini wajib diisi.',
            'bg.regex' => 'Warna latar harus berupa kode hex (contoh #0a5fd1).',
        ];
    }
}
