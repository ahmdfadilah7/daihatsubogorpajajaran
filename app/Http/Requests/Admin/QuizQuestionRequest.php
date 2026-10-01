<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuizQuestionRequest extends FormRequest
{
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
            'question' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:60'],
            'options' => ['required', 'array', 'min:1'],
            'options.*.text' => ['required', 'string', 'max:255'],
            'options.*.icon' => ['required', 'string', 'max:60'],
            'options.*.scores' => ['nullable', 'array'],
            'options.*.scores.*.car_model' => ['required_with:options.*.scores.*.points', 'string', 'max:100'],
            'options.*.scores.*.points' => ['required_with:options.*.scores.*.car_model', 'integer', 'between:0,100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Kolom ini wajib diisi.',
            'question.required' => 'Pertanyaan wajib diisi.',
            'icon.required' => 'Ikon wajib diisi.',
            'options.required' => 'Minimal satu pilihan jawaban diperlukan.',
            'options.min' => 'Minimal satu pilihan jawaban diperlukan.',
            'options.*.text.required' => 'Teks pilihan wajib diisi.',
            'options.*.icon.required' => 'Ikon pilihan wajib diisi.',
            'options.*.scores.*.car_model.required_with' => 'Model mobil wajib dipilih bila poin diisi.',
            'options.*.scores.*.points.required_with' => 'Poin wajib diisi bila model dipilih.',
            'options.*.scores.*.points.between' => 'Poin harus antara 0 dan 100.',
            'options.*.scores.*.points.integer' => 'Poin harus berupa angka.',
        ];
    }
}
