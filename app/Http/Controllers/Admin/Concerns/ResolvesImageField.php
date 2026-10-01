<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

trait ResolvesImageField
{
    /**
     * Prefix that resolveImage() / resolveNamedImage() prepend to files we
     * store ourselves on the public disk. Only values carrying this prefix are
     * eligible for deletion.
     */
    private string $uploadedImagePrefix = 'storage/uploads/';

    /**
     * Delete an image file from the public disk, but ONLY when the value is one
     * of OUR uploaded files (i.e. it starts with "storage/uploads/", the exact
     * prefix resolveImage() produces).
     *
     * Never touches absolute URLs (http/https), seeded static assets like
     * "img/halo.jpeg", or any other relative path — those are not user uploads
     * and deleting them would break the site. Missing files are a no-op and any
     * storage error is swallowed so cleanup never blocks the user action.
     */
    protected function deleteUploadedImage(?string $value): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }

        if (! str_starts_with($value, $this->uploadedImagePrefix)) {
            return;
        }

        // Map the public-relative value back to the public disk path by
        // stripping the leading "storage/" (e.g. storage/uploads/x.jpg -> uploads/x.jpg).
        $path = substr($value, strlen('storage/'));

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (Throwable) {
            // A missing file or transient storage error must never block the
            // user's action; cleanup is best-effort.
        }
    }

    /**
     * Whether a stored value points at one of our uploaded files.
     */
    protected function isUploadedImage(?string $value): bool
    {
        return is_string($value) && str_starts_with($value, $this->uploadedImagePrefix);
    }

    /**
     * Resolve the final image string with the precedence (design B.9):
     *   (newly uploaded file path) ?? (submitted non-empty string) ?? (existing stored value)
     *
     * Uploaded files are stored on the public disk under uploads/ and the
     * returned path is public-relative ("storage/uploads/...") so the rule's
     * storage/ prefix is satisfied and @json emits it unchanged.
     *
     * @return string|null Never null when the request path has been validated;
     *                     null only when nothing was supplied and no existing
     *                     value is passed (callers guard this).
     */
    protected function resolveImage(Request $request, string $stringField, ?string $existing): ?string
    {
        if ($request->hasFile('image')) {
            $stored = $request->file('image')->store('uploads', 'public');

            return 'storage/'.$stored;
        }

        $submitted = $request->input($stringField);
        if (is_string($submitted) && trim($submitted) !== '') {
            return $submitted;
        }

        return $existing;
    }
}
