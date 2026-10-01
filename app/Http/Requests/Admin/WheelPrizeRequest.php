<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ImageAndColorRules;
use Illuminate\Foundation\Http\FormRequest;

class WheelPrizeRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:80'],
            'short' => ['required', 'string', 'max:60'],
            'color' => $this->hexRule(),
            'weight' => ['required', 'integer', 'min:1'],
            'msg' => ['required', 'string', 'max:160'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Kolom ini wajib diisi.',
            'color.regex' => 'Warna harus berupa kode hex (contoh #0a5fd1).',
            'weight.min' => 'Bobot minimal 1.',
            'weight.integer' => 'Bobot harus berupa angka.',
        ];
    }
}
