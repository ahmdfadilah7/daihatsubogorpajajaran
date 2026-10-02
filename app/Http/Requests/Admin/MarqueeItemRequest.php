<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ImageAndColorRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarqueeItemRequest extends FormRequest
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
            'text' => ['required', 'string', 'max:100'],
            'icon' => ['required', 'string', 'max:50', Rule::in(array_keys(config('icons.list', [])))],
            'color' => $this->hexRule(),
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Kolom ini wajib diisi.',
            'icon.in' => 'Ikon yang dipilih tidak dikenali.',
            'color.regex' => 'Warna harus berupa kode hex (contoh #0a5fd1).',
            'sort_order.integer' => 'Urutan harus berupa angka.',
            'sort_order.min' => 'Urutan tidak boleh kurang dari 0.',
        ];
    }
}
