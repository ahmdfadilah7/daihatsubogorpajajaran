<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Image fields paired with their dedicated file-input name.
     *
     * @var array<string, string>
     */
    private array $imageFields = [
        'logo' => 'logo_file',
        'favicon' => 'favicon_file',
        'og_image' => 'og_image_file',
    ];

    public function edit()
    {
        $settings = SiteSetting::allAsArray();

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(SettingRequest $request)
    {
        $data = $request->validated();
        $existing = SiteSetting::allAsArray();

        // Resolve each image field with the precedence:
        //   uploaded file ?? submitted string ?? existing stored value.
        foreach ($this->imageFields as $stringField => $fileField) {
            $data[$stringField] = $this->resolveNamedImage(
                $request,
                $fileField,
                $stringField,
                $existing[$stringField] ?? null
            );
            unset($data[$fileField]);
        }

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        SiteSetting::flushCache();

        return redirect()->route('admin.settings.edit')
            ->with('sukses', 'Pengaturan website berhasil disimpan.');
    }

    /**
     * Resolve an image value from a named file input, submitted string, or
     * the existing stored value — mirroring ResolvesImageField but keyed off
     * a per-field file-input name so multiple image fields can share one form.
     */
    private function resolveNamedImage(Request $request, string $fileField, string $stringField, ?string $existing): ?string
    {
        if ($request->hasFile($fileField)) {
            $stored = $request->file($fileField)->store('uploads', 'public');

            return 'storage/'.$stored;
        }

        $submitted = $request->input($stringField);
        if (is_string($submitted) && trim($submitted) !== '') {
            return $submitted;
        }

        return $existing;
    }
}
