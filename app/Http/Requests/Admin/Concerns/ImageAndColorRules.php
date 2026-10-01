<?php

namespace App\Http\Requests\Admin\Concerns;

/**
 * Shared validation building blocks used by the admin FormRequests
 * (design B.9): the hex-color rule, the image-string rule, and the
 * optional upload rule. Centralised so there is a single definition.
 */
trait ImageAndColorRules
{
    /**
     * Shared hex rule for color/bg/accent fields: #RRGGBB (6 hex digits).
     *
     * @return array<int, string>
     */
    protected function hexRule(): array
    {
        return ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
    }

    /**
     * Shared rule for the image STRING field (img or src): accepts an
     * absolute http(s):// URL OR a relative public path (img/ , storage/ ,
     * upload/). Returns the rule array; required-ness is layered on by the
     * caller (required_without on CREATE only).
     *
     * @return array<int, mixed>
     */
    protected function imageStringRule(): array
    {
        return [
            'nullable',
            'string',
            'max:500',
            function ($attribute, $value, $fail) {
                if ($value !== null && $value !== ''
                    && ! preg_match('#^(https?://|img/|storage/|upload/)#', $value)) {
                    $fail('Harus berupa URL (http/https) atau path gambar (img/, storage/, atau upload/).');
                }
            },
        ];
    }

    /**
     * Shared rule for the optional upload field `image`.
     *
     * @return array<int, string>
     */
    protected function imageUploadRule(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];
    }
}
