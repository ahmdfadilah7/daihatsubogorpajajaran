<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;

trait ResolvesImageField
{
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
