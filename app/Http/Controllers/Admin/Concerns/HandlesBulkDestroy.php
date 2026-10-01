<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shared bulk-delete behaviour for the admin index pages.
 *
 * One `bulkDestroy()` method validates the submitted ids, loads the selected
 * models, applies a per-entity guard (so category-in-use rows and the current
 * user's own account are skipped rather than deleted), removes the rest inside
 * a transaction, and cleans up any owned uploaded file best-effort afterwards.
 *
 * Hosts customise behaviour through small hooks:
 *   - bulkModelClass()  : FQCN of the model (required).
 *   - bulkRouteName()   : index route to redirect back to (required).
 *   - bulkImageField()  : column holding an uploaded path, or null (default null).
 *   - bulkGuard($model) : non-null Indonesian reason to SKIP a row, or null to
 *                         allow deletion (default null).
 *
 * The trait pulls in ResolvesImageField so every host gets deleteUploadedImage()
 * without a duplicate-use conflict (PHP flattens identical trait use), matching
 * the file-cleanup rules used by the single-row destroy methods.
 */
trait HandlesBulkDestroy
{
    use ResolvesImageField;

    /**
     * FQCN of the model this controller manages.
     */
    abstract protected function bulkModelClass(): string;

    /**
     * Named route of the index page to redirect back to.
     */
    abstract protected function bulkRouteName(): string;

    /**
     * Column holding an uploaded image path (e.g. 'img' / 'src'), or null when
     * the entity owns no uploaded file.
     */
    protected function bulkImageField(): ?string
    {
        return null;
    }

    /**
     * Return a non-null Indonesian reason to SKIP this model (counted as
     * "dilewati"), or null to allow its deletion.
     */
    protected function bulkGuard(Model $model): ?string
    {
        return null;
    }

    /**
     * Delete the selected rows, skipping guarded ones and cleaning up files.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        /** @var class-string<Model> $class */
        $class = $this->bulkModelClass();
        $table = (new $class)->getTable();

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:'.$table.',id'],
        ]);

        $models = $class::whereIn('id', $validated['ids'])->get();

        $imageField = $this->bulkImageField();

        $deletable = [];
        $skipped = 0;

        foreach ($models as $model) {
            if ($this->bulkGuard($model) !== null) {
                $skipped++;

                continue;
            }

            $deletable[] = $model;
        }

        // Capture the uploaded-file values BEFORE deleting so cleanup can run
        // after the transaction commits (mirrors the single destroy ordering).
        $imagePaths = [];
        if ($imageField !== null) {
            foreach ($deletable as $model) {
                $imagePaths[] = $model->{$imageField};
            }
        }

        DB::transaction(function () use ($deletable) {
            foreach ($deletable as $model) {
                $model->delete();
            }
        });

        // File cleanup is best-effort and runs OUTSIDE the transaction so a
        // storage hiccup never rolls back a committed delete.
        if ($imageField !== null) {
            foreach ($imagePaths as $path) {
                $this->deleteUploadedImage($path);
            }
        }

        $deletedCount = count($deletable);

        return redirect()->route($this->bulkRouteName())
            ->with($this->bulkFlash($deletedCount, $skipped));
    }

    /**
     * Build the Indonesian flash bag: sukses for the deleted count, gagal for
     * the skipped note. Returns an array suitable for ->with(...).
     *
     * @return array<string, string>
     */
    protected function bulkFlash(int $deletedCount, int $skippedCount): array
    {
        $flash = [];

        if ($deletedCount > 0) {
            $flash['sukses'] = $deletedCount.' data berhasil dihapus.';
        }

        if ($skippedCount > 0) {
            $flash['gagal'] = $this->bulkSkippedMessage($skippedCount);
        }

        return $flash;
    }

    /**
     * Indonesian note describing why some rows were skipped. Overridable so each
     * entity can phrase its own reason.
     */
    protected function bulkSkippedMessage(int $skippedCount): string
    {
        return $skippedCount.' data dilewati.';
    }
}
